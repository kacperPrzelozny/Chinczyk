<?php
session_start();
function updateTime()
{
    require("phpConnect.php");
    $connect = @new mysqli($host, $user, $passwd, $dbname);
    mysqli_query($connect, "set names utf8mb4");
    $gameID = $_SESSION["uniqid"];
    $select = "SELECT * FROM pokoje WHERE game_id = '$gameID'";
    $result = $connect->query($select);
    $row = $result->fetch_all(MYSQLI_ASSOC)[0];
    $data = json_decode($row["data"], true);
    $currentPlayer = [];
    foreach ($data as $key => $values) {
        if ($data[$key]["status"] == 3) {
            $currentPlayer["color"] = $key;
            $currentPlayer["time"] = $data[$key]["time"];
        }
    }
    $timeLeft = 60 - (time() - intval($currentPlayer["time"]));
    if ($currentPlayer["color"] != $_SESSION["color"])
        $_SESSION["thrown"] = false;
    // if ($timeLeft < 0) $timeLeft = 0;
    $currentPlayer["time"] = $timeLeft;
    $currentPlayer["thisPlayer"] = $_SESSION["color"];
    if ($timeLeft <= 0) {
        changeStatuses($data);
    }
    if (isset($_SESSION["thrown"])) {
        $currentPlayer["thrown"] = $_SESSION["thrown"];
    }
    echo json_encode($currentPlayer);
}
function throwADice()
{
    require("phpConnect.php");
    $connect = @new mysqli($host, $user, $passwd, $dbname);
    mysqli_query($connect, "set names utf8mb4");
    $gameID = $_SESSION["uniqid"];
    $select = "SELECT * FROM pokoje WHERE game_id = '$gameID'";
    $result = $connect->query($select);
    $row = $result->fetch_all(MYSQLI_ASSOC)[0];
    $users = json_decode($row["data"], true);
    if ($users[$_SESSION["color"]]["status"] == "3" && !$_SESSION["thrown"]) {
        $_SESSION["thrown"] = true;
        $number = rand(1, 6);
        $_SESSION["number"] = $number;
        $data = [];
        $data["randomNumber"] = $number;
        $data["color"] = $_SESSION["color"];
        $pawns = $users[$_SESSION["color"]]["pawns"];
        $possibleMoves = [];
        $endingFields = [false, false, false, false];
        for ($i = 0; $i < 4; $i++) {
            if ($pawns[$i] >= 44) {
                $endingFields[$pawns[$i] - 44] = true;
            }
        }
        for ($i = 0; $i < 4; $i++) {
            if ($pawns[$i] < 4) {
                if ($number == 1 || $number == 6) {
                    $possibleMoves[$i] = true;
                } else {
                    $possibleMoves[$i] = false;
                }
            } else if ($pawns[$i] >= 4) {
                if ($pawns[$i] + $number >= 44 && $pawns[$i] + $number < 48) {
                    if ($endingFields[$pawns[$i] + $number - 44] == false) {
                        $possibleMoves[$i] = true;
                    } else {
                        $possibleMoves[$i] = false;
                    }
                } else if ($pawns[$i] + $number >= 48) {
                    $possibleMoves[$i] = false;
                } else {
                    $possibleMoves[$i] = true;
                }
            }
        }
        $data["possibleMoves"] = $possibleMoves;
        echo json_encode($data);
    } else {
        $data = [];
        $data["color"] = $_SESSION["color"];
        $data["randomNumber"] = $_SESSION["number"];
        $data["possibleMoves"] = $_SESSION["possibleMoves"];
        echo json_encode($data);
    }
    $howMuch = 0;
    for ($i = 0; $i < 4; $i++) {
        if ($possibleMoves[$i] == false) {
            $howMuch++;
        } // roboczo, do usuniecia albo zmiany
    }
    if ($howMuch == 4) changeStatuses($users);
    $_SESSION["possibleMoves"] = $possibleMoves;
    // changeStatuses($users);
}
function sendNumber()
{
    $number = ["number" => $_SESSION["number"]];
    require("phpConnect.php");
    $connect = @new mysqli($host, $user, $passwd, $dbname);
    mysqli_query($connect, "set names utf8mb4");
    $gameID = $_SESSION["uniqid"];
    $select = "SELECT * FROM pokoje WHERE game_id = '$gameID'";
    $result = $connect->query($select);
    $row = $result->fetch_all(MYSQLI_ASSOC)[0];
    $users = json_decode($row["data"], true);
    $pawns = $users[$_SESSION["color"]]["pawns"];
    $possibleMoves = $_SESSION["possibleMoves"];
    $number["pawns"] = $pawns;
    $number["possibleMoves"] = $possibleMoves;
    echo json_encode($number);
}
function move()
{
    $numberOfPawn = $_POST["n"];
    $howMuch = $_SESSION["number"];
    $currentGameId = $_SESSION["uniqid"];
    require("phpConnect.php");
    $connect = @new mysqli($host, $user, $passwd, $dbname);
    mysqli_query($connect, "set names utf8mb4");
    $gameID = $_SESSION["uniqid"];
    $select = "SELECT * FROM pokoje WHERE game_id = '$gameID'";
    $result = $connect->query($select);
    $row = $result->fetch_all(MYSQLI_ASSOC)[0];
    $users = json_decode($row["data"], true);
    $currentPosition = $users[$_SESSION["color"]]["pawns"][$numberOfPawn];
    if ($currentPosition >= 0 && $currentPosition < 4) {
        $users[$_SESSION["color"]]["pawns"][$numberOfPawn] = 4;
    } else {
        $users[$_SESSION["color"]]["pawns"][$numberOfPawn] = $users[$_SESSION["color"]]["pawns"][$numberOfPawn] + $howMuch;
    }
    $i = 0;
    if ($_SESSION["color"] == "red") {
        foreach($users as $color => $value){
            if($color == "blue"){
                $bluePawns = $users["blue"]["pawns"];
                for($i=0;$i<4;$i++){
                    if($users[$_SESSION["color"]]["pawns"][$numberOfPawn]>=4&& $users[$_SESSION["color"]]["pawns"][$numberOfPawn]<=23){
                        if($bluePawns[$i]== $users[$_SESSION["color"]]["pawns"][$numberOfPawn]+20){
                            $users["blue"]["pawns"][$i] = $i; 
                        }
                    }
                    else if($users[$_SESSION["color"]]["pawns"][$numberOfPawn] >= 24 && $users[$_SESSION["color"]]["pawns"][$numberOfPawn] <= 43){
                        if ($bluePawns[$i] == $users[$_SESSION["color"]]["pawns"][$numberOfPawn] - 20) {
                            $users["blue"]["pawns"][$i] = $i;
                        }
                    }
                }
            }
            if ($color == "green") {
                $greenPawns = $users["green"]["pawns"];
                for ($i = 0; $i < 4; $i++) {
                    if ($users[$_SESSION["color"]]["pawns"][$numberOfPawn] >= 4 && $users[$_SESSION["color"]]["pawns"][$numberOfPawn] <= 33) {
                        if ($greenPawns[$i] == $users[$_SESSION["color"]]["pawns"][$numberOfPawn] + 10) {
                            $users["green"]["pawns"][$i] = $i;
                        }
                    } else if ($users[$_SESSION["color"]]["pawns"][$numberOfPawn] >= 34 && $users[$_SESSION["color"]]["pawns"][$numberOfPawn] <= 43) {
                        if ($greenPawns[$i] == $users[$_SESSION["color"]]["pawns"][$numberOfPawn] - 30) {
                            $users["green"]["pawns"][$i] = $i;
                        }
                    }
                }
            }
            if ($color == "yellow") {
                $yellowPawns = $users["yellow"]["pawns"];
                for ($i = 0; $i < 4; $i++) {
                    if ($users[$_SESSION["color"]]["pawns"][$numberOfPawn] >= 4 && $users[$_SESSION["color"]]["pawns"][$numberOfPawn] <= 13) {
                        if ($yellowPawns[$i] == $users[$_SESSION["color"]]["pawns"][$numberOfPawn] + 30) {
                            $users["yellow"]["pawns"][$i] = $i;
                        }
                    } else if ($users[$_SESSION["color"]]["pawns"][$numberOfPawn] >= 14 && $users[$_SESSION["color"]]["pawns"][$numberOfPawn] <= 43) {
                        if ($yellowPawns[$i] == $users[$_SESSION["color"]]["pawns"][$numberOfPawn] - 10) {
                            $users["yellow"]["pawns"][$i] = $i;
                        }
                    }
                }
            }
        }
    }
    else if ($_SESSION["color"] == "blue") {
        foreach ($users as $color => $value) {
            if ($color == "red") {
                $redPawns = $users["red"]["pawns"];
                for ($i = 0; $i < 4; $i++) {
                    if ($users[$_SESSION["color"]]["pawns"][$numberOfPawn] >= 4 && $users[$_SESSION["color"]]["pawns"][$numberOfPawn] <= 23) {
                        if ($redPawns[$i] == $users[$_SESSION["color"]]["pawns"][$numberOfPawn] + 20) {
                            $users["red"]["pawns"][$i] = $i;
                        }
                    } else if ($users[$_SESSION["color"]]["pawns"][$numberOfPawn] >= 24 && $users[$_SESSION["color"]]["pawns"][$numberOfPawn] <= 43) {
                        if ($redPawns[$i] == $users[$_SESSION["color"]]["pawns"][$numberOfPawn] - 20) {
                            $users["red"]["pawns"][$i] = $i;
                        }
                    }
                }
            }
            if ($color == "yellow") {
                $greenPawns = $users["yellow"]["pawns"];
                for ($i = 0; $i < 4; $i++) {
                    if ($users[$_SESSION["color"]]["pawns"][$numberOfPawn] >= 4 && $users[$_SESSION["color"]]["pawns"][$numberOfPawn] <= 33) {
                        if ($yellowPawns[$i] == $users[$_SESSION["color"]]["pawns"][$numberOfPawn] + 10) {
                            $users["yellow"]["pawns"][$i] = $i;
                        }
                    } else if ($users[$_SESSION["color"]]["pawns"][$numberOfPawn] >= 34 && $users[$_SESSION["color"]]["pawns"][$numberOfPawn] <= 43) {
                        if ($yellowPawns[$i] == $users[$_SESSION["color"]]["pawns"][$numberOfPawn] - 30) {
                            $users["yellow"]["pawns"][$i] = $i;
                        }
                    }
                }
            }
            if ($color == "green") {
                $greenPawns = $users["green"]["pawns"];
                for ($i = 0; $i < 4; $i++) {
                    if ($users[$_SESSION["color"]]["pawns"][$numberOfPawn] >= 4 && $users[$_SESSION["color"]]["pawns"][$numberOfPawn] <= 13) {
                        if ($greenPawns[$i] == $users[$_SESSION["color"]]["pawns"][$numberOfPawn] + 30) {
                            $users["green"]["pawns"][$i] = $i;
                        }
                    } else if ($users[$_SESSION["color"]]["pawns"][$numberOfPawn] >= 14 && $users[$_SESSION["color"]]["pawns"][$numberOfPawn] <= 43) {
                        if ($greenPawns[$i] == $users[$_SESSION["color"]]["pawns"][$numberOfPawn] - 10) {
                            $users["green"]["pawns"][$i] = $i;
                        }
                    }
                }
            }
        }
    }
    else if ($_SESSION["color"] == "green") {
        foreach ($users as $color => $value) {
            if ($color == "yellow") {
                $yellowPawns = $users["yellow"]["pawns"];
                for ($i = 0; $i < 4; $i++) {
                    if ($users[$_SESSION["color"]]["pawns"][$numberOfPawn] >= 4 && $users[$_SESSION["color"]]["pawns"][$numberOfPawn] <= 23) {
                        if ($yellowPawns[$i] == $users[$_SESSION["color"]]["pawns"][$numberOfPawn] + 20) {
                            $users["yellow"]["pawns"][$i] = $i;
                        }
                    } else if ($users[$_SESSION["color"]]["pawns"][$numberOfPawn] >= 24 && $users[$_SESSION["color"]]["pawns"][$numberOfPawn] <= 43) {
                        if ($yellowPawns[$i] == $users[$_SESSION["color"]]["pawns"][$numberOfPawn] - 20) {
                            $users["yellow"]["pawns"][$i] = $i;
                        }
                    }
                }
            }
            if ($color == "blue") {
                $bluePawns = $users["blue"]["pawns"];
                for ($i = 0; $i < 4; $i++) {
                    if ($users[$_SESSION["color"]]["pawns"][$numberOfPawn] >= 4 && $users[$_SESSION["color"]]["pawns"][$numberOfPawn] <= 33) {
                        if ($bluePawns[$i] == $users[$_SESSION["color"]]["pawns"][$numberOfPawn] + 10) {
                            $users["blue"]["pawns"][$i] = $i;
                        }
                    } else if ($users[$_SESSION["color"]]["pawns"][$numberOfPawn] >= 34 && $users[$_SESSION["color"]]["pawns"][$numberOfPawn] <= 43) {
                        if ($bluePawns[$i] == $users[$_SESSION["color"]]["pawns"][$numberOfPawn] - 30) {
                            $users["blue"]["pawns"][$i] = $i;
                        }
                    }
                }
            }
            if ($color == "red") {
                $redPawns = $users["red"]["pawns"];
                for ($i = 0; $i < 4; $i++) {
                    if ($users[$_SESSION["color"]]["pawns"][$numberOfPawn] >= 4 && $users[$_SESSION["color"]]["pawns"][$numberOfPawn] <= 13) {
                        if ($redPawns[$i] == $users[$_SESSION["color"]]["pawns"][$numberOfPawn] + 30) {
                            $users["red"]["pawns"][$i] = $i;
                        }
                    } else if ($users[$_SESSION["color"]]["pawns"][$numberOfPawn] >= 14 && $users[$_SESSION["color"]]["pawns"][$numberOfPawn] <= 43) {
                        if ($redPawns[$i] == $users[$_SESSION["color"]]["pawns"][$numberOfPawn] - 10) {
                            $users["red"]["pawns"][$i] = $i;
                        }
                    }
                }
            }
        }
    }
    else if ($_SESSION["color"] == "yellow") {
        foreach ($users as $color => $value) {
            if ($color == "green") {
                $greenPawns = $users["green"]["pawns"];
                for ($i = 0; $i < 4; $i++) {
                    if ($users[$_SESSION["color"]]["pawns"][$numberOfPawn] >= 4 && $users[$_SESSION["color"]]["pawns"][$numberOfPawn] <= 23) {
                        if ($greenPawns[$i] == $users[$_SESSION["color"]]["pawns"][$numberOfPawn] + 20) {
                            $users["green"]["pawns"][$i] = $i;
                        }
                    } else if ($users[$_SESSION["color"]]["pawns"][$numberOfPawn] >= 24 && $users[$_SESSION["color"]]["pawns"][$numberOfPawn] <= 43) {
                        if ($greenPawns[$i] == $users[$_SESSION["color"]]["pawns"][$numberOfPawn] - 20) {
                            $users["green"]["pawns"][$i] = $i;
                        }
                    }
                }
            }
            if ($color == "red") {
                $redPawns = $users["red"]["pawns"];
                for ($i = 0; $i < 4; $i++) {
                    if ($users[$_SESSION["color"]]["pawns"][$numberOfPawn] >= 4 && $users[$_SESSION["color"]]["pawns"][$numberOfPawn] <= 33) {
                        if ($redPawns[$i] == $users[$_SESSION["color"]]["pawns"][$numberOfPawn] + 10) {
                            $users["red"]["pawns"][$i] = $i;
                        }
                    } else if ($users[$_SESSION["color"]]["pawns"][$numberOfPawn] >= 34 && $users[$_SESSION["color"]]["pawns"][$numberOfPawn] <= 43) {
                        if ($redPawns[$i] == $users[$_SESSION["color"]]["pawns"][$numberOfPawn] - 30) {
                            $users["red"]["pawns"][$i] = $i;
                        }
                    }
                }
            }
            if ($color == "blue") {
                $bluePawns = $users["blue"]["pawns"];
                for ($i = 0; $i < 4; $i++) {
                    if ($users[$_SESSION["color"]]["pawns"][$numberOfPawn] >= 4 && $users[$_SESSION["color"]]["pawns"][$numberOfPawn] <= 13) {
                        if ($bluePawns[$i] == $users[$_SESSION["color"]]["pawns"][$numberOfPawn] + 30) {
                            $users["blue"]["pawns"][$i] = $i;
                        }
                    } else if ($users[$_SESSION["color"]]["pawns"][$numberOfPawn] >= 14 && $users[$_SESSION["color"]]["pawns"][$numberOfPawn] <= 43) {
                        if ($bluePawns[$i] == $users[$_SESSION["color"]]["pawns"][$numberOfPawn] - 10) {
                            $users["blue"]["pawns"][$i] = $i;
                        }
                    }
                }
            }
        }
    }
    $pawns = $users[$_SESSION["color"]]["pawns"];
    $isRunning = 1;
    $endingFields = [false, false, false, false];
    for ($i = 0; $i < 4; $i++) {
        if ($pawns[$i] >= 44) {
            $endingFields[$pawns[$i] - 44] = true;
        }
    }
    $howMany = 0;
    for ($i = 0; $i < 4; $i++) {
        if ($endingFields[$i]==true) {
            $howMany++;
        }
    }
    if($howMany==4){
        $isRunning = 2;
        $users[$_SESSION["color"]]["is_winner"] = true;
    }
    $_SESSION["number"] = 0;
    $_SESSION["possibleMoves"] = [];
    $_SESSION["thrown"] = false;
    $data = json_encode($users);
    $update = "UPDATE pokoje SET data='$data' is_running='$isRunning' WHERE game_id='$currentGameId'";
    $connect->query($update);
    changeStatuses($users);
}

