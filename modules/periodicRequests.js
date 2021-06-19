import {gameInterface} from '/chinczyk/modules/gameInterface.js';
export class periodicRequests {
    static changeStatus() {
        let checkBox = this;
        let status;
        let label = document.getElementById("ready")
        if(checkBox.checked == true) {
            status = 1
            label.innerHTML = "Gotowy do gry";
        }
        else{
            status = 0
            label.innerHTML = "Czekam na innych graczy";
        } 
        var xhttp = new XMLHttpRequest();
        xhttp.open("POST", "perdiodicRequests.php", true);
        xhttp.onreadystatechange = function () {
            if (this.readyState == 4 && this.status == 200) {
                periodicRequests.getResponse()
            }
        };
        xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        xhttp.send("status="+status);
    }
    static getResponse() {
        var xhttp = new XMLHttpRequest();
        xhttp.open("POST", "add_player.php", true);
        xhttp.onreadystatechange = function () {
            if (this.readyState == 4 && this.status == 200) {
                let ob = JSON.parse(this.responseText);
                gameInterface.userInfo(ob)
            }
        };
        xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        xhttp.send("f=2");
    }
}