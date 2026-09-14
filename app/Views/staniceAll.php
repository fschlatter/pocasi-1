<?php $this->extend("layout/layout") ?>
<?php $this->section("content"); ?>

<div class="row justify-content-center">
<?php foreach ($stations as $row): ?>
    <div class="card col-12 col-md-5 col-lg-3 m-1">
        <div class="card-body">
            <h5 class="card-title">
                <?= anchor('stanice/' . $row['S_ID'], esc($row['place'])) ?>
            </h5>
            <p class="card-text">Elevation: <?= esc($row['height']) ?> m n. m.</p>
            <p class="card-text">Latitude: <?= esc($row['geo_latitude']) ?>°</p>
            <p class="card-text">Longitude: <?= esc($row['geo_longtitude']) ?>°</p>
            <p class="card-text">
                <?= img([
                    'src'   => base_url('obrazky/vlajky/' . $row['flag']),
                    'class' => 'img-fluid',
                    'alt'   => esc($row['place']),
                ]) ?>
            </p>
        </div>
    </div>
<?php endforeach; ?>
</div>

<?php $this->endsection() ?>