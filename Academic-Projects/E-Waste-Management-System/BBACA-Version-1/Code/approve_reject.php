<?php
$server_name="localhost:3307";
$username="root";
$password="machine317";
$database_name="ewaste_management";

$conn = new mysqli($server_name, $username, $password, $database_name);

if(!$conn) {
	die("Connection Failed: " . mysqli_connect_error());
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['request_id']) && isset($_POST['action'])) {
        $request_id = $_POST['request_id'];
        $action = $_POST['action'];

        // Update query
        $status = ($action == "approve") ? "Approved" : "Rejected";
        $update_query = "UPDATE sell_request SET status='$status' WHERE id=$request_id";

        if (mysqli_query($conn, $update_query)) {
            echo "<script>alert('Request $status successfully!'); window.location.href='sale_fetch.php';</script>";
        } else {
            echo "<script>alert('Error updating record.'); window.location.href='sale_fetch.php';</script>";
        }
    } else {
        echo "<script>alert('Please select a request.'); window.location.href='sale_fetch.php';</script>";
    }
}

mysqli_close($conn);
?>
