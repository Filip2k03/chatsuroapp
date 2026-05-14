<?php
$asset = __DIR__ . '/../assets/js/app.js';
$version = file_exists($asset) ? filemtime($asset) : time();
$clientConfig = [
    "isLoggedIn" => $isLoggedIn,
    "userRole" => $userRole,
    "apiBase" => "index.php",
];
?>
<script>
window.SLOPARA_CONFIG = <?= json_encode($clientConfig, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;
</script>
<script src="assets/js/app.js?v=<?= $version ?>" defer></script>
