<?php
	session_start();
	error_reporting(0);
	$profile=$_SESSION['username'];
	if($profile==true)
	{

	}
	else
	{
		header('location:customer_login.php');
	}
?>


<!DOCTYPE html>
<html>
<head>
<title>Profile</title>
<style type="text/css">
body
{
	margin: 0px;
	overflow-x: hidden;
}
.top
{
	width: 1536px;
	height: 90px;
	background-color: white;
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
	left: 71%;
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
	border-bottom: 3px solid #1cc606;
}
.top h3.logout
{
	font: 18px Calibri;
	color: black;
	background-color: #fb0442;
	position: absolute;
	top: 2.3%;
	left: 88%;
	padding: 5px 18px; 
	cursor: pointer;
}
div#line
{
	width: 1536px;
	height: 1px;
	background-color: #1cc606;
	position: absolute;
	top: 13%;
}
.sell a,.buy a
{
	font: 25px Calibri;
	border: 2px solid black;
	width: 278px;
	padding: 10px 40px;
	color: black;
	text-decoration: none;
	background-color: #05c267;
	border-radius: 20px;
}
.sell a
{
	position: absolute;top: 85%;left: 24%;
}
.buy a
{
	position: absolute;top: 85%;left: 50%;
}
#bg
{
	width: 1536px;
	height: 652px;
	position: absolute;top: 13.3%;
	background-image: url("customerbg.jpeg");
	background-position: center;
	background-size: cover;
	z-index: -1;
}
#bg div
{
	width: 100%;
	height: 100%;
	background-color: black;
	opacity: 0.6;
	position: absolute;top: 0%;left: 0%;
	z-index: -1
}
#bg p
{
	font: 45px Candara;
	color: white;
	position: absolute;left: 7%;top: 9%;
}
</style>
</head>
<div class="top"><img src="logo.jpeg"><h1>GreenDream</h1><p><i>managed by </i><b>SHREE RECYCLERS</b></p>
	<ul>
		<li><a href="homepage.html">HOME</li></a>
		<li><a href="feedback.html">FEEDBACK</li></a></font>
	</ul>
			<a href="logout.php"><h3 class="logout">LOG OUT</h3></a>
</div>
			<div id="line"></div>

		<div class="sell"><a href="sell.html">Click Here To Sell E-Waste</a></div>
		<div class="buy"><a href="buy.html">Click Here To Buy E-Waste</a></div>

		<div id="bg">
			 		<p> Welcome <?php echo $_SESSION['username']; ?></p>
			<div></div>
		</div>