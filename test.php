<?php

include_once('include/persistence.php');

$database = new persistence();
// $database->db_import_dump('persistence/DATABASE.DUMP.SQL');
// echo mysqli_error($database->get_db());

$query_users = array("id"=>1, "name"=>"'". mysqli_real_escape_string($database->get_db(), "test") . "'", "email"=>"");
foreach ($query_users as $key => $value) {
  echo $key . ',';
  echo $value . ',';
}

?>
