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

?>

  
<!DOCTYPE html>
<html>
<head>
<title>Television</title>
<style type="text/css">
body
{
  margin: 0px;
  overflow-x: hidden;
  background-color: #fff5b3;
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
  position: relative;bottom: 60px;left: 118px;
}
.top ul
{
  position: absolute;
  top: 3%;
  left: 75%;
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
  border-bottom: 3px solid #1cc606;
}
#footer
{
  width: 1536px;
  height: 50px;
  background-color: #2b2b2b;
  position: absolute;top: 93.3%;
}
#footer h1
{
  font: 23px Candara;
  color: #0eb4fb;
  position: absolute;top: -10%;left: 72%;
}
.mob
{
  width: 500px;
  height: 300px;
  border: 1px solid black;
  background-color: white;
  box-shadow: 0px 0px 5px 1px black;
  
h2
{
  font: 28px Calibri;
}
h3 a
{
  font: 25px Calibri;
  background-color: #02a29f;
  width: 140px;
  position: absolute;top: 70%;left: 20%;
  padding: 10px 30px;
  color: black;
  text-decoration: none;
}
</style>
</head>
<body>
<div class="top"><img src="logo.jpeg"><h1>GreenDream</h1><p><i>managed by </i><b>SHREE RECYCLERS</b></p>
  <ul>
    <li><a href="homepage.html">HOME</li></a>
    <li><a href="feedback.html">FEEDBACK</li></a>
    <li><a href="customer_login.php">LOG OUT</li></a></font>
  </ul>
</div>


<?php
$res=mysqli_query($conn,"SELECT * FROM product WHERE p_category='tv' ");

while($row=mysqli_fetch_array($res))
{
?>
      <center><div class="mob">
      <h2><center><b> Televisions </center></b><br>
      Product Name : <?php echo $row['2'];?><br>
      Product ID : <?php echo $row['1'];?><br>
      Price : <?php echo $row['3'];?></h2>
      <h3><a href="order.php?p_id=<?php echo "$row[1]" ?>">ORDER NOW</a></h3>
    </div></center>


<?php
}

?>



<div id="footer">
      <h1><i><b>E-WASTE MANAGEMENT SYSTEM</i></b></h1>
    </div>

</body>
</html>

