<?php

/*
 * by AlexanderPHP
 * http://habrahabr.ru/post/148151/
 */
require_once 'cfg.php'; 

if (!isset($_GET['user']))
    die('Введите имя пользователя');

$user = $_GET['user'];
$query = "SELECT * FROM news WHERE user='$user'";
$result = mysqli_query($link, "SETNAMES utf-8");
$result = mysqli_query($link, $query);
$row = mysqli_fetch_array($result);

echo "ID: ".$row['id']."<br/>";
echo "Автор: ".$row['user']."<br/>";
echo $row['text'];