// VARIABLES
var test = true;        // not OK!
const hello = "world";  // OK!
// String 
let line: string = "1"; // OK!
// Boolean
let isDone: boolean = false;
// Number
let decimal: number = 6;
let hex: number = 0xf00d;
let binary: number = 0b1010;
let octal: number = 0o744;
// Array
let list: number[] = [1, 2, 3];
let list2: Array<number> = [1, 2, 3];
// Tuple
let x: [string, number];
x = ["hello", 10];
// Enum
enum Color {
    Red,
    Green,
    Blue,
}
let c: Color = Color.Green;
// Unknown
let notSure: unknown = 4;   // Better than any
notSure = "maybe a string instead";
notSure = false;
// Any
declare function getValue(key: string): any;    // do not use! Displays no errors!
const str: string = getValue("myString");
// Void
const warnUser = (): void => {
    console.log("This is my warning message");
}
// Null
let n: null = null;
// Undefined
let u: undefined = undefined;
// Never
function error(message: string): never {    // Function that never ends!
    throw new Error(message);
}
// Object
declare function create(o: object | null): void;
create({ prop: 0 });
create(null);

console.log("aa", line);