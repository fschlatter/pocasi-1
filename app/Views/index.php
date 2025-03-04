<?php $this->extend("layout/layout"); ?>
<?php $this->section("content") ?>
<h1>Seznam zemí</h1>
<div class="row">
    
    <div class="col-6">
        <?php use App\Models\Zeme;
        
        $table = new \CodeIgniter\View\Table();
        $pole = array('Id', 'Jméno', "Zkratka", "Info");  
        $table->setHeading($pole);
        foreach ($bundesland as $row) {

            $table->addRow($row['id'], anchor("zeme/".$row['id'],$row['name']), $row['short_name'],anchor("zemeInfo/".$row['id'],"Více informací o ".$row['name']));
        }
        
        $template = [
            'table_open' => '<table class="table table-bordered">',
            
            'thead_open'  => '<thead>',
            'thead_close' => '</thead>',
            
            'heading_row_start'  => '<tr>',
            'heading_row_end'    => '</tr>',
            'heading_cell_start' => '<th class="th">',
            'heading_cell_end'   => '</th>',
        
            'tfoot_open'  => '<tfoot>',
            'tfoot_close' => '</tfoot>',
        
            'footing_row_start'  => '<tr>',
            'footing_row_end'    => '</tr>',
            'footing_cell_start' => '<td class="td">',
            'footing_cell_end'   => '</td>',
            
            'tbody_open'  => '<tbody>',
            'tbody_close' => '</tbody>',
            
            'row_start'  => '<tr>',
            'row_end'    => '</tr>',
            'cell_start' => '<td class="td">',
            'cell_end'   => '</td>',
        
            'row_alt_start'  => '<tr>',
            'row_alt_end'    => '</tr>',
            'cell_alt_start' => '<td class="td">',
            'cell_alt_end'   => '</td>',
            
            'table_close' => '</table>',
        ];
        $table->setTemplate($template);
        echo $table->generate();
        ?>
    </div>
</div>
<?php $this->endsection() ?>