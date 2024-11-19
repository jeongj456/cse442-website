<?php
    include '../Navbar/navbar.php';
?>

<!DOCTYPE html>

<html lang="en">
    <head>
        <title>Macronutrients</title>
        <link rel="stylesheet" href="../Navbar/navbar.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

        <link href='https://fonts.googleapis.com/css?family=Josefin Sans' rel='stylesheet'>
        <meta charset="UTF-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" media="screen and (max-width: 600px)" href="macros_mobile.css">
        <link rel="stylesheet" media="screen and (min-width: 601px)" href="macros.css">
    </head>
    <body>
        <form action="backend.php" method="POST">
            <input type="hidden" name="csrf_token" value="<?php session_start(); echo $_SESSION['csrf_token'];?>">
            <div class="container">
                <div class="top-of-page">
                    <div class="team-name">
                        <a href="https://se-prod.cse.buffalo.edu/CSE442/2024-Fall/cse-442m/Workout_ProfilePage/Profile.php" style="color:white; text-decoration: none;">Athletic Legs</a>
                    </div>

                    <div class="made-buttons" id="users-macro">
                        <?php 
                            $auth_token = $_COOKIE["Authentication_Token"];
                            $db_server = "localhost";
                            $db_user = "********";
                            $db_pass = "********";
                            $db_name = "********";
                            $conn;

                            try{
                                $conn = mysqli_connect($db_server, $db_user, $db_pass, $db_name);
                            }catch(mysqli_sql_exception){
                                echo"Connection Unsuccessful :(";
                            }

                            $query = "SELECT * FROM User_Accounts_Table WHERE id = '$auth_token'";

                            // Execute the query
                            $result = $conn->query($query);
                            
                            if ($result->num_rows == 1) {
                                $row = $result->fetch_assoc();
                                $username = $row['Username'];
                            } else {
                                #echo "No user is found";
                            }

                            $query = "SELECT * FROM macro WHERE username = '$username' ORDER BY id DESC";

                            // Execute the query
                            $result = $conn->query($query);
                            // Fetch the results
                            if ($result) {
                                while ($row = $result->fetch_assoc()){
                                    $calories = $row['calories'];
                                    $carbs = $row['carbs'];
                                    $protein = $row['protein'];
                                    $fat = $row['fat'];
                                    echo"<a href='https://se-prod.cse.buffalo.edu/CSE442/2024-Fall/cse-442m/Workout_MacroPage/macrosPieChart.php?calories=$calories&carbs=$carbs&protein=$protein&fat=$fat&inp=0' style='color:black;text-decoration: none;'>Current Macros</a>";
                                    break;
                                }
                            } else {
                                echo "No macros in database";
                            }

                            #echo"<a href='https://se-prod.cse.buffalo.edu/CSE442/2024-Fall/cse-442m/Workout_MacroPage/macrosPieChart.php?calories=$calories&carbs=$carbs&protein=$protein&fat=$fat&inp=0' style='color:black;text-decoration: none;'>Current Macros</a>";
                        ?>
                    </div>
                </div>
                <div class="content">
                    <div id="title" >Discover Your Macros</div>
                    
                    <div class="bottom-container">
                        <div class="left-container" >
                            <div id="units">
                                Metric
                                <label class="switch">
                                    <input type="checkbox" name="unit" id="unit">
                                    <span class="slider round"></span>
                                </label>
                                Imperial
                            </div>
                            <script>
                                document.getElementById("unit").addEventListener("change", switchUnit);
                                function switchUnit() {
                                    var unit = document.getElementById("heightt").placeholder; // Get current placeholder value
                                    if (unit == 'cm'){
                                        // Get the value in the attribute of the given tag with the element 'heightt' (in this case, placeholder)
                                        document.getElementById("heightt").placeholder = "in"; // Replaces the current placeholder
                                        document.getElementById("weight").placeholder = "lbs";
                                    }else{
                                        document.getElementById("heightt").placeholder = "cm";
                                        document.getElementById("weight").placeholder = "kg";
                                    }
                                }

                            </script>
                            <div class="row">
                                <div class="user-inputs" id="genders">
                                    Genders
                                    <div class="gender-buttons">
                                        <div class="made-buttons" id="male">
                                            <input type="radio" id="male-button" name="genders" value="male">
                                            <label for="male-button">Male</label>
                                        </div>
                                        <div class="made-buttons" id="female">
                                            <input type="radio" id="female-button" name="genders" value="female">
                                            <label for="female-button">Female</label>
                                        </div>
                                        <div class="made-buttons" id="prefer-not-to-say">
                                            <input type="radio" id="prefer-not-to-say-button" name="genders" value="prefer-not-to-say">
                                            <label for="prefer-not-to-say-button">Prefer not to say</label>
                                        </div>
                                    </div>

                                </div>

                                <div id="input-age">
                                    <label for="age" style="font-size: 20px;">Age</label><br>
                                    <input type="text" id="age" name="age" placeholder="Years">
                                </div>
                                <script>
                                    document.getElementById("age").addEventListener("blur", checkAge);
                                    function checkAge() {
                                        var age = document.getElementById("age").value; // Checks the value returned by the html input element with the id "age"
                                        const numbers = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
                                        if (age.length > 0){
                                            for (let i = 0; i < age.length; i++) {
                                                if (!numbers.includes(String(age[i]))) {
                                                    alert("Input valid age");
                                                    break;
                                                }
                                            }
                                            if ((age < 18) || (age > 80)){
                                                alert("Please enter an age between 18 and 80 years old");
                                            }
                                        }
                                    }
                                </script>
                            </div>

                            <div id="input-height">
                                <label for="height" style="font-size: 20px;">Height</label><br>
                                <input type="text" id="heightt" name="height" placeholder="cm">
                            </div>
                            <script>
                                document.getElementById("heightt").addEventListener("blur", checkHeight);
                                function checkHeight() {
                                    var height = document.getElementById("heightt").value;
                                    const numbers = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
                                    if (height.length > 0){
                                        for (let i = 0; i < height.length; i++) {
                                            if (!numbers.includes(String(height[i]))) {
                                                alert("Input valid height");
                                                break;
                                            }
                                        }
                                        if ((height <= 0) || (height > 300)){
                                            alert("Please enter a valid height");
                                        }
                                    }
                                }
                            </script>
                            
                            <div id="input-weight">
                                <label for="weight" style="font-size: 20px;">Weight</label><br>
                                <input type="text" id="weight" name="weight" placeholder="kg">
                            </div>
                            <script>
                                document.getElementById("weight").addEventListener("blur", checkWeight);
                                function checkWeight() {
                                    var weight = document.getElementById("weight").value;
                                    const numbers = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
                                    if (weight.length > 0){
                                        for (let i = 0; i < weight.length; i++) {
                                            if (!numbers.includes(String(weight[i]))) {
                                                alert("Input valid weight");
                                                break;
                                            }
                                        }
                                        if ((weight <= 0) || (weight > 1500)){
                                            alert("Please enter a valid weight");
                                        }
                                    }
                                }
                            </script>

                            <div class="user-inputs" id="lifestyle">
                                Lifestyle
                                <div class="lifestyle-buttons">
                                    <div class="made-buttons" id="sedintary">
                                        <input type="radio" id="sedintary-button" name="lifestyle-choices" value="sedintary">
                                        <label for="sedintary-button">Sedintary</label>
                                    </div>
                                    <div class="made-buttons" id="semi-active">
                                        <input type="radio" id="semi-active-button" name="lifestyle-choices" value="semi-active">
                                        <label for="semi-active-button">Semi Active</label>
                                    </div>
                                    <div class="made-buttons" id="active">
                                        <input type="radio" id="active-button" name="lifestyle-choices" value="active">
                                        <label for="active-button">Active</label>
                                    </div>
                                    <div class="made-buttons" id="very-active">
                                        <input type="radio" id="very-active-button" name="lifestyle-choices" value="very-active">
                                        <label for="very-active-button">Very Active</label>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="user-inputs" id="diet">Type of Diet
                                    <div class="diet-buttons">
                                        <div class="made-buttons" id="cutting">
                                            <input type="radio" id="cutting-button" name="diet-choices" value="cutting">
                                            <label for="cutting-button">Cutting</label>
                                        </div>
                                        <div class="made-buttons" id="maintenance">
                                            <input type="radio" id="maintenance-button" name="diet-choices" value="maintenance">
                                            <label for="maintenance-button">Maintenance</label>
                                        </div>
                                        <div class="made-buttons" id="bulking">
                                            <input type="radio" id="bulking-button" name="diet-choices" value="bulking">
                                            <label for="bulking-button">Bulking</label>
                                        </div>
                                    </div>
                                </div>
                            
                                <div class="page-loading">
                                    <button type="reset" id="refresh">↻</button>
                                    <!--Reset the background text in the weight and height textbox to metric units-->
                                    <script>
                                        document.getElementById("refresh").addEventListener("click", resetUnit);
                                        function resetUnit() {
                                            document.getElementById("heightt").placeholder = "cm";
                                            document.getElementById("weight").placeholder = "kg";
                                        }
                                    </script>
                                    <button type="submit" id="submit">✓</button>
                                </div>
                            </div> 
                        </div>
                    
                
                        <div class="right-container">
                            <div class="info-box" id="what-are-macros">What are Macronutrients?
                                <div id="macro-info"><a href="https://www.forbes.com/health/nutrition/what-are-macros/">Macronutrients</a> are comprised of carbohydrates, fats and proteins—the three types of essential nutrients we consume in the largest quantities that account for daily calorie intake. While each macronutrient functions differently in the body, all three contribute to generating energy (calories) and are necessary elements of nutrition.</div>
                            </div>
                            <div class="info-row">
                                <div class="info-box" id="carbs">Carbohydrates can be broken down into three subcategories—sugars, starches and <a href="https://www.forbes.com/health/nutrition/best-fiber-snacks/">fiber</a>—all of which are important for overall health. Sugars and starches are converted into glucose that the body uses for energy. Meanwhile, fiber isn't broken down by the body and instead may contribute to healthy digestion in the form of stool regularity, as well as normal <a href="https://www.forbes.com/health/wellness/healthy-cholesterol-levels/">cholesterol levels</a>.</div>
                                <div class="info-box" id="fats">Fats are another important source of dietary energy and aid in the absorption of certain fat soluble <a href="https://www.forbes.com/health/supplements/best-vitamins-for-energy/">vitamins</a>, such as A, <a href="https://www.forbes.com/health/supplements/vitamin-d-guide/">D</a>, E and K. Dietary fats are organized into two subgroups: saturated fats and unsaturated fats. Research suggests that saturated fat should comprise no more than 6% of total fats consumed. Alternatively, consuming unsaturated fats may help lower cholesterol levels.</div>
                                <div class="info-box" id="protein"><a href="https://www.forbes.com/health/nutrition/how-much-protein-do-you-need/">Protein</a> contains amino acids, which are vital building blocks for many bodily functions. In addition to producing energy, protein is an important nutrient for collagen production, which helps build and repair muscles and bones, and it helps regulate the functions of protein hormones like insulin. Myriad plant and animal food sources provide protein, such as chicken, beans and eggs.</div>
                            </div>
                        </div>
                    </div>
                </div>

                
            </div>
        </form>
    </body>
</html>