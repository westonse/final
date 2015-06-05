<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
	<link rel = "stylesheet" type = "text/css" href = "createAccount.css">
</head>
<?php
include 'secret.php';
if(isset($_POST['username']) && isset($_POST['groupname']) && isset($_POST['password'])){
	//set profile picture in folder if set 
		$uploaddir = '/nfs/stak/students/w/westonse/public_html/newFinal/uploads/';
		$path = $uploaddir . $_FILES['fileToUpload']['name'];
		if (move_uploaded_file($_FILES['fileToUpload']['tmp_name'], $path)) {
			chmod("$path", 0777);
			echo "Picture is valid, and was successfully uploaded.\n";
		}
		else{
			$path = "noPic";
		}
	//set username, groupname, points in user table 
	$mysqli = new mysqli("oniddb.cws.oregonstate.edu","westonse-db",$SQLpass,"westonse-db");
	if($mysqli->connect_errno){
		echo "Failed to connect to mySQL: (" . $mysqli->connect_errno . ")" . $mysqli->connect_error;
	}
	$username = $_POST['username'];  
	$groupname = $_POST['groupname'];
	$password = password_hash($_POST['password'], PASSWORD_DEFAULT);
	$points = 0;
	if (!($stmt = $mysqli->prepare("INSERT INTO  `westonse-db`.`users` (`username` ,`groupname` ,`points`,`picPath`,`password`)VALUES ('$username', '$groupname','$points','$path','$password');"))) {
		echo "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
	}
  
	if (!$stmt->execute()) {
		echo "Execute failed: (" . $mysqli->errno . ") " . $mysqli->error;
	}
	echo "Your account has been created successfully please click <a href = 'pickemLogin.html'> here </a> to login and begin playing!";
}
else if(isset($_POST['username']) && !isset($_POST['groupname'])){
	echo"
	<body>
	Groupname is required please try again
	<form action = 'createAccount.php' method = 'post'>
	<input type='text' id='username' name = 'username'> Enter Username <input type='button' id='check_username' value='Check Availability'> 
	<div id='username_result'></div>  
	<input type ='text'  name = 'groupname'>Enter group name </input>
	<input type = 'submit'>
	</form>";
}
else if(!isset($_POST['username']) && isset($_POST['groupname'])){
	echo"
	<body>
	Username is required please try again
	<form action = 'createAccount.php' method = 'post'>
	<input type='text' id='username' name = 'username'> Enter Username<input type='button' id='check_username' value='Check Availability'> 
	<div id='username_result'></div>  
	<input type ='text'  name = 'groupname'>Enter group name</input>
	<input type = 'submit'>
	</form>";
}
else{
	echo"
	<body>
	<div id = 'container'>
	<div class = 'accountForm'>
	<form enctype='multipart/form-data' action = 'createAccount.php' method = 'post'>
	<input type='text' id='username' name = 'username' required> Enter Username <br> <input type='button' id='check_username' value='Check Availability'> 
	<div id='username_result'></div><br>  
	<input type ='text'  name = 'groupname' required> Enter group name</input><br><br>
	<input type ='password'  name = 'password' required> Create password</input><br><br>
	Select image to upload for profile picture:
	<input type = 'hidden' name = 'MAX_FILE_SIZE' value = '100000000'>
	<input type='file' name='fileToUpload' id='fileToUpload'><br><br>
	<input type = 'submit'>
	</form>
	</div>
	</div>";
}
?>
		
</body>

<script type="text/javascript" src="jquery-2.1.4.js"></script>
	
<script>
$(document).ready(function() {  
  
        //the min chars for username  
        var min_chars = 3;  
  
        //result texts  
        var characters_error = 'Minimum amount of chars is 3';  
        var checking_html = 'Checking...';  
  
        //when button is clicked  
        $('#check_username').click(function(){  
            //run the character number check  
            if($('#username').val().length < min_chars){  
                //if it's bellow the minimum show characters_error text '  
                $('#username_availability_result').html(characters_error);  
            }else{  
                //else show the cheking_text and run the function to check  
                $('#username_result').html(checking_html);  
                check_username();  
            }  
        });  
     
    var timeToDisplay = 20000;

    var slideshow = $('#container');
    var urls = [
       'http://static.foxsports.com/content/fscom/img/2011/09/29/8_20110929225029241_600_400.JPG',
       'http://www.eonline.com/eol_images/Entire_Site/2014513/rs_634x1141-140613051823-634.Michael-Jordan-JR-61314.jpg',
       'http://static.foxsports.com/content/fscom/img/2011/09/29/4_20110929225008233_600_400.JPG',
	   'http://blog.lenovo.com/images/uploads/blog/Rice.jpg',
	   'https://encrypted-tbn2.gstatic.com/images?q=tbn:ANd9GcTs_mbe6eTzMR5zRErV4X6JUhcpB7I69fO_drNS219HpyHkxfh4rg',
	   'https://blog-blogmediainc.netdna-ssl.com/upload/SportsBlogcom/192348/0761574001426956572_filepicker.jpg',
	   'https://encrypted-tbn3.gstatic.com/images?q=tbn:ANd9GcRSYkNYkfi5UakNg47_wsHxobv6skfsUg-Ty5yI1VBkLbgZA8o-vA',
	   'http://tensportsclub.com/wp-content/uploads/2014/08/Babe-Ruth-4.jpg',
	   'https://encrypted-tbn1.gstatic.com/images?q=tbn:ANd9GcT8vMV0b1QneygdJEi_UBguS8YUoWzuzmK4oVbsxqk1FtZ9FDdo2w'
    ];

    var index = 0;
    var transition = function() {
        var url = urls[index];

        slideshow.css('background-image', 'url(' + url + ')');

        index = index + 1;
        if (index > urls.length - 1) {
            index = 0;
        }
    };

    var run = function() {
        transition();
        slideshow.fadeIn('slow', function() {
            setTimeout(function() {
                slideshow.fadeOut('slow', run);
            }, timeToDisplay);
        });
    }

    run();
  });  
  
 
function check_username(){  
  
        //get the username  
        var username = $('#username').val();  
  
        //use ajax to run the check  
        $.post("verifyName.php", { username: username },  
            function(result){  
                
                if(result == 0){  
                    $('#username_result').html('Username Available'); 
                     
                }else{  
                     $('#username_result').html('Username not available please choose a differnet one');  
                }  
        });  
  
}  

</script>
</body>
</html>