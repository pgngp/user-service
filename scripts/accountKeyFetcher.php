<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once(__DIR__ . '/../src/DbHelper.php');

// Make sure the required number of args are passed
if (count($argv) < 5) {
	echo "Need 5 arguments: email, key, sleep interval, and number of tries\n";
	exit;
}

// Save args in local variables
$email = $argv[1];
$key = $argv[2];
$sleepInterval = $argv[3];
$numTries = $argv[4];

// Validate input
if (empty($email) || empty($key) || empty($sleepInterval) || empty($numTries)) {
	echo "One of the input arg is empty\n";
	exit;
}

// Run command to fetch the account key. If the command fails, sleep for a while and try again.
$cmd = "curl -H 'Content-Type: application/json' -X POST https://account-key-service.herokuapp.com/v1/account -d '{\"email\":\"$email\",\"key\":\"$key\"}'";
$output = '';
$returnCode = 1;
while ($returnCode != 0 && $numTries > 0) {
	exec("$cmd 2>/dev/null", $output, $returnCode);
	if ($returnCode == 0) {
		break;
	}
	sleep($sleepInterval); // Sleep and try again
	--$numTries;
}

// If max number of tries has been reached, exit.
if ($numTries <= 0) {
	echo "Max number of times reached in trying to fetch the account key\n";
	exit;
}

// Update the 'user' table with the account key for the given email
$arr = json_decode($output[0], true);
$email = $arr['email'];
$accountKey = $arr['account_key'];
$dbHelper = new DbHelper();
$pdo = $dbHelper->getConnection();
if ($pdo == NULL) {
	echo "Error creating DB connection\n";
	exit;
}
$updateStatement = "update `test`.`user` " . 
	"set `account_key` = '$accountKey' " . 
	"where `email` = '$email'";
$pdo->exec($updateStatement);
$errInfo = $pdo->errorInfo();
if ($errInfo[1] != NULL) {
	echo "Error updating User table: {$errInfo[2]}\n";
	exit;
}
echo "Updated User table with accountKey '$accountKey' for email '$email'\n";

exit;
