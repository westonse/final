<?php
session_start();
include 'secret.php';
if(isset($_GET['action']) && $_GET['action'] == 'end'){                      
	$_SESSION = array();
	session_destroy();
	echo '<meta http-equiv="refresh" content="0,URL=http://web.engr.oregonstate.edu/~westonse/newFinal/pickemLogin.html" />';
	die();
}
if(session_status() == PHP_SESSION_ACTIVE){
//set session attributes upon login 
if(!isset($_SESSION['name'])){
		$_SESSION['name'] = $_GET['name'];
}

if($_SESSION['name']!=null){
	$mysqli = new mysqli("oniddb.cws.oregonstate.edu","westonse-db",$SQLpass,"westonse-db");
	if($mysqli->connect_errno){
		echo "Failed to connect to mySQL: (" . $mysqli->connect_errno . ")" . $mysqli->connect_error;

	}
	$username = $_SESSION['name'];
	if (!($stmt = $mysqli->prepare('select username,groupname,points from users where username = "'. $username .'"'))) {
		echo "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
	}
  
	if (!$stmt->execute()) {
		echo "Execute failed: (" . $mysqli->errno . ") " . $mysqli->error;
	}
	$User = NULL;
	$group = NULL;
	$points = NULL;
	if (!$stmt->bind_result($User,$group,$points)) {
		echo "Binding output parameters failed: (" . $stmt->errno . ") " . $stmt->error;
	}
	while($stmt->fetch()) {
		echo "Hi $User your group is $group and your points are $points. Click <a href = 'http://web.engr.oregonstate.edu/~westonse/newFinal/home.php?action=end'> here </a> to logout ";
	}
	$_SESSION['points'] = $points;
	$_SESSION['group'] = $group;
$stmt->close();
}
else{
	echo '<meta http-equiv="refresh" content="0,URL=http://web.engr.oregonstate.edu/~westonse/newFinal/pickemLogin.html">';
}
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
	<link rel = "stylesheet" type = "text/css" href = "home.css">

</head>

<body>
<div id = "container">
	<div class = "Standings">
	<table id ="standingsTable" style = "color: rgba(255,255,255,1)">
		<thead>
		<caption style = "color: rgba(255,255,255,1)"> <?php echo "STANDINGS FOR $_SESSION[group]"; ?> </caption>
		    <th> User <th> Pic <th> Points 
		</thead>
		<tbody>
		<?php 	$mysqli = new mysqli("oniddb.cws.oregonstate.edu","westonse-db",$SQLpass,"westonse-db");
				if($mysqli->connect_errno){
					echo "Failed to connect to mySQL: (" . $mysqli->connect_errno . ")" . $mysqli->connect_error;
				}
				if (!($stmt = $mysqli->prepare("select username,points,picPath from users where groupname = '$group' order by points desc"))) {
					echo "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
				}
				if (!$stmt->execute()) {
					echo "Execute failed: (" . $mysqli->errno . ") " . $mysqli->error;
				}
	$User = NULL;
	$groupUsers = [];
	$points = NULL;
	$picPath = NULL;
	if (!$stmt->bind_result($User,$points,$picPath)) {
		echo "Binding output parameters failed: (" . $stmt->errno . ") " . $stmt->error;
	}
	//display standings table
	while($stmt->fetch()) {
			
			array_push($groupUsers,$User);
			if($User == $_SESSION['name']){
				echo '<tr><td style = "background-color: yellow; color: black">'.$User;
			}
			else{
				echo '<tr><td>'.$User;
			}
			if($picPath!="noPic"){
				$imgPath = str_replace('/nfs/stak/students/w/westonse/public_html/newFinal/uploads','uploads',$picPath);
				echo '<td>'."<img src = '$imgPath' height = '50' width = '50'></img>";
			}
			else{
				echo '<td>'."<img src = 'http://www.engraversnetwork.com/files/placeholder.jpg' height = '50' width = '50'></img>";
			}
			if($User == $_SESSION['name']){
				echo '<td style = "background-color: yellow; color: black">'.$points;
			}
			else{
				echo '<td>' . $points;
			}
			
	}?>
		</tbody>
	</table>
	</div>
	<div class = 'searchForm'> <p style = "text-align: center"> SEARCH FOR TODAY'S GAMES </p>
		<form action = 'http://web.engr.oregonstate.edu/~westonse/newFinal/home.php' method = 'post'>
		<select name = 'searchSport'>
			<option value='MLB'>MLB</option>
			<option value='NBA'>NBA</option>
			<option value='NFL'>NFL</option>
		</select>
	<button type='submit'>Search</button></form>
	<?php 
	//if user is searching for today's games fetch according results from sportsradar API 
	if(isset($_POST['searchSport'])){
	$today = getdate();
	
	$year = $today['year'];
	$day = $today['mday'];
	$month = $today['mon'];
		//if user searches for NBA games fetch results and display
		if($_POST['searchSport'] == 'NBA'){
			$json_url = "http://api.sportradar.us/nba-t3/games/$year/$month/$day/schedule.json?api_key=9a9z8upxs364nsgz7wp9sw8g";
			$json = file_get_contents($json_url);
			$data = json_decode($json, TRUE);
			$games = $data['games'];
				foreach($games as $value){
				$identity = $value['id'];
				$homeTeam = $value['home']['name'];
				$awayTeam = $value['away']['name'];
				$gameTime = strtotime($value['scheduled']);
				$gameTitle = $homeTeam . " Vs " . $awayTeam . " Scheduled for: $gameTime";
				$urlTitle = urlencode($gameTitle);
				echo "<div class = 'searchGame'> $gameTitle </div><br>";
				echo "<form action = 'http://web.engr.oregonstate.edu/~westonse/newFinal/home.php?addGame=$identity&addTitle=$urlTitle&home=$homeTeam&away=$awayTeam'  method = 'post'> <button type='submit'>Add</button> </form>"; 
			}
		}
		//if user searches for MLB games fetch results and display
		if($_POST['searchSport'] == 'MLB'){
			$json_url = "http://api.sportradar.us/mlb-t5/games/$year/$month/$day/schedule.json?api_key=ancyztxdzs6tp82vnecjf3b5";
			$json = file_get_contents($json_url);
			$data = json_decode($json, TRUE);
			$games = $data['league']['games'];
			foreach($games as $value){
				$identity = $value['id'];
				$homeTeam = $value['home']['name'];
				$awayTeam = $value['away']['name'];
				$gameTime = $value['scheduled'];
				$gameTitle = $homeTeam . " Vs " . $awayTeam . " Scheduled for: $gameTime";
				$urlTitle = urlencode($gameTitle);
				echo "<div class = 'searchGame'> $gameTitle </div><br>";
				echo "<form action = 'http://web.engr.oregonstate.edu/~westonse/newFinal/home.php?addGame=$identity&addTitle=$urlTitle&home=$homeTeam&away=$awayTeam'  method = 'post'> <button type='submit'>Add</button> </form>"; 
			}
			
		}
		//if user searches for NFL games fetch results and display
		if($_POST['searchSport'] == 'NFL'){
			$json_url = "http://api.sportradar.us/nfl-t1/2015/REG/1/schedule.json?api_key=j346whw2jbfaadtsvjnwtwnu";
			$json = file_get_contents($json_url);
			$data = json_decode($json, TRUE);
			$games = $data['games'];
			foreach($games as $value){
				$identity = $value['id'];
				$homeTeam = $value['home'];
				$awayTeam = $value['away'];
				$gameTime = $value['scheduled'];
				$gameTitle = $homeTeam . " Vs " . $awayTeam . " Scheduled for: $gameTime";
				$urlTitle = urlencode($gameTitle);
				echo "<div class = 'searchGame'> $gameTitle </div><br>";
				echo "<form action = 'http://web.engr.oregonstate.edu/~westonse/newFinal/home.php?addGame=$identity&addTitle=$urlTitle&home=$homeTeam&away=$awayTeam'  method = 'post'> <button type='submit'>Add</button> </form>"; 
			}
		}
	}
	?>
	</div>
		<?php 	
				//if a game has been added update the database 
				if(isset($_GET['addGame'])){
					$ID = $_GET['addGame'];
					$title = urldecode($_GET['addTitle']);
					$home = $_GET['home'];
					$away = $_GET['away'];
					$NBA_url = "http://api.sportradar.us/nba-t3/games/$ID/boxscore.json?api_key=9a9z8upxs364nsgz7wp9sw8g";
					$NFL_url = "http://api.sportradar.us/nfl-t1/2015/REG/1/$away/$home/boxscore.json?api_key=j346whw2jbfaadtsvjnwtwnu";
					$MLB_url = "http://api.sportradar.us/mlb-t5/games/$ID/boxscore.json?api_key=ancyztxdzs6tp82vnecjf3b5";
					$NBA_json = file_get_contents($NBA_url);
					$NFL_json = file_get_contents($NFL_url);
					$MLB_json = file_get_contents($MLB_url);
					$NBA_data = json_decode($NBA_json, TRUE);
					$MLB_data = json_decode($MLB_json, TRUE);
					$NFL_data = json_decode($NFL_json, TRUE);
					$NBA_status = $NFL_data['status'];
					$MLB_status = $MLB_data['game']['status'];
					$NFL_status = $NBA_data['status'];
					$homeScore = $NBA_data['home']['points'];
					$awayScore = $NBA_data['away']['points']; 
					$winner = $homeScore>$awayScore; 
						//if the game is over or already in the group it cant be added
					if($homeScore!=NULL){
						echo "game has been closed or is in progress and cannot be added";
					}
					else if($MLB_status=='closed' || $MLB_status=='inprogress' ){
						echo "game has been closed or is in progress and cannot be added";
					}
					else if($NFL_status=='closed' || $MLB_status=='inprogress'){
						echo "game has been closed or is in progress and cannot be added";
					}
					else{
						//check if game has already been added 
						$mysqli = new mysqli("oniddb.cws.oregonstate.edu","westonse-db",$SQLpass,"westonse-db");
						if($mysqli->connect_errno){
							echo "Failed to connect to mySQL: (" . $mysqli->connect_errno . ")" . $mysqli->connect_error;
						}
						if (!($stmt = $mysqli->prepare("select id from games where groupname = '$_SESSION[group]'"))) {
							echo "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
						}
						if (!$stmt->execute()) {
							echo "Execute failed: (" . $mysqli->errno . ") " . $mysqli->error;
						}
						$identity = NULL;
						if (!$stmt->bind_result($identity)) {
							echo "Binding output parameters failed: (" . $stmt->errno . ") " . $stmt->error;
						}
						while($stmt->fetch()){
							$identity = $identity;
						}					
						if($ID!=$identity){
							$mysqli = new mysqli("oniddb.cws.oregonstate.edu","westonse-db",$SQLpass,"westonse-db");
							if($mysqli->connect_errno){
								echo "Failed to connect to mySQL: (" . $mysqli->connect_errno . ")" . $mysqli->connect_error;
							}
							if (!($stmt = $mysqli->prepare("INSERT INTO  `westonse-db`.`games` (`id` ,`title` ,`groupname`,`home`,`away`,`winner`)VALUES ('$ID', '$title','$group','$home','$away','Scheduled');"))) {
								echo "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
							}
							if (!$stmt->execute()) {
								echo "Execute failed: (" . $mysqli->errno . ") " . $mysqli->error;
							}
							foreach($groupUsers as $value){
								$mysqli = new mysqli("oniddb.cws.oregonstate.edu","westonse-db",$SQLpass,"westonse-db");
								if($mysqli->connect_errno){
									echo "Failed to connect to mySQL: (" . $mysqli->connect_errno . ")" . $mysqli->connect_error;
								}
								if (!($stmt = $mysqli->prepare("INSERT INTO  `westonse-db`.`selections` (`username` ,`id`,`home` ,`away`,`selection`) VALUES ('$value', '$ID','$home','$away','$home');"))) {
									echo "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
								}
								if (!$stmt->execute()) {
									echo "Execute failed: (" . $mysqli->errno . ") " . $mysqli->error;
								}
							}
						}
					}	
				}

		?>
	<div class = "gamesToPlay"> <p style = "text-align: center">MAKE YOUR PICKS</p>
	<?php
				//Fetch games in group
   				$mysqli = new mysqli("oniddb.cws.oregonstate.edu","westonse-db",$SQLpass,"westonse-db");
				if($mysqli->connect_errno){
					echo "Failed to connect to mySQL: (" . $mysqli->connect_errno . ")" . $mysqli->connect_error;
				}
				if (!($stmt = $mysqli->prepare("select id, home, away, selection from selections where username = '$_SESSION[name]'"))) {
					echo "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
				}
				if (!$stmt->execute()) {
					echo "Execute failed: (" . $mysqli->errno . ") " . $mysqli->error;
				}
				$id = NULL;
				$selection = NULL;
				$home = NULL;
				$away = NULL;
				if (!$stmt->bind_result($id,$home,$away,$selection)) {
					echo "Binding output parameters failed: (" . $stmt->errno . ") " . $stmt->error;
				}
				$statusChange = 0;
				$closedGames = [];
				$points = $_SESSION['points'];
				while($stmt->fetch()) {
				/*CHECK FOR GAME RESULTS*/
					$NBA_url = "http://api.sportradar.us/nba-t3/games/$id/boxscore.json?api_key=9a9z8upxs364nsgz7wp9sw8g";
					$NFL_url = "http://api.sportradar.us/nfl-t1/2015/REG/1/$away/$home/boxscore.json?api_key=j346whw2jbfaadtsvjnwtwnu";
					$MLB_url = "http://api.sportradar.us/mlb-t5/games/$id/boxscore.json?api_key=ancyztxdzs6tp82vnecjf3b5";
					$NBA_json = file_get_contents($NBA_url);
					$NFL_json = file_get_contents($NFL_url);
					$MLB_json = file_get_contents($MLB_url);
					$NBA_data = json_decode($NBA_json, TRUE);
					$MLB_data = json_decode($MLB_json, TRUE);
					$NFL_data = json_decode($NFL_json, TRUE);
					$NBA_status = $NFL_data['status'];
					$MLB_status = $MLB_data['game']['status'];
					$NFL_status = $NBA_data['status'];
					$homeScore = $NBA_data['home']['points'];
					$awayScore = $NBA_data['away']['points']; 
					$winner = $homeScore>$awayScore; 
					//if the game is over award points to user
					if($homeScore!=NULL){
							$homeScore = $NBA_data['home']['points'];
							$awayScore = $NBA_data['away']['points']; 
							$winner = $homeScore>$awayScore; 
							array_push($closedGames,$id);
							echo "Game closed, points updated ";
							$statusChange = 1;
							if($winner==true && $selection==$home){
								$points = $points + 1;
							}
							if($winner==false && $selection==$away){
								$points = $points + 1;
							}
						
						/*SHOW BOX SCORE UPDATE DATABASE TELL USER TO REFRESH TO SEE POINT CHANGE*/
							echo "$home: '$homeScore' $away: '$awayScore'. Your pick was $selection. Refresh page to be awarded points.";
					}
					
					else if($MLB_status=='closed'){
						$homeScore = $MLB_data['game']['home']['runs'];
						$awayScore = $MLB_data['game']['away']['runs'];
						$winner = $homeScore>$awayScore;
						array_push($closedGames,$id);
						echo "Game closed, points updated ";
						$statusChange = 1;
						if($winner==true && $selection==$home){
							$points = $points + 1;
						}
						if($winner==false && $selection==$away){
							$points = $points + 1;
						}
						/*SHOW BOX SCORE UPDATE DATABASE TELL USER TO REFRESH TO SEE POINT CHANGE*/
						echo "$home: $homeScore $away: $awayScore. Your pick was $selection. Refresh page to be awarded points. <br>";
						
					}
					else if($NFL_status=='closed'){
						$homeScore = $NFL_data['home']['points'];
						$awayScore = $NFL_data['away']['points'];
						$winner = $homeScore>$awayScore;
						array_push($closedGames,$id);
						echo "Game closed, points updated ";
						$statusChange = 1;
						if($winner==true && $selection==$home){
							$points = $points + 1;
						}
						if($winner==false && $selection==$away){
							$points = $points + 1;
						}
						/*SHOW BOX SCORE UPDATE DATABASE TELL USER TO REFRESH TO SEE POINT CHANGE*/
						echo "$home: $homeScore $away: $awayScore. Your pick was $selection. Refresh to see point change.";
						
					}
					//else display form to change selection
					else{
						echo " <div class = 'activeGame'>Active Game: $home vs $away. Your pick: $selection. </div><br>";
						echo "<form action = 'http://web.engr.oregonstate.edu/~westonse/newFinal/home.php' method = 'post'>
						<button type='submit' name ='changeSelection' value='$id'>Change Selection</button></form>";
					}

				}
				//Set points if a game has ended 
				if($statusChange==1){
					$mysqli = new mysqli("oniddb.cws.oregonstate.edu","westonse-db",$SQLpass,"westonse-db");
					if($mysqli->connect_errno){
						echo "Failed to connect to mySQL: (" . $mysqli->connect_errno . ")" . $mysqli->connect_error;
					}
					if (!($stmt = $mysqli->prepare("update users set points=$points where username = '$_SESSION[name]'"))) {
						echo "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
					}
					if (!$stmt->execute()) {
						echo "Execute failed: (" . $mysqli->errno . ") " . $mysqli->error;
					}
				}
				if($closedGames!=[]){
					//delete closed games and selections from database 
					foreach($closedGames as $value){
						$mysqli = new mysqli("oniddb.cws.oregonstate.edu","westonse-db",$SQLpass,"westonse-db");
						if($mysqli->connect_errno){
							echo "Failed to connect to mySQL: (" . $mysqli->connect_errno . ")" . $mysqli->connect_error;
						}
						if (!($stmt = $mysqli->prepare("DELETE FROM games WHERE id = '$value' and groupname = '$group'"))) {
							echo "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
						}
						if (!$stmt->execute()) {
							echo "Execute failed: (" . $mysqli->errno . ") " . $mysqli->error;
						}
						$mysqli = new mysqli("oniddb.cws.oregonstate.edu","westonse-db",$SQLpass,"westonse-db");
						if($mysqli->connect_errno){
							echo "Failed to connect to mySQL: (" . $mysqli->connect_errno . ")" . $mysqli->connect_error;
						}
						if (!($stmt = $mysqli->prepare("DELETE FROM selections WHERE id = '$value' and username = '$_SESSION[name]'"))) {
							echo "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
						}
						if (!$stmt->execute()) {
							echo "Execute failed: (" . $mysqli->errno . ") " . $mysqli->error;
						}
				
					}
				}
					
				//if user has changed a selection update the database
				if(isset($_POST['changeSelection'])){
					$mysqli = new mysqli("oniddb.cws.oregonstate.edu","westonse-db",$SQLpass,"westonse-db");
					if($mysqli->connect_errno){
						echo "Failed to connect to mySQL: (" . $mysqli->connect_errno . ")" . $mysqli->connect_error;
					}
					if (!($stmt = $mysqli->prepare("select id,home,away,selection from selections where username = '$_SESSION[name]' and id = '$_POST[changeSelection]'"))) {
						echo "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
					}
					if (!$stmt->execute()) {
						echo "Execute failed: (" . $mysqli->errno . ") " . $mysqli->error;
					}
					$id = NULL;
					$selection = NULL;
					$home = NULL;
					$away = NULL;
					if (!$stmt->bind_result($id,$home,$away,$selection)) {
						echo "Binding output parameters failed: (" . $stmt->errno . ") " . $stmt->error;
					}
					$gameClosed = false;
					while($stmt->fetch()){
						if($selection == $home){
							$newSelection = $away;
						}
						if($selection == $away){
							$newSelection = $home;
						}
						$NBA_url = "http://api.sportradar.us/nba-t3/games/$id/boxscore.json?api_key=9a9z8upxs364nsgz7wp9sw8g";
						$NFL_url = "http://api.sportradar.us/nfl-t1/2015/REG/1/$away/$home/boxscore.json?api_key=j346whw2jbfaadtsvjnwtwnu";
						$MLB_url = "http://api.sportradar.us/mlb-t5/games/$id/boxscore.json?api_key=ancyztxdzs6tp82vnecjf3b5";
						$NBA_json = file_get_contents($NBA_url);
						$NFL_json = file_get_contents($NFL_url);
						$MLB_json = file_get_contents($MLB_url);
						$NBA_data = json_decode($NBA_json, TRUE);
						$MLB_data = json_decode($MLB_json, TRUE);
						$NFL_data = json_decode($NFL_json, TRUE);
						$NBA_status = $NFL_data['status'];
						$MLB_status = $MLB_data['game']['status'];
						$NFL_status = $NBA_data['status'];
						$homeScore = $NBA_data['home']['points'];
						$awayScore = $NBA_data['away']['points']; 
						$winner = $homeScore>$awayScore; 
						//if the game is over or already in the group it cant be added
						if($NBA_status!=NULL && $NBA_status!='scheduled'){
							$gameClosed = true;
							echo "Can't change selection game is in progress";
						}	
						else if($MLB_status!=NULL && $MLB_status!="scheduled"){
							$gameClosed = true;
							echo "Can't change selection game is in progress";
							
						}
						else if($NFL_status!= NULL && $NFL_status!='scheduled'){
							$gameClosed = true;
							echo "Can't change selection game is in progress";
						}
						else{
							$gameClosed = false;
						}
					}
					if($gameClosed==false){
						$mysqli = new mysqli("oniddb.cws.oregonstate.edu","westonse-db",$SQLpass,"westonse-db");
						if($mysqli->connect_errno){
							echo "Failed to connect to mySQL: (" . $mysqli->connect_errno . ")" . $mysqli->connect_error;
						}
						if (!($stmt = $mysqli->prepare("update selections set selection='$newSelection' where username = '$_SESSION[name]' and id = '$id'"))) {
							echo "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
						}
						if (!$stmt->execute()) {
							echo "Execute failed: (" . $mysqli->errno . ") " . $mysqli->error;
						}
						echo '<meta http-equiv="refresh" content="0,URL=http://web.engr.oregonstate.edu/~westonse/newFinal/home.php" />';
					}
				}
				
	?>
	</div>
	<div class = "messageBoard">
	<form action="http://web.engr.oregonstate.edu/~westonse/newFinal/home.php" id="addTweet" method = 'post'> <p style = "font: normal 20px/1 'Lucida Console', Monaco, monospace; color: rgba(255,255,255,1); text-align: center">GROUP MESSAGE BOARD</p>
	<br>
	<br>
	<p>Add new tweet: </p>
	<input style = "float: left" type = "text" name = "title" required> Subject </input> <br><br>
	<textarea rows="4" cols="50" maxlength = "140" name="newPost" form="addTweet">Enter text here (140 character limit):</textarea>
	<input type="submit" value = 'Post to Message Board'>
	</form>
<br>


			<?php
				//add new post if user has made one 
				if(isset($_POST['newPost'])){
					$content = $_POST['newPost'];
					$title = $_POST['title'];
					$group = $_SESSION['group'];
					$user = $_SESSION['name'];
					$mysqli = new mysqli("oniddb.cws.oregonstate.edu","westonse-db",$SQLpass,"westonse-db");
					if($mysqli->connect_errno){
						echo "Failed to connect to mySQL: (" . $mysqli->connect_errno . ")" . $mysqli->connect_error;
					}
					if (!($stmt = $mysqli->prepare("INSERT INTO  `westonse-db`.`tweets` (`username` ,`groupname`,`title` ,`content`) VALUES ('$user', '$group','$title','$content');"))) {
						echo "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
					}
					if (!$stmt->execute()) {
						echo "Execute failed: (" . $mysqli->errno . ") " . $mysqli->error;
					}
					
				}
				//delete post 
				if(isset($_POST['deletePost'])){
					$title = $_POST['deletePost'];
					$group = $_SESSION['group'];
					$mysqli = new mysqli("oniddb.cws.oregonstate.edu","westonse-db",$SQLpass,"westonse-db");
					if($mysqli->connect_errno){
						echo "Failed to connect to mySQL: (" . $mysqli->connect_errno . ")" . $mysqli->connect_error;
					}
					if (!($stmt = $mysqli->prepare("DELETE FROM tweets WHERE title = '$title' and groupname = '$group'"))) {
						echo "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
					}
					if (!$stmt->execute()) {
						echo "Execute failed: (" . $mysqli->errno . ") " . $mysqli->error;
					}
				}
				//display posts
				$mysqli = new mysqli("oniddb.cws.oregonstate.edu","westonse-db",$SQLpass,"westonse-db");
				if($mysqli->connect_errno){
					echo "Failed to connect to mySQL: (" . $mysqli->connect_errno . ")" . $mysqli->connect_error;
				}
				if (!($stmt = $mysqli->prepare("SELECT tweets.username, tweets.groupname, tweets.title, tweets.content, users.picPath FROM users INNER JOIN tweets ON tweets.groupname=users.groupname where tweets.groupname = '$_SESSION[group]'"))) {
					echo "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
				}
				if (!$stmt->execute()) {
					echo "Execute failed: (" . $mysqli->errno . ") " . $mysqli->error;
				}
					$tweets = [];
					$user = NULL;
					$groupname = NULL;
					$title = NULL;
					$content = NULL;
					$picPath = NULL;
					if (!$stmt->bind_result($user,$groupname,$title,$content,$picPath)) {
						echo "Binding output parameters failed: (" . $stmt->errno . ") " . $stmt->error;
					}
					while($stmt->fetch()){
						if(!(in_array($title,$tweets))){
							echo "<div class = 'user'> User: $user </div>";
							echo "<div class = 'userContent'> Message: $content </div> ";
							if($user==$_SESSION['name']){
								echo "<form action = 'http://web.engr.oregonstate.edu/~westonse/newFinal/home.php' method = 'post'><button type = 'submit' name = 'deletePost' value = '$title'>Delete</button></form>";
							}
							echo "<br> <br>";
							array_push($tweets,$title);
						}
					}
					}
                ?>
				</div>

</div>			
</body>
</html>