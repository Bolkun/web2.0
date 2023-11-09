<?php

include_once "class/Article.php";
include_once "class/article/Music.php";
include_once "class/article/Film.php";
include_once "class/article/Serie.php";
include_once "class/article/Furniture.php";

//Dummy Articles
$music_cd1 = new Music(1,'Musik-CD', 'Thriller von Michael Jackson', 9.99, 'Rod Temperton');
$film1 = new Film(2, 'Film', 'Dracula', 4.99, 'Bram Stoker');
$serie1 = new Serie(3, 'Serie', 'Game of Thrones (Staffel 1)', 12.98, 'David Benioff');
$furniture1 = new Furniture(4, 'Furniture', 'Beistelltisch', 299.00, 98 );
$furniture2 = new Furniture(5, 'Furniture', 'Schrank', 199.99, 120 );

?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Test Shop</title>
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
<form action="save.php" method="post">
    <h1 class="text-center">User</h1>
    <div class="form-group">
        <label>Vorname</label>
        <input type="text" name="forname" class="form-control" minlength="3" maxlength="15">
    </div>
    <div class="form-group">
        <label>Nachname</label>
        <input type="text" name="surname" class="form-control" minlength="3" maxlength="15">
    </div>
    <div class="form-group">
        <label>Strasse</label>
        <input type="text" name="street" class="form-control">
    </div>
    <div class="form-group">
        <label>PLZ</label>
        <input type="number" name="postcode" class="form-control" placeholder="01234" minlength="5" maxlength="5">
    </div>
    <div class="form-group">
        <label>Ort</label>
        <input type="text" name="city" class="form-control">
    </div>
    <div class="form-group">
        <label>E-Mail</label>
        <input type="email" name="email" class="form-control">
    </div>
    <div class="form-group">
        <label>Geburtsdatum</label>
        <input type="date" name="birthsday" class="form-control">
    </div>
    <br><hr>
    <h1 class="text-center">Shop</h1>
    <div class="form-group">
        <label><?php $music_cd1->printCategory(); $music_cd1->printName(); $music_cd1->printPrice(); $music_cd1->printArtist(); ?></label>
        <input type="number" name="music_cd1_quantity" class="form-control" min="0" max="5" value="0">
    </div>
    <div class="form-group">
        <label><?php $film1->printCategory(); $film1->printName(); $film1->printPrice(); $film1->printArtist(); ?></label>
        <input type="number" name="film1_quantity" class="form-control" min="0" max="5" value="0">
    </div>
    <div class="form-group">
        <label><?php $serie1->printCategory(); $serie1->printName(); $serie1->printPrice(); $serie1->printArtist(); ?></label>
        <input type="number" name="serie1_quantity" class="form-control" min="0" max="5" value="0">
    </div>
    <div class="form-group">
        <label><?php $furniture1->printCategory(); $furniture1->printName(); $furniture1->printPrice(); $furniture1->printWeight(); ?></label>
        <input type="number" name="furniture1_quantity" class="form-control" min="0" max="5" value="0">
    </div>
    <div class="form-group">
        <label><?php $furniture2->printCategory(); $furniture2->printName(); $furniture2->printPrice(); $furniture2->printWeight(); ?></label>
        <input type="number" name="furniture2_quantity" class="form-control" min="0" max="5" value="0">
    </div>
    <div class="form-group text-center">
        <button type="submit" class="btn btn-primary">Bestellen</button>
    </div>
</form>
</body>
</html>
