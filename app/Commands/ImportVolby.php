<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Config\Services;

/**
 * Stáhne okrskové výsledky voleb do PS 2025 z otevřených dat ČSÚ (volby.cz),
 * vybere okrsky jedné obce (výchozí 592005 = Uherské Hradiště) a uloží je do
 * app/Data/volby_uh_2025.json, ze kterého čte stránka /volby.
 *
 * Použití (lokálně, např. v Laragonu):
 *   php spark volby:import
 *   php spark volby:import --obec 592005 --no-verify
 *
 * Dávky jsou celorepublikové (vysledky_okrsky_00001.xml, 00002, ...), takže se
 * projdou všechny a filtruje se podle CIS_OBEC. Stejný okrsek může přijít
 * vícekrát – platí záznam s nejvyšším PORADI_ZPRAC.
 */
class ImportVolby extends BaseCommand
{
    protected $group       = 'Volby';
    protected $name        = 'volby:import';
    protected $description = 'Stáhne okrskové výsledky PS 2025 z volby.cz a uloží je do app/Data/volby_uh_2025.json.';
    protected $usage       = 'volby:import [--obec <kód>] [--max-batch <n>] [--no-verify]';
    protected $options     = [
        '--obec'      => 'Kód obce (CIS_OBEC). Výchozí 592005 (Uherské Hradiště).',
        '--max-batch' => 'Pojistka: nejvyšší číslo dávky, na kterém se skončí. Výchozí 2000.',
        '--no-verify' => 'Nekontrolovat SSL certifikát (pomůže na Windows bez CA bundlu).',
    ];

    private const NS       = 'http://www.volby.cz/ps/';
    private const BASE_URL = 'https://www.volby.cz/appdata/ps2025/odata/okrsky/vysledky_okrsky_%05d.xml';

    /** Číslo kandidátky (KSTRANA) => zkratka použitá na stránce. */
    private const PARTIES = [
        2  => 'MZH',
        3  => 'JaSaN',
        4  => 'VÝZVA',
        5  => 'SMSka',
        6  => 'SPD',
        7  => 'ČSSD',
        8  => 'PŘÍSAHA',
        9  => 'Levice',
        10 => 'ČR1',
        11 => 'SPOLU',
        15 => 'Generace',
        16 => 'Piráti',
        17 => 'Koruna Česká',
        18 => 'Volt',
        20 => 'AUTO',
        22 => 'ANO',
        23 => 'STAN',
        25 => 'Stačilo!',
        26 => 'Voluntia',
    ];

