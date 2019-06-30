<?php
/*
	l - Принцип подстановки Барбары Лисков.
	(Liskov Substitution Principle, LSP)
*/
$bird    = new Bird();
//$bird = new Duck();
//$bird = new Pinguin();
$birdRun = new BirdRun($bird);
$birdRun->run();
?>