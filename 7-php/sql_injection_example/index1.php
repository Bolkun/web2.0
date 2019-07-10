<?php

/*
 * by AlexanderPHP
 * http://habrahabr.ru/post/148151/
 */
require_once ('cfg.php'); 

if (!isset($_GET['id']))
    die('Введите ID');

$id = $_GET['id'];
$query = "SELECT * FROM news WHERE id=$id";
$result = mysqli_query($link,"SETNAMES utf-8");
$result = mysqli_query($link, $query);
$row = mysqli_fetch_array($result);
if(!$row)
    die('Статья не найдена');


echo "ID: ".$row['id']."<br/>";
echo "Автор: ".$row['user']."<br/>";
echo $row['text'];