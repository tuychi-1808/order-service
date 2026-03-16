<?php

declare(strict_types=1);

error_reporting(E_ALL & ~E_DEPRECATED & ~E_WARNING);
ini_set('display_errors', '0');

require_once __DIR__ . '/../vendor/autoload.php';

use App\Presentation\Http\Kernel;

$kernel = new Kernel();
$kernel->handle();