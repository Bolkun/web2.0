<?php

/*
	d - Принцип инверсии зависимостей
	(англ. Dependency Inversion Principle, DIP)
*/
class lowRankingMale {
    public function eat() {
        $wife = new Wife();
        $food = $wife->getFood();
        // ... eat
    }
}

class averageRankingMale {
 
    private $wife;
 
    public function __construct(Wife $wife) {	// ПЛОХО - зависимость от деталей!
        $this->wife = $wife;
    }
 
    public function eat() {
        $food = $this->wife->getFood();
        // ... eat
    }
}

class highRankingMale {
 
    private $foodProvider;
 
    public function __construct(IFoodProvider $foodProvider) {	// ХОРОШО - зависимость от абстракций не от деталей!
        $this->foodProvider = $foodProvider;
    }
 
    public function eat() {
        $food = $this->foodProvider->getFood();
        // ... eat
    }
}