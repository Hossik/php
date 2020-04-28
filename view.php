<?php
require_once "pdo.php";
require_once "util.php";

if ( ! isset($_GET['profile_id']) ) {
  $_SESSION['error'] = "Missing profile_id";
  header('Location: index.php');
  return;
}
$stmt = $pdo->prepare("SELECT * FROM Profile where profile_id = :xyz");
$stmt->execute(array(":xyz" => $_GET['profile_id']));
$row = $stmt->fetch(PDO::FETCH_ASSOC);
if ( $row === false ) {
    $_SESSION['error'] = 'Bad value for profile_id';
    header( 'Location: index.php' ) ;
    return;
}
$na = htmlentities($row['first_name']);
$la = htmlentities($row['last_name']);
$em = htmlentities($row['email']);
$he = htmlentities($row['headline']);
$su = htmlentities($row['summary']);
$profile_id = $row['profile_id'];

$positions = loadPos($pdo,$_GET['profile_id']);
?>
<html>
<head>
<title>Hossik's Profile View</title>

<?php require_once "head.php"; ?>
</head>

<body>
<div class="container">
<h1>Profile information</h1>
<p>First Name: <?= $na ?> </p>
<p>Last Name: <?= $la ?> </p>
<p>Email: <?= $em ?> </p>
<p>Headline: <?= $he ?> </p>
<p>Summary: <?= $su ?> </p>
<?php

$pos = 0;

foreach( $positions as $position){
    $pos++;
    echo('<ul>');
    echo('<li>'.$position['year'].':'.$position['description'].'</li>');
    echo("</ul>\n");
}
?>
<a href="index.php">Done</a>
</div>
<script data-cfasync="false" src="/cdn-cgi/scripts/5c5dd728/cloudflare-static/email-decode.min.js"></script></body>
</html>
