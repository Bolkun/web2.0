// VARIABLES
var test = true; // not OK!
var hello = "world"; // OK!
// String 
var line = "1"; // OK!
// Boolean
var isDone = false;
// Number
var decimal = 6;
var hex = 0xf00d;
var binary = 10;
var octal = 484;
// Array
var list = [1, 2, 3];
var list2 = [1, 2, 3];
// Tuple
var x;
x = ["hello", 10];
// Enum
var Color;
(function (Color) {
    Color[Color["Red"] = 0] = "Red";
    Color[Color["Green"] = 1] = "Green";
    Color[Color["Blue"] = 2] = "Blue";
})(Color || (Color = {}));
var c = Color.Green;
// Unknown
var notSure = 4; // Better than any
notSure = "maybe a string instead";
notSure = false;
var str = getValue("myString");
// Void
var warnUser = function () {
    console.log("This is my warning message");
};
// Null
var n = null;
// Undefined
var u = undefined;
// Never
function error(message) {
    throw new Error(message);
}
create({ prop: 0 });
create(null);
console.log("aa", line);
