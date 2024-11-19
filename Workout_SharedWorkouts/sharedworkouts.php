<?php
include '../Navbar/navbar.php';
?>

<!DOCTYPE html>

<html lang="en">

<head>
    <title>Shared Workouts</title>
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
    <input type="hidden" id="csrf_token" value="<?php session_start();
    echo $_SESSION['csrf_token']; ?>"></>
    <div class="container">
        <div class="top-of-page">
            <div class="title">
                Shared Workouts
            </div>
        </div>

        <div>Accepted Workouts</div>

        <div class="content">
            <div class="top">
                <div id="units">
                    Received
                    <label class="switch">
                        <input type="checkbox" name="unit" id="unit">
                        <span class="slider round"></span>
                    </label>
                    Shared
                </div>



                <div class="SearchBar">
                    <form id="searchForm">
                        <input type="text" id="searchInput" placeholder="Search title or @username..." required>
                        <button type="submit" id="searchworkoutname">Search</button>
                    </form>
                    <button id="clearSearchBtn">Clear Filter</button>
                </div>
                <div class=actionBtns>
                    <div class="made-buttons" id="incoming">
                        <a href="incomingworkouts.php" style="text-decoration: none; color: inherit;">
                            Incoming Workouts
                        </a>
                    </div>

                    <div>
                        <button id="incoming" class="made-buttons" onclick="openBlockModal()"
                            style="border: none; text-decoration: none; color: inherit;">
                            Block a User
                        </button>
                    </div>
                </div>

                <div id="blockUserModal" class="blockedModal">
                    <div class="blockedModal-content">
                        <span class="closeBlockModal"
                            onclick="document.getElementById('blockUserModal').style.display='none'">&times;</span>
                        <h2>Block a User</h2>
                        <form id="blockUserForm">
                            <label for="blockUsername">Enter Username to Block:</label>
                            <input type="text" id="blockUsername" name="username" required placeholder="Username">
                            <button type="submit">Block User</button>
                        </form>
                    </div>
                </div>

                <div id="logDataModal" class="modal">
                    <div class="modal-content-logWorkout">
                        <span class="logDataClose">&times;</span>
                        <h2>Log Workout Data</h2>
                        <form id="logDataForm">
                            <div id="logWorkoutDetails">
                                <!-- All workout rows will populate here -->
                            </div>
                        </form>
                        <button type="button" id="saveLogDataBtn">Save Data</button>
                    </div>
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

                        # Get all workouts id numbers in shared workouts table with the email equal to sent_to and acceptance set to 1(means you accepted)
                        // $query = "SELECT * FROM shared_workouts WHERE sent_to = '$email' AND acceptance = 1";
                        $query = "
                            SELECT * FROM shared_workouts 
                            WHERE sent_to = '$email' 
                            AND acceptance = 1 
                            AND sent_from NOT IN 
                                (SELECT Blocked_User 
                                FROM Blocked_Users 
                                WHERE Blocked_By_User = '$email')
                        ";
                        // $query = "SELECT * FROM shared_workouts WHERE sent_to = '$email' AND acceptance = 1";
                        $query = "
                            SELECT * FROM shared_workouts 
                            WHERE sent_to = '$email' 
                            AND acceptance = 1 
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
                                                    <li class='log-data' data-id='" . $cardIDX . "'>Log Data</li>
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
                            var logDataModal = document.getElementById("logDataModal");
                            var logDataClose = document.getElementsByClassName("logDataClose")[0];


                            document.getElementById("unit").addEventListener("change", switchCards);
                            document.getElementById("searchForm").addEventListener("submit", getCards);
                            document.getElementById("clearSearchBtn").addEventListener("click", switchCards);
                            document.querySelector('.workout-list').addEventListener('click', workoutListSelector);


                            logDataClose.onclick = function () {
                                logDataModal.style.display = "none";
                            };

                            // Close the Log Data modal when clicking outside of it
                            window.onclick = function (event) {
                                if (event.target == logDataModal) {
                                    logDataModal.style.display = "none";
                                }
                            };

                            document.getElementById("saveLogDataBtn").addEventListener("click", function () {
                                // Retrieve all the logged workout details
                                const sets = Array.from(document.querySelectorAll("input[name='sets[]']")).map(input => input.value);
                                const reps = Array.from(document.querySelectorAll("input[name='reps[]']")).map(input => input.value);
                                const weights = Array.from(document.querySelectorAll("input[name='weight[]']")).map(input => input.value);
                                const workoutDetails = [];
                                // Collecting workout details to match the format expected by AddFullWorkout
                                Array.from(document.querySelectorAll(".workout-row")).forEach((row, index) => {
                                    workoutDetails.push({
                                        workout: row.querySelector("p").textContent,
                                        sets: sets[index],
                                        reps: reps[index],
                                        weight: weights[index]
                                    });
                                });
                                const data = {
                                    Workout_Title: document.querySelector("#logWorkoutDetails h3").textContent, // or another identifier
                                    workout_Info: workoutDetails,
                                    Workout_TypeInputs: ["Strength", "Endurance"], // Example type inputs, replace as necessary
                                    Workout_Date: new Date().toISOString().slice(0, 10), // Current date in yyyy-mm-dd format
                                    loggingWorkout: true // Flag to indicate the logging operation
                                };
                                fetch("./backend.php", {
                                    method: "POST",
                                    headers: {
                                        "X-CSRF-Token": csrf_token,
                                        "Content-Type": "application/json"
                                    },
                                    body: JSON.stringify(data)
                                })
                                    .then(response => response.json())
                                    .then(result => {
                                        if (result.success) {
                                            alert("Workout data logged successfully!");
                                            logDataModal.style.display = "none";  // Close the modal
                                        } else {
                                            alert("Error logging data: " + result.message);
                                        }
                                    })
                                    .catch(error => {
                                        console.error("Error:", error);  // Log full error
                                        console.error("Full response text:", error.response ? error.response.text() : "No response text");
                                        alert("An error occurred while logging the data. Please check the console for more details.");
                                    });
                                logDataModal.style.display = "none";
                            });

                            function workoutListSelector() {
                                if (event.target.classList.contains('log-data')) {
                                    const workoutId = event.target.getAttribute('data-id');

                                    // Use `closest` to find the nearest individual workout card
                                    const workoutElement = event.target.closest('.individualWorkout');

                                    // Get the title and individual workout items within that specific card
                                    const dayName = workoutElement.querySelector('.WorkoutTitle strong').textContent;
                                    const workouts = Array.from(workoutElement.querySelectorAll('.WorkoutList li')).map(li => li.textContent.trim());

                                    // Populate the modal with workout details for this specific workout card
                                    const logWorkoutDetails = document.getElementById("logWorkoutDetails");
                                    logWorkoutDetails.innerHTML = `<h3>${dayName}</h3>`;

                                    workouts.forEach((workout, index) => {
                                        const workoutRow = document.createElement('div');
                                        workoutRow.classList.add('workout-row');
                                        workoutRow.innerHTML = `
                                                <p>${workout}</p>
                                                <label for="sets-${index}">Sets:</label>
                                                <input type="number" id="sets-${index}" name="sets[]" required>

                                                <label for="reps-${index}">Reps:</label>
                                                <input type="number" id="reps-${index}" name="reps[]" required>

                                                <label for="weight-${index}">Weight (lbs):</label>
                                                <input type="number" id="weight-${index}" name="weight[]" required>
                                        `;
                                        logWorkoutDetails.appendChild(workoutRow);
                                    });

                                    logDataModal.style.display = "block";  // Show the modal
                                }

                            }

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

                            document.querySelector('.workout-list').addEventListener('click', function (event) {
                                if (event.target.classList.contains('fa-ellipsis-v')) {
                                    // Get the dropdown content that is next to the clicked ellipsis
                                    var dropdown = event.target.nextElementSibling;

                                    // Hide all other dropdowns
                                    document.querySelectorAll('.dropdown-content').forEach((content) => {
                                        if (content !== dropdown) {
                                            content.style.display = 'none';
                                        }
                                    });

                                    // Toggle the current dropdown visibility
                                    dropdown.style.display = (dropdown.style.display === 'block') ? 'none' : 'block';
                                }

                            });

                            document.querySelector('.workout-list').addEventListener('click', function (event) {
                                if (event.target.classList.contains('fa-ellipsis-v')) {
                                    // Get the dropdown content that is next to the clicked ellipsis
                                    var dropdown = event.target.nextElementSibling;

                                    // Hide all other dropdowns
                                    document.querySelectorAll('.dropdown-content').forEach((content) => {
                                        if (content !== dropdown) {
                                            content.style.display = 'none';
                                        }
                                    });

                                    // Toggle the current dropdown visibility
                                    dropdown.style.display = (dropdown.style.display === 'block') ? 'none' : 'block';
                                }

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




                            /*


                            Blocking a user


                            */


                            // Get modal and button elements
                            var blockUserModal = document.getElementById("blockUserModal");
                            var closeBtn = document.getElementsByClassName("closeBlockModal")[0];
                            var blockUserForm = document.getElementById("blockUserForm");

                            // Open the modal when the Block button is clicked
                            function openBlockModal() {
                                blockUserModal.style.display = "block";
                            }

                            // Close the modal when the close button is clicked
                            closeBtn.onclick = function () {
                                blockUserModal.style.display = "none";
                            };

                            // Close the modal if user clicks outside of it
                            window.onclick = function (event) {
                                if (event.target == blockUserModal) {
                                    blockUserModal.style.display = "none";
                                }
                            };

                            // Handle form submission
                            blockUserForm.addEventListener("submit", function (event) {
                                event.preventDefault();
                                const username = document.getElementById("blockUsername").value;
                                const data = {
                                    blockedUsername: username,
                                    block: true
                                };
                                fetch('../Workout_BlockUser/blockedUser.php', {
                                    method: 'POST',
                                    headers: {
                                        "X-CSRF-Token": csrf_token,
                                        "Content-Type": "application/json"
                                    },
                                    body: JSON.stringify(data),
                                })
                                    .then(response => response.json())
                                    .then(data => {
                                        if (data.success) {
                                            alert("User blocked successfully!");
                                        } else {
                                            alert("Error blocking user: " + data.message);
                                        }
                                        blockUserModal.style.display = "none";
                                    })
                                    .catch(error => console.error("Error:", error));
                            });




                            function switchCards() {
                                document.getElementById("searchInput").value = "";

                                document.getElementById("searchInput").value = "";

                                var toggle = document.getElementById("unit"); // Get toggle to see if it is checked
                                var workoutcards = document.getElementById("allcards");
                                if (toggle.checked) {
                                    // Display workouts you shared(sent)
                                    fetch('./backend.php?action=sent_from', {
                                        method: 'GET',
                                        headers: {
                                            "X-CSRF-Token": csrf_token,
                                            'Content-Type': 'application/json',
                                        },
                                    })
                                        .then(response => response.json())
                                        .then(result => {
                                            workoutcards.innerHTML = result.message;
                                            console.log(result.message);
                                        })
                                        .catch(error => {
                                            console.error("Error:", error);
                                            alert("An error occurred while trying to retrieve all shared workouts.");
                                        });
                                } else {
                                    // Display workouts you shared(sent)
                                    fetch('./backend.php?action=sent_to', {
                                        method: 'GET',
                                        headers: {
                                            "X-CSRF-Token": csrf_token,
                                            'Content-Type': 'application/json',
                                        },
                                    })
                                        .then(response => response.json())
                                        .then(result => {
                                            workoutcards.innerHTML = result.message;
                                            console.log(result.message);
                                        })
                                        .catch(error => {
                                            console.error("Error:", error);
                                            alert("An error occurred while trying to retrieve all received workouts.");
                                        });
                                }
                            }

                            function getCards() {
                                event.preventDefault();
                                var workoutcards = document.getElementById("allcards");
                                var workout_name = encodeURIComponent(document.getElementById("searchInput").value.trim());
                                var searchUserOrTitle = workout_name.startsWith("%40");
                                var searchUserOrTitle = workout_name.startsWith("%40");
                                var to_or_from;
                                if (document.getElementById("unit").checked) {
                                    to_or_from = 'sent_from'; //shared
                                } else {
                                    to_or_from = 'sent_to';  //received and acceptance is 1
                                }

                                fetch('./backend.php?action=search&workout_name=' + workout_name + '&tof=' + to_or_from + '&type=' + searchUserOrTitle, {
                                    method: 'GET',
                                    headers: {
                                        "X-CSRF-Token": csrf_token,
                                        'Content-Type': 'application/json',
                                    },
                                })
                                    .then(response => {
                                        if (!response.ok) {
                                            throw new Error("Network response was not ok");
                                        }
                                        return response.json();
                                    })
                                    .then(result => {
                                        workoutcards.innerHTML = result.workouts || result.message;
                                        console.log(result.message || result.workouts);
                                    })
                                    .catch(error => {
                                        console.error("Error:", error);
                                        alert("An error occurred while searching. Please try again.");
                                    });
                            }

                            document.addEventListener('DOMContentLoaded', function () {
                                const workoutList = document.querySelector('.workout-list'); // Parent container for event delegation

                                // Ensure workoutList exists
                                if (workoutList) {
                                    workoutList.addEventListener('click', function (event) {
                                        // Check if the clicked element is a vertical ellipsis icon
                                        if (event.target.classList.contains('fa-ellipsis-v') || event.target.classList.contains('VerticalEllipses')) {
                                            const dropdown = event.target.nextElementSibling; // The associated dropdown content

                                            // Close any other open dropdowns
                                            document.querySelectorAll('.dropdown-content').forEach((content) => {
                                                if (content !== dropdown) {
                                                    content.style.display = 'none';
                                                }
                                            });

                                            // Toggle visibility of the current dropdown
                                            if (dropdown) {
                                                dropdown.style.display = dropdown.style.display === 'block' ? 'none' : 'block';
                                            }

                                            // Prevent event bubbling to avoid unexpected behavior
                                            event.stopPropagation();
                                        }
                                    });

                                    // Close dropdowns when clicking outside of them
                                    document.addEventListener('click', function (event) {
                                        if (!event.target.closest('.dropdown-content') && !event.target.classList.contains('fa-ellipsis-v')) {
                                            document.querySelectorAll('.dropdown-content').forEach((dropdown) => {
                                                dropdown.style.display = 'none';
                                            });
                                        }
                                    });
                                }
                            });
                            document.addEventListener('DOMContentLoaded', function () {
                                document.getElementById('clearSearchBtn').click();
                            });

                        </script>
                    </div>
                </div>


            </div>
        </div>
    </div>
</body>

</html>