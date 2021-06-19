import {
    gameInterface
} from '/chinczyk/modules/gameInterface.js';
import {
    periodicRequests
} from '/chinczyk/modules/periodicRequests.js';
import {
    pawn,
    fields,
    game
} from '/chinczyk/modules/game.js'
export class ajaxRequest {
    static send() {
        var xhttp = new XMLHttpRequest();
        xhttp.open("POST", "add_player.php", true);
        xhttp.onreadystatechange = function () {
            if (this.readyState == 4 && this.status == 200) {
                ajaxRequest.getResponse();
            }
        };
        xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        xhttp.send("nick=" + document.getElementById('nick').value + "&f=1");
    }
    static getResponse() {
        var xhttp = new XMLHttpRequest();
        xhttp.open("POST", "add_player.php", true);
        xhttp.onreadystatechange = function () {
            if (this.readyState == 4 && this.status == 200) {
                let ob = JSON.parse(this.responseText);
                // let gameData = JSON.parse(ob.data)
                gameInterface.addUsers();
                let userColor = ob.thisPlayerColor
                let gameData = JSON.parse(ob.data)
                let userData = Object.values(gameData)
                let colors = Object.keys(gameData)
                let checkBox = document.getElementById("status");
                let whichPlayer = colors.indexOf(userColor)
                if(userData[whichPlayer].status == 1) checkBox.checked = true
                else if (userData[whichPlayer].status == 0) checkBox.checked = false
                gameInterface.addCanvas();
                gameInterface.userInfo(ob)
                if(ob.is_running==0)
                setInterval(periodicRequests.getResponse, 3000);
            }
        };
        xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        xhttp.send("f=2");
    }
    static checkIfGame() {
        var xhttp = new XMLHttpRequest();
        xhttp.open("POST", "add_player.php", true);
        xhttp.onreadystatechange = function () {
            if (this.readyState == 4 && this.status == 200) {
                console.log(this.responseText)
                if (this.responseText == "gameExist") ajaxRequest.getResponse();
                else if (this.responseText == "gameIsRunning"){
                    ajaxRequest.getResponse();
                    setInterval(game.updateTime, 3000);
                }
                else if (this.responseText == "afterThrow") {
                    ajaxRequest.getResponse();
                    setInterval(game.updateTime, 3000);
                    
                    setTimeout(function(){let btn = document.getElementById("throw");
                    btn.addEventListener("click", game.throwDice, true);
                    btn.click();
                },100)
                }
                else addButton();
            }
        };
        xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        xhttp.send("f=3");
    }
}
export function addButton() {
    const input = document.createElement("INPUT")
    const button = document.createElement("BUTTON")
    input.id = "nick";
    input.placeholder = "podaj swój nick";
    input.style.fontSize = "30px";
    button.id = "button"
    button.innerHTML = "OK"
    document.getElementById("start").appendChild(input)
    document.getElementById("start").appendChild(button)
    button.addEventListener("click", ajaxRequest.send, true);
}