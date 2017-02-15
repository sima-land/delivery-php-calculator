<?php

defined('TEST_DIR') or define('TEST_DIR', __DIR__ . DIRECTORY_SEPARATOR);
require_once TEST_DIR . '../vendor/autoload.php';

$classLoader = new \Composer\Autoload\ClassLoader();
$classLoader->addPsr4("SimaLand\\DeliveryCalculator\\tests\\", __DIR__, true);
$classLoader->register();
