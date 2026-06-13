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

  
if(isset($_POST['Update']))
 {
    $pid=$_POST['p_id'];
    $pprice=$_POST['p_price'];
    $pstock=$_POST['p_stock'];
  
    $result=mysqli_query($conn,"UPDATE product SET p_price='$pprice', p_stock='$pstock' WHERE p_id='$pid' ");
        if(mysqli_affected_rows($conn) > 0)

        { 

          ?>
           <script>
              { 
                 alert("Product updated Successfully..!");
                 location="product.html";
              }
              
          </script>
          <?php
        }
        else
              {
                ?>
                <script>
                  {
                    alert("Invalid Product Id/ Product Id dosent exists!!");
                    location="product.html";
                  }
              
                </script>
            
          <?php

              }
}
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
#footer
{
  width: 1536px;
  height: 50px;
  background-color: #2b2b2b;
  position: absolute;top: 93.4%;
}
#footer h1
{
  font: 23px Candara;
  color: #0eb4fb;
  position: absolute;top: -10%;left: 72%;
}
form
{
  border: 1px solid black;
  width: 550px;
  position: absolute;top: 20%;left: 30%;
  padding: 20px;
}
td
{
  font: 25px Calibri;
  padding: 15px 20px;
}
input[type=text]
{
  width: 250px;height: 25px; 
  font: 25px Calibri; 
}
input[type=submit]
{
  border: none;
  background-color: black;
  color: white;
  font: 22px Calibri;
  padding: 10px 20px;
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

    <div id="footer">
      <h1><i><b>E-WASTE MANAGEMENT SYSTEM</i></b></h1>
    </div>

    

    <form method="POST" action="updateProduct.php">
   <table>
   <tr>
       <td >Product ID:</td>
       <td><input type="text" name="p_id" required></td>
   </tr>
    

    <tr>
       <td>Updated Price:</td>
       <td><input type="text" name="p_price" required></td>
    </tr>

     <tr>
       <td>Updated Stock:</td>
       <td><input type="text" name="p_stock" required></td>
    </tr>

    <tr>
       <td><input type="submit" value="Update" name="Update"></td>
    </tr>

</table>


</body>
</html>