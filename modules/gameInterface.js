import {
    periodicRequests
} from '/chinczyk/modules/periodicRequests.js'
import {
    pawn,
    fields,
    game
} from '/chinczyk/modules/game.js'
export class gameInterface {
    static addUsers() {
        let userBox = document.getElementById("start")
        userBox.style.marginTop = "100px";
        userBox.style.width = "80%";
        userBox.style.height = "100px";
        userBox.style.justifyContent = "center";
        userBox.style.alignItems = "center";
        userBox.style.marginLeft = "auto";
        userBox.style.marginRight = "auto";
        userBox.innerHTML = '';
        for (let i = 0; i < 5; i++) {
            if (i != 4) {
                let user = document.createElement("DIV")
                user.classList.add('playerBox');
                user.style.width = "250px";
                user.style.height = "100px";
                user.style.paddingTop = "40px";
                user.style.textAlign = "center";
                user.style.fontSize = "30px";
                user.style.color = "black";
                user.style.borderRadius = "20px";
                user.style.marginLeft = "20px";
                user.style.backgroundColor = "gray";
                userBox.appendChild(user);
                if (i == 0) user.id = "red";
                else if (i == 1) user.id = "blue";
                else if (i == 2) user.id = "green";
                else if (i == 3) user.id = "yellow";
            } else {
                let checkBox = document.createElement("INPUT");
                let label = document.createElement("P");
                label.id = "ready";
                label.style.display = "block";
                label.style.width = "200px";
                label.style.fontSize = "20px";
                label.innerHTML = "Czekam na innych graczy";
                checkBox.type = "checkbox";
                checkBox.id = "status";
                checkBox.addEventListener("click", periodicRequests.changeStatus, true)
                checkBox.style.width = "70px";
                checkBox.style.height = "35px";
                userBox.appendChild(checkBox);
                userBox.appendChild(label);
            }
        }
    }
    static userInfo(ob) {
        let userBoxes = document.getElementsByClassName('playerBox');
        let userColor = ob.thisPlayerColor
        let gameData = JSON.parse(ob.data)
        let userData = Object.values(gameData)
        let colors = ["#CD5C5C", "#00FFFF", "#00FF00", "#FFFF00"]
        let isRunning = ob.is_running;
        if (isRunning == 1) {
            let colors2 = {
                "red": "#CD5C5C",
                "blue": "#00FFFF",
                "green": "#00FF00",
                "yellow": "#FFFF00"
            }
            let checkBox = document.getElementById("status");
            let label = document.getElementById("ready");
            document.getElementById("start").removeChild(checkBox);
            document.getElementById("start").removeChild(label);
            let playerBoxes = document.getElementsByClassName('playerBox')
            for (var i = 0; i < 4; i++) {
                playerBoxes[i].style.backgroundColor = "gray";
            }
            let currentPlayerBox = document.getElementById(userColor)
            currentPlayerBox.style.backgroundColor = colors2[userColor]
            var highestIntervalId = setInterval(";");
            for (var i = 0; i < highestIntervalId; i++) {
                clearInterval(i);
            }
            setInterval(game.updateTime, 1000);
            let btn = document.getElementById("throw")
            btn.addEventListener("click", game.throwDice, true);
        }
        for (let i = 0; i < userData.length; i++) {
            let nick = decodeURIComponent(userData[i].nick);
            userBoxes[i].innerHTML = nick
            if (userData[i].status == 1) userBoxes[i].style.backgroundColor = colors[i];
            if (userData[i].status == 0) userBoxes[i].style.backgroundColor = "gray";
        }
    }
    static addCanvas() {
        let gameBoard = document.createElement("DIV");
        gameBoard.id = "board";
        let gameBoardCanvas = document.createElement("CANVAS");
        gameBoard.style.position = "relative";
        gameBoard.style.width = "640px";
        gameBoard.style.height = "440px";
        gameBoard.style.marginTop = "50px"
        gameBoard.style.marginLeft = "auto";
        gameBoard.style.marginRight = "auto";
        document.body.appendChild(gameBoard);
        gameBoardCanvas.width = "440";
        gameBoardCanvas.height = "440";
        gameBoard.appendChild(gameBoardCanvas);
        var ctx = gameBoardCanvas.getContext("2d");
        let img = document.getElementById("foto")
        img.onload = function () {
            ctx.drawImage(img, 0, 0, 440, 440);
        };
        img.src = "img/plansza.png"
        let colors = ["#5a0600", "blue", "green", "#de9933"];
        let pawns = [];
        for (var i = 0; i < 4; i++) {
            pawns.push([])
            for (var j = 0; j < 4; j++) {
                pawns[i].push(new pawn(j, colors[i], j + 1));
            }
        }
        let panel = document.createElement("DIV")
        let dice = document.createElement("div")
        let time = document.createElement("DIV")
        let btn = document.createElement("button")
        btn.id = "throw"
        btn.innerHTML = "Rzuć kostką"
        time.id = "time"
        dice.id = "dice"
        panel.id = "panel"

        panel.appendChild(dice);
        panel.appendChild(btn);
        panel.appendChild(time);
        gameBoard.appendChild(panel)
        btn = document.getElementById("throw")
        btn.style.display = "none"
        time = document.getElementById("time")
        time.style.marginTop = "80px"
    }
}