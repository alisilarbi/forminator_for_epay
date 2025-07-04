<?php
require_once 'env.php';
loadEnv();

echo getenv('X_APP_KEY');
