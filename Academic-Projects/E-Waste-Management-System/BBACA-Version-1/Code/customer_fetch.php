<?php

$server_name="localhost:3307";
$username="root";
$password="machine317";
$database_name="ewaste_management";

$conn=new mysqli($server_name, $username, $password, $database_name);
if(!$conn)
{
	die("Connection Failed:" . mysql_connect_error());
}

$query="SELECT * FROM registration";
$result=mysqli_query($conn,$query);
?>

<!DOCTYPE html>
<html>
<head>
	<title> Fetch Data from Database </title>
<style type="text/css">
body
{
	margin: 0px;
	overflow-x: hidden;
	background-color: #fff5b3;
}

.table-container {
	overflow-x: auto;
	width: 95%; /* or 100% */
	margin: auto;
}

.top
{
	width: 1536px;
	height: 90px;
	background-color: #fff5b3;
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
	left: 79%;
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
.top ul li a:hover
{
	color: black;
	border-bottom: 3px solid black;
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
	background-color: black;
	position: absolute;
	top: 13%;
}
#tbl
{
	margin-top: 30px;
}
</style>
</head>

<body>
<div class="top"><img src="logo.jpeg"><h1>GreenDream</h1><p><i>managed by </i><b>SHREE RECYCLERS</b></p>
	<ul>
		<li><a href="homepage.html">HOME</li></a>
	</ul>
			<h3 class="logout">LOG OUT</h3>
</div>
			<div id="line"></div>


		<div class="table-container">

		<table align="center" border="1" cellpadding="20" id="tbl">
		<tr>
			<th colspan="13"> Customer Details </th>
		</tr>
		<tr>
			<td>id</td>
			<td>firstName</td>
			<td>lastName</td>
			<td>email</td>
			<td>phNo</td>
			<td>username</td>
			<td>password</td>
			<td>conPassword</td>
			<td>DOB</td>
			<td>gender</td>
			<td>address</td>
			<td>city</td>
			<td>pincode</td>
		</tr>
<?php
	while($rows=mysqli_fetch_assoc($result))
	{
?>
		<tr>
			<td><?php echo $rows['id'] ?></td>
			<td><?php echo $rows['firstName'] ?></td>
			<td><?php echo $rows['lastName'] ?></td>
			<td><?php echo $rows['email'] ?></td>
			<td><?php echo $rows['phNo'] ?></td>
			<td><?php echo $rows['username'] ?></td>
			<td><?php echo $rows['password'] ?></td>
			<td><?php echo $rows['conPassword'] ?></td>
			<td><?php echo $rows['DOB'] ?></td>
			<td><?php echo $rows['gender'] ?></td>
			<td><?php echo $rows['address'] ?></td>
			<td><?php echo $rows['city'] ?></td>
			<td><?php echo $rows['pincode'] ?></td>
		</tr>
<?php
	}
?>
	</table>
	</div>
</body>
</html>