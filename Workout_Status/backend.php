<?php
include '../Workout_Login/Verify_Account.php';
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
// Fetches only 1 row
$row = $result->fetch_assoc();
$email = $row['Email'];
$username = $row['Username'];
session_start();
if ($_SERVER['HTTP_X_CSRF_TOKEN'] != $_SESSION['csrf_token']) {
    echo ('Fail');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    $request = $data['Request'];

    // If the request is add
    if($request == 'add'){
        $action = htmlspecialchars($data['Action']);
        $postBody = htmlspecialchars($data['PostBody']);
        // Insert the new data into the database
        $stmt = $conn->prepare("INSERT INTO status_feed (Username, Email, actn, postBody) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $username, $email, $action, $postBody);
        try{
            $stmt->execute();
            // If it exectues, send a json string of this dictionary that can be accessed by doing result.message
            echo json_encode(['success' => true, 'message' => 'Successfully inserted into database']);
            exit;
        }catch(Exception){
            echo json_encode(['success' => false, 'message' => 'Could not insert into database']);
        }
    } else if($request == 'delete'){
        $id = $data['iD'];
        $stmt = $conn->prepare("DELETE FROM status_feed WHERE id = '$id'");
        try{
            $stmt->execute();
            // If it exectues, send a json string of this dictionary that can be accessed by doing result.message
            echo json_encode(['success' => true, 'message' => 'Successfully deleted from database']);
            exit;
        }catch(Exception){
            echo json_encode(['success' => false, 'message' => 'Could not delete database']);
        }
    } else if($request == 'edit'){
        $id = $data['iD'];
        # Don't forget to use '' around your variables in query strings
        $query = "SELECT * FROM status_feed WHERE id = '$id'";
        try{
            $result = $conn->query($query);
            $row = $result->fetch_assoc();
            $action = $row['actn'];
            $postBody = $row['postBody'];
            // If it exectues, send a json string of this dictionary that can be accessed by doing result.message
            echo json_encode(['success' => true, 'action' => $action, 'postBody' => $postBody]);
            exit;
        }catch(Exception){
            echo json_encode(['success' => false, 'message' => 'Could not delete database']);
        }
    } else if($request == 'edit_add'){
        $id = $data['iD'];
        $action = htmlspecialchars($data['Action']);
        $postBody = htmlspecialchars($data['PostBody']);
        # Don't forget to use '' around your variables in query strings
        $stmt = $conn->prepare("UPDATE status_feed SET actn = ?, postBody = ? WHERE id = ?");
        $stmt->bind_param("ssi", $action, $postBody, $id);
        try{
            $stmt->execute();
            // If it exectues, send a json string of this dictionary that can be accessed by doing result.message
            echo json_encode(['success' => true, 'message' => 'Successfully updated from database']);
            exit;
        }catch(Exception){
            echo json_encode(['success' => false, 'message' => 'Could not update database']);
        }        
    }
    
}

?>