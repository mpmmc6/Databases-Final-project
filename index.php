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
			list($target, $message) = processSignin();
			break;
		case 'signOut':
			$message = setOutStatus();
			break;
        case 'createUser':
            list($target, $message, $data) = processCreateUser();
            break;
	}

    switch($target) {
		case 'signinForm':
			presentSigninForm($message, $data);
			break;
		case 'createUserForm':
            presentCreateUserForm($message, $data);
            break;
        default:
			presentSigninlist($message);
	}

    function presentSigninList($message = "") {
		$stylesheet = 'Signin.css';
		$userID = $_GET['userID'];
        
		$signins = array();

		// Create connection
		require('db_credentials.php');
		
        $mysqli = new mysqli($servername, $username, $password, $dbname);
	
		if ($mysqli->connect_error) {
			$message = $mysqli->connect_error;
		} else {
             if ($_GET['userID']){
                 $sql = "SELECT * FROM Signins WHERE userID='$userID' ORDER BY addDate";
            } else {
			     $sql = "SELECT * FROM Signins ORDER BY addDate";
            }
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
		
		$html .= "<p><a class='SigninButton' href='index.php?target=signinForm'>Sign in to Datacenter</a></p>\n";
	
		if (count($signins) < 1) {
			$html .= "<p>No info to display!</p>\n";
			return $html;
		}
	
		$html .= "<table>\n";
		$html .= "<tr><th>Actions</th><th>Signed in?</th>";
		
        
		foreach ($signins as $signin) {
			$visitID = $signin['id'];
			$userID = $signin['userID'];
			$reason = ($signin['reason']) ? $signin['reason'] : '';
            $equipment = $signin['affectedEquipment'];
            $outDate = ($signin['outDate']) ? $signin['outDate'] : '';
			$addDate = $signin['addDate'];
            
			$outAction = 'out';
			$status = 'sign out';
            if ($outDate) {
				$status = 'Signed Out';
			}
			
            
			
			$html .= "<tr>
                        <td>
                            <form action='index.php' method='post'> <input type='hidden' name='action' value='delete' /> <input type='hidden' name='id' value='$visitID' /> <input type='submit' value='Delete'> </form>
                        </td>
                        <td>
                            <form action='index.php' method='post'> <input type='hidden' name='action' value='signOut' /> <input type='hidden' name='id' value='$visitID' /> <input type='submit' value='$status'></form>
                        </td>
                        <td>$userID</td>
                        <td>$addDate</td>
                        <td>$outDate</td>
                        <td>$reason</td>
                        <td>$equipment</td>
                    </tr>\n";
		}
        
		$html .= "</table>\n";
	
        
        
         $html .= "<p><a class='SigninButton' href='index.php?target=createUserForm'>Create New User</a></p>\n";
        
        if ($_GET['userID']){
            
            $html .= '<form action="index.php" method="get">
                          <input type="submit" value="Clear Filter"/>
                      </form>';
                
        } else {
            $html .= "<form action='index.php' method='get'>
                          <input type='text' name='userID' value='' placeholder='Specify Pawprint' maxlength='255' size='80' />
                      </form>";
        }
        
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

    function setOutStatus() {
		$id = $_POST['id'];
	
		$message = "";  
		
		$outDate = 'null';
	
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
				$sql = "UPDATE Signins SET outDate = NOW() WHERE id = '$id'";
				if ( $result = $mysqli->query($sql) ) {
					$message = "Record  # $id is signed out of the Datacenter.";
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
        
        <html lang="en">
<head>
    <link rel="stylesheet" type="text/css" href="external.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta.2/css/bootstrap.min.css" integrity="sha384-PsH8R72JQ3SOdhVi3uxftmaW6Vc51MKb0q5P2rRUpPvrszuE4W1povHYgTpBfshb" crossorigin="anonymous">
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta.2/js/bootstrap.min.js" integrity="sha384-alpBpkh1PFOepccYVYDB4do5UnbKysX5WZXm3XxPqe5iKTfUKjNkCk9SaVuEZflJ" crossorigin="anonymous"></script>
    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.3/umd/popper.min.js" integrity="sha384-vFJXuSJphROIrBnz7yo7oB41mKfc8JzQZiCq4NCceLEaO4IHwicKwpJf9c9IpFgh" crossorigin="anonymous"></script>
    <title> Databases final project </title>
    
   <meta charset= "utf-8">
    </head>
<body>
EOT1;
        
    if($message){
            $html .= "<p class='message'>$message</p>\n";
        }
$html .= <<<EOT2

    

    <nav class="navbar navbar-expand-lg navbar-dark bg-dark" >
        <a class="navbar-brand" href="#">Final project</a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria- controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <ul class="navbar-nav mr-auto">
                <li class="nav-item active">
                    <a class="nav-link" href="index.html">Home <span class="sr-only">(current)</span></a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="signin.html">Sign in</a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="signup.html">Sign up</a>
                </li>


            </ul>

        </div>
    </nav>
    

     <div ng-controller="Sign-in">
      
        <!--add student header-->
        <div id="first">
          <div class="panel panel-default">
            <div id = "title">Sign in</div>
            <div class="panel-body">
    <form action="index.php" method="post">
        <input type="hidden" name="action" value="add"/>
        <div class="col-md-12">
                  <input type="text" ng-model="studentList.studentNumber" name="userID" value="$userID" id="username" class="form-control" placeholder="pawprint">
        </div>
        <div class="col-md-12">
                  <!-- <input type="text" ng-model="studentList.studentNumber" name="reason" id="Equitment used" class="form-control" placeholder="Equitment used"> -->
                  
                  <textarea name="reason" rows="6" cols="80" placeholder="reason" ng-model="studentList.studentNumber" class="form-control">$reason</textarea>
        </div>
        <div class="col-md-12">
                  <!-- <input type="text" ng-model="studentList.studentNumber"  class="form-control" placeholder="Reason"> -->
                  
                  <textarea name="equipment" rows="6" cols="80" placeholder="Affected Equipment" ng-model="studentList.studentNumber" class="form-control">$equipment</textarea>                  
        </div>
                  <!--submit button-->
        <div id= "signin">
                  <input class="btn btn-danger btn-block" type="submit" value="Sign in">
        </div>
                  
     </form>
              </div>
            </div>
         </div>
    </div>
    
</body>


</html>      
EOT2;
print $html;
}
 
    function processSignin() {
		$message = '';
		
		if ( $_POST['cancel'] ) {
			$message = 'Sign in was cancelled.';
			return array('', $message);
		}
		
		if (! $_POST['userID'] ) {
			$message = 'A pawprint is required.';
			return array('signinForm', $message, $_POST);
		}
	
		$userID = $_POST['userID'];
		$reason = $_POST['reason'] ? $_POST['reason'] : "";
        $equipment = $_POST['equipment'] ? $_POST['equipment'] : "";
        
		// Create connection
		require('db_credentials.php');
		$mysqli = new mysqli($servername, $username, $password, $dbname);
        
        $result = $mysqli->query("SELECT DCuserID FROM DCusers WHERE DCuserID = '$userID'");
        
        if($result->num_rows == 0) {
            // row not found, do stuff...
            $message = "$userID is not an authorized user<br>Create a new user or use existing credentials";
            return array('', $message);
        } 
        
        // Check connection
		if ($mysqli->connect_error) {
			$message = $mysqli->connect_error;
		} else {
			$userID = $mysqli->real_escape_string($userID);
			$reason = $mysqli->real_escape_string($reason);
			$equipment = $mysqli->real_escape_string($equipment);
	
			$sql = "INSERT INTO Signins (userID, reason, affectedEquipment, addDate) VALUES ('$userID', '$reason', '$equipment', NOW())";
	
			if ($result = $mysqli->query($sql)) {
				$message = "$userID is signed into the Datacenter";
			} else {
				$message = $mysqli->error;
			}

		}
		
		return array('', $message);
	}

    function presentCreateUserForm($message="", $data=null ){
        $userID = '';
        $name = '';
        
        $html = <<<EOT1
            <!DOCTYPE html>
            <html>
                <head>
                    <title>Create User</title>
                    <link rel="stylesheet" type="text/css" href="Signin.css"
                </head>
                
                <body>
                    <h1>Create New User</h1>
                
                    <form action="index.php" method="post">
                        <input type="hidden" name="action" value="createUser"/>

                        <input type="text" name="userID" value="$userID" placeholder="pawprint" maxlength="255" size="80">

                        <input type="text" name="name" value="$name" placeholder="First Name" maxlength="255" size="80">

                        <input type="submit" name='submit' value="Submit"> <input type="submit" name='cancel' value="Cancel">
                    </form>
                </body>
            </html>
EOT1;

        print $html;
    }

    function processCreateUser(){
        $message = '';
        $userID = '';
        $name = '';        
        
        if ( $_POST['cancel'] ) {
            $message = 'Create user was cancelled.';
            return array('', $message, null);
		}
		
		if (! $_POST['userID'] ) {
			$message = 'A pawprint is required.';
			return array('createUser', $message, $_POST);
		}
        if (! $_POST['name'] ) {
			$message = 'A name is required.';
			return array('createUser', $message, $_POST);
		}
	
		$userID = $_POST['userID'];
		$name = $_POST['name'];

		// Create connection
		require('db_credentials.php');
		$mysqli = new mysqli($servername, $username, $password, $dbname);

		// Check connection
		if ($mysqli->connect_error) {
			$message = $mysqli->connect_error;
		} else {
			$userID = $mysqli->real_escape_string($userID);
			$name = $mysqli->real_escape_string($name);
            
            $sql = "SELECT * FROM DCusers WHERE userID='$userID'";
            $result = $mysqli->query($sql);
            if ($result->num_rows < 1){
                $sql = "INSERT INTO DCusers (DCuserID, FirstName, addDate) VALUES ('$userID', '$name', NOW())";
                if ($result = $mysqli->query($sql)) {
                    $message = "New user added";
                
            } else {
                $message = "Pawprint already added";
                //$message .= $mysqli->error;
            }} 
		}
		
		return array('', $message);
    }


?>