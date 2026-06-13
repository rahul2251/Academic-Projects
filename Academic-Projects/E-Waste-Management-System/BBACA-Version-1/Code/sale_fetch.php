<?php

$server_name="localhost:3307";
$username="root";
$password="machine317";
$database_name="ewaste_management";

$conn=new mysqli($server_name, $username, $password, $database_name);
if(!$conn)
{
	die("Connection Failed:" . mysqli_connect_error()); // Fixed mysql_connect_error() to mysqli_connect_error()
}

$query="SELECT * FROM sell_request";
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
button
{
	font: 25px Calibri;
	margin-left: 25px;
	padding: 5px 30px;
	cursor: pointer;
}
#footer
{
	width: 1536px;
	height: 50px;
	background-color: #2b2b2b;
	position: ;top: 93.4%;
}
#footer h1
{
	font: 23px Candara;
	color: #0eb4fb;
	position: absolute;top: -10%;left: 72%;
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

<form method="POST" action="approve_reject.php">
	<table align="center" border="1" cellpadding="20" id="tbl">
		<tr>
			<th colspan="6"> Sell Request  </th>
		</tr>
		<tr>
			<td>Select</td>
			<td>ID</td>
			<td>Username</td>
			<td>Pickup Date</td>
			<td>Product Name</td>
			<td>Quantity</td>
			<td>Status</td>
		</tr>
		<?php while($rows=mysqli_fetch_assoc($result)) { ?>
		<tr>
			<td><input type="radio" name="request_id" value="<?php echo $rows['id']; ?>"></td>
			<td><?php echo $rows['id'] ?></td>
			<td><?php echo $rows['name'] ?></td>
			<td><?php echo $rows['pickup_date'] ?></td>
			<td><?php echo $rows['productList'] ?></td>
			<td><?php echo $rows['nos'] ?></td>
			<td><?php echo $rows['status'] ?></td>
		</tr>
		<?php } ?>
	</table>

	<div style="text-align: center; margin-top: 20px;">
		<button type="submit" name="action" value="approve">APPROVE</button>
		<button type="submit" name="action" value="reject">REJECT</button>
	</div>
</form>

	<div id="footer">
		<h1><i><b>E-WASTE MANAGEMENT SYSTEM</i></b></h1>
	</div>

</body>
</html>
