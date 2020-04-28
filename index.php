<?php
require_once "pdo.php";
require_once "util.php";
session_start();

if (isset($_SESSION["user_id"]) ) {
    $_SESSION['session'] = $_SESSION['user_id']  ;
}
if (isset($_SESSION["user_id"]) ) {
    $_SESSION['edit'] = $_SESSION['user_id']  ;
}
?>
<html>
<head>
<title>Hossik's Automobile Tracker</title>
<?php require_once "head.php"; ?>
</head>
<body>
<div class="container">
<h1>Hossik's Resume Registry</h1>
<?php
flashMessages();

if ( ! isset($_SESSION['user_id']) ) {
    echo('<a'.'  '.'href'.'='."login.php".'>'.'please'." ".'log in'.'</a>');
  }
  else{
   echo('<a'.' '. 'href'.'='."logout.php".'>'.'Logout'.'</a>');
  }

echo('<table border="1">'."\n");
$stmt = $pdo->query("SELECT first_name, headline, profile_id FROM Profile ");

    echo "<tr><td>";
    echo 'Name' ;
    echo("</td><td>");
    echo 'Headline';
    echo("</td><td>");
    if (isset($_SESSION['user_id']) ){
    echo "Action";}
    echo("</td></tr>");
    while ( $row = $stmt->fetch(PDO::FETCH_ASSOC) ) {
    $name = htmlentities($row['first_name']);
    echo "<tr><td>";
    echo('<a href="view.php?profile_id='.$row['profile_id'].'">'.$name.'</a>');
    echo("</td><td>");
    echo(htmlentities($row['headline']));
    echo("</td><td>");
    if (isset($_SESSION['user_id']) ){
    echo('<a href="edit.php?profile_id='.$row['profile_id'].'">Edit</a> / ');
    echo('<a href="delete.php?profile_id='.$row['profile_id'].'">Delete</a>');}
    echo("</td></tr>\n");
}
?>
</table>
<a href="add.php">Add New Entry</a>
</html>