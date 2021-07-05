<?php

include_once 'PhpVersionAutoloader.php';

use DevWL\Demo\CustomClass;

var_dump($loader);

$test = new CustomClass();

var_dump($loader);