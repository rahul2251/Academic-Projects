<?php
session_start();

$server_name="localhost:3307";
$username="root";
$password="machine317";
$database_name="ewaste_management";

$conn=new mysqli($server_name, $username, $password, $database_name);
if(!$conn)
{
	die("Connection Failed:" . mysql_connect_error());
}
if(isset($_POST['login']))
{
	$un = $_POST['username'];
	$pwd = $_POST['password'];
	$fn = $_POST['firstName'];

	$query = "SELECT * FROM registration WHERE username='$un' && password='$pwd'";
	$data = mysqli_query($conn,$query);
	$total = mysqli_num_rows($data);
	
	if($total==1)
	{
		echo "login successful";
		$_SESSION['username']=$un;
		header('location:customer_page.php');
	}
	else
	{
		?>
           <script>
             	alert("Login Failed !! username and password is incorrect");
               {
                 location="customer_login.php";
               }
              
          </script>
    	<?php
	}
}
?>


<!DOCTYPE html>
<html>
<head>
<title>Login Form</title>
<style type="text/css">
body
{
	margin: 0px;
	overflow-x: hidden;
	background-color: #73f7e8;
}
.top
{
	width: 1536px;
	height: 90px;
	background-color: #73f7e8;
}
.top img
{
	width: 65px;
	height: 65px;
	border-radius: 20%;
	margin: 15px 40px;
}
.top h1
{
	font: 39px Candara;
	color: black;
	position: absolute;
	top: -1.2%;
	left: 7.5%;
}
.top p
{
	font: 18px Calibri;
	position: relative;top: -60px;left: 118px;
}
.top ul
{
	position: absolute;
	top: 3%;
	left: 51%;
}
.top ul li
{
	display: inline-block;
	list-style: none;
}
.top ul li a
{
	color: black;
	text-decoration: none;
	border: 0px solid black;
	font: 17.5px Calibri;
	padding: 5px 18px;
}
.top ul li a#home
{
	color: black;
	border-bottom: 3px solid #1cc606;
}
.top ul li a:hover
{
	color: black;
	border-bottom: 3px solid black;
}
form
{
	position: absolute;
	top: 55%;
	left: 50%;
	transform: translate(-50%,-50%);
	box-shadow: 0px 0px 4px 1px #4e4b4b;
	border: none;
	background-color: white; 
	padding: 60px 40px;
}
form h1
{
	font: 33px Berlin Sans FB;
	position: relative;
	top: 50px;
}
form img
{
	width: 100px;
	height: 100px;
	border: 0px solid blue;
	border-radius: 50%;
	position: relative;
	bottom: 120px;
}
form input[type=text],form input[type=password]
{
	font: 22px Calibri;
	width: 300px;
	height: 20px;
	padding: 10px 10px;
	position: relative;bottom: 40px;
}
form input[type=submit]
{
	font: 25px Calibri;
	border: none;
	width: 325px;
	height: 45px;
	cursor: pointer;
	border: 1px solid black;
	background-color: #138cd8;
	position: relative;bottom: 20px;
}
p
{
	font: 18px Calibri;
	position: absolute;
	top: 87.5%;left: 41%;
}

</style>
</head>
<body>
<div class="top"><img src="logo.jpeg"><h1>GreenDream</h1><p><i>managed by </i><b>SHREE RECYCLERS</b></p>
	<ul>
		<li><a href="homepage.html">HOME</li></a>
		<li><a href="about.html">ABOUT US</li></a>
		<li><a href="gallery.html">GALLERY</li></a>
		<li><a href="#">CONTACT US</li></a></font>
	</ul>
</div>
		<hr color="black">
			
	<form action="" method="POST">
		<center><h1>Customer Login</h1>
		<img src="loginlogo.png"></center>
		<input type="text" placeholder="Username" name="username" required ><br><br>
		<input type="password" placeholder="Password" name="password" required><br><br>
		<input type="submit" name="login" value="LogIn">
	</form>
				<p>Don't have an account? <a href="registration.php">Create now!</a></p>

</body>
</html>
