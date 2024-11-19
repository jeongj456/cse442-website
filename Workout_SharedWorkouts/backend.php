<?php
include '../Workout_Login/Verify_Account.php';
//include '../Workout_History/WorkoutHistory_BackEnd.php';
$conn;
# Connects to database
$db_server = "localhost";
$db_user = "********";
$db_pass = "********";
$db_name = "********";
$auth_token = $_COOKIE["Authentication_Token"];
$email = "";

try {
    $conn = new mysqli($db_server, $db_user, $db_pass, $db_name);
} catch (mysqli_sql_exception) {
    echo "Connection Unsuccessful :(";
}

# Getting email from auth token
$query = "SELECT * FROM User_Accounts_Table WHERE id = '$auth_token'";
$result = $conn->query($query);
$row = $result->fetch_assoc();
$email = $row['Email'];
session_start();
if ($_SERVER['HTTP_X_CSRF_TOKEN'] != $_SESSION['csrf_token']) {
    echo ('Fail');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    if ($_GET['action'] === 'sent_to') {
        $UserID = UserEmailFromID($_COOKIE['Authentication_Token']);
        # Get all workouts id numbers in shared workouts table with the email equal to sent_to and acceptance set to 1(means you accepted)
        $query = "
        SELECT * FROM shared_workouts 
        WHERE sent_to = '$email' 
        AND acceptance = 1
        AND sent_from NOT IN 
            (SELECT Blocked_User 
            FROM Blocked_Users 
            WHERE Blocked_By_User = '$UserID')
        ";
        $all_workouts = getAllCards($query);
        echo json_encode(['success' => true, 'message' => $all_workouts]);
    } elseif ($_GET['action'] === 'sent_from') {
        # Get all workouts id numbers in shared workouts table with the email equal to sent_from/you sent
        $query = "
        SELECT * FROM shared_workouts 
        WHERE sent_from = '$email'";
        $all_workouts = getAllCards($query);
        echo json_encode(['success' => true, 'message' => $all_workouts]);
    } elseif ($_GET['action'] === 'search') {
        // echo "1";
        if ($_GET['type'] === 'true') { //if they are searching for a user
            // echo "2";
            $userToSearch = isset($_GET["workout_name"]) ? ltrim($_GET["workout_name"], '@') : ''; // trim out the @ of the searched username. so @legday would go to legday 
            $query = "
            SELECT Username, Email FROM User_Accounts_Table 
            WHERE Username = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param('s', $userToSearch);
            $stmt->execute();
            $userResult = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if ($userResult) {
                $userEmail = $userResult['Email']; //get the email from the username
                $currentUserID = UserEmailFromID($_COOKIE['Authentication_Token']); //logged in user
                if ($_GET['tof'] === "sent_to") {
                    $UserID = UserEmailFromID($_COOKIE['Authentication_Token']);
                    $query = "
                    SELECT * FROM shared_workouts 
                    WHERE sent_from = '$userEmail' 
                    AND sent_to = '$currentUserID' 
                    AND acceptance = 1
                    AND sent_from NOT IN 
                        (SELECT Blocked_User 
                        FROM Blocked_Users 
                        WHERE Blocked_By_User = '$UserID')
                    ";
                    $all_workouts = getAllCards($query);//get all cards relating 
                    echo json_encode(['success' => true, 'message' => $all_workouts]);
                } else if ($_GET['tof'] === "sent_from") {
                    $query = "
                    SELECT * FROM shared_workouts 
                    WHERE sent_from = '$currentUserID' 
                    AND sent_to = '$userEmail'";
                    $all_workouts = getAllCards($query);//get all cards relating 
                    echo json_encode(['success' => true, 'message' => $all_workouts]);
                }
            } else {
                echo json_encode(['success' => false, 'message' => 'No workouts from user found']);
            }
        } else {//if they are searching for a workout
            // echo "3";
            if ($_GET['tof'] === "sent_from") {
                $query = "SELECT * FROM shared_workouts WHERE sent_from = '$email'";
                $all_workouts = searchAllCards($query, $_GET['workout_name']);
                echo json_encode(['success' => true, 'message' => $all_workouts]);
            } elseif ($_GET['tof'] === "sent_to") {
                $UserID = UserEmailFromID($_COOKIE['Authentication_Token']);
                $query = "
                    SELECT * FROM shared_workouts 
                    WHERE sent_to = '$email' 
                    AND acceptance = 1 
                    AND sent_from NOT IN 
                        (SELECT Blocked_User 
                        FROM Blocked_Users 
                        WHERE Blocked_By_User = '$UserID')
                ";
                $all_workouts = searchAllCards($query, $_GET['workout_name']);
                echo json_encode(['success' => true, 'message' => $all_workouts]);
            }
        }// else {
        //     echo json_encode(['success' => false, 'message' => 'Action not within bounds']);
        // }
    }
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    if (isset($data['loggingWorkout']) && $data['loggingWorkout'] === true) {
        $CardID = isset($data['id'])? $data['id'] : -1;
        $response = AddFullWorkOut(
            $data['Workout_Title'],
            $data['Workout_Date'],
            $data['workout_Info'],
            $data['Workout_TypeInputs'],
            $CardID
        );
        echo json_encode($response);
        exit;
    } else if (isset($data['action']) && $data['action'] === 'acceptWorkout') {
        $id = $data['id'];
        $query = "UPDATE shared_workouts SET acceptance = 1 WHERE idx = '$id'";

        if ($conn->query($query) === TRUE) {
            echo json_encode(['success' => true, 'message' => 'Workout accepted']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update acceptance flag']);
        }
        exit;
    } else if (isset($data['action']) && $data['action'] === 'denyWorkout') {
        $id = $data['id'];
        $query = "UPDATE shared_workouts SET acceptance = -1 WHERE idx = '$id'";
        if ($conn->query($query) === TRUE) {
            echo json_encode(['success' => true, 'message' => 'Workout accepted']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update acceptance flag']);
        }
        exit;
    }
}

function getAllCards($query)
{
    global $conn;
    $all_shared_workouts = "";
    $result = $conn->query($query);
    $rows = $result->fetch_all(MYSQLI_ASSOC);
    foreach ($rows as $row) {
        $id = $row['ID'];
        $cardIDX = $row['idx'];
        $query = "SELECT * FROM planned_workouts WHERE ID = '$id'";
        $result = $conn->query($query);
        $wks = $result->fetch_assoc();
        $workout_title = $wks['Workout_Title'];
        $workout_movements = $wks['Workout_Movements'];
        $movements = "";
        $array = explode(',', $workout_movements); //split string into array seperated by ', '
        foreach ($array as $value) //loop over values
        {
            $movement = "<li>" . $value . "</li>";
            $movements .= $movement;
        }
        $all_shared_workouts .= "
        <div class='individualWorkout'>
           <div class='VerticalEllipses'>
                <i class='fa fa-ellipsis-v'></i>
                <div class='dropdown-content'>
                    <ul>
                        <li class='log-data' data-id= {$cardIDX} >Log Data</li>
                    </ul>
                </div>
            </div>
            <div class='WorkoutTitle'>
                <strong>" . $workout_title . "</strong>
                <hr>
                <br>
            </div>
            <div class='WorkoutList'>
                <ul>" . $movements . "</ul>
            </div>
        </div>";
    }
    return $all_shared_workouts;
}

function searchAllCards($query, $workout_name)
{
    global $conn;
    $all_shared_workouts = "";
    $result = $conn->query($query);
    $rows = $result->fetch_all(MYSQLI_ASSOC);
    foreach ($rows as $row) {
        $id = $row['ID'];
        $cardIDX = $row['idx'];
        $query = "SELECT * FROM planned_workouts WHERE ID = '$id' AND Workout_title LIKE '%$workout_name%'";
        $result = $conn->query($query);
        $wks = $result->fetch_assoc();
        $workout_title = $wks['Workout_Title'];
        $workout_movements = $wks['Workout_Movements'];
        $movements = "";
        $array = explode(',', $workout_movements); //split string into array seperated by ', '
        foreach ($array as $value) //loop over values
        {
            $movement = "<li>" . $value . "</li>";
            $movements .= $movement;
        }
        if ($workout_title != '') {
            $all_shared_workouts .= "
            <div class='individualWorkout'>
                <div class='VerticalEllipses'>
                    <i class='fa fa-ellipsis-v'></i>
                    <div class='dropdown-content'>
                        <ul>
                            <li class='log-data' data-id= {$cardIDX}>Log Data</li>
                        </ul>
                    </div>
                </div>
                <div class='WorkoutTitle'>
                    <strong>" . $workout_title . "</strong>
                    <hr>
                    <br>
                </div>
                <div class='WorkoutList'>
                    <ul>" . $movements . "</ul>
                </div>
            </div>";
        }
    }
    if ($all_shared_workouts != '') {
        return $all_shared_workouts;
    }
    return 'No workouts found';
}



function DataBase_Connect(): mysqli
{
    //This is the test servers name
    // $database_name = "tjmehok_db";
    $database_name = "********";
    //I belive this is the full name Matt gave us in his email
    $server_name = "localhost:3306";
    //The user name and password is how we sign into PHP admin
    $user_name = "********";
    $password = "********";
    // $user_name   = "root";
    // $password = "";

    //This is the most important part
    //we are creating the mysqli class so it will eaisily connect everytime
    try {
        $connect = new mysqli($server_name, $user_name, $password, $database_name);
        //check the connection status
        //If the connection failed
    } catch (PDOException $e) {
        die("Connection failed: " . mysqli_connect_error());
    }
    return $connect;
    //If the connection succeded
}

function SharedWorkoutCreatorID($WorkoutID): string{
    $DB = DataBase_Connect();
    $CreatorID = "";

    $UserID = UserEmailFromID($_COOKIE["Authentication_Token"]);

    //Grabbing the creator ID
    /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    $grabDataBase = $DB->prepare("SELECT * FROM shared_workouts WHERE sent_to = ? AND idx = ?");
    $grabDataBase->bind_param("si", $UserID,  $WorkoutID); // Bind the UserID parameter
    
    $grabDataBase -> execute();
    $DataResult = $grabDataBase -> get_result();

    if( $DataResult->num_rows == 1){
        $row = $DataResult->fetch_assoc();
        $CreatorID =  $row["sent_from"];
        $DB ->close();
        return $CreatorID;
    }else{
        echo "data number of rows we got: " . $DataResult->num_rows . "    ";
        echo "Incoming WorkoutID: " . $WorkoutID . "    ";
        echo "Incoming User ID: " . $UserID . "    ";
    }

    $DB ->close();
    return "ERERERERERERERROOR";


}

//Add a fullworkout to thomas's database so it can be used on the workout history page
function AddFullWorkOut($Workout_Title, $Workout_Date, $workout_Info, $Workout_TypeInputs, $Cardidx)
{
    //echo $Cardidx;
    $DB = DataBase_Connect();
    if (!$DB) {
        error_log('Database connection failed: ' . mysqli_connect_error());
        echo json_encode(['success' => false, 'message' => 'Database connection failed']);
        exit;
    }

    $CreatorID = SharedWorkoutCreatorID($Cardidx);

    //echo $CreatorID;
    if($CreatorID == "ERERERERERERERROOR"){
        error_log('Database connection failed: ' . mysqli_connect_error());
        echo json_encode(['success' => false, 'message' => 'Not able to eaisily grab the creator ID']);
        exit;
    }

    //$CreatorID = SharedWorkoutCreatorID();
    error_log('Attempting to save workout: ' . $Workout_Title . ' with exercises: ' . json_encode($workout_Info));
    $Grab_dataBase = $DB->prepare("INSERT INTO planned_workouts_FULL(Workout_Title, Workout_Movements, User_ID, Creator_ID, Workout_Results, Workout_Type, Workout_Date) VALUES (?, ?, ?, ?, ?, ?, ?)");
    if (!$Grab_dataBase) {
        error_log('Failed to prepare statement: ' . $DB->error);
        return ['success' => false, 'message' => 'Failed to prepare statement: ' . $DB->error];
    }
    $workout_Movements = "";
    $workout_Results = "";
    //var_dump($workout_Info);
    //THIS WHOLE for each needs to be altered so that the explode can work properly with it
    for ($x = 0; $x <= count($workout_Info) - 1; $x++) {
        $exercise = $workout_Info[$x]['workout'];
        $weight = $workout_Info[$x]['weight'];
        $reps = $workout_Info[$x]['reps'];
        $sets = $workout_Info[$x]['sets'];

        $workout_Movements .= $exercise . ",";
        $workout_Results .= $exercise . "," . $sets . "," . $reps . "," . $weight . "+_)(*&^%";
    }
    // Define a default Workout_type. Adjust this logic later as needed.
    $WorkoutTypes = "";
    foreach ($Workout_TypeInputs as $workout_TypeInput) {
        $WorkoutTypes = $WorkoutTypes . $workout_TypeInput . ",";
    }

    // Define the user using the website currently
    //This line might need to be replaced for temp local testing
    $UserID = UserEmailFromID($_COOKIE['Authentication_Token']);
    //$UserID = "TestUser@gmail.com";
    $Grab_dataBase->bind_param("sssssss", $Workout_Title, $workout_Movements, $UserID, $CreatorID, $workout_Results, $WorkoutTypes, $Workout_Date);
    if (!$Grab_dataBase->execute()) {
        error_log('SQL execution failed: ' . $Grab_dataBase->error);
        echo json_encode(['success' => false, 'message' => 'SQL execution failed']);
        exit;
    }
    $insert_id = $DB->insert_id;
    $DB->close();
    return ['success' => true, 'workoutID' => $insert_id];
}

?>