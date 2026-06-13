<?php
$server_name = "localhost:3307";
$username = "root";
$password = "machine317";
$database_name = "ewaste_management";

$conn = new mysqli($server_name, $username, $password, $database_name);
if ($conn->connect_error) {
    die("Connection Failed: " . $conn->connect_error);
}

session_start();

if (!isset($_SESSION['username'])) {
    die("User not logged in.");
}

$un = $_SESSION['username'];
;$pid = $_GET['p_id'];
;$price = $_GET['p_price'];
$odate = date("Y-m-d");

// Get the user's ID from the registration table
$get_user = mysqli_query($conn, "SELECT id FROM registration WHERE username = '$un'");
$user_row = mysqli_fetch_assoc($get_user);

if (!$user_row) {
    die("User not found in registration table.");
}

$user_id = $user_row['id'];

// Now insert using the user's actual ID
$query = mysqli_query($conn, "INSERT INTO `order` (id, p_id, p_price, order_date) VALUES ('$user_id', '$pid', '$price', '$odate')");

if ($query) {
    ?>
    <script>
        alert("Order Successful..!!");
        location = "thank you.php";
    </script>
    <?php
} else {
    ?>
    <script>
        alert("Something went wrong: <?php echo mysqli_error($conn); ?>");
    </script>
    <?php
}
?>
