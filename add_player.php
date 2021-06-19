<?php
session_start();
function addToRoom(){
    require_once("phpConnect.php");
    $connect = @new mysqli($host,$user,$passwd,$dbname);
    mysqli_query($connect, "set names utf8mb4");
    $colors = array("1" => "red", "2" => "blue", "3" => "green", "4" => "yellow");
    if($connect->connect_errno != 0 ){
        echo "Connection failed: ";
    }
    else{
        $select = "SELECT * FROM `pokoje` WHERE is_running=0";
        $result = $connect->query($select);
        $_SESSION["nick"] = $_POST["nick"];
        if($result->num_rows == 0){
            $insert_time = time();
            $last_act = time();
            $isRunning = 0;
            $status = "0";
            $userInfo = array(
                "nick"=> rawurlencode($_POST["nick"]),
                "insert_time"=>$insert_time,
                "last_act"=>$last_act, 
                "status"=>$status,
                "pawns" => [0,1,2,3],
                "is_winner" => false
            );
            $toInsert = array($colors["1"]=>$userInfo);
            $_SESSION["color"] = $colors["1"];
            $data = json_encode($toInsert);
            $gameId = uniqid();
            $_SESSION["uniqid"] = $gameId;
            $insert = "INSERT INTO pokoje (game_id,data,is_running) VALUES ('$gameId','$data','$isRunning')";
            if ($connect->query($insert) === TRUE) {
                // echo "New record created successfully";
            } 
            else {
                echo "Error: " . $insert . "<br>" . $connect->error;
            }
        }
        else if($result->num_rows == 1){
            $row = $result->fetch_all(MYSQLI_ASSOC);
            $currentUsers = json_decode($row[0]["data"],true);
            $_SESSION["uniqid"] = $row[0]["game_id"];
            $currentGameId = $row[0]["game_id"];
            $numberOfPlayers = count($currentUsers);
            $insert_time = time();
            $last_act = time();
            $isRunning = false;
            $status = "0";
            $newUserInfo = array(
                "nick"=> rawurlencode($_POST["nick"]),
                "insert_time"=>$insert_time,
                "last_act"=>$last_act, 
                "status"=>$status,
                "pawns" => [0, 1, 2, 3],
                "is_winner" => false
            );
            $_SESSION["color"] = $colors[$numberOfPlayers+1];
            if($numberOfPlayers!=3){
                $currentUsers[$colors[$numberOfPlayers+1]] = $newUserInfo;
                $data = json_encode($currentUsers);
                $update = "UPDATE pokoje SET data='$data' WHERE game_id='$currentGameId'";
                $connect->query($update);    
            }
            else if($numberOfPlayers==3){
                $currentUsers[$colors[$numberOfPlayers+1]] = $newUserInfo;
                $currentUsers["red"]["status"] = "3";
                $currentUsers["red"]["time"] = time();
                $currentUsers["blue"]["status"] = "2";
                $currentUsers["green"]["status"] = "2";
                $currentUsers["yellow"]["status"] = "2";
                $data = json_encode($currentUsers);
                $update = "UPDATE pokoje SET data='$data', is_running='1' WHERE game_id='$currentGameId'";
                $connect->query($update);
            }
        }
    }
}
function sendRoomData(){
    require_once("phpConnect.php");
    $connect = @new mysqli($host,$user,$passwd,$dbname);
    mysqli_query($connect, "set names utf8mb4");
    if($connect->connect_errno != 0 ){
        echo "Connection failed: ";
    }
    else{
        $gameId = $_SESSION["uniqid"];
        $select = "SELECT * FROM `pokoje` WHERE game_id='$gameId'";
        $result = $connect->query($select);
        $row = $result->fetch_all(MYSQLI_ASSOC)[0];
        $row["thisPlayerColor"] = $_SESSION["color"];
        echo json_encode($row);
    }
}
function checkIfGame(){
    if(isset($_SESSION["uniqid"])){
        require_once("phpConnect.php");
        $connect = @new mysqli($host, $user, $passwd, $dbname);
        mysqli_query($connect, "set names utf8mb4");
        $gameId = $_SESSION["uniqid"];
        $select = "SELECT * FROM `pokoje` WHERE game_id='$gameId'";
        $result = $connect->query($select);
        if($result->num_rows == 1){
            $row = $result->fetch_all(MYSQLI_ASSOC)[0];
            if($row["is_running"]==0) echo "gameExist";
            else if ($row["is_running"] == 1){
                if($_SESSION["thrown"]==true){
                    echo "afterThrow";
                }
                else echo "gameIsRunning"; 
            } 
            else if ($row["is_running"] == 2) echo "gameNotExist";
        }
        else if ($result->num_rows == 0) {
            echo "gameNotExist";
            session_destroy();
        }
    }
    else{
        echo "gameNotExist";
        session_destroy();
    }
}

if(isset($_POST["nick"])){
    addToRoom();
}
else if($_POST["f"]!==null&&$_POST["f"]=="2"){
    sendRoomData();
}
else if($_POST["f"]!==null&&$_POST["f"]=="3"){
    checkIfGame();
}
