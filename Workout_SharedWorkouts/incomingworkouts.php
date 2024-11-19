<?php
include '../Navbar/navbar.php';
?>

<!DOCTYPE html>

<html lang="en">

<head>
    <title>Incoming Workouts</title>
    <link rel="stylesheet" href="../Navbar/navbar.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href='https://fonts.googleapis.com/css?family=Josefin Sans' rel='stylesheet'>
    <link rel="stylesheet" media="screen and (max-width: 600px)" href="sharedworkouts_mobile.css">
    <link rel="stylesheet" media="screen and (min-width: 601px)" href="sharedworkouts.css">
</head>

<body>
    <form action="backend.php" method="POST">
        <input type="hidden" id="csrf_token" value="<?php session_start();
        echo $_SESSION['csrf_token']; ?>"></>
        <div class="container">
            <div class="top-of-page">
                <div class="title">
                    Shared Workouts
                </div>
            </div>
            <div>Incoming Workouts</div>
            <div class="content">
                <div class="bottom-container">
                    <div class="top-incomingworkouts">
                        <div class="made-buttons" id="incoming">
                            <a href="sharedworkouts.php" style="text-decoration: none; color: inherit;">
                                Accepted Workouts
                            </a>
                        </div>
                    </div>

                    <div class="bottom">

                        <div class="workout-list">
                            <div class="workout" id="allcards">
                                <?php
                                $conn = mysqli_connect('localhost', '********', '********', '********');
                                # Getting email from auth token
                                $auth_token = $_COOKIE["Authentication_Token"];
                                $query = "SELECT * FROM User_Accounts_Table WHERE id = '$auth_token'";
                                $result = $conn->query($query);
                                $row = $result->fetch_assoc();
                                $email = $row['Email'];

                                # Get all workouts id numbers in shared workouts table with the email equal to sent_to and the user accepted(1)
                                $query = "
                                SELECT * FROM shared_workouts 
                                WHERE sent_to = '$email' 
                                AND acceptance = 0
                                AND sent_from NOT IN 
                                    (SELECT Blocked_User 
                                    FROM Blocked_Users 
                                    WHERE Blocked_By_User = '$email')
                                ";
                                $result = $conn->query($query);
                                $rows = $result->fetch_all(MYSQLI_ASSOC);
                                foreach ($rows as $row) {
                                    $id = $row['ID'];
                                    $cardIDX = $row['idx'];
                                    $query1 = "SELECT * FROM planned_workouts WHERE ID = '$id'";
                                    $result1 = $conn->query($query1);
                                    $wks = $result1->fetch_assoc();
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
                                                            <li class='accept-workout' data-id='" . $cardIDX . "'>Accept</li>
                                                            <li class='deny-workout' data-id='" . $cardIDX . "'>Deny</li>
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
                                echo $all_shared_workouts;

                                ?>
                                <script>
                                    const csrf_token = document.getElementById("csrf_token").value;
                                    document.querySelectorAll('.fa-ellipsis-v').forEach((ellipsis) => {
                                        ellipsis.addEventListener('click', function (event) {
                                            // Get the dropdown content that is next to the clicked ellipsis
                                            var dropdown = this.nextElementSibling;

                                            // Hide all other dropdowns
                                            document.querySelectorAll('.dropdown-content').forEach((content) => {
                                                if (content !== dropdown) {
                                                    content.style.display = 'none';
                                                }
                                            });

                                            // Toggle the current dropdown visibility
                                            dropdown.style.display = (dropdown.style.display === 'block') ? 'none' : 'block';
                                        });
                                    });


                                    // Close the dropdown if the user clicks outside of it
                                    window.addEventListener('click', function (event) {
                                        // If the clicked element is not an ellipsis or within a dropdown, close all dropdowns
                                        if (!event.target.matches('.fa-ellipsis-v') && !event.target.closest('.dropdown-content')) {
                                            document.querySelectorAll('.dropdown-content').forEach((dropdown) => {
                                                dropdown.style.display = 'none';
                                            });
                                        }
                                    });


                                    document.querySelector('.workout-list').addEventListener('click', function (event) {
                                        // Check if the clicked element is a delete button
                                        if (event.target.classList.contains('accept-workout')) {
                                            const workoutId = event.target.getAttribute('data-id');
                                            console.log("Workout ID: ".workoutId);
                                            fetch('backend.php', {
                                                method: 'POST',
                                                headers: {
                                                    "X-CSRF-Token": csrf_token,
                                                    'Content-Type': 'application/json',
                                                },
                                                body: JSON.stringify({ action: 'acceptWorkout', id: workoutId }),
                                            })
                                                .then(response => response.json())
                                                .then(data => {
                                                    if (data.success) {
                                                        event.target.closest('.individualWorkout').remove();
                                                    } else {
                                                        alert("Failed to accept workout.");
                                                    }
                                                })
                                                .catch(error => {
                                                    console.error("Error:", error);
                                                });

                                        } else if (event.target.classList.contains('deny-workout')) {
                                            const workoutId = event.target.getAttribute('data-id');
                                            console.log("Workout ID: ".workoutId);
                                            fetch('backend.php', {
                                                method: 'POST',
                                                headers: {
                                                    "X-CSRF-Token": csrf_token,
                                                    'Content-Type': 'application/json',
                                                },
                                                body: JSON.stringify({ action: 'denyWorkout', id: workoutId }),
                                            })
                                                .then(response => response.json())
                                                .then(data => {
                                                    if (data.success) {
                                                        event.target.closest('.individualWorkout').remove();
                                                    } else {
                                                        alert("Failed to accept workout.");
                                                    }
                                                })
                                                .catch(error => {
                                                    console.error("Error:", error);
                                                });
                                        }
                                    });
                                </script>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </form>
</body>

</html>