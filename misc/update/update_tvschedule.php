<?php
// Run this once per day.
require_once realpath(dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'bootstrap.php');

use newzflash\TvRage;

(new TvRage(['Echo' => true]))->updateSchedule();
