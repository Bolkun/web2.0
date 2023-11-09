<?php

use de\serhiy\User; // Path to the file
use de\google\User as GoogleUser;

require_once 'class/a.php';
require_once 'class/b.php';

// 1
$user = new User();
$user->name = 'Frank';
echo $user->name . '<br />';

// 2
$g_user = new GoogleUser();
$g_user->email = 'abc@mail.de';
echo $g_user->email . '<br />';

// 3
$user = new de\google\User();
$user->email = '12345@mail.de';
echo $user->email . '<br />';
