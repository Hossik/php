<?php
    session_start();
    require_once "pdo.php";
    require_once "util.php";
    if ( isset($_POST["account"]) && isset($_POST["pw"]) ) {      
            $salt = "XyZzy12*_";
            $check = hash('md5', $salt.$_POST['pw']);
            $stmt = $pdo->prepare('SELECT user_id, name FROM users
            WHERE email = :em AND password = :pw');
            $stmt->execute(array( ':em' => $_POST['account'], ':pw' => $check));
            $row = $stmt->fetch(PDO::FETCH_ASSOC);  
                if ( $row !== false ) {
                    $_SESSION['name'] = $row['name'];
                    $_SESSION['user_id'] = $row['user_id'];
                    // Redirect the browser to index.php
                    header("Location: index.php");
                    return;
                } 
                else {
                    $_SESSION["error"] = "Incorrect password.";
                    header( 'Location: login.php' ) ;
                    return;
                }
           }
?>
<html>
<head>
<title>Hossik's Login Page</title>

</head>
<body>
<div class="container">
<h1>Please Log In</h1>
<?php flashMessages();
 ?>
<form method="POST" action="login.php">
<label for="account">Email</label>
<input type="text" name="account" id="account"><br/>

<label for="id_1723">Password</label>
<input type="password" name="pw" id="id_1723"><br/>

<input type="submit" onclick="return doValidate();" value="Log In">
<a href="index.php">Cancel</a></p>
</form>
<script>
function doValidate() {
    console.log('Validating...');
    try {
        addr = document.getElementById('account').value;
        pw = document.getElementById('id_1723').value;
        console.log("Validating addr="+addr+" pw="+pw);
        if (addr == null || addr == "" || pw == null || pw == "") {
            alert("Both fields must be filled out");
            return false;
        }
        if ( addr.indexOf('@') == -1 ) {
            alert("Invalid email address");
            return false;
        }
        return true;
    } catch(e) {
        return false;
    }
    return false;
}
</script>
</body> 
</html>