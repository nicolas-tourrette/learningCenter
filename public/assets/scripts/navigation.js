let CheminRepertoire = document.location.href.split('/');
let pathLength = CheminRepertoire.length;
let regex = /\?[a-zA-Z0-9=&éè%]*/gmi;
let maPage = CheminRepertoire[pathLength - 1].replace(regex, "");
let item;
let idForItem = "";

console.log(CheminRepertoire);
console.log(maPage);
console.log(pathLength);

if (maPage == "" || maPage == "#") {
    idForItem = "index";
}
else if (maPage != "" || maPage != "#") {
    if(pathLength > 5){
        idForItem = "my-"
    }
    idForItem += CheminRepertoire[pathLength - 1];
}

if (idForItem.indexOf("#") > 0) {
    idForItem = idForItem.substring(0, idForItem.indexOf("#"));
}
item = document.getElementById(idForItem);

if (item != null) {
    item.classList.add("mm-active");
    if (CheminRepertoire[pathLength - 2] == "apps" && pathLength <= 5) {
        item.parentElement.parentElement.classList.add("mm-show");
        item.parentElement.parentElement.parentElement.classList.add("mm-active");
    }
    if (pathLength > 7) {
        item.parentElement.parentElement.classList.add("mm-show");
        item.parentElement.parentElement.parentElement.classList.add("mm-active");
        item.parentElement.parentElement.parentElement.parentElement.classList.add("mm-show");
        item.parentElement.parentElement.parentElement.parentElement.parentElement.classList.add("mm-active");
    }
}