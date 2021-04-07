const addId = <T extends object>(obj: T) => {  // generic T type
    const id = Math.random().toString(16);
    return {    // merging obj with id
        ...obj,
        id,
    };
};

interface WorkerInterface {
    name: string;
}

const user3: WorkerInterface = {
    name: "Jack",
};

const result = addId<WorkerInterface>(user3);
console.log("result", result);