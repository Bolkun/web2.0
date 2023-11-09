<?php

class Math {
    public const PI = 3.1415926;
    private static $counter = 0;

    public static function sin($x)
    {
        self::$counter++;
        return sin($x);
    }

    public function pi2()
    {
        echo self::sin(8) . "<br>";
        self::$counter++;
        return self::PI ** 2;   // same as Math::PI ** 2;
    }

    public static function getCounter()
    {
        return self::$counter;
    }
}
/*
$math = new Math();
echo $math->sin(5) . "<br>"; // still works!
*/
echo Math::sin(5) . "<br>";  // sin(radians)
echo Math::pi2() . "<br>";
echo Math::getCounter() . "<br>";
echo Math::PI;