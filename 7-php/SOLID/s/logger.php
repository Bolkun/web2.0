<?php

class Logger {
 
    private function saveToFile($message) {
        //...
    }
    public function log($message) {
        //...
        $this->saveToFile($message);
    }
}