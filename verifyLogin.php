<?php
include 'secret.php';
$mysqli = new mysqli("oniddb.cws.oregonstate.edu","westonse-db",$SQLpass,"westonse-db");
if($mysqli->connect_errno){
	echo "Failed to connect to mySQL: (" . $mysqli->connect_errno . ")" . $mysqli->connect_error;
}

$userPassword = $_POST['password'];
$username =$_POST['username'];  
if (!($stmt = $mysqli->prepare('select username, password from users where username = "'. $username .'"'))) {
    echo "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
}
  
if (!$stmt->execute()) {
    echo "Execute failed: (" . $mysqli->errno . ") " . $mysqli->error;
}
$realPassword = NULL;
$realUsername = NULL;
if (!$stmt->bind_result($realUsername,$realPassword)) {
    echo "Binding output parameters failed: (" . $stmt->errno . ") " . $stmt->error;
}
	while($stmt->fetch()) {
		
		if($realUsername==$username && password_verify($userPassword,$realPassword)){  
			echo 1;  
		}else{  
			echo "$realUsername, $realPassword, $userPassword";	
			echo 0;  
		}  
	}

	
?>