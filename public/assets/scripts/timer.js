let runningTime = true;

function startTimer(duration, display) {
    runningTime = true;
    duration = duration * 60;

    let timer = duration, minutes, seconds;
    let displayHalf = false;
    let display5m = false;

    if (localStorage.getItem("timer") != "NaN" && localStorage.getItem("timer") != null) {
        timer = localStorage.getItem("timer");
    }

    setInterval(function () {
        minutes = parseInt(timer / 60, 10)
        seconds = parseInt(timer % 60, 10);

        minutes = minutes < 10 ? "0" + minutes : minutes;
        seconds = seconds < 10 ? "0" + seconds : seconds;

        if (runningTime) {
            localStorage.setItem("timer", timer);
        }

        if (timer > 0.1 * duration && timer <= 0.5 * duration && !displayHalf) {
            display.classList.add("text-warning");
            displayHalf = true;
            document.getElementById("time").innerHTML = 0.5 * duration / 60;
            $('#confirm').modal("show");
        }
        else if (timer <= 5 * 60 && !display5m) {
            display.classList.add("text-danger");
            display5m = true;
            document.getElementById("time").innerHTML = 5;
            $('#confirm').modal("show");
        }

        display.textContent = minutes + ":" + seconds;

        if (--timer < 0) {
            timer = duration;
            document.getElementsByTagName("form")[0].submit();
        }
    }, 1000);
}

document.getElementsByTagName("form")[0].addEventListener("submit", function () {
    console.log("Removing timer...");
    runningTime = false;
    localStorage.setItem("timer", NaN);
    console.log(localStorage.getItem("timer"));
});