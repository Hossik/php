<?php
require_once "pdo.php";
require_once "util.php";

session_start();

if (isset($_POST['cancle'])){
    header('location: index.php');
    return;
  }

//Handle the incoming data 
  if ( isset($_POST['first_name']) && isset($_POST['last_name']) && isset($_POST['email']) && 
  isset($_POST['headline']) && isset($_POST['summary']) ) {

    $msg = validateProfile();
    if ( is_string($msg) ){
      $_SESSION['error'] = $msg;
      header("location: edit.php?profile_id=".$_POST["profile_id"]);
      return;
    }
    // Validate position entries if present 
    $msg = validatePos();
    if ( is_string($msg) ){
      $_SESSION['error'] = $msg;
      header('location: edit.php?profile_id='.$_POST['profile_id']);
      return;
    }
    //should validate education 
    $msg = validateEdu();
    if ( is_string($msg) ){
      $_SESSION['error'] = $msg;
      header('location: edit.php?profile_id='.$_POST['profile_id']);
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
 // clear the old position entries 
$stmt = $pdo->prepare("DELETE FROM Position WHERE 
              profile_id=:profile_id");
$stmt->execute(array(":profile_id" => $_POST['profile_id'] ) );

//Insert the position entries
insertPositions($pdo, $_POST['profile_id']);

//clear the old education entries
$stmt = $pdo->prepare("DELETE FROM Education WHERE 
              profile_id=:profile_id");
$stmt->execute(array(":profile_id" => $_POST['profile_id'] ) );
//insert the education entries
insertEducations($pdo, $_POST['profile_id']);
   
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

//load up th profile in question 
 $stmt = $pdo->prepare("SELECT * FROM Profile where profile_id = :xyz
AND user_id = :uid");
$stmt->execute(array(":xyz" => $_GET['profile_id'],
       ':uid' => $_SESSION['user_id']));
$profile = $stmt->fetch(PDO::FETCH_ASSOC);
if ($profile === false ){
  $_SESSION['error'] = "Could not lead profile"."G".$_GET['profile_id']."U".$_SESSION['user_id'];
  header('location: index.php');
  return;
} 
//load up the position and education rows
$positions = loadPos($pdo, $_GET['profile_id']);
$schools = loadEdu($pdo, $_GET['profile_id']);


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
<?php flashMessages() ;
if ( ! isset($_SESSION['user_id']) ) {
  die('please'.'<a'.'  '.'href'.'='."login.php".'>'.'log in'.'</a>');
}
else{
  echo('<a'.' '. 'href'.'='."logout.php".'>'.'Logout'.'</a>');
 }
 ?>
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

$countEdu = 0;
echo('<p>Education: <input type="submit" id="addEdu" value="+">'."\n");
echo('<div id="edu_fields">'."\n");
if ( count($schools) > 0){
foreach( $schools as $school){
    $countEdu++;
    echo('<div id="edu'.$countEdu.'">');
    echo
    '<p>Year: <input type="text" name="edu_year'.$countEdu.'"value="'.$school['year'].'"/>
    <input type="button" value="-" onclick="$(\'#edu'.$countEdu.'\').remove();return false;"></p>
    <p>School: <input type="text" size="80" name="edu_school'.$countEdu.'"class="school"
    value="'.htmlentities($school['name']).'"/>';
    echo "\n</div>\n";
  }
}
echo("</div></p>\n");

$countPos = 0;
echo('<p>Position: <input type="submit" id="addPos" value="+">'."\n");
echo('<div id="position_fields">'."\n");

foreach( $positions as $position){
    $countPos++;
    echo('<div id="position'.$countPos.'">'."\n");
    echo('<p>Year: <input type="text" name="year'.$countPos.'"');
    echo('value="'.$position['year'].'"/>'."\n");
    echo('<input type="button" value="-" ');
    echo('onclick="$(\'#position'.$countPos.'\').remove();return false;">'."\n");
    echo("</p>\n");
    echo('<textarea name="desc'.$countPos.'" rows="8" cols="80">'."\n");
    echo(htmlentities($position['description'])."\n");
    echo("\n</textarea>\n</div>\n");
}
echo("</div></p>\n");
?>

<p>
<input type="submit" value="Save">
<input type="submit" name="cancle" value="cancle">

</p>
</form>
<script>
countPos = <?= $countPos ?>;
countEdu = <?= $countEdu ?>;
// http://stackoverflow.com/questions/17650776/add-remove-html-inside-div-using-javascript

  
    /*window.console && console.log('Requesting JSON'); 
      $.getJSON('positions.php', function(posses){
      window.console && console.log('JSON Received'); 
      window.console && console.log('what' , posses);
      posses.length = countPos
      for (var i = 0; i < countPos; i++) {
        poss = posses[i];
        $('#position_fields').append(
            '<div id="position'+countPos+'"> \
            <p>Year: <input type="text" name="year'+countPos+'" value='+poss['year']+' /> \
            <input type="button" value="-" \
                onclick="$(\'#position'+countPos+'\').remove();return false;"></p> \
            <textarea name="desc'+countPos+'" rows="8" cols="80">'+poss['description']+'</textarea>\
            </div>');
      };
  });*/
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
    
    $('#addEdu').click(function(event){
        // http://api.jquery.com/event.preventdefault/
        event.preventDefault();
        if ( countEdu >= 9 ) {
            alert("Maximum of nine education entries exceeded");
            return;
        }
        countEdu++;
        window.console && console.log("Adding education "+countEdu);
        //Grab some HTML with hot spots and insert into the DOM 
        var source = $("#edu-template").html();
        $('#edu_fields').append(source.replace(/@COUNT@/g,countEdu));

        //add the even handler to the new ones
        $('.school').autocomplete({
          source: "school.php"
        });
      });
      $('.school').autocomplete({
          source: "school.php"
        });

  });
  </script>
  <!-- HTML with Substitution hot spots -->
  <script id="edu-template">
  <div id="edu@COUNT@">
  <p>Year: <input type ="text" name="edu_year@COUNT@" value="" />
  <input type="button" value="-" onclick="$('#edu@COUNT@').remove();return false;"><br>
  <p>School: <input type="text" size="80" name="edu_school@COUNT@" class="school" value="" />
</p>
</div>
</script>
</div>
</body>
</html>