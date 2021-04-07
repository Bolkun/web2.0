interface ICustomer {
    getFullName(): string;
}

class Customer implements ICustomer {    // no checkings in js after compile !!! 
    // default all vars are public
    firstName: string;
    lastName: string;
    private email: string;  // protected
    readonly unchangable: string;   // cannot be rewritten
    static maxAge = 50;

    constructor(firstName: string, lastName: string) {
        this.firstName = firstName;
        this.lastName = lastName;
    }

    getFullName(): string {
        return this.firstName + " " + this.lastName;
    }
}

class Admin extends Customer {
    private editor: string;
    setEditor(editor: string):void {
        this.editor = editor;
    }

    getEditor(): string {
        return this.editor;
    }
}

const customer = new Customer("Harry", "Potter");
console.log(customer.getFullName);
console.log(Customer.maxAge);

const admin = new Admin("Mr", "Bin");
console.log(admin.getEditor);