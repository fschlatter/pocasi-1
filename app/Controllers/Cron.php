<?php

namespace App\Controllers;

use App\Models\SData;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Endpoint určený pro volání přes HTTP (např. ISPConfig "URL cron", který
 * cíl stahuje přes wget), protože tamní cron umí spouštět jen URL, ne
 * shellové příkazy jako `php spark ...`.
 *
 * Dělá přesně to samé jako `php spark pocasi:purge-old`: soft-delete
 * (nastaví deleted_at) na záznamech v pocasi_data starších než N let.
 *
 * Chráněno tajným tokenem v .env (cron.purgeToken), protože jinak by si
 * tuhle URL mohl zavolat kdokoliv na internetu a smazat data mimo plán.
 */
class Cron extends BaseController
{
    public function purgeOld(): ResponseInterface
    {
        $expectedToken = env('cron.purgeToken');
        $givenToken    = $this->request->getGet('token');

        if (empty($expectedToken) || ! hash_equals((string) $expectedToken, (string) $givenToken)) {
            return $this->response
                ->setStatusCode(403)
                ->setContentType('text/plain')
                ->setBody('Forbidden: missing or invalid token');
        }

        $years  = (int) ($this->request->getGet('years') ?? 11);
        $years  = $years > 0 ? $years : 11;
        $cutoff = date('Y-m-d H:i:s', strtotime("-{$years} years"));

        $model = new SData();

        $count = $model->where('date <', $cutoff)->countAllResults();

        if ($count > 0) {
            $model->where('date <', $cutoff)->delete();
        }

        $message = "OK: soft-deleted {$count} record(s) older than {$years} years (before {$cutoff}).";
        log_message('info', '[cron:purge-old] ' . $message);

        return $this->response
            ->setStatusCode(200)
            ->setContentType('text/plain')
            ->setBody($message);
    }
}
