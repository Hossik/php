<?php
require_once "pdo.php";
require_once "util.php";

session_start();

if ( ! isset($_SESSION['user_id']) ) {
    die('please'.'<a'.'  '.'href'.'='."login.php".'>'.'log in'.'</a>');
  }
  else{
    echo('<a'.' '. 'href'.'='."logout.php".'>'.'Logout'.'</a>');
   }
if (isset($_POST['cancle'])){
    header('location: index.php');
    return;
  }

  if ( isset($_POST['first_name']) && isset($_POST['last_name']) && isset($_POST['email']) && 
  isset($_POST['headline']) && isset($_POST['summary']) ) {
    $msg = validateProfile();
    if ( is_string($msg) ){
      $_SESSION['error'] = $msg;
      header('location: edit.php?profile_id='.$_GET['profile_id']);
      return;
    }
    $msg = validatePos();
    if ( is_string($msg) ){
      $_SESSION['error'] = $msg;
      header('location: edit.php?profile_id='.$_GET['profile_id']);
      return;
    }

$stmt = $pdo->prepare('UPDATE Profile SET first_name = :fn,
last_name = :ln , email = :em, headline = :he, summary =:su
WHERE profile_id = :profile_id AND user_id=:uid');
$stmt->execute(array(
        ':profile_id' => $_POST['profile_id'],
        ':uid' => $_SESSION['user_id'],
        ':fn' => $_POST['first_name'],
        ':ln' => $_POST['last_name'],
        ':em' => $_POST['email'],
        ':he' => $_POST['headline'],
        ':su' => $_POST['summary'])
    );

    $stmt = $pdo->prepare('DELETE FROM Position
    WHERE profile_id = :profile_id');
    $stmt->execute(array( ':profile_id' => $_POST['profile_id']));

    
    $rank = 1;
      for($i=1; $i<=9; $i++) {
          if ( ! isset($_POST['year'.$i]) ) continue;
          if ( ! isset($_POST['desc'.$i]) ) continue;
          $year = $_POST['year'.$i];
          $desc = $_POST['desc'.$i];
        
          $stmt = $pdo->prepare('INSERT INTO Position 
          (profile_id, rank, year, description) 
                VALUES ( :profile_id, :rank, :year, :desc)');
          $stmt->execute(array(
            ':profile_id' => $_POST['profile_id'],
            ':rank' => $rank,
            ':year' => $year,
            ':desc' => $desc)
          );
          $rank++;
        }

    $_SESSION['success'] = 'Record updated';
    header( 'Location: index.php' ) ;
    return;
    }


// Guardian: Make sure that user_id is present
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

flashMessages();

$na = htmlentities($row['first_name']);
$la = htmlentities($row['last_name']);
$em = htmlentities($row['email']);
$he = htmlentities($row['headline']);
$su = htmlentities($row['summary']);
$profile_id = $row['profile_id'];
   
$positions = loadPos($pdo,$_GET['profile_id']);
?>
<!DOCTYPE html>
<html>
<head>
<title>Hossik's Adding Profile add</title>
<?php require_once "head.php"; ?>
</head>
<body>
<div class="container">
<h1>Editing Profile for <?= htmlentities($_SESSION['name']); ?></h1>
<?php flashMessages() ?>
<form method="post" action="edit.php">
<input type="hidden" name="profile_id" value="<?= htmlentities($_GET['profile_id']); ?>"
/>
<p>First Name:
<input type="text" name="first_name" value="<?= $na ?>"> </p>
<p>Last Name:
<input type="text" name="last_name" value="<?= $la ?>"> </p>
<p>Email:
<input type="text" name="email" value="<?= $em ?>"> </p>
<p>Headline:
<input type="text" name="headline" value="<?= $he ?>"></p>
<p>Summary:<br>
<input name="summary" rows="8" cols="80" value="<?= $su ?>">
</p>
<?php

$pos = 0;
echo('<p>Position: <input type="submit" id="addPos" value="+">'."\n");
echo('<div id="position_fields">'."\n");
foreach( $positions as $position){
    $pos++;
    echo('<div id="position'.$pos.'">'."\n");
    echo('<p>Year: <input type="text" name="year'.$pos.'"');
    echo('value="'.$position['year'].'"/>'."\n");
    echo('<input type="button" value="-" ');
    echo('onclick="$(\'#position'.$pos.'\').remove();return false;">'."\n");
    echo("</p>\n");
    echo('<textarea name="desc'.$pos.'" rows="8" cols="80">'."\n");
    echo(htmlentities($position['description'])."\n");
    echo("\n</textarea>\n</div>\n");
}
echo("</div></p>\n");
?>

<p>
<input type="submit" value="Save"/></p>
<input type="submit" name="cancle" value="cancle">
</p>
</form>
<script>
countPos = <?= $pos ?>;
// http://stackoverflow.com/questions/17650776/add-remove-html-inside-div-using-javascript
$(document).ready(function(){
    window.console && console.log('Document ready called');
    $('#addPos').click(function(event){
        // http://api.jquery.com/event.preventdefault/
        event.preventDefault();
        if ( countPos >= 9 ) {
            alert("Maximum of nine position entries exceeded");
            return;
        }
        countPos++;
        window.console && console.log("Adding position "+countPos);
        $('#position_fields').append(
            '<div id="position'+countPos+'"> \
            <p>Year: <input type="text" name="year'+countPos+'" value="" /> \
            <input type="button" value="-" \
                onclick="$(\'#position'+countPos+'\').remove();return false;"></p> \
            <textarea name="desc'+countPos+'" rows="8" cols="80"></textarea>\
            </div>');
    });
});
</script>
</div>
</body>
</html>
