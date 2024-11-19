<?php

# Connects to database
$db_server = "localhost";
$db_user = "********";
$db_pass = "********";
$db_name = "********";
$conn;
$unit;
$gender;
$age;
$height;
$weight;
$lifestyle;
$diet;
$username = ""; //When binding param, can't leave variables blank
$auth_token = $_COOKIE["Authentication_Token"];

try{
  $conn = new mysqli($db_server, $db_user, $db_pass, $db_name);
}catch(mysqli_sql_exception){
  echo"Connection Unsuccessful :(";
}
session_start();
if (empty($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {     
  echo('Fail');
  exit();  
}


function calcMacro($gender, $age, $height, $weight, $lifestyle, $diet){
  $calories = 0;
  $carbs = 0;
  $protein = 0;
  $fat = 0;

  # Calculate the Basic Metabolic Rate
  if ($gender == "male"){
    $calories = ((10 * $weight) + ($height * 6.25) - (5 * $age) + 5);
  }elseif ($gender == "female"){
    $calories = ((10 * $weight) + ($height * 6.25) - (5 * $age) - 161);
  }elseif ($gender == "prefer-not-to-say"){
    $calories = ((10 * $weight) + ($height * 6.25) - (5 * $age) - 50);
  }

  # Adjust the calories according to lifestyle
  if ($lifestyle == "sedintary"){
    $calories = $calories * 1.2;
  }elseif ($lifestyle == "semi-active"){
    $calories = $calories * 1.375;
  }elseif ($lifestyle == "active"){
    $calories = $calories * 1.625;
  }elseif ($lifestyle == "very-active"){
    $calories = $calories * 1.9;
  }

  # Adjust according to diet type wanted
  if ($diet == "cutting"){
    $calories = $calories * 0.85;
  }elseif ($diet == "maintenance"){
    $calories = $calories + 0;
  }elseif ($diet == "bulking"){
    $calories = $calories + 500;
  }

  # Split to find the amount of calories alloted for each macro
  $carbs = $calories *0.4;
  if ($diet == "cutting"){
    $protein = $calories * 0.4;
    $fat = $calories * 0.2;
  }else{
    $protein = $calories * 0.3;
    $fat = $calories * 0.3;
  }

  # Divide the number of calories to find the number of grams
  $carbs = $carbs / 4;
  $protein = $protein / 4;
  $fat = $fat / 9;

  return array($calories, $carbs, $protein, $fat);
}

# Checks if the user filled out the entire form
if(empty($_POST["genders"])){
  die("Return to previous page and please select a gender");
}elseif(empty($_POST["age"])){
  die("Return to previous page and please enter your age");
}elseif(empty($_POST["height"])){
  die("Return to previous page and please enter your height");
}elseif(empty($_POST["weight"])){
  die("Return to previous page and please enter your weight");
}elseif(empty($_POST["lifestyle-choices"])){
  die("Return to previous page and please select a lifestyle");
}elseif(empty($_POST["diet-choices"])){
  die("Return to previous page and please select a type of diet");
}


$stmt = $conn->prepare("INSERT INTO macro_user_info (username, unit, gender, age, height, weight, lifestyle, diet) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("sssiiiss", $username, $unit, $gender, $age, $height, $weight, $lifestyle, $diet);

# Getting username from auth token
$query = "SELECT * FROM User_Accounts_Table WHERE id = '$auth_token'";

// Execute the query
$result = $conn->query($query);

if ($result->num_rows == 1) {
  $row = $result->fetch_assoc();
  $username = $row['Username'];
} else {
  echo "No saved data in database";
}
# Checks the unit
if (isset($_POST["unit"])) {
  $unit = "imperial";
} else {
  $unit = "metric";
}
$gender = $_POST["genders"];
$age = htmlspecialchars($_POST["age"]);
$height = htmlspecialchars($_POST["height"]);
$weight = htmlspecialchars($_POST["weight"]);
$lifestyle = $_POST["lifestyle-choices"];
$diet = $_POST["diet-choices"];

try{
  $stmt->execute();
}catch(Exception){
  echo "Insertion Failed";
}

# Insert the calculated macros into the database
$stmt = $conn->prepare("INSERT INTO macro (username, calories, carbs, protein, fat) VALUES (?, ?, ?, ?, ?)");
$stmt->bind_param("siiii", $username, $calories, $carbs, $protein, $fat);

if ($unit == "imperial"){
  # Convert inches to centimeters
  $height = ($height * 2.54);
  # Convert pounds to kg
  $weight = ($weight * 0.4535924);
}

$ar = calcMacro($gender, $age, $height, $weight, $lifestyle, $diet);
$calories = round($ar[0]);
$carbs = round($ar[1]);
$protein = round($ar[2]);
$fat = round($ar[3]);

$stmt->execute();

# Redirects to a new macro page once user inputs are inserted into the database
header("Location: https://se-prod.cse.buffalo.edu/CSE442/2024-Fall/cse-442m/Workout_MacroPage/macrosPieChart.php?calories=$calories&carbs=$carbs&protein=$protein&fat=$fat");

exit();



?>