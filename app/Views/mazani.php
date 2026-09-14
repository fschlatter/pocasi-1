<?php $this->extend("layout/layout") ?>
<?php $this->section("content"); ?>

<div class="row justify-content-center">
    <div class="col-12 col-md-8 col-lg-6">
        <h1>Smazání dat stanice</h1>
        <p class="text-muted">
            Vybraná data se nesmažou fyzicky, pouze se označí jako smazaná (soft delete)
            a přestanou se dál zobrazovat v přehledech.
        </p>

        <?php if (session()->getFlashdata('message')): ?>
            <div class="alert alert-success"><?= esc(session()->getFlashdata('message')) ?></div>
        <?php endif; ?>

        <?php if (session()->getFlashdata('errors')): ?>
            <div class="alert alert-danger">
                <ul class="mb-0">
                    <?php foreach (session()->getFlashdata('errors') as $error): ?>
                        <li><?= esc($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form action="<?= base_url('index.php/mazani') ?>" method="post">
            <?= csrf_field() ?>

            <div class="mb-3">
                <label for="station_id" class="form-label">Stanice</label>
                <select name="station_id" id="station_id" class="form-select" required>
                    <option value="">-- vyberte stanici --</option>
                    <?php foreach ($stations as $station): ?>
                        <option value="<?= esc($station['S_ID']) ?>"
                            <?= old('station_id') == $station['S_ID'] ? 'selected' : '' ?>>
                            <?= esc($station['place']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="mb-3">
                <label for="month" class="form-label">Měsíc</label>
                <select name="month" id="month" class="form-select" required>
                    <option value="">-- vyberte měsíc --</option>
                    <?php foreach ($months as $num => $name): ?>
                        <option value="<?= $num ?>" <?= old('month') == $num ? 'selected' : '' ?>>
                            <?= esc($name) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="mb-3">
                <label for="year" class="form-label">Rok</label>
                <input type="number" name="year" id="year" class="form-control"
                       min="1900" max="<?= date('Y') ?>"
                       value="<?= old('year') ?>" required>
            </div>

            <button type="submit" class="btn btn-danger"
                    onclick="return confirm('Opravdu smazat vybraná data (soft delete)?');">
                Smazat (soft delete)
            </button>
        </form>
    </div>
</div>

<?php $this->endsection() ?>
