<?php $this->extend("layout/layout")?>
<?php $this->section("content");
use App\Models\SData;
use CodeIgniter\View\Table; ?>
<div class="row">
    <div class="col-12">
        <h1>Stanice</h1>
        <?php
        $table = new Table();
        $pole = array('Stanice', 'Datum', 'Teplota prumer', 'Vlhkost', 'Tlak', 'Vítr', 'Srážky');
        $table->setHeading($pole);
        foreach ($data as $row) {
            $table->addRow($row['id'], $row['date'], $row['mid_2m'], $row['humidity'], $row['mid_aid_pressure'], $row['mid_wind'], $row['precipitation']);
        }
        $template = [
            'table_open' => '<table class="table table-bordered">',
            'thead_open' => '<thead>',
            'thead_close' => '</thead>',
            'heading_row_start' => '<tr>',
            'heading_row_end' => '</tr>',
            'heading_cell_start' => '<th class="th">',
            'heading_cell_end' => '</th>',
            'tfoot_open' => '<tfoot>',
            'tfoot_close' => '</tfoot>',
            'footing_row_start' => '<tr>',
            'footing_row_end' => '</tr>',
            'footing_cell_start' => '<td class="td">',
            'footing_cell_end' => '</td>',
            'tbody_open' => '<tbody>',
            'tbody_close' => '</tbody>',
            'row_start' => '<tr>',
            'row_end' => '</tr>',
            'cell_start' => '<td class="td">',
            'cell_end' => '</td>',
            'row_alt_start' => '<tr>',
            'row_alt_end' => '</tr>',
            'cell_alt_start' => '<td class="td">',
            'cell_alt_end' => '</td>',
            'table_close' => '</table>',
        ];
        $table->setTemplate($template);
        echo $table->generate();
        ?>
    </div>

<?php $this->endsection()?>