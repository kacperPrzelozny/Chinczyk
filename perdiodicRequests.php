<?php
session_start();
function startGame(){
    require("phpConnect.php");
    $connect = @new mysqli($host, $user, $passwd, $dbname);
    $isRunning = 1;
    $gameId = $_SESSION["uniqid"];
    $update = "UPDATE pokoje SET is_running='$isRunning' WHERE game_id='$gameId'";
    $connect->query($update); 
    // echo "startGame";
}
function changeStatus($newStatus){
    require("phpConnect.php");
    $connect = @new mysqli($host, $user, $passwd, $dbname);
    $gameId = $_SESSION["uniqid"];
    $select = "SELECT * FROM `pokoje` WHERE game_id='$gameId'";
    $result = $connect->query($select);
    $row = $result->fetch_all(MYSQLI_ASSOC)[0];
    if($row["is_running"]==0){
        $usersData = json_decode($row["data"], true);
        $currentPlayerColor = $_SESSION["color"];
        $usersData[$currentPlayerColor]["status"] = $newStatus;
        $usersData[$currentPlayerColor]["last_act"] = time();
        $data = json_encode($usersData);
        $update = "UPDATE pokoje SET data='$data' WHERE game_id='$gameId'";
        $connect->query($update);
        $numberOfPlayers = count($usersData);
        $readyPlayers = 0;
        foreach ($usersData as $values) {
            if ($values["status"] == "1") {
                $readyPlayers++;
            }
        }
        if ($numberOfPlayers == $readyPlayers && $numberOfPlayers > 1) {
            foreach ($usersData as $key => $values) {
                if($key=="red"){
                    $usersData[$key]["status"] = "3";
                    $usersData[$key]["time"] = time();
                }
                else{
                   $usersData[$key]["status"] = "2";
                }
            }
            $data = json_encode($usersData);
            $update = "UPDATE pokoje SET data='$data' WHERE game_id='$gameId'";
            $connect->query($update);
            startGame();
        } 
    }
    else{
        echo "gameHasAlreadyStarted";
    }
}
if(isset($_POST["status"])){
    changeStatus($_POST["status"]);
}
?>