<?php $this->extend("layout/layout")  ?>
<?php $this->section("content") ?>
    <?php use App\Models\Stanice; ?>
    <div class="row justify-content-center">
    <?php foreach ($stanice as $row): ?> 
        <div class="card col-12 col-md-6 col-lg-4 m-1">
            <div class="card-body">
                <h5 class="card-title"><?php echo anchor("stanice/".$row['S_ID'],$row['place']); ?></h5>
                <p class="card-text">Elevation: <?php echo $row['height'];?>m n. m.</p>
                <p class="card-text">Latitude: <?php echo $row['geo_latitude'];?>°</p>
                <p class="card-text">Longitude: <?php echo $row['geo_longtitude'];?>°</p>
            </div>
        </div>  
    <?php endforeach; ?>
    </div>
<?php $this->endSection() ?>