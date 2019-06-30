<?php
/*
	S - Принцип единственной обязанности (ответственности)
	Single responsibility princple
*/
	$logger  = new Logger();
	$product = new Product($logger);
	$product->setPrice(10);
?>