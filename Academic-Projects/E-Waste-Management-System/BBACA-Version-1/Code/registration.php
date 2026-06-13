<!DOCTYPE html>
<html>
<head>
    <title>Registration form</title>
    <style type="text/css">
        body {
            margin: 0px;
            overflow-x: hidden;
        }
        .top {
            width: 1536px;
            height: 90px;
            background-color: white;
        }
        .top img {
            width: 65px;
            height: 65px;
            border-radius: 20%;
            margin: 15px 40px;
        }
        .top h1 {
            font: 39px Candara;
            color: black;
            position: absolute;
            top: -1.2%;
            left: 7.5%;
        }
        .top p {
            font: 18px Calibri;
            position: relative;
            top: -60px;
            left: 118px;
        }
        .top ul {
            position: absolute;
            top: 3%;
            left: 51%;
        }
        .top ul li {
            display: inline-block;
            list-style: none;
        }
        .top ul li a {
            color: black;
            text-decoration: none;
            border: 0px solid black;
            font: 17.5px Calibri;
            padding: 5px 18px;
        }
        .top ul li a#home {
            color: black;
            border-bottom: 3px solid #1cc606;
        }
        .top ul li a:hover {
            color: black;
            border-bottom: 3px solid #1cc606;
        }
        input, form {
            font: 15px Calibri;
            padding: 5px;
        }
        #ok {
            font: 25px Calibri;
            border: 1px solid black;
            width: 200px;
            height: 45px;
            position: relative;
            bottom: 30px;
            cursor: pointer;
            color: black;
            background-color: #0ce4b9;
        }
        #cancel {
            font: 25px Calibri;
            border: 1px solid black;
            width: 120px;
            height: 45px;
            position: relative;
            bottom: 30px;
            cursor: pointer;
            color: black;
            background-color: #0ce4b9;
        }
        input[type=text], input[type=date], input[type=email], input[type=password] {
            box-shadow: 0px 0px 4px 1px #4e4b4b;
            border: none;
            font: 15px Calibri;
            width: 320px;
            height: 20px;
        }
        textarea {
            box-shadow: 0px 0px 4px 1px #4e4b4b;
            border: none;
            font: 15px Calibri;
        }
        p#log {
            font: 24px Calibri;
            position: relative;
            bottom: 40px;
        }
        sup {
            color: red;
        }
        label {
            font: 20px Bahnschrift;
        }
        #ph {
            position: relative;
            left: 380px;
            bottom: 49px;
        }
        #no {
            position: relative;
            bottom: 47.5px;
            left: 45px;
        }
        #nm {
            width: 200px;
        }
        #pin {
            position: relative;
            left: 380px;
            bottom: 114.5px;
        }
        #code {
            position: relative;
            left: 380px;
            bottom: 125px;
        }
        form {
            position: relative;
            top: 40px;
            margin: 100px 200px;
            border: 2px solid #0ce4b9;
            padding-left: 50px;
            padding-top: 35px;
            box-shadow: 0px 0px 4px 1px #0ce4b9;
        }
        #title {
            font: 32px Calibri;
            position: absolute;
            top: 19%;
            left: 13%;
            background-color: #0ce4b9;
            padding: 5px 446px;
        }
        div#line {
            width: 1536px;
            height: 1px;
            background-color: #1cc606;
            position: absolute;
            top: 13%;
        }
    </style>
</head>
<body>
<div class="top">
    <img src="logo.jpeg">
    <h1>GreenDream</h1>
    <p><i>managed by </i><b>SHREE RECYCLERS</b></p>
    <ul>
        <li><a href="homepage.html">HOME</a></li>
        <li><a href="about.html">ABOUT US</a></li>
        <li><a href="gallery.html">GALLERY</a></li>
        <li><a href="#">CONTACT US</a></li>
    </ul>
</div>

<p id="title">Registration Form</p>

<form action="" method="POST">
    <label for="name">Name<sup>*</sup></label><br><br>
    <input type="text" placeholder="First name" name="firstName" required id="nm" pattern="[A-Za-z]+">
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
    <input type="text" placeholder="Last name" name="lastName" required id="nm" pattern="[A-Za-z]+"><br><br><br>

    <label for="email">Email Address<sup>*</sup></label><br><br>
    <input type="email" name="email" required>
    <label for="phNo" id="no">Mobile Number<sup>*</sup></label><br><br>
    <input id="ph" type="text" name="phNo" required pattern="[0-9]{10}"><br>

    <label for="username">Username<sup>*</sup></label><br><br>
    <input type="text" name="username" required><br><br><br>

    <label for="password">Enter Password<sup>*</sup></label><br><br>
    <input type="password" name="password" required><br><br><br>

    <label for="conPassword">Confirm Password<sup>*</sup></label><br><br>
    <input type="password" name="conPassword" required><br><br><br>

    <label for="DOB">Date of Birth<sup>*</sup></label><br><br>
    <input type="date" name="DOB" required><br><br><br>

    <label for="gender">Gender<sup>*</sup></label><br><br>
    <input type="radio" name="gender" value="male" required> Male
    <input type="radio" name="gender" value="female" required> Female
    <input type="radio" name="gender" value="other" required> Other<br><br><br>

    <label for="address">Address<sup>*</sup></label><br><br>
    <textarea rows="5" cols="62" placeholder="Enter your address" name="address" required></textarea><br><br><br>

    <label for="city">City<sup>*</sup></label><br><br>
    <input type="text" name="city" required><br><br><br>

    <label for="pincode" id="pin">Pincode<sup>*</sup></label><br><br>
    <input type="text" name="pincode" required id="code" pattern="[0-9]{6}"><br><br>

    <input id="ok" type="submit" name="register" value="Submit">
    <input id="cancel" type="reset" name="cancel" value="Cancel">
</form>

<center><p id="log">Already have an account? <a href="customer_login.php">LogIn</a></p></center>
<div id="line"></div>

<?php
// Database connection
$servername = "localhost:3307";
$dbUsername = "root"; // Default MySQL username for XAMPP
$dbPassword = "machine317"; // Default MySQL password for XAMPP
$dbname = "ewaste_management";

// Create a connection
$conn = new mysqli($servername, $dbUsername, $dbPassword, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Collect form data
    $firstName = mysqli_real_escape_string($conn, $_POST['firstName']);
    $lastName = mysqli_real_escape_string($conn, $_POST['lastName']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $phNo = mysqli_real_escape_string($conn, $_POST['phNo']);
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);
    $conPassword = mysqli_real_escape_string($conn, $_POST['conPassword']);
    $DOB = mysqli_real_escape_string($conn, $_POST['DOB']);
    $gender = mysqli_real_escape_string($conn, $_POST['gender']);
    $address = mysqli_real_escape_string($conn, $_POST['address']);
    $city = mysqli_real_escape_string($conn, $_POST['city']);
    $pincode = mysqli_real_escape_string($conn, $_POST['pincode']);

    // Check if passwords match
    if ($password !== $conPassword) {
        echo "<script>alert('Passwords do not match');</script>";
    } else {
        // Hash password
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);

        // SQL query to insert data into the database
        $sql = "INSERT INTO registration (firstName, lastName, email, phNo, username, password, DOB, gender, address, city, pincode) 
                VALUES ('$firstName', '$lastName', '$email', '$phNo', '$username', '$hashed_password', '$DOB', '$gender', '$address', '$city', '$pincode')";

        if ($conn->query($sql) === TRUE) {
            echo "<script>alert('Registration successful');</script>";
        } else {
            echo "Error: " . $sql . "<br>" . $conn->error;
        }
    }
}

// Close the connection
$conn->close();
?>

</body>
</html>
