<?php

require 'php/class/User.php';
require 'php/class/Database.php';

$user = new User();
$user->regUser();

?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>To-Do-Liste</title>
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.8.1/css/all.css" integrity="sha384-50oBUHEmvpQ+1lW4y57PTFmhCaXp0ML5d60M1M7uH2+nqUivzIebhndOJK28anvf" crossorigin="anonymous">
    <link rel="stylesheet" href="css/lib/bootstrap.min.css">
    <script src="js/lib/jquery-3.2.1.slim.min.js"></script>
    <script src="js/lib/bootstrap.min.js"></script>
    <script src="js/lib/popper.min.js"></script>
    <script src="js/lib/jquery-3.4.1.min.js"></script>
    <link rel="stylesheet" href="css/main.css">
    <script src="js/main.js"></script>
</head>
<body>
    <h1 class="text-center">Registration</h1>
    <form action="<?php $_SERVER['PHP_SELF']; ?>" method="post">
        <div class="form-group">
            <label for="exampleInputEmail1">Login</label>
            <input type="text" name="name" class="form-control" placeholder="Enter Login" minlength="3" maxlength="15" required>
        </div>
        <div class="form-group">
            <label for="exampleInputPassword1">Password</label>
            <input type="password" name="password" class="form-control" placeholder="Password" minlength="3" maxlength="32" required>
        </div>
        <div class="form-group">
            <label for="exampleInputPassword2">Reenter Password</label>
            <input type="password" name="password2" class="form-control" placeholder="Reenter Password" minlength="3" maxlength="32" required>
        </div>
        <div class="form-group text-center">
            <button type="submit" class="btn btn-primary">Submit</button>
        </div>
    </form>
</body>
</html>
