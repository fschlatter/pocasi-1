<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <?= $this->include("layout/css");?> 
    <?= $this->include("layout/js");?>
</head>
<body>
    <div class="container-fluid align-content-center">
        <?= $this->renderSection("content");?>
    </div>
</body>
</html>
