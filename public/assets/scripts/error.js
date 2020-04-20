readFile("/assets/datas/error.json", function (content, errorCode) {
    try {
        json = JSON.parse(content);
        console.log("Résultat :", json);
        displayError(json, errorCode);
    }
    catch (err) {
        console.error("Erreur lors de la lecture du JSON.")
    }
}, "e1001");

function readFile(url, callback, errorCode) {
    var xhr = new XMLHttpRequest();
    xhr.open('GET', url);
    xhr.onreadystatechange = function () {
        if (xhr.readyState == 4 && (xhr.status == 200 || xhr.status == 0))
            callback(xhr.responseText, errorCode);
    }
    xhr.send();
}

function displayError(json, errorCode){
    console.log("Error code #" + json[errorCode].code + " &mdash; " + json[errorCode].message);
}

