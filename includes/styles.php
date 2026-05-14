<?php
$asset = __DIR__ . '/../assets/css/app.css';
$version = file_exists($asset) ? filemtime($asset) : time();
?>
<link rel="stylesheet" href="assets/css/app.css?v=<?= $version ?>">
