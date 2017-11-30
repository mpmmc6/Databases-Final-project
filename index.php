<?php

    require('process.php');

    session_start();

	$message = '';
	
	$target = $_GET['target'];
	$action = $_POST['action'];
	$data = null;

    switch($action) {
		case 'delete':
			$message = deleteRecord();
			break;
		case 'add':
			list($target, $message, $data) = processSignin();
			break;
		case 'signOut':
			$message = setOutStatus('completed');
			break;
	}

    switch($target) {
		case 'signinForm':
			presentSigninForm($message, $data);
			break;
		default:
			presentSigninlist($message);
	}

    function presentSigninList($message = "") {
		$stylesheet = 'Signin.css';
		
		$signins = array();

		// Create connection
		require('db_credentials.php');
		
        $mysqli = new mysqli($servername, $username, $password, $dbname);
	
		if ($mysqli->connect_error) {
			$message = $mysqli->connect_error;
		} else {
			$sql = "SELECT * FROM Signins ORDER BY addDate";
			if ($result = $mysqli->query($sql)) {
				if ($result->num_rows > 0) {
					while($row = $result->fetch_assoc()) {
						array_push($signins, $row);
					}
				}
				$result->close();
			} else {
				$message = $mysqli->error;
			}
			$mysqli->close();
		}
	
		print generatePageHTML("Users in Datacenter", generateSignedinTableHTML($signins, $message), $stylesheet);
	}

    function generateSignedinTableHTML($signins, $message) {
		$html = "<h1>Users in Datacenter</h1>\n";
		
		if ($message) {
			$html .= "<p class='message'>$message</p>\n";
		}
		
		$html .= "<p><a class='SigninButton' href='index.php?target=Signinform'>+ Sign in to Datacenter</a></p>\n";
	
		if (count($signins) < 1) {
			$html .= "<p>No info to display!</p>\n";
			return $html;
		}
	
		$html .= "<table>\n";
		$html .= "<tr><th>actions</th><th>Signed in?</th>";
		
		foreach ($signins as $signin) {
			$visitID = $signin['id'];
			$userID = $signin['userID'];
			$reason = ($signin['reason']) ? $signin['reason'] : '';
            $equipment = $signin['affectedEquipment'];
            $outDate = ($signin['outDate']) ? $signin['outDate'] : '';
						
			$outAction = 'out';
			$outLabel = 'signed out';
			if ($outDate) {
				$outAction = 'set_signed_out';
				$outLabel = 'Signed Out';
			}
			
			$html .= "<tr>
                        <td>
                            <form action='index.php' method='post'> <input type='hidden' name='action' value='delete' /> <input type='hidden' name='id' value='$id' /> <input type='submit' value='Delete'> </form>
                        </td>
                            
                        <td>
                            <form action='index.php' method='post'> <input type='hidden' name='action' value='$outAction' /> <input type='hidden' name='id' value='$id' /> <input type='submit' value='$outLabel'></form>
                        </td>
                        
                        <td>$inDate</td>
                        <td>$outDate</td>
                        <td>$reason</td>
                        <td>$equipment</td>
                    </tr>\n";
		}
        
		$html .= "</table>\n";
	
		return $html;
	}

    function deleteRecord() {
		$id = $_POST['id'];
	
		$message = "";
	
		if (!$id) {
			$message = "No record was specified to delete.";
		} else {
			// Create connection
			require('db_credentials.php');
			$mysqli = new mysqli($servername, $username, $password, $dbname);
			// Check connection
			if ($mysqli->connect_error) {
				$message = $mysqli->connect_error;
			} else {
				$id = $mysqli->real_escape_string($id);
				$sql = "DELETE FROM Signins WHERE id = $id";
				if ( $result = $mysqli->query($sql) ) {
					$message = "Record was deleted.";
				} else {
					$message = $mysqli->error;
				}
				$mysqli->close();
			}
		}
	
		return $message;
	}

    function setOutStatus($status) {
		$id = $_POST['id'];
	
		$message = "";  
		
		$outDate = 'null';
		if ($status == 'out') {
			$outDate = 'NOW()';
		}
	
		if (!$id) {
			$message = "No record was specified to change in/out status.";
		} else {
			// Create connection
			require('db_credentials.php');
			$mysqli = new mysqli($servername, $username, $password, $dbname);
			// Check connection
			if ($mysqli->connect_error) {
				$message = $mysqli->connect_error;
			} else {
				$id = $mysqli->real_escape_string($id);
				$sql = "UPDATE Signins SET outDate = $outDate WHERE id = '$id'";
				if ( $result = $mysqli->query($sql) ) {
					$message = "Visit was updated to $status.";
				} else {
					$message = $mysqli->error;
				}
				$mysqli->close();
			}
		}
	
		return $message;
	}

    function presentSignInForm($message = "", $data = null){
        $userID= '';
        $reason = '';
        $equipment = '';
        
        $html = <<<EOT1
            <!DOCTYPE html>
            <html>
                <head>
                    <title>Datacenter Manager</title>
                    <link rel="stylesheet" type="text/css" href="Signin.css"
                </head>
                
                <body>
                    <h1>Visitors</h1>
            EOT1;
        
        if($message){
            $html .= "<p class='message'>$message</p>\n";
        }
        
        $html .= <<<EOT2
                
                <form action="index.php" method="post">
                    <input type="hidden" name="action" value="add"/>
                    
                    <input type="text" name="userID" value="$userID" placeholder="pawprint" maxlength="255" size="80"></p>
                    
                    <p>Reason for Visit<br/>
                        <textarea name="reason" rows="6" cols="80" placeholder="reason">$reason</textarea>
                    </p>
                    
                    <p>Affected Equipment<br/>
                        <textarea name="equipment" rows="6" cols="80" placeholder="Affected Equipment">$equipment</textarea>
                    </p>
                    
                    <input type="submit" name='submit' value="Submit"> <input type="submit" name='cancel' value="Cancel">
                </form>
            </body>
        </html>
EOT2;
        
    print $html;
        
        
        
    }

    function processSignin() {
		$message = '';
		
		if ($_POST['cancel']) {
			$message = 'Sign in was cancelled.';
			return array('', $message);
		}
		
		if (! $_POST['title']) {
			$message = 'A pawprint is required.';
			return array('signinForm', $message, $_POST);
		}
	
		$userID = $_POST['userID'];
		$reason = $_POST['reason'] ? $_POST['reason'] : "";
        $equipment = $_POST['equipment'] ? $_POST['equipment'] : "";

		// Create connection
		require('db_credentials.php');
		$mysqli = new mysqli($servername, $username, $password, $dbname);

		// Check connection
		if ($mysqli->connect_error) {
			$message = $mysqli->connect_error;
		} else {
			$userID = $mysqli->real_escape_string($userID);
			$reason = $mysqli->real_escape_string($reason);
			$equipment = $mysqli->real_escape_string($equipment);
	
			$sql = "INSERT INTO Signins (userID, reason, equipment, addDate) VALUES ('$userID', '$reason', '$equipment', NOW())";
	
			if ($result = $mysqli->query($sql)) {
				$message = "Record was added";
			} else {
				$message = $mysqli->error;
			}

		}
		
		return array('', $message);
	}


?>
