<?php
namespace API;

require_once (__DIR__ . '/../User/User.php');

use User\User;
use Exception;

/**
 * This class implements a method to reroute API requests.
 */
class API
{

    private $method;

    private $args;

    private $endpoint;

    private $user;

    private $queryStr;

    private $inputArr;
    
    private $argArr;

    /**
     * Constructor
     */
    public function __construct(
        $requestUri, 
        $queryStr = '', 
        $inputArr = array(), 
        $method = 'GET', 
        $argArr = array(),
        User $user = NULL)
    {
        $this->method = $method;
        $this->args = explode('/', rtrim($requestUri, '/'));
        $this->endpoint = array_shift($this->args);
        $this->queryStr = $queryStr;
        $this->inputArr = $inputArr;
        $this->argArr = $argArr;
        $this->user = $user ?  : new User();
    }

    /**
     * Reroutes API requests to the proper method.
     */
    public function processAPI()
    {
        // If this is an invalid endpoint, throw exception
        if ($this->endpoint !== 'users') {
            throw new Exception('Invalid endpoint');
        }
        
        // Reroute to proper destination
        if ($this->method === 'GET' && $this->queryStr != '') {
            return $this->user->getUsers($this->queryStr);
        } elseif ($this->method === 'GET') {
            return $this->user->getAllUsers($this->argArr);
        } elseif ($this->method === 'POST') {
            return $this->user->addUser($this->inputArr);
        } else {
            throw new Exception('Invalid method');
        }
    }
}
