var User = /** @class */ (function () {
    function User() {
    }
    return User;
}());
// Object
var user = {
    name: 'Monster',
    age: 18,
    getMessage: function () {
        return "Hello " + name;
    }
};
var user2 = {
    name: 'Jack',
    getMessage: function () {
        return "Hello " + name;
    }
};
console.log(user.name);
// const user2: {name: string, age: number} = {
//     name: 'Jack',
// };
