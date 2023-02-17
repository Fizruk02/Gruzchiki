<?php
// Определяем корень
define('PATH', dirname(__FILE__));

require 'system/Bt.php';
require 'system/lib/Dev.php';

use system\core\Router;

$config = require __DIR__ . '/system/config/config.php';
$router = (new Router($config))->run();
