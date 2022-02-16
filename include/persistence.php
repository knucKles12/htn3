<?php

/*
 * Persistence Library
 */

class persistence {

  // Variables
  private $dbserver = "%dbserver%";
  private $dbuser = "%dbuser%";
  private $dbpassword = "%dbpassword%";
  private $dbname = "%dbname%";
  // private $dbserver = C_db_server;
  // private $dbname = C_db_name;
  // private $dbuser = C_db_user;
  // private $dbpassword = C_db_password;
  // Database object
  private $database = null;

  /*
  * CONSTRUCTOR
  */
  public function __construct() {
    // Connect to database
    $this->database = new mysqli($this->dbserver, $this->dbuser, $this->dbpassword, $this->dbname);

    // Check connection
    if ($this->database -> connect_errno) {
      $logmessage = "<h3>Failed to connect to MySQL: " . $this->database -> connect_error . PHP_EOL . "</h3></br>";
      #file_put_contents('error.log', $logmessage, FILE_APPEND);
      echo $logmessage;
      exit();
    }
  }

  /*
  * GET DATABASE OBJECT
  */
  public function get_db() {
    return $this->database;
  }

  /*
  * IMPORT SQL DUMP FILE
  */
  public function db_import_dump($dump) {
    $tempLine = '';
    // Read in the full file
    $lines = file($dump);

    // Loop through each line
    foreach ($lines as $line) {

      // Skip it if it's a comment
      if (substr($line, 0, 2) == '--' || $line == '')
        continue;

        // Add this line to the current segment
        $tempLine .= $line;
        // If its semicolon at the end, so that is the end of one query
        if (substr(trim($line), -1, 1) == ';')  {
          // Perform the query
          $this->database -> query($tempLine) or print("Error in " . $tempLine .":". mysqli_error($this->database));
          // Reset temp variable to empty
          $tempLine = '';
        }
      }
    }

  /*
  * READ SETTINGS FROM DATABASE
  */
  public function db_readsettings($query) {
    $queryresult = $this->database -> query($query);

    while ($row = $queryresult->fetch_assoc()) {
        $result[$row["name"]] = $row["value"];
    }

    return $result;
  }

  /*
  * SEND GENERIC QUERY TO DATABASE
  */
  public function db_query($query) {
    return $this->database -> query($query);
  }

  // UNFINISHED //
  public function db_insert_into($table, $data) {
    $keys = '';
    $values = '';
    foreach ($data as $key => $value) {
      $keys .= $key . ',';
      $values .= $value . ',';
    }
    $query = 'INSERT INTO ' . $table . ' (' . substr($keys, 0, -1) . ') VALUES (' . substr($values, 0, -1) . ');';
    echo $query;
    return $this->database -> query($query);
  }

  /*
  * DESTRUCTOR
  */
  public function __destruct() {
    // Close connection
    $this->database -> close();
  }

}