function sendPawnsInfo()
{
    require("phpConnect.php");
    $connect = @new mysqli($host, $user, $passwd, $dbname);
    mysqli_query($connect, "set names utf8mb4");
    $gameID = $_SESSION["uniqid"];
    $select = "SELECT * FROM pokoje WHERE game_id = '$gameID'";
    $result = $connect->query($select);
    $row = $result->fetch_all(MYSQLI_ASSOC)[0];
    echo json_encode($row);
}
function changeStatuses($data)
{
    $numberOfPlayers = count($data);
    if ($data["red"]["status"] == "3") {
        $data["red"]["status"] = "2";
        $data["blue"]["status"] = "3";
        $data["blue"]["time"] = time();
    } else if ($data["yellow"]["status"] == "3") {
        $data["yellow"]["status"] = "2";
        $data["red"]["status"] = "3";
        $data["red"]["time"] = time();
    } else if ($data["blue"]["status"] == "3") {
        if ($numberOfPlayers == 2) {
            $data["blue"]["status"] = "2";
            $data["red"]["status"] = "3";
            $data["red"]["time"] = time();
        } else {
            $data["blue"]["status"] = "2";
            $data["green"]["status"] = "3";
            $data["green"]["time"] = time();
        }
    } else if ($data["green"]["status"] == "3") {
        if ($numberOfPlayers == 3) {
            $data["green"]["status"] = "2";
            $data["red"]["status"] = "3";
            $data["red"]["time"] = time();
        } else {
            $data["green"]["status"] = "2";
            $data["yellow"]["status"] = "3";
            $data["yellow"]["time"] = time();
        }
    }
    require("phpConnect.php");
    $connect = @new mysqli($host, $user, $passwd, $dbname);
    mysqli_query($connect, "set names utf8mb4");
    $gameID = $_SESSION["uniqid"];
    $data = json_encode($data);
    $update = "UPDATE pokoje SET data='$data' WHERE game_id = '$gameID'";
    $connect->query($update);
}
if (isset($_POST["g"]) && $_POST["g"] == 1) {
    updateTime();
}
if (isset($_POST["g"]) && $_POST["g"] == 2) {
    throwADice();
}
if (isset($_POST["g"]) && $_POST["g"] == 3) {
    sendNumber();
}
if (isset($_POST["g"]) && $_POST["g"] == 4) {
    move();
}
if (isset($_POST["g"]) && $_POST["g"] == 5) {
    sendPawnsInfo();
}
