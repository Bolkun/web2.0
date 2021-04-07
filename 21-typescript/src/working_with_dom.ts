const someElement = document.querySelector(".foo") as HTMLInputElement; // Element is the highest class in hierarchy
console.log('someElement', someElement.value);

const someElement2 = document.querySelector(".bar");
someElement2.addEventListener('blur', (event) => {
    const target = event.target as HTMLInputElement;
    console.log('event', target.value);
});