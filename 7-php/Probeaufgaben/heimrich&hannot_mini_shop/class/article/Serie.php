<?php


class Serie extends Article
{
    private $sArtist;

    public function __construct($iId, $sCategory, $sName, $fPrice, $sArtist)
    {
        $this->sArtist = $sArtist;

        parent::__construct($iId, $sCategory, $sName, $fPrice);
    }

    public function printArtist()
    {
        echo $this->sArtist;
    }

}