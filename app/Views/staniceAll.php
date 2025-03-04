<?php $this->extend("layout/layout")?>
<?php $this->section("content"); ?>
<div class="row justify-content-center">
<?php
foreach ($damik as $row): ?> 
    <div class="card col-12 col-md-5 col-lg-3 m-1">
        <div class="card-body">
            <h5 class="card-title"><?php echo anchor("stanice/".$row['S_ID'],$row['place']); ?> <span></span></h5>
            <p class="card-text">Elevation: <?php echo $row['height'];?>m n. m.</p>
            <p class="card-text">Latitude: <?php echo $row['geo_latitude'];?>°</p>
            <p class="card-text">Longitude: <?php echo $row['geo_longtitude'];?>°</p>
            <?php $img = array(
                    "src" => base_url("obrazky/vlajky/".$row['flag']),
                    "class" => "img-fluid"
                ); ?>
                <p class="card-text"><span><?= img($img) ?></span></p>
        </div>
    </div>  
<?php endforeach;?>
</div>
<?php $this->endsection() ?>