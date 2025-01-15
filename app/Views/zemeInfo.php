<?php $this->extend("layout/layout"); ?>
<?php $this->section("content"); ?>
<?php 
    $img1 = array(
        "src" => base_url("obrazky/mapy/".$bundesland['map']),
        "alt" => "mapa ".$bundesland['name'],
        "class" => "img-fluid perfect-size mx-auto",
        "style" => "height: 80vh"
    );
    $img2 = array(
        "src" => base_url("obrazky/vlajky/".$bundesland['flag']),
        "alt" => "mapa ".$bundesland['name'],
        "class" => "mx-auto",
        "style" => "width: 100px"
    );
    
    
?>
<h1 class="text-center">Informace o zemi <span><?= $bundesland['name']?> </span> <span class=""><?= img($img2) ?></span></h1>
<div class="container">
<?= img($img1) ?>

</div>
<?php $this->endsection() ?>