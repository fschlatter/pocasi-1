<?php $this->extend("layout/layout")?>
<?php $this->section("content"); ?>



<div class="row">
    <div class="col-12">
        <h1>Stanice</h1>
        <?php
        use CodeIgniter\View\Table;
        $table = new Table();
        $pole = array('Id', 'Datum', 'Teplota prumer', 'Vlhkost', 'Tlak', 'Vítr', 'Srážky');
        $table->setHeading($pole);
        foreach ($data as $row) {
            $date = date("j.n.Y",strtotime($row['date']));
            $table->addRow($row['id'], $date, $row['mid_2m'], $row['humidity'], $row['mid_air_pressure'], $row['mid_wind'], $row['precipitation']);
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
        echo $table->generate();?>
        <div class="d-flex align-items-center justify-content-center"><?= $pager[0]->links();?></div>
    </div>

<?php $this->endsection()?>