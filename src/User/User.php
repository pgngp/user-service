<?php
namespace User;

require_once (__DIR__ . '/../DbHelper.php');

use PDO;
use DbHelper;

/**
 * This class implements methods that serve the API requests.
 */
class User
{

    private $dbHelper;

    /**
     * Constructor
     */
    public function __construct(DbHelper $dbHelper = NULL)
    {
        $this->dbHelper = $dbHelper ?  : new DbHelper();
    }

    /**
     * Returns users that match the given search criteria.
     */
    public function getUsers($searchStr)
    {
        $query = "select `email`, `phone_number`, `full_name`, `password`, `key`, `account_key`, `metadata` " . "from `test`.`user` " . "where email like '%$searchStr%' or full_name like '%$searchStr%' or metadata like '%$searchStr%' " . "order by `id` desc";
        $pdo = $this->dbHelper->getConnection();
        if ($pdo == NULL) {
            header('HTTP/1.0 500 Internal Server Error');
            return json_encode(array());
        }
        
        $result = $pdo->query($query);
        $numRows = $result->rowCount();
        if ($numRows === 0) {
            header('HTTP/1.0 422 Unprocessable Entity');
            return json_encode(array());
        } elseif ($numRows === 1) {
            $row = $result->fetch(PDO::FETCH_ASSOC);
            return json_encode(array(
                'email' => $row['email'],
                'phone_number' => $row['phone_number'],
                'full_name' => $row['full_name'],
                'key' => $row['key'],
                'account_key' => $row['account_key'],
                'metadata' => $row['metadata']
            ));
        } else {
            $arr = array();
            $i = 0;
            foreach ($result as $row) {
                $arr[$i]['email'] = $row['email'];
                $arr[$i]['phone_number'] = $row['phone_number'];
                $arr[$i]['full_name'] = $row['full_name'];
                $arr[$i]['key'] = $row['key'];
                $arr[$i]['account_key'] = $row['account_key'];
                $arr[$i]['metadata'] = $row['metadata'];
                ++ $i;
            }
            
            return json_encode(array(
                'users' => $arr
            ));
        }
    }

    /**
     * Returns all users.
     */
    public function getAllUsers($argArr)
    {
        $page = $argArr[0];
        if (!is_numeric($page)) {
            echo "Error: Page should be a number\n";
            return;
        }
        $perPage = $argArr[1];
        $offset = ($page - 1) * $perPage;
        
        $query = "select `email`, `phone_number`, `full_name`, `password`, `key`, `account_key`, `metadata` " . 
            "from `test`.`user` " . 
            "order by `id` desc " . 
            "limit $perPage offset $offset";
        $pdo = $this->dbHelper->getConnection();
        if ($pdo == NULL) {
            header('HTTP/1.0 500 Internal Server Error');
            return json_encode(array());
        }
        
        $result = $pdo->query($query);
        $numRows = $result->rowCount();
        if ($numRows === 0) {
            return json_encode(array());
        } elseif ($numRows === 1) {
            $row = $result->fetch(PDO::FETCH_ASSOC);
            return json_encode(array(
                'email' => $row['email'],
                'phone_number' => $row['phone_number'],
                'full_name' => $row['full_name'],
                'key' => $row['key'],
                'account_key' => $row['account_key'],
                'metadata' => $row['metadata']
            ));
        } else {
            $arr = array();
            $i = 0;
            foreach ($result as $row) {
                $arr[$i] = array();
                $arr[$i]['email'] = $row['email'];
                $arr[$i]['phone_number'] = $row['phone_number'];
                $arr[$i]['full_name'] = $row['full_name'];
                $arr[$i]['key'] = $row['key'];
                $arr[$i]['account_key'] = $row['account_key'];
                $arr[$i]['metadata'] = $row['metadata'];
                ++$i;
            }
            
            return json_encode(array(
                'users' => $arr
            ));
        }
    }

    /**
     * Adds the given user to the DB.
     *
     * $sleepInterval is used to specify the interval for which the process to fetch the account key
     * should sleep in case the external service fails.
     *
     * $numTries is the number of tries we make to fetch the account key from the external service.
     */
    public function addUser($inputArr, $sleepInterval = 300, $numTries = 5)
    {
        // Save input args locally
        $email = trim($inputArr['email']);
        $phoneNumber = trim($inputArr['phone_number']);
        $fullName = trim($inputArr['full_name']);
        $key = hash('sha256', $email . $phoneNumber . $fullName);
        $password = hash('sha256', $inputArr['password'] . $key);
        $metadata = trim($inputArr['metadata']);
        
        // Validate input
        $messageArr = array();
        if (empty($email)) {
            $messageArr[] = 'Email is empty';
        }
        if (strlen($email) > 100) {
            $messageArr[] = 'Email length is longer than max 100 chars';
        }
        if (! preg_match('/^.*\@.*\..*$/', $email)) {
            $messageArr[] = 'Invalid email format';
        }
        if (empty($phoneNumber)) {
            $messageArr[] = 'Phone number is empty';
        }
        if (strlen($phoneNumber) > 20) {
            $messageArr[] = 'Phone number is longer than 20 chars';
        }
        if (! preg_match('/^[0-9]+$/', $phoneNumber)) {
            $messageArr[] = 'Invalid phone number format';
        }
        if (strlen($fullName) > 200) {
            $messageArr[] = 'Full name is longer than 200 chars';
        }
        if (empty($inputArr['password'])) {
            $messageArr[] = 'Password is empty';
        }
        if (strlen($password) > 100) {
            $messageArr[] = 'Password is longer than 100 chars';
        }
        if (strlen($key) > 100) {
            $messageArr[] = 'Key is longer than 100 chars';
        }
        if (strlen($metadata) > 2000) {
            $messageArr[] = 'Metadata is longer than 2000 chars';
        }
        if (! empty($messageArr)) {
            return json_encode(array(
                'errors' => $messageArr
            ));
        }
        
        // Insert new user into DB
        $insertStatement = "insert into `test`.`user` " . "(`email`, `phone_number`, `full_name`, `password`, `key`, `account_key`, `metadata`) " . "values ('$email', '$phoneNumber', '$fullName', '$password', '$key', NULL, '$metadata')";
        $pdo = $this->dbHelper->getConnection();
        if ($pdo == NULL) {
            header('HTTP/1.1 500 Internal Server Error');
            return json_encode(array());
        }
        $pdo->exec($insertStatement);
        $errInfo = $pdo->errorInfo();
        if ($errInfo[1] != NULL) {
            header('HTTP/1.1 422 Unprocessable Entity');
            return json_encode(array(
                'errors' => $pdo->errorInfo()
            ));
        }
        
        // Run script to fetch the account key in the background
        $this->setAccountKey($email, $key, $sleepInterval, $numTries);
        
        // Return the new user's info
        header('HTTP/1.1 201 Created');
        return json_encode(array(
            'email' => $email,
            'phone_number' => $phoneNumber,
            'full_name' => $fullName,
            'password' => $password,
            'key' => $key,
            'account_key' => NULL,
            'metadata' => $metadata
        ));
    }

    /**
     * Runs the script to fetch the account key in the background.
     */
    private function setAccountKey($email, $key, $sleepInterval, $numTries)
    {
        $scriptPath = __DIR__ . '/../../scripts/accountKeyFetcher.php';
        exec("php $scriptPath $email $key $sleepInterval $numTries >/dev/null &");	
	}
}
