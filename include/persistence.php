<?php

/*
 * Persistence Library
 */

class Persistence {

  // Load config
  private $dbserver = C_db_server;
  private $dbname = C_db_name;
  private $dbuser = C_db_user;
  private $dbpassword = C_db_password;
  // Database object
  public $database = null;

  // Constructor
  public function __construct() {
    // Connect to database
    $this->database = new mysqli($this->dbserver, $this->dbuser, $this->dbpassword, $this->dbname);

    // Check connection
    if ($this->database -> connect_errno) {
      $logmessage = "Failed to connect to MySQL: " . $this->database -> connect_error . PHP_EOL;
      #file_put_contents('error.log', $logmessage, FILE_APPEND);
      echo $logmessage;
      exit();
    }
  }

  // Get database object
  public function get_db() {
    return $this->database;
  }

  // Read from database
  public function db_read($query) {
    $queryresult = $this->database -> query($query);

    while($row = mysqli_fetch_assoc($queryresult)) {
      $result[] = $row;
    }
    return $result;
  }

  // Write to database
  public function db_write($query) {
    $this->database -> query($query);
  }

  // Destructor
  public function __destruct() {
    // Close connection
    $this->database -> close();
  }

}
