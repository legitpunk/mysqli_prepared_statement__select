<?php

$mysqli = new mysqli("localhost", "username", "password", "databaseName");
if($mysqli->connect_error) {
  exit('Error connecting to database'); //Should be a message a typical user could understand in production
}
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$mysqli->set_charset("utf8mb4");

$stmt = $mysqli->prepare("SELECT * FROM myTable WHERE name = ? AND age = ?");
$stmt->bind_param("si", $_POST['name'], $_POST['age']);
$stmt->execute();
//fetching result would go here, but will be covered later
$stmt->close();

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
try {
  $mysqli = new mysqli("localhost", "username", "password", "databaseName");
  $mysqli->set_charset("utf8mb4");
} catch(Exception $e) {
  error_log($e->getMessage());
  exit('Error connecting to database'); //Should be a message a typical user could understand
}

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
set_exception_handler(function($e) {
  error_log($e->getMessage());
  exit('Error connecting to database'); //Should be a message a typical user could understand
});
$mysqli = new mysqli("localhost", "username", "password", "databaseName");
$mysqli->set_charset("utf8mb4");

// insert
$stmt = $mysqli->prepare("INSERT INTO myTable (name, age) VALUES (?, ?)");
$stmt->bind_param("si", $_POST['name'], $_POST['age']);
$stmt->execute();
$stmt->close();

// update
$stmt = $mysqli->prepare("UPDATE myTable SET name = ? WHERE id = ?");
$stmt->bind_param("si", $_POST['name'], $_SESSION['id']);
$stmt->execute();
$stmt->close();

// delete
$stmt = $mysqli->prepare("DELETE FROM myTable WHERE id = ?");
$stmt->bind_param("i", $_SESSION['id']);
$stmt->execute();
$stmt->close();

// check if rows affected
$stmt = $mysqli->prepare("UPDATE myTable SET name = ?");
$stmt->bind_param("si", $_POST['name'], $_POST['age']);
$stmt->execute();
if($stmt->affected_rows === 0) exit('No rows updated');
$stmt->close();

// mysqli info
$stmt = $mysqli->prepare("UPDATE myTable SET name = ?");
$stmt->bind_param("si", $_POST['name'], $_POST['age']);
$stmt->execute();
$stmt->close();
echo $mysqli->info;

// get latest primary key inserted
$stmt = $mysqli->prepare("INSERT INTO myTable (name, age) VALUES (?, ?)");
$stmt->bind_param("si", $_POST['name'], $_POST['age']);
$stmt->execute();
echo $mysqli->insert_id;
$stmt->close();

// error catching
try {
  $stmt = $mysqli->prepare("INSERT INTO myTable (name, age) VALUES (?, ?)");
  $stmt->bind_param("si", $_POST['name'], $_POST['age']);
  $stmt->execute();
  $stmt->close();
} catch(Exception $e) {
  if($mysqli->errno === 1062) echo 'Duplicate entry';
}

// get result
$stmt = $mysqli->prepare("SELECT id, name, age FROM myTable WHERE name = ?");
$stmt->bind_param("s", $_POST['name']);
$stmt->execute();
$arr = $stmt->get_result()->fetch_assoc();
if(!$arr) exit('No rows');
var_export($arr);
$stmt->close();

// try catch
try {
  $stmt = $mysqli->prepare("DELETE FROM myTable WHERE id = ?");
  $stmt->bind_param("i", $_SESSION['id']);
  $stmt->execute();
  $stmt->close();

  $stmt = $mysqli->prepare("SELECT id, name, age FROM myTable WHERE name = ?");
  $stmt->bind_param("s", $_POST['name']);
  $stmt->execute();
  $arr = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
  $stmt->close();

  try {
    $mysqli->autocommit(FALSE); //turn on transactions
    $stmt = $mysqli->prepare("INSERT INTO myTable (name, age) VALUES (?, ?)");
    $stmt->bind_param("si", $name, $age);
    $name = 'John';
    $age = 21;
    $stmt->execute();  
    $name = 'Rick';
    $age = 24;
    $stmt->execute();
    $stmt->close();
    $mysqli->autocommit(TRUE); //turn off transactions + commit queued queries
  } catch(Exception $e) {
    $mysqli->rollback(); //remove all queries from queue if error (undo)
    throw $e;
  }  
} catch (Exception $e) {
  error_log($e);
  exit('Error message for user to understand');
}