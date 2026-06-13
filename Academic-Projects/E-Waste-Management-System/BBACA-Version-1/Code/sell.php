<?php
$server_name = "localhost:3307";
$username = "root";
$password = "machine317";
$database_name = "ewaste_management";

// Create connection
$conn = new mysqli($server_name, $username, $password, $database_name);

// Check connection
if ($conn->connect_error) {
    die("Connection Failed: " . $conn->connect_error);
}

if (isset($_POST['submit'])) {
    $name = $_POST['name'];
    $pickup_date = $_POST['pickup_date'];
    $items = implode(", ", $_POST['item']);  // Convert array to string
    $nos = $_POST['nos'];

    // Insert sell request into the sell_request table
    $sql = "INSERT INTO sell_request (name, pickup_date, productList, nos) VALUES ('$name', '$pickup_date', '$items', '$nos')";

    if ($conn->query($sql) === TRUE) {
        echo "<script>alert('Sell request submitted successfully!'); location='Thankyou.html';</script>";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }

    $conn->close();
}
?>
