<?php

namespace App\Commands;

use App\Models\SData;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

/**
 * Spark command that soft-deletes pocasi_data records older than
 * a given number of years (default 11), so they no longer show up
 * anywhere in the app but stay in the database for archival/audit.
 *
 * Usage:
 *   php spark pocasi:purge-old
 *   php spark pocasi:purge-old --years 5
 *
 * Meant to be triggered automatically once a month by a cron job
 * (or CodeIgniter's Task Scheduler) on the production server.
 */
class PurgeOldData extends BaseCommand
{
    protected $group       = 'Pocasi';
    protected $name        = 'pocasi:purge-old';
    protected $description = 'Soft-deletes pocasi_data records older than N years (default 11).';
    protected $usage       = 'pocasi:purge-old [--years <int>]';
    protected $options     = [
        '--years' => 'Age threshold in years. Records with date older than this are soft-deleted. Default: 11.',
    ];

    public function run(array $params)
    {
        $years  = (int) (CLI::getOption('years') ?? $params['years'] ?? 11);
        $cutoff = date('Y-m-d H:i:s', strtotime("-{$years} years"));

        $model = new SData();

        $count = $model->where('date <', $cutoff)->countAllResults();

        if ($count === 0) {
            CLI::write("Žádné záznamy starší než {$years} let (před {$cutoff}) nebyly nalezeny.", 'yellow');

            return;
        }

        $model->where('date <', $cutoff)->delete();

        CLI::write("Soft-delete proveden: {$count} záznam(ů) starších než {$years} let (před {$cutoff}).", 'green');
    }
}
