<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Adds the `deleted_at` column to `pocasi_data` so the SData model
 * can use CodeIgniter's soft-delete feature (useSoftDeletes = true).
 *
 * Rows are never physically removed by the app anymore; delete()
 * just stamps `deleted_at` with the current timestamp, and every
 * find()/findAll()/paginate() call automatically hides soft-deleted
 * rows unless you explicitly call withDeleted() or onlyDeleted().
 */
class AddDeletedAtToPocasiData extends Migration
{
    private string $table = 'pocasi_data';

    public function up()
    {
        if (! $this->db->fieldExists('deleted_at', $this->table)) {
            $this->forge->addColumn($this->table, [
                'deleted_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
            ]);
        }

        // Index speeds up both the "hide deleted rows" WHERE clause used on
        // every query and the monthly purge job's WHERE date < ... scan.
        $indexExists = $this->db->query(
            "SHOW INDEX FROM `{$this->table}` WHERE Key_name = 'idx_pocasi_data_deleted_at'"
        )->getNumRows() > 0;

        if (! $indexExists) {
            $this->db->query(
                "ALTER TABLE `{$this->table}` ADD INDEX idx_pocasi_data_deleted_at (deleted_at)"
            );
        }
    }

    public function down()
    {
        if ($this->db->fieldExists('deleted_at', $this->table)) {
            $this->forge->dropColumn($this->table, 'deleted_at');
        }
    }
}
