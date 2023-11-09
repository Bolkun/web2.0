<?php

// Abstract classes
// https://www.php.net/manual/ru/language.oop5.abstract.php

abstract class Shape
{  // 1. by abstract class objects cannot be created!

    protected $x;
    protected $y;

    abstract public function draw();    // 2. abstract method means that a function must be created in daughter classes!

    public function __toString()
    {      // Polymorphism for daughter classes
        return print_r($this, true);
    }

}

class Circle extends Shape
{

    private $r;

    public function __construct($x, $y, $r)
    {
        $this->x = $x;
        $this->y = $y;
        $this->r = $r;
    }

    public function draw()
    {
        echo 'Draw cicle with center coordinates ' . $this->x . ' and ' . $this->y;
        echo '<br />Radius ' . $this->r;
    }
}

class Rectangle extends Shape
{

    private $w;
    private $h;

    public function __construct($x, $y, $w, $h)
    {
        $this->x = $x;
        $this->y = $y;
        $this->w = $w;
        $this->h = $h;
    }

    public function draw()
    {
        echo 'Draw rectangle with coordinates from left-uper corner ' . $this->x . ' and ' . $this->y;
        echo '<br />Width ' . $this->w . ', Height ' . $this->h;
    }
}

$circle = new Circle(5, 8, 10);
$rect = new Rectangle(20, 20, 40, 10);
$r = new Rectangle(210, 220, 430, 102);
$list = [$circle, $rect, $r];
foreach ($list as $object) {
    // If Object instance of a class
    /*
    if ($l instanceof Circle) $l->drawCircle();   //if draw methods will have different names!
    elseif ($l instanceof Rectangle) $l->drawRect();
    */
    $object->draw();
    echo '<br>---------------------------------------------------------------<br />';
}
