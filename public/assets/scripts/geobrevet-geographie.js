function readJsonFile(url, callback) {
    var xhr = new XMLHttpRequest();
    xhr.open('GET', url);
    xhr.onreadystatechange = function () {
        if (xhr.readyState == 4 && (xhr.status == 200 || xhr.status == 0))
            callback(xhr.responseText);
    }
    xhr.send();
}

function affecterRessource(id, imageSrc){
    if(imageSrc != ""){
        paragraphe = document.getElementById("form_" + id + "_p");
        image = document.getElementById("form_" + id + "_i");

        paragraphe.style.display = "block";
        image.src = imageSrc;

        image.onload = () => {}
    }
}

function actJson(content) {
    try {
        json = JSON.parse(content);
        questions = json["questions"];
        console.log("Résultat :", questions);
        for (let i = 0; i < questions.length; ++i) {
            affecterRessource(questions[i].id, questions[i].image);
        }
    }
    catch (err) {
        console.error("Error code #1002 — Erreur lors de la lecture de la ressource.");
    }
}
