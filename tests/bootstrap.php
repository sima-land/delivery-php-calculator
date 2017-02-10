<?php

defined('TEST_DIR') or define('TEST_DIR', __DIR__ . DIRECTORY_SEPARATOR);
require_once TEST_DIR . '../vendor/autoload.php';
//require_once TEST_DIR . '../src/Calculator.php';
//require_once TEST_DIR . '../src/ItemInterface.php';
//require_once TEST_DIR . '../src/SettlementInterface.php';
//require_once 'Point.php';
//require_once 'Item.php';
//require_once 'PackingVolumeFactorSource.php';

$classLoader = new \Composer\Autoload\ClassLoader();
$classLoader->addPsr4("SimaLand\\DeliveryCalculator\\tests\\", __DIR__, true);
$classLoader->register();
