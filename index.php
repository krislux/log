<?php

require 'vendor/autoload.php';

use KrisLux\Log\Logger;

$log = new Logger([], new KrisLux\Log\Drivers\SingleFileDriver() );

require 'test/testclass.php';

$test = new Testsuite\Testclass;