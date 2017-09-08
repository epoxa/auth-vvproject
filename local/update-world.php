<?php

use YY\Core\Cache;
use YY\Core\Data;
use YY\Core\Importer;
use YY\System\YY;

//require_once __DIR__ . "/../config/env.php";
require_once __DIR__ . "/../config/config.php";
require_once __DIR__ . "/../vendor/autoload.php";

Data::InitializeStorage();
YY::LoadWorld();

$configTimestamp = Importer::reloadWorld();
Cache::Flush();
