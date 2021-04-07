var someElement = document.querySelector(".foo"); // Element is the highest class in hierarchy
console.log('someElement', someElement.value);
var someElement2 = document.querySelector(".bar");
someElement2.addEventListener('blur', function (event) {
    var target = event.target;
    console.log('event', target.value);
});
