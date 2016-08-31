CEATE DATABASE spin_results;
CREATE TABLE Player
(
PlayerID int(32) NOT NULL PRIMARY KEY,
Name varchar(255) NOT NULL,
Credits int(11) DEFAULT 0,
lifetime_spins bigint(20) DEFAULT 0,
salt binary(64)
);
<?php
$player_id = (int)$_POST['id'];
$credits_won = (int)$_POST['coins_won']);    // won 150
$credits_bet = (int)$_POST['coin_bet']);     // bet 250
//retrieve salt value from client

$db = new mysqli("localhost", "user", "password", "spin_results");
if ($db->connect_errno){
    die;
}
$sql = "SELECT Name FROM Player WHERE PlayerID = $player_id";
if (!$result = $db->query($sql)){
    echo $db->errno;
    die;
}
if ($result->num_rows === 0){
    echo "Player not found\n";
    die;
}

$player = $result->fetch_assoc();
$salt = hash('md5', $_POST['hash'] . $player['Name']);
$sql = "SELECT * FROM Player WHERE PlayerID = $player_id AND salt = $salt";
$result = $db->query($sql);
if ($result->num_rows === 0){
    echo "please try your request again\n";
    die;
}
$player = $result->fetch_assoc();
//validate coins won and bet
if (($credits_won || $credits_bet) < 0){
    echo "invalid data received\n";
    die;
}
$credits_update = $credits_won - $credits_bet;
$availableCredits = (int) $player['Credits'];
if ($availableCredits < 0){
    echo "invalid spin\n";
    die;
}
//unique player found with id with no spoof
$sql = "UPDATE Player SET lifetime_spins = lifetime_spins + 1, Credits = Credits + $credits_update WHERE PlayerID = $player_id";
$db->query($sql);
if ($db->affected_rows != 1){
    echo "there was a problem processing your request\n";
    die;
}

$sql = "SELECT * FROM Player WHERE PlayerID = $player_id";
$result = $db->query($sql);
$player = $result->fetch_assoc();

//player updated .. generate jSON
//JSON  Player ID, Name, Credits, Lifetime Spins, LifeTime Average Return
$credits = (int) $player['Credits'];
$lifetime_spins = (int) $player['lifetime_spins'];
$lifetime_average = $credits/$lifetime_spins;
$data = array($player_id, htmlentities($player['Name']), $credits, $lifetime_spins, $lifetime_average); 

echo json_encode($data);
?>
