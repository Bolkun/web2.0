<?php
/**
 * Created by PhpStorm.
 * User: Besitzer
 * Date: 26.11.2018
 * Time: 21:05
 */

class DarkSky
{
    private $url = 'https://api.darksky.net/forecast';
    private $key = 'c5a8beae6b351332dbbb750d1d8e723d';
    private $latitude = '52.520008';
    private $longitude = '13.404954';
    private $time = '1543186800';   //26.11.18

    public function __construct($url, $key, $latitude, $longitude, $time){
        $this->url = $url;
        $this->key = $key;
        $this->latitude = $latitude;
        $this->longitude = $longitude;
        $this->time = $time;
    }

    public function buildApiUrl(){
        return $this->url . '/' . $this->key . '/' . $this->latitude . ',' . $this->longitude . ',' . $this->time;
    }

    public function jsonDecodeApiUrl(){
        return json_decode(file_get_contents($this->buildApiUrl()));
    }

    public function getCurrentTemperature(){
        return  $this->jsonDecodeApiUrl()->currently->temperature;
    }

    public function getCurrentSummary(){
        return  $this->jsonDecodeApiUrl()->currently->summary;
    }

    public function convertDateInSeconds($date){
        return strtotime($date);
    }

    public function convertSecondsInDate($seconds){
        return date("d.m.y", $seconds);
    }

    public function setTime($seconds){
        $this->time = $seconds;
    }

    public function getCertainDateHighTemperature(){
        return $this->jsonDecodeApiUrl()->daily->data[0]->temperatureHigh;
    }

    public function getCertainDateLowTemperature(){
        return $this->jsonDecodeApiUrl()->daily->data[0]->temperatureLow;
    }

    public function getCertainDateSummary(){
        return $this->jsonDecodeApiUrl()->daily->data[0]->summary;
    }

}
