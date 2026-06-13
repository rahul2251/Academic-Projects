<!DOCTYPE html>
<html>
<head>
    <title>AddNewProduct</title>
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
option,select
{
  font: 20px Calibri;
}
input[type=submit]
{
  border: none;
  background-color: black;
  color: white;
  font: 22px Calibri;
  padding: 10px 20px;
}
input[type=file]
{
  font: 20px Calibri;
}
</style>
</head>
<body>

  <div class="top">

      <img src="logo.jpeg"><h1>GreenDream</h1><p><i>managed by </i><b>SHREE RECYCLERS</b></p>
        <ul>
           <li><a href="homepage.html">HOME</li></a>
        </ul>
              <h3 class="logout">LOG OUT</h3>
  </div>

      <div id="line"></div>


    <form method="post" action="addProduct.php">
<table>
    <tr>
       <td >Product ID:</td>
       <td><input type="text" name="p_id"></td>
   </tr>
    <tr>
       <td >Product Name:</td>
       <td><input type="text" name="p_name"></td>
   </tr>

    <tr>
       <td>Product Price:</td>
       <td><input type="text" name="p_price"></td>
    </tr>

     <tr>
       <td>Stock:</td>
       <td><input type="text" name="p_stock"></td>
    </tr>

      <tr>
         <td>Category:</td>
       <td><select name="category" required>
            <option value="Mobile">Mobile</option>
            <option value="Laptop">Laptop</option>
            <option value="Washing Machine">Washing Machine</option>
            <option value="T.V">T.V</option>
           </select>
       </td>
     </tr>

    <tr>
       <td><input type="submit" value="Add New Product" name="submit"></td>
    </tr>

</table>

</form>
</body>
</html>




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

if(isset($_POST['submit']))
{
      $pid=$_POST['p_id'];
      $pnm=$_POST['p_name'];
      $pprice=$_POST['p_price'];
      $pstk=$_POST['p_stock'];
      $category=$_POST['category'];
      
      $res = mysqli_query($conn, "INSERT INTO product (p_id, p_name, p_price, p_stock, p_category) VALUES ('$pid', '$pnm', '$pprice', '$pstk', '$category')");

        if($res>0)
        { 

          ?>
           <script>
             	alert("New Product Inserted Successfully..!");
               {
                 location="product.html";
               }
              
          </script>
          <?php
        }
        else
        {
          ?>
          <script>
            alert("No Product Added");
            {
              location="product.html";
            }
           
          </script>
         
          <?php

        }
    }

?>


