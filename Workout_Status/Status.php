<?php
include '../Navbar/navbar.php';
?>

<!DOCTYPE html>

<html lang="en">

<head>
    <title>Status</title>
    <link rel="stylesheet" href="../Navbar/navbar.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href='https://fonts.googleapis.com/css?family=Josefin Sans' rel='stylesheet'>
    <link rel="stylesheet" media="screen and (max-width: 600px)" href="status_mobile.css">
    <link rel="stylesheet" media="screen and (min-width: 601px)" href="status.css">
</head>

<body>
    <input type="hidden" id="csrf_token" value="<?php session_start(); echo $_SESSION['csrf_token']; ?>"></>

    <!-- A modal that is initially hidden for user creating a status card -->
    <div class="modal" id="addPostModal">
        <div class="modal-content">
            <!-- x button in top right corner to close modal -->
            <span id="close" class="close" onclick="close_modal()">&times;</span>
            <h2>Update Status</h2>
            <form id="workoutForm">
                <input type="text" id="action" placeholder="Post action..." required>
                <textarea id="postBody" placeholder="Body of post..." required></textarea>
            </form>
            <!-- Create Status Button that calls the function on click, passing in the element that was clicked and the click itself -->
            <button type="button" id="submitPost" class="submitPost" onclick="submitPost()">Submit Post</button>
        </div>
    </div>
    <!-- same as above but for editing status cards -->
    <div class="modal" id="editPostModal">
        <div class="modal-content">
            <!-- the      onclick="myFunction()"    calls the js function to close the modal-->
            <span id="close" class="close" onclick="close_edit_modal()">&times;</span>
            <h2>Update Status</h2>
            <form id="workoutForm">
                <input type="text" id="editaction" required>
                <textarea id="editpostBody" required></textarea>
            </form>
            <!-- Update Status Button -->
            <button type="button" id="updatePost" class="updatePost">Update Post</button>
        </div>
    </div>

    <div class="container">
        <div class="top-of-page">
            <div class="title">
                Status
            </div>
        </div>

        <div class="content">

            <div class="top">
                <!-- Search icon that calls the js function on click, passing in the div element and the click itself-->
                <i class="fa fa-search-plus search-icon" id="search-icon" onclick="open_close_search(this, event)"></i>
                <div class="SearchBar" id="SearchBar">
                    <!-- Refreshing the page, calling the js function on click -->
                    <button id="clearSearchBtn" onclick="refresh()">Clear</button>
                    <form id="searchForm">
                        <input type="text" id="searchInput" placeholder="Search status cards..." required>
                        <!-- In a button, if you have type submit, it automatically refreshes the page, in order to not refresh, use type button and it will default to your functions action -->
                        <button type="button" id="searchworkoutname" onclick="search(this, event)" onsubmit="search(this, event)">Search</button>
                    </form>
                </div>
                <!-- Opens the modal on click, calling the js function to do this -->
                <div class="made-buttons" id="add-post" onclick="open_modal()" style="text-decoration: none; color: inherit;">
                    Add Post
                </div>
            </div>

            <div class="bottom" id="bottom">
                
                <?php
                    // Connect to the database
                    $conn = mysqli_connect('localhost', '********', '********', '********');

                    function get_status_feeds($conn, $username){
                        $retval = '';
                        // Select the rows from the table where the username is the current user
                        $sql = "SELECT * FROM status_feed WHERE Username = ?";
                        $stmt = $conn->prepare($sql);
                        $stmt->bind_param("s", $username);
                        $stmt->execute();
                        $result = $stmt->get_result(); // get the mysqli result
                        $rows = $result->fetch_all(MYSQLI_ASSOC); // fetch all data  
                        foreach ($rows as $row) {
                            $action = $row['actn'];
                            $postBody = $row['postBody'];
                            // Create and append the div element
                            $retval .= "<div class='post-card'><div id='username'>" . $username . "</div><div id='action'>" . $action . "</div><div id='card-body'>" . $postBody . "</div></div>";

                        }
                        return $retval;
                    }
                    function get_workout_history($conn, $username){
                        $retval = '';
                        // Get all rows where creater id and not deleted
                        $sql = "SELECT * FROM planned_workouts_FULL WHERE Creator_ID = ? AND Deleted = 0";
                        $stmt = $conn->prepare($sql);
                        $stmt->bind_param("s", $username);
                        $stmt->execute();
                        $result = $stmt->get_result(); // get the mysqli result
                        $rows = $result->fetch_all(MYSQLI_ASSOC); // fetch all data  
                        foreach ($rows as $row) {
                            $action = 'I worked out!';
                            $postBody = 'I did this workout -->' . $row['Workout_Title'];
                            $retval .= "<div class='post-card'><div id='username'>" . $username . "</div><div id='action'>" . $action . "</div><div id='card-body'>" . $postBody . "</div></div>";

                        }
                        return $retval;
                    }                       
                    
                    $status_page = '';
                    # Getting email from auth token
                    $auth_token = $_COOKIE["Authentication_Token"];
                    $sql = "SELECT * FROM User_Accounts_Table WHERE id = ?"; // SQL with parameters
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("s", $auth_token);
                    $stmt->execute();
                    $result = $stmt->get_result(); // get the mysqli result
                    $row = $result->fetch_assoc(); // fetch data   
                    $email = $row['Email'];        // Current logged in user's email
                    $username = $row["Username"];  // Current logged in user's username
                    $status = "Accepted";


                    # Get all status posts from current user
                    $sql = "SELECT * FROM status_feed WHERE Username = ?";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("s", $username);
                    $stmt->execute();
                    $result = $stmt->get_result(); // get the mysqli result
                    $rows = $result->fetch_all(MYSQLI_ASSOC); // fetch all data
                    foreach ($rows as $row) {
                        $action = $row['actn'];
                        $postBody = $row['postBody'];
                        $id = $row['id'];
                        # If you have quotes inside of string, the outside quotes must be " "
                        $status_page .= "<div class='post-card'>
                                            <div id='card-top'>
                                                <div id='username'>" . $username . "</div>
                                                <i class='fas fa-ellipsis-v ellipsis-icon' onclick='open_close_dropdown_menu(this, event)'></i>
                                                <div id='dropdown-menu' class='dropdown-menu' style='display: none;'>
                                                    <p onclick='edit_Post(this, event)'>Edit</p>
                                                    <div id=" . $id . " display='none'></div>
                                                    <p style='color: red;' onclick='delete_Post(this, event)'>Delete</p>
                                                </div>
                                            </div><div id='action'>" . $action . "</div>
                                            <div id='card-body'>" . $postBody . "</div>
                                        </div>";
                    }

                    # Get all friends to current user using username
                    $sql = "SELECT * FROM Friends_Table WHERE Sent_Request = ? OR Received_Request = ? AND status = ?"; // SQL with parameters
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("sss", $username, $username, $status);
                    $stmt->execute();
                    $result = $stmt->get_result(); // get the mysqli result
                    $rows = $result->fetch_all(MYSQLI_ASSOC); // fetch all data  
                    foreach ($rows as $row) {
                        $friend = '';
                        $sent = $row['Sent_Request'];       //username of friend
                        $received = $row['Received_Request'];
                        // If your username is in the sent column, your friend is the one that received the request
                        if ($sent == $username){
                            $friend = $received;
                        }else{
                            // If your username is in the received column, your friend is the one that sent the request
                            $friend = $sent;
                        }

                        # Gets all status posts from friend
                        $status_page .= get_status_feeds($conn, $friend);
                        // Get all workout history feeds from friend
                        $status_page .= get_workout_history($conn, $friend);

                    }

                    echo $status_page;
                ?>

                <script>
                    const csrf_token = document.getElementById("csrf_token").value;

                    // --------------------------------Listens for enter button click for search bar-------------------------------------- 
                    // Gets the search input box and adds a listener to see if the user presses enter. If it does, it automatically clicks the search button
                    // Get the search input field
                    var input = document.getElementById("searchInput");

                    // Execute a function when the user presses a key on the keyboard
                    input.addEventListener("keypress", function(event) {
                    // If the user presses the "Enter" key on the keyboard
                    if (event.key === "Enter") {
                        // Cancel the default action, if needed
                        event.preventDefault();
                        // Trigger the button element with a click
                        document.getElementById("searchworkoutname").click();
                    }
                    });
                    // ----------------------------------------------------------------------

                    // ---------------------------Listens for enter button click for submit post modal------------------------------------------- 
                    // Gets the modal input boxes and adds a listener to see if the user presses enter. If it does, it automatically clicks the submit post button
                    // Get the action input textbox
                    var action = document.getElementById("action");

                    // Execute a function when the user presses a key on the keyboard
                    action.addEventListener("keypress", function(event) {
                    // If the user presses the "Enter" key on the keyboard
                    if (event.key === "Enter") {
                        // Cancel the default action, if needed
                        event.preventDefault();
                        // Trigger the button element with a click
                        document.getElementById("submitPost").click();
                    }
                    });

                    // Get the post body textbox
                    var postBody = document.getElementById("postBody");

                    // Execute a function when the user presses a key on the keyboard
                    postBody.addEventListener("keypress", function(event) {
                    // If the user presses the "Enter" key on the keyboard
                    if (event.key === "Enter") {
                        // Cancel the default action, if needed
                        event.preventDefault();
                        // Trigger the button element with a click
                        document.getElementById("submitPost").click();
                    }
                    });
                    // ----------------------------------------------------------------------

                    // -------------------------------Listens for enter button click for edit modal--------------------------------------- 
                    // Gets the modal input boxes and adds a listener to see if the user presses enter. If it does, it automatically clicks the submit post button
                    // Get the edit action input textbox
                    var editaction = document.getElementById("editaction");

                    // Execute a function when the user presses a key on the keyboard
                    editaction.addEventListener("keypress", function(event) {
                    // If the user presses the "Enter" key on the keyboard
                    if (event.key === "Enter") {
                        // Cancel the default action, if needed
                        event.preventDefault();
                        // Trigger the button element with a click
                        document.getElementById("updatePost").click();
                    }
                    });

                    // Get the edit post body textbox
                    var editpostBody = document.getElementById("editpostBody");

                    // Execute a function when the user presses a key on the keyboard
                    editpostBody.addEventListener("keypress", function(event) {
                    // If the user presses the "Enter" key on the keyboard
                    if (event.key === "Enter") {
                        // Cancel the default action, if needed
                        event.preventDefault();
                        // Trigger the button element with a click
                        document.getElementById("updatePost").click();
                    }
                    });
                    // ----------------------------------------------------------------------

                    function close_modal(){
                        document.getElementById("addPostModal").style.display = "none";
                    }


                    function open_modal(){
                        document.getElementById("addPostModal").style.display = "block";
                    }


                    function close_edit_modal(){
                        document.getElementById("editPostModal").style.display = "none";
                    }


                    function open_close_dropdown_menu(element, event){
                        // Using the click itself, get the div element that was clicked --> same as the parameter element
                        const ellipsis = event.target;
                        const menu = ellipsis.nextElementSibling;
                        // if the modal is not open, display it; else close it
                        if (menu.style.display == "none"){
                            menu.style.display = "block";
                        }else{
                            menu.style.display = "none";
                        }
                    }


                    function open_close_search(element, event){
                        // Using the click itself, get the div element that was clicked --> same as the parameter element
                        const search = event.target;
                        const bar = search.nextElementSibling;
                        // If the search bar is displaying, hide it; else show it
                        if (bar.style.display == "flex"){
                            bar.style.display = "none";
                            search.style.color = "black";
                        }else{
                            bar.style.display = "flex";
                            search.style.color = "#006CA1";
                        }
                    }


                    function edit_Post(element, event){
                        // Close the dropdown menu
                        element.parentElement.style.display = "none";
                        // Get id of the card that the user wants to edit
                        var id = event.target.nextElementSibling.id;
                        var body = document.getElementById("bottom");

                        const data = {Request: 'edit', iD: id};
                        // json stringify the data and send it to the backend, including the hidden CSRF token that's hidden at the top of the html
                        fetch('./backend.php', {
                            method: 'POST',
                            headers: {
                                "X-CSRF-Token": csrf_token,
                                'Content-Type': 'application/json',
                            },
                            body: JSON.stringify(data)
                        })
                            .then(response => response.json())
                            .then(result => {
                                // Displays existing user data into edit modal
                                document.getElementById("editpostBody").innerHTML = result.postBody;
                                document.getElementById("editaction").value = result.action;
                                document.getElementById("editPostModal").style.display = "block";

                                // Had an issue where this was triggering on the modal load instead of when the user clicks on the button in the modal
                                // https://stackoverflow.com/questions/35667267/addeventlistenerclick-firing-immediately fixed it
                                document.getElementById("updatePost").addEventListener("click", function(){
                                    updatePost(id);
                                });
                            })
                            .catch(error => {
                                console.error("Error:", error);
                                alert("An error occurred while trying to update status.");
                            });
                    }

  
                    function submitPost(){
                        var action = document.getElementById("action").value.trim();
                        var postBody = document.getElementById("postBody").value.trim();
                        var body = document.getElementById("bottom");

                        const data = {Request: "add", Action: action, PostBody: postBody};
                        // json stringify the data and send it to the backend, including the hidden CSRF token that's hidden at the top of the html
                        if (action.length > 0 && postBody.length > 0) {
                            fetch('./backend.php', {
                                method: 'POST',
                                headers: {
                                    "X-CSRF-Token": csrf_token,
                                    'Content-Type': 'application/json',
                                },
                                body: JSON.stringify(data)
                            })
                                .then(response => response.json())
                                .then(result => {
                                    // Refresh the page to load the new data in the database
                                    window.location.reload();
                                })
                                .catch(error => {
                                    console.error("Error:", error);
                                    alert("An error occurred while trying to update status.");
                                });
                        }
                    }


                    function updatePost(id){
                        var action = document.getElementById("editaction").value.trim();
                        var postBody = document.getElementById("editpostBody").value.trim();
                        var body = document.getElementById("bottom");

                        const data = {Request: "edit_add", iD: id, Action: action, PostBody: postBody};
                        // json stringify the data and send it to the backend, including the hidden CSRF token that's hidden at the top of the html
                        if (action.length > 0 && postBody.length > 0) {
                            fetch('./backend.php', {
                                method: 'POST',
                                headers: {
                                    "X-CSRF-Token": csrf_token,
                                    'Content-Type': 'application/json',
                                },
                                body: JSON.stringify(data)
                            })
                                .then(response => response.json())
                                .then(result => {
                                    // Reload the page to now show the updated database
                                    window.location.reload();
                                })
                                .catch(error => {
                                    console.error("Error:", error);
                                    alert("An error occurred while trying to update status.");
                                });
                        }
                    }


                    function delete_Post(element, event){
                        // Get id of the card that the user wants to delete
                        var id = event.target.previousElementSibling.id;
                        var body = document.getElementById("bottom");

                        const data = {Request: 'delete', iD: id};
                        // json stringify the card id that is being deleted and send it to the backend, including the hidden CSRF token that's hidden at the top of the html
                        fetch('./backend.php', {
                            method: 'POST',
                            headers: {
                                "X-CSRF-Token": csrf_token,
                                'Content-Type': 'application/json',
                            },
                            body: JSON.stringify(data)
                        })
                            .then(response => response.json())
                            .then(result => {
                                // Reload database, now that the card is deleted
                                window.location.reload();
                            })
                            .catch(error => {
                                console.error("Error:", error);
                                alert("An error occurred while trying to update status.");
                            });
                    }


                    function search(element, event){
                        var search_value = element.previousElementSibling.value;
                        if (search_value.length == 0){
                            alert('Please enter a search value');
                            throw new error();
                        }
                        // Escape HTML characters using encodeURIComponent
                        search_value = encodeURIComponent(search_value);

                        status_feed = '';
                        // Gets the list of all status cards
                        var feed_length = document.querySelectorAll('.post-card').length;
                        // Getting all status cards (need to add in the . before the class name like css)
                        document.querySelectorAll('.post-card').forEach((card) => {
                            // You have the html element card, to access the things inside the card, use querySelector to find the id(preface with # like css) or class(preface with . like css)
                            var username = card.querySelector('#username').innerHTML;
                            var action = card.querySelector('#action').innerHTML;
                            var body = card.querySelector('#card-body').innerHTML;

                            // If card does contain what you're looking for, add it to status_feed variable
                            if (username.toUpperCase().includes(search_value.toUpperCase())  ||  action.toUpperCase().includes(search_value.toUpperCase()) ||  body.toUpperCase().includes(search_value.toUpperCase())){
                                // Because card is an object HTMLdivElement from the querySelectorAll, need to convert it to a HTML string in order for the front end to show it on the screen
                                status_feed += card.outerHTML;
                            }
                        });
                        // If there are no matches
                        if (status_feed === ''){
                            alert('No match, please try again');
                        } else {
                            // If there are matches, set the bottom container innerHTML to the status feed and hide the search bar and button
                            document.getElementById('bottom').innerHTML = status_feed;
                            // Hides the search bar and search button once clicked
                            document.getElementById('searchForm').style.display = "none";
                            document.getElementById('clearSearchBtn').style.display = "block";
                        }
                    }


                    function refresh(){
                        window.location.reload();
                    }

                

                </script>
            </div>


        </div>
    </div>
</body>

</html>