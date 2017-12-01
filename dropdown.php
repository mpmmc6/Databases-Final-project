<?php


    require('db_credentials.php');
	
    $dbhandle = mysql_connect($servername, $username, $password) or die("Unable to connect to MySQL");
	
    $selected = mysql_select_db("class", $dbhandle) or die("Could not select examples");
	//$choice = mysql_real_escape_string($_GET['choice']);
	
	$query = "SELECT * FROM DCusers order by addDate desc";
	
	$result = mysql_query($query);
	


	while ($row = mysql_fetch_array($result)) {
   		echo "<option>" . $row{'DCuserID'} . "</option>";
        //echo $row{'DCuserID'};

    }
    
    
?>