    public function run(array $params)
    {
        $obec     = (string) (CLI::getOption('obec') ?? '592005');
        $maxBatch = (int) (CLI::getOption('max-batch') ?? 2000);
        $verify   = ! array_key_exists('no-verify', CLI::getOptions());

        $client = Services::curlrequest([
            'timeout'     => 60,
            'http_errors' => false,
            'verify'      => $verify,
        ]);

        $best      = []; // CIS_OKRSEK => nejnovější záznam
        $batches   = 0;

        CLI::write("Stahuji dávky okrsků, hledám obec {$obec}...", 'yellow');

        for ($n = 1; $n <= $maxBatch; $n++) {
            $url  = sprintf(self::BASE_URL, $n);
            $body = null;

            for ($try = 1; $try <= 3; $try++) {
                try {
                    $response = $client->get($url);
                } catch (\Throwable $e) {
                    CLI::error("Dávka {$n}: {$e->getMessage()} (pokus {$try}/3)");
                    continue;
                }

                $status = $response->getStatusCode();

                if ($status === 404) {
                    break 2; // další dávka už neexistuje -> konec
                }
                if ($status === 200) {
                    $body = $response->getBody();
                    break;
                }

                CLI::error("Dávka {$n}: HTTP {$status} (pokus {$try}/3)");
            }

            if ($body === null) {
                CLI::error("Dávku {$n} se nepodařilo stáhnout, končím. Nic se neuložilo.");

                return EXIT_ERROR;
            }

            $xml = @simplexml_load_string($body);
            if ($xml === false) {
                CLI::error("Dávka {$n}: neplatné XML, končím. Nic se neuložilo.");

                return EXIT_ERROR;
            }

            $batches++;
            $xml->registerXPathNamespace('ps', self::NS);

            foreach ($xml->xpath('//ps:OKRSEK[@CIS_OBEC="' . $obec . '"]') as $okrsek) {
                $id    = (int) $okrsek['CIS_OKRSEK'];
                $order = (int) $okrsek['PORADI_ZPRAC'];

                if (isset($best[$id]) && $best[$id]['order'] > $order) {
                    continue;
                }

                $children = $okrsek->children(self::NS);
                $ucast    = $children->UCAST_OKRSEK;

                $parties = [];
                foreach ($children->HLASY_OKRSEK as $h) {
                    $code  = (int) $h['KSTRANA'];
                    $votes = (int) $h['HLASY'];
                    if ($votes <= 0) {
                        continue;
                    }
                    $name           = self::PARTIES[$code] ?? ('Strana č. ' . $code);
                    $parties[$name] = ($parties[$name] ?? 0) + $votes;
                }
                arsort($parties);

                $best[$id] = [
                    'order'   => $order,
                    'voters'  => (int) $ucast['ZAPSANI_VOLICI'],
                    'issued'  => (int) $ucast['VYDANE_OBALKY'],
                    'parties' => $parties,
                ];
            }

            if ($n % 10 === 0) {
                CLI::write("  ... dávka {$n}, nalezeno okrsků: " . count($best));
            }
        }

        if ($batches === 0) {
            CLI::error('Nepodařilo se stáhnout žádnou dávku (první dávka vrátila 404).');

            return EXIT_ERROR;
        }

        if (! $best) {
            CLI::error("Ve {$batches} dávkách není žádný okrsek obce {$obec}. Nic se neuložilo.");

            return EXIT_ERROR;
        }

        ksort($best);

        // --- sestavení JSON ---
        $path = APPPATH . 'Data/volby_uh_2025.json';
        $json = is_file($path) ? json_decode(file_get_contents($path), true) : [];
        $json = is_array($json) ? $json : [];

        $townParties = [];
        $sumVoters   = 0;
        $sumIssued   = 0;
        $okrsky      = [];

        foreach ($best as $id => $o) {
            foreach ($o['parties'] as $name => $votes) {
                $townParties[$name] = ($townParties[$name] ?? 0) + $votes;
            }
            $sumVoters += $o['voters'];
            $sumIssued += $o['issued'];

            $okrsky[] = [
                'id'      => $id,
                'turnout' => $o['voters'] > 0 ? round($o['issued'] / $o['voters'] * 100, 2) : null,
                'parties' => $o['parties'],
            ];
        }
        arsort($townParties);

        $json['election']     = $json['election'] ?? 'Volby do Poslanecké sněmovny Parlamentu ČR 3.–4. 10. 2025';
        $json['municipality'] = $json['municipality'] ?? 'Uherské Hradiště';
        $json['source']       = 'ČSÚ – otevřená data (volby.cz), okrskové výsledky';
        $json['okrsky_total'] = max(array_keys($best));
        $json['town']         = [
            'turnout' => $sumVoters > 0 ? round($sumIssued / $sumVoters * 100, 2) : null,
            'parties' => $townParties,
        ];
        $json['okrsky'] = $okrsky;

        file_put_contents(
            $path,
            json_encode($json, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
        );

        CLI::write("Hotovo: prošlo se {$batches} dávek, uloženo " . count($okrsky) . " okrsků do {$path}", 'green');
        CLI::write('Kontrola: ANO ve městě = ' . ($townParties['ANO'] ?? 0)
            . ' hlasů (očekává se 4048), účast = ' . $json['town']['turnout'] . ' % (očekává se 68,21).');

        return EXIT_SUCCESS;
    }
}