<!-- 
 
How to import the navbar
1. At the top of the page have this chunk of code (ignore the comment slashes, I dont want anything to break)
//        <?php
//            include '../Navbar/navbar.php';
//        ?>


2.Then inside of your <head> tag paste these lines of code 
        <link rel="stylesheet" href="../Navbar/navbar.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

4. Then run your page and test if its there
-->
<!-- <link `rel="stylesheet" type="text/css" href="./navbar.css"> -->
<div class="hamburger-menu">
    <i class="fa fa-bars" id="menuToggle"></i>
    <div class="nav-menu" id="navMenu">
	    <a href="https://se-prod.cse.buffalo.edu/CSE442/2024-Fall/cse-442m/Workout_Landing/Landing_Page.php">Landing Page</a>
        <a href="https://se-prod.cse.buffalo.edu/CSE442/2024-Fall/cse-442m/Workout_ProfilePage/Profile.php">Profile</a>
        <a href="https://se-prod.cse.buffalo.edu/CSE442/2024-Fall/cse-442m/Workout_PlannedWorkouts/PlannedWorkout.php">Planned Workout</a>
        <a href="https://se-prod.cse.buffalo.edu/CSE442/2024-Fall/cse-442m/Workout_SharedWorkouts/sharedworkouts.php">Shared Workouts</a>
        <a href="https://se-prod.cse.buffalo.edu/CSE442/2024-Fall/cse-442m/Workout_History/WorkoutHistory_FrontEnd.php">Workout History</a>
        <a href="https://se-prod.cse.buffalo.edu/CSE442/2024-Fall/cse-442m/Workout_MacroPage/Macros.php">Macronutrients</a>
        <a href="https://se-prod.cse.buffalo.edu/CSE442/2024-Fall/cse-442m/Workout_Stats/WorkoutStats.php">My Stats</a>
        <a href="https://se-prod.cse.buffalo.edu/CSE442/2024-Fall/cse-442m/Workout_Friends/Friends.php">Friends</a>
        <a href="https://se-prod.cse.buffalo.edu/CSE442/2024-Fall/cse-442m/Workout_Status/Status.php">Status</a>
    </div>
</div>

<script>

    document.querySelector('.hamburger-menu').addEventListener('click', function () {
        const menu = document.querySelector('.nav-menu');
        menu.style.display = menu.style.display === 'block' ? 'none' : 'block';
    });
</script>