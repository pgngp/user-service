### Overview
This program is used to service GET and POST requests to fetch existing users and to create new user, respectively. This program implements REST APIs for this purpose.

### Requirements
This program has been implemented using `PHP 7` and `MySQL 5.7` on `Ubuntu Linux 16.04`.

### Installation
1. Unpack the code into the root web directory. 
2. Create the database user, database, and table using `scripts/createTable.sql`. 
3. Run `composer install` from the root web directory to install `PhpUnit`.
4. Update `conf/settings.ini` with appropriate values for your DB instance.

### Run unit tests
From the root web directory, run the following command to run all the unit tests:
```bash
$ php vendor/bin/phpunit test
```

### Fetch all users
From the client machine, run the following curl command:
```sh
$ curl -H "Content-Type: application/json" -i http://<SERVER_IP>/v1/users
```

When this command is issued, the following sequence takes place:
```
.htaccess (reroutes the API request) 
  -> v1/api.php 
  -> src/API/API.php (processAPI()) 
  -> src/User/User.php (getAllUsers())
  -> fetches data from DB
  -> returns json encoded string containing the users
```

### Fetch users using search criteria
From the client machine, run the following curl command:
```sh
$ curl -H "Content-Type: application/json" -i http://<SERVER_IP>/v1/users?query=<SEARCH_CRITERIA>
```

When this command is issued, the following sequence takes place:
```
.htaccess (reroutes the API request) 
  -> v1/api.php 
  -> src/API/API.php (processAPI()) 
  -> src/User/User.php (getUsers())
  -> fetches data from DB
  -> returns json encoded string containing the users
```

### Add user
From the client machine, run the following curl command:
```sh
$ curl -H "Content-Type: application/json" -X POST -i http://<SERVER_IP>/v1/users -d "{\"email\":\"<EMAIL_ADDR>\", \"phone_number\":\"<PHONE_NUM>\", \"full_name\":\"<FULL_NAME>\", \"password\":\"<PASSWORD>\", \"metadata\":\"<METADATA>\"}"
```

When this command is issued, the following sequence takes place:
```
.htaccess (reroutes the API request) 
  -> v1/api.php 
  -> src/API/API.php (processAPI()) 
  -> src/User/User.php (addUser()) 
  -> inserts record in DB 
  -> calls scripts/accountKeyFetcher.php and runs it in the background (this script is used to fetch account key from external service and update the DB record)
  -> returns json encoded string containing the user that was just added
```

Note: The `accountKeyFetcher.php` script makes 5 attempts to fetch the account key from the external service. After each failed attempt, it sleeps for 5 mins. The values of max number of attempts and sleep interval can be changed in `User.php` (`addUser()`).
