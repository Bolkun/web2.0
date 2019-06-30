<?php

class BirdRun {
 
    private $bird; 
    public function __construct(Bird $bird) {
        $this->bird = $bird;
    }
 
    public function run() {
        $flySpeed = $this->bird->fly();
    }
}