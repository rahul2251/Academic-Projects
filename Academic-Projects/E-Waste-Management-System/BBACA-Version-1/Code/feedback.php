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
    $email = $_POST['email'];
    $phNo = $_POST['phNo'];
    $comment = $_POST['comment'];

    // Insert feedback into the feedback table
    $sql = "INSERT INTO feedback (name, email, phNo, comment) VALUES ('$name', '$email', '$phNo', '$comment')";

    if ($conn->query($sql) === TRUE) {
        echo "<script>alert('Feedback submitted successfully!'); location='thankyou.html';</script>";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }

    $conn->close();
}
?>
