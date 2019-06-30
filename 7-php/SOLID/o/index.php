<?php
/*
	O - Принцип открытости /закрытости
	Open/closed principle
*/
	$logger  = new DBLogger();
	$product = new Product($logger);	//we can give 2 classes due to interface
	$product->setPrice(10);
?>