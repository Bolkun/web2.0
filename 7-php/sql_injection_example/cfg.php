<?php

/*
 * by AlexanderPHP
 * http://habrahabr.ru/post/148151/
 */

$dbhost = "localhost";
$dbusername = "root";
$dbpass = "";
$dbname = "sqlinj";

mysqli_connect($dbhost, $dbusername, $dbpass);
$link = mysqli_connect($dbhost, $dbusername, $dbpass);
mysqli_select_db($link, $dbname);

?>