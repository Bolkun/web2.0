<?php

//require_once 'lib/circle_class.php';
//require_once 'lib/point_class.php';

/*
function __autoload($classname) {
    require_once "lib/{$classname}_class.php";  //$user = new User(); echo $user->name; // works!
}
*/

set_include_path(get_include_path() . PATH_SEPARATOR . 'core' . PATH_SEPARATOR . 'lib');
spl_autoload_extensions('_class.php');
spl_autoload_register();

$user = new User();
echo $user->name . '<br />';

$c = new Circle();
echo $c->r;
