<?php
include 'secret.php';
$mysqli = new mysqli("oniddb.cws.oregonstate.edu","westonse-db",$SQLpass,"westonse-db");
if($mysqli->connect_errno){
	echo "Failed to connect to mySQL: (" . $mysqli->connect_errno . ")" . $mysqli->connect_error;
}
$username =$_POST['username'];  
if (!($stmt = $mysqli->prepare('select username from users where username = "'. $username .'"'))) {
    echo "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
}
  
if (!$stmt->execute()) {
    echo "Execute failed: (" . $mysqli->errno . ") " . $mysqli->error;
}
$result = NULL;
if (!$stmt->bind_result($result)) {
    echo "Binding output parameters failed: (" . $stmt->errno . ") " . $stmt->error;
}
	while($stmt->fetch()) {
		if($result==$username){  
			echo 1;  
		}else{  
			echo 0;  
		}  
	}
?>