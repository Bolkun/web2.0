// alias
type ID = string;
type PopularTag = string;
type MaybePopularTag = PopularTag | null;

interface IAnimal {
    id: ID;
    name: string;
    surname: string;
}
const PopularTags: PopularTag[] = ["dragon", "coffee"]; 
const dragonsTag: MaybePopularTag = "dargon";

let username: string = "alex";
let pageName: string | number = 1;
let errorMessage: string | null = null;

let animnal: IAnimal | null = null;

// type conversion
let vUnknown: unknown = 10;
let s2: string = vUnknown as string;
let numericPageName: number = (pageName as unknown) as number;