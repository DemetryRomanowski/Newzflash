<?php
require_once realpath(dirname(dirname(dirname(dirname(dirname(__DIR__))))) . DIRECTORY_SEPARATOR . 'bootstrap.php');

use newzflash\PreDb;

(new PreDb(['Echo' => true]))->checkPre((isset($argv[1]) && is_numeric($argv[1]) ? $argv[1] : false));
