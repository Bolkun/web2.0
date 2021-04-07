class User {}
interface UserInterface {
    name: string;   // mandatory
    age?: number;   // optional
    getMessage(): string;
}

// Object
const user: UserInterface = {
    name: 'Monster',
    age: 18,
    getMessage() {
        return "Hello " + name;
    },
};

const user2: UserInterface = {
    name: 'Jack',
    getMessage() {
        return "Hello " + name;
    },
};

console.log(user.name);



// const user2: {name: string, age: number} = {
//     name: 'Jack',
// };