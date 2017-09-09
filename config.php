<?php
$config['admob'] = 0;
$config['random'] = base64_encode(random_bytes(10));
header("Content-Type", "application/json");
echo json_encode($config);
