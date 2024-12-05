<?= $this->extend('layout/sablona'); ?>
<?= $this->section("content"); ?>
<div class="container">
    <?php

use App\Models\Station;

    $table = new \CodeIgniter\View\Table();
    $pole = array('Id', 'Zem', "Zkratka");
    $table->setHeading($pole);
    foreach ($bundesland as $row) {
        $table->addRow($row->id, anchor("stanice",$row->name), $row->short_name);
    }



    $template = array(
        'table_open' => '<table class="table table-bordered">',
        'thead_open' => '<thead>',
        'thead_close' => '</thead>',
        'heading_row_start' => '<tr>',
        'heading_row_end' => ' </tr>',
        'heading_cell_start' => '<th>',
        'heading_cell_end' => '</th>',
        'tbody_open' => '<tbody>',
        'tbody_close' => '</tbody>',
        'row_start' => '<tr>',
        'row_end'  => '</tr>',
        'cell_start' => '<td>',
        'cell_end' => '</td>',
        'row_alt_start' => '<tr>',
        'row_alt_end' => '</tr>',
        'cell_alt_start' => '<td>',
        'cell_alt_end' => '</td>',
        'table_close' => '</table>'
    );
    $table->setTemplate($template);
    echo $table->generate();
    ?>
</div>
<?= $this->endSection(); ?>