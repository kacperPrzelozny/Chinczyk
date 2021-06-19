export class game {
    static updateTime() {
        var xhttp = new XMLHttpRequest();
        xhttp.open("POST", "game.php", true);
        xhttp.onreadystatechange = function () {
            if (this.readyState == 4 && this.status == 200) {
                let colors = {
                    "red": "#CD5C5C",
                    "blue": "#00FFFF",
                    "green": "#00FF00",
                    "yellow": "#FFFF00"
                }
                let ob = JSON.parse(this.responseText)
                let thrown = ob.thrown
                // console.log(thrown)
                let currentColor = ob.color
                let timeLeft = document.getElementById('time');
                timeLeft.style.backgroundColor = colors[currentColor]
                timeLeft.innerHTML = ob.time;
                if (ob.thisPlayer == ob.color) {
                    if (thrown == undefined || thrown == false) {
                        document.getElementById("throw").style.display = "block";
                        timeLeft.style.marginTop = "20px"
                    } else {
                        document.getElementById("throw").style.display = "none";
                        timeLeft.style.marginTop = "80px"
                    }
                } else {
                    game.deleteProposition()
                    var highestIntervalId = setInterval(";");
                    for (var i = 0; i < highestIntervalId; i++) {
                        clearInterval(i);
                    }
                    setInterval(game.updateTime, 1000);
                    let pawns = document.getElementsByClassName("pawn");
                    for (let i = 0; i < pawns.length; i++) {
                        let color = pawns[i].id.substr(0, pawns[i].id.length - 1);
                        pawns[i].style.backgroundColor = color;
                    }
                    document.getElementById("throw").style.display = "none";
                    timeLeft.style.marginTop = "80px"
                    setTimeout(function () {
                        let dice = document.getElementById('dice')
                        dice.style.backgroundImage = "";
                    }, 1000)
                }
                game.refreshPawns();
            }
        };
        xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        xhttp.send("g=1");
    }
    static throwDice() {
        console.log("DZIAŁAJ KURWO JEBANA")
        this.style.display = "none"
        let timeLeft = document.getElementById('time');
        timeLeft.style.marginTop = "80px"
        var xhttp = new XMLHttpRequest();
        xhttp.open("POST", "game.php", true);
        xhttp.onreadystatechange = function () {
            if (this.readyState == 4 && this.status == 200) {
                let ob = JSON.parse(this.responseText);
                if (typeof (ob.randomNumber) == "number") {
                    let dice = document.getElementById('dice')
                    dice.style.backgroundImage = "url('http://przelozny.ct8.pl/chinczyk/img/kostka.png')";
                    dice.style.backgroundPositionX = (ob.randomNumber - 1) * -160 + "px";
                }
                var synth = window.speechSynthesis;
                var voices = [];

                function populateVoiceList() {
                    voices = synth.getVoices();
                }
                if (speechSynthesis.onvoiceschanged !== undefined) {
                    speechSynthesis.onvoiceschanged = populateVoiceList;
                }
                populateVoiceList();
                var u = new SpeechSynthesisUtterance();
                u.pitch = 1;
                u.rate = 1;
                u.text = ob.randomNumber;
                u.voice = voices[0];
                synth.speak(u);
                if (ob.randomNumber != "Nie oszukuj!")
                    game.showPosibilities(ob);
            }
        };
        xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        xhttp.send("g=2");
    }
    static showProposition() {
        let pawnDiv = this
        let timeLeft = document.getElementById('time');
        timeLeft.style.marginTop = "80px"
        var xhttp = new XMLHttpRequest();
        xhttp.open("POST", "game.php", true);
        xhttp.onreadystatechange = function () {
            if (this.readyState == 4 && this.status == 200) {
                let ob = JSON.parse(this.responseText)
                let number = ob.number
                let id = parseInt(pawnDiv.id[pawnDiv.id.length - 1]) - 1;
                let proposition = 0
                if (ob.pawns[id] >= 0 && ob.pawns[id] < 4) {
                    proposition = 4;
                } else {
                    proposition = ob.pawns[id] + number
                }
                let color = pawnDiv.id.substr(0, pawnDiv.id.length - 1)
                let propositionDiv = document.createElement("DIV")
                propositionDiv.classList.add("proposition");
                propositionDiv.style.position = "absolute";
                propositionDiv.style.width = "20px";
                propositionDiv.style.height = "20px";
                propositionDiv.style.top = fields[color][proposition][0] + "px"
                propositionDiv.style.left = fields[color][proposition][1] + "px"
                document.getElementById("board").appendChild(propositionDiv);
            }
        };
        xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        xhttp.send("g=3");
    }
    static move() {
        game.deleteProposition()
        let dice = document.getElementById('dice')
        dice.style.backgroundImage = "";
        var highestIntervalId = setInterval(";");
        for (var i = 0; i < highestIntervalId; i++) {
            clearInterval(i);
        }
        setInterval(game.updateTime, 1000);
        let pawns = document.getElementsByClassName("pawn");
        for (let i = 0; i < pawns.length; i++) {
            let color = pawns[i].id.substr(0, pawns[i].id.length - 1);
            pawns[i].style.backgroundColor = color;
        }
        let id = parseInt(this.id[this.id.length - 1]) - 1;
        for (let i = 0; i < pawns.length; i++) {
            pawns[i].removeEventListener("mouseover", game.showProposition, true);
            pawns[i].removeEventListener("mouseout", game.deleteProposition, true);
            pawns[i].removeEventListener("click", game.move, true);
            pawns[i].style.cursor = "default";
        }
        var xhttp = new XMLHttpRequest();
        xhttp.open("POST", "game.php", true);
        xhttp.onreadystatechange = function () {
            if (this.readyState == 4 && this.status == 200) {
                game.refreshPawns();
            }
        };
        xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        xhttp.send("n=" + id + "&g=4");
    }
    static refreshPawns() {
        let colors = {
            "red": "#5a0600",
            "blue": "blue",
            "green": "green",
            "yellow": "#de9933"
        }
        var xhttp = new XMLHttpRequest();
        xhttp.open("POST", "game.php", true);
        xhttp.onreadystatechange = function () {
            if (this.readyState == 4 && this.status == 200) {
                let ob = JSON.parse(this.responseText)
                let record = JSON.parse(ob.data)
                let players = Object.keys(record)
                let data = Object.values(record)
                for (let i = 0; i < players.length; i++) {
                    let pawns = data[i].pawns
                    for (let j = 0; j < 4; j++) {
                        let pawn = document.getElementById(colors[players[i]] + (j + 1));
                        pawn.style.top = fields[colors[players[i]]][pawns[j]][0] + "px";
                        pawn.style.left = fields[colors[players[i]]][pawns[j]][1] + "px";
                    }
                }
                for (let i = 0; i < players.length; i++) {
                    if (data[i].is_winner == true) {
                        var highestIntervalId = setInterval(";");
                        for (var j = 0; j < highestIntervalId; j++) {
                            clearInterval(j);
                        }
                        setTimeout(function () {alert("Wygrał gracz o nicku: " + data[i].nick)}, 100)
                    }
                }

            }
        };
        xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        xhttp.send("g=5");
    }
    static showPosibilities(ob) {
        let colors = {
            "red": "#5a0600",
            "blue": "blue",
            "green": "green",
            "yellow": "#de9933"
        }
        let color = ob.color
        let posibilities = ob.possibleMoves
        // console.log(posibilities);
        for (let i = 0; i < 4; i++) {
            if (posibilities[i] == true) {
                let pawn = document.getElementById(colors[color] + (i + 1));
                pawn.style.cursor = "pointer";
                pawn.addEventListener("mouseover", game.showProposition, true);
                pawn.addEventListener("mouseout", game.deleteProposition, true);
                pawn.addEventListener("click", game.move, true);
                setInterval(function () {
                    if (pawn.style.backgroundColor != "white") {
                        pawn.style.backgroundColor = "white";
                    } else {
                        pawn.style.backgroundColor = colors[ob.color];
                    }
                }, 500)
            }
        }
    }
    static deleteProposition() {
        let prop = document.getElementsByClassName("proposition")
        for (let i = prop.length - 1; i >= 0; i--) {
            prop[i].parentNode.removeChild(prop[i])
        }
    }
}
export class pawn {
    constructor(field, color, id) {
        this.field = field
        this.color = color
        this.id = id
        this.drawPawn()
    }
    drawPawn() {
        let board = document.getElementById("board")
        let pawn = document.createElement("DIV")
        pawn.id = this.color + this.id;
        pawn.style.backgroundColor = this.color
        pawn.classList.add("pawn");
        pawn.style.position = "absolute";
        pawn.style.top = fields[this.color][this.field][0] + "px"
        pawn.style.left = fields[this.color][this.field][1] + "px"
        pawn.style.width = "20px"
        pawn.style.height = "20px"
        pawn.style.borderWidth = "1px"
        pawn.style.borderStyle = "solid"
        pawn.style.borderColor = "black"
        pawn.style.borderRadius = "16px"
        board.appendChild(pawn);
    }
}
export const fields = {
    "#5a0600": [
        [330, 50],
        [330, 90],
        [370, 50],
        [370, 90],
        [410, 170],
        [370, 170],
        [330, 170],
        [290, 170],
        [250, 170],
        [250, 130],
        [250, 90],
        [250, 50],
        [250, 10],
        [210, 10],
        [170, 10],
        [170, 50],
        [170, 90],
        [170, 130],
        [170, 170],
        [130, 170],
        [90, 170],
        [50, 170],
        [10, 170],
        [10, 210],
        [10, 250],
        [50, 250],
        [90, 250],
        [130, 250],
        [170, 250],
        [170, 290],
        [170, 330],
        [170, 370],
        [170, 410],
        [210, 410],
        [250, 410],
        [250, 370],
        [250, 330],
        [250, 290],
        [250, 250],
        [290, 250],
        [330, 250],
        [370, 250],
        [410, 250],
        [410, 210],
        [370, 210],
        [330, 210],
        [290, 210],
        [250, 210],
        [210, 210],
    ],
    "blue": [
        [50, 330],
        [50, 370],
        [90, 330],
        [90, 370],
        [10, 250],
        [50, 250],
        [90, 250],
        [130, 250],
        [170, 250],
        [170, 290],
        [170, 330],
        [170, 370],
        [170, 410],
        [210, 410],
        [250, 410],
        [250, 370],
        [250, 330],
        [250, 290],
        [250, 250],
        [290, 250],
        [330, 250],
        [370, 250],
        [410, 250],
        [410, 210],
        [410, 170],
        [370, 170],
        [330, 170],
        [290, 170],
        [250, 170],
        [250, 130],
        [250, 90],
        [250, 50],
        [250, 10],
        [210, 10],
        [170, 10],
        [170, 50],
        [170, 90],
        [170, 130],
        [170, 170],
        [130, 170],
        [90, 170],
        [50, 170],
        [10, 170],
        [10, 210],
        [50, 210],
        [90, 210],
        [130, 210],
        [170, 210],
        [210, 210],

    ],
    "green": [
        [330, 330],
        [330, 370],
        [370, 330],
        [370, 370],
        [250, 410],
        [250, 370],
        [250, 330],
        [250, 290],
        [250, 250],
        [290, 250],
        [330, 250],
        [370, 250],
        [410, 250],
        [410, 210],
        [410, 170],
        [370, 170],
        [330, 170],
        [290, 170],
        [250, 170],
        [250, 130],
        [250, 90],
        [250, 50],
        [250, 10],
        [210, 10],
        [170, 10],
        [170, 50],
        [170, 90],
        [170, 130],
        [170, 170],
        [130, 170],
        [90, 170],
        [50, 170],
        [10, 170],
        [10, 210],
        [10, 250],
        [50, 250],
        [90, 250],
        [130, 250],
        [170, 250],
        [170, 290],
        [170, 330],
        [170, 370],
        [170, 410],
        [210, 410],
        [210, 370],
        [210, 330],
        [210, 290],
        [210, 250],
        [210, 210],

    ],
    "#de9933": [
        [50, 50],
        [50, 90],
        [90, 50],
        [90, 90],
        [170, 10],
        [170, 50],
        [170, 90],
        [170, 130],
        [170, 170],
        [130, 170],
        [90, 170],
        [50, 170],
        [10, 170],
        [10, 210],
        [10, 250],
        [50, 250],
        [90, 250],
        [130, 250],
        [170, 250],
        [170, 290],
        [170, 330],
        [170, 370],
        [170, 410],
        [210, 410],
        [250, 410],
        [250, 370],
        [250, 330],
        [250, 290],
        [250, 250],
        [290, 250],
        [330, 250],
        [370, 250],
        [410, 250],
        [410, 210],
        [410, 170],
        [370, 170],
        [330, 170],
        [290, 170],
        [250, 170],
        [250, 130],
        [250, 90],
        [250, 50],
        [250, 10],
        [210, 10],
        [210, 50],
        [210, 90],
        [210, 130],
        [210, 170],
        [210, 210],
    ]
}