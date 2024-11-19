<?php
    include '../Navbar/navbar.php';
?>

<!DOCTYPE html>

<html lang="en">
    <head>
        <title>Nutrition Calculator</title>
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
            <div class="container">
                <div class="top-of-page">

                    <div class="team-name" style="margin-right: 43%">
                        <a href="https://se-prod.cse.buffalo.edu/CSE442/2024-Fall/cse-442m/Workout_ProfilePage/Profile.php" style="color:white; text-decoration: none;">Athletic Legs</a>
                    </div>

                </div>
                <div class="content">
                    <div id="title">Discover Your Macros</div>
                    
                    <div class="bottom-container">
                        <div class="macro-calculated">
                            
                            <?php 
                                $queries = array();
                                parse_str($_SERVER['QUERY_STRING'], $queries);
                                $calories = $queries['calories'];
                                $carbs = $queries['carbs'];
                                $protein = $queries['protein'];
                                $fat = $queries['fat'];
                                $inp = $queries['inp'];
                                echo"<div id='calperday'>Daily Caloric Intake<br><div id='calories'>$calories Cal</div></div>";
                                echo"<div class='piechart'>
                                    <div id='fat-amount'>Fat $fat g</div>
                                    <div id='carbs-amount'>Carbs $carbs g</div>
                                    <div id='protein-amount'>Protein $protein g</div>
                                    </div>"; 
                                if (!isset($inp)){
                                    echo"<form><input type='button' id='edit-macros' value='Edit' onclick='history.back()'></form>";
                                }
                                    
                            ?>
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