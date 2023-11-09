<?php

set_include_path(get_include_path() . PATH_SEPARATOR . 'php/class');
spl_autoload_extensions('.php');
spl_autoload_register();

session_start();

$user = new User();
$user->logUser();

$liste = new Liste();
$liste->saveTask();

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
<div class="jumbotron text-center">
    <h2>Hello <?php $user->getName(); ?>!</h2>
    <form action="<?php $_SERVER['PHP_SELF']; ?>" method="post">
        <div class="form-group has-search">
            <span class="fa fa-search form-control-feedback"></span>
            <input type="text" name="task" class="form-control" placeholder="Write your tasks here ...">
        </div>
        <input type="submit" value="Save" class="btn btn-primary">
    </form>
</div>

<div class="container" id="tasks">
    <?php $liste->printListData(); ?>
</div>
</body>
</html>

