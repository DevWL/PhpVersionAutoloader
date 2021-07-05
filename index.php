<?php

include_once 'PhpVersionAutoloader.php';

use CustomClass;

var_dump($loader);

$test = new CustomClass();

var_dump($loader);