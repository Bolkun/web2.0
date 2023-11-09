<?php

include_once "class/User.php";
include_once "class/controller/UserController.php";
include_once "class/controller/ArticleController.php";
$user = new User();
$userController = new UserController();

if ($_SERVER['REQUEST_METHOD'] == 'POST'){
    if(!empty($_POST['forname'])){
        if($userController->validateForname($_POST['forname'])){
            $user->setForname($_POST['forname']);
        } else {
            echo "Not a valid Forname";
            die;
        }
    } else {
        echo "Enter your Forname";
        die;
    }
    if(!empty($_POST['surname'])){
        if($userController->validateSurname($_POST['surname'])){
            $user->setSurname($_POST['surname']);
        } else {
            echo "Not a valid Surname";
            die;
        }
    } else {
        echo "Enter your Surname";
        die;
    }
    if(!empty($_POST['street'])){
        if($userController->validateStreet($_POST['street'])){
            $user->setStreet($_POST['street']);
        } else {
            echo "Not a valid Street";
            die;
        }
    } else {
        echo "Enter your Street";
        die;
    }
    if(!empty($_POST['postcode'])){
        if($userController->validatePostcode($_POST['postcode'])){
            $user->setPostcode($_POST['postcode']);
        } else {
            echo "Not a valid Postcode";
            die;
        }
    } else {
        echo "Enter your Postcode";
        die;
    }
    if(!empty($_POST['city'])){
        if($userController->validateCity($_POST['city'])){
            $user->setCity($_POST['city']);
        } else {
            echo "Not a valid City";
            die;
        }
    } else {
        echo "Enter your City";
        die;
    }
    if(!empty($_POST['email'])){
        if($userController->validateEmail($_POST['email'])){
            $user->setEmail($_POST['email']);
        } else {
            echo "Not a valid Email";
            die;
        }
    } else {
        echo "Enter your Email";
        die;
    }
    if(!empty($_POST['birthsday'])){
        if($userController->validateBirthsday($_POST['birthsday'])){
            $user->setBirthsday($_POST['birthsday']);
        } else {
            echo "Not a valid Birthsday";
            die;
        }
    } else {
        echo "Enter your Birthsday";
        die;
    }
    $articleController = new ArticleController();
    if($articleController->validateArticle()){
        //save in csv

        //display data
    } else {
        echo "Sie haben nichts Ausgewählt";
        die;
    }
} else {
    echo "Something went wrong by recieving Data";
    die;
}
