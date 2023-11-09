<?php


class Furniture extends Article
{
    private $iWeight;

    public function __construct($iId, $sCategory, $sName, $fPrice, $iWeight)
    {
        $this->iWeight = $iWeight;

        parent::__construct($iId, $sCategory, $sName, $fPrice);
    }

    public function getWeight()
    {
        return $this->iWeight;
    }

    public function printWeight()
    {
        echo $this->iWeight . 'kg';
    }
}