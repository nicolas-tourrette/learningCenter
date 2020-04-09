var buttonMore = document.getElementById("displaymore");
var buttonLess = document.getElementById("displayless");
var elementsDemasques = 0;

function displayMoreVersion(){
    var versionMasked = document.getElementsByClassName("masked");
    var compteur = 0;
    var limite = 5;

    if(versionMasked.length != 0){
        while (versionMasked.length != 0 && compteur < limite) {
            elementsDemasques++;
            versionMasked[0].classList.remove("masked");
            buttonLess.classList.remove("masked");
            compteur++;
        }
        if (versionMasked.length == 0) {
            buttonMore.classList.add("masked");
        }
    }
    else{
        buttonMore.classList.add("masked");
    }
}

function displayLessVersion() {
    var version = document.querySelectorAll("div.version");
    var nbMasquees = document.querySelectorAll("div.version.masked").length+1;
    var compteur = 0;
    var limite = 5;

    buttonMore.classList.remove("masked");

    if (elementsDemasques > 0){
        while (elementsDemasques > 0 && compteur < limite) {
            if (!version[(version.length - nbMasquees - compteur)].classList.contains("masked")){
                version[(version.length - nbMasquees - compteur)].classList.add("masked");
                elementsDemasques--;
                compteur++;
            }
            if (elementsDemasques == 0){
                buttonMore.classList.remove("masked");
                buttonLess.classList.add("masked");
            }
        }
    }
    else {
        elementsDemasques = 0;
        buttonLess.classList.add("masked");
    }
}