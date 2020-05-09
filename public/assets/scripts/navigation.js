let CheminRepertoire = document.location.href.split('/');
let pathLength = CheminRepertoire.length;
let regex = /\?[a-zA-Z0-9=&éè%]*/gmi;
let maPage = CheminRepertoire[pathLength - 1].replace(regex, "");
let item;
let idForItem = "";

if (maPage.indexOf("#") > 0) {
    maPage = maPage.substring(0, maPage.indexOf("#"));
}

console.log(CheminRepertoire);
console.log(maPage);
console.log(pathLength);

if (maPage == "" || maPage == "#") {
    idForItem = "index";
}
else if(maPage == "dashboard"){
    idForItem = "my-" + CheminRepertoire[pathLength - 2];
}
else if (maPage != "" || maPage != "#") {
    if(maPage == "dashboard"){
        idForItem = "my-" + CheminRepertoire[pathLength - 2] + "-" ;
    }
    else if(pathLength > 7){
        idForItem = "my-" + CheminRepertoire[pathLength - 3] + "-";
    }
    idForItem += CheminRepertoire[pathLength - 1];
}

if (maPage == "version" || CheminRepertoire[pathLength - 2] == "compte"){
    idForItem = CheminRepertoire[pathLength - 2];
}

if (idForItem.indexOf("#") > 0) {
    idForItem = idForItem.substring(0, idForItem.indexOf("#"));
}

console.log(idForItem);

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