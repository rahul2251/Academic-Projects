<?php
session_start();

// 1. Check if user is already logged in
if(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true){
    if($_SESSION["role"] === 'admin'){
        header("location: admin_dashboard.php");
    } elseif($_SESSION["role"] === 'collector'){ // UPDATED
        header("location: collector_dashboard.php"); // UPDATED
    } else {
        header("location: dashboard.php");
    }
    exit;
}

require_once "db_connect.php";
$email = $password = ""; $error_msg = "";

if($_SERVER["REQUEST_METHOD"] == "POST"){
    $email = trim($_POST["email"]);
    $password = trim($_POST["password"]);
    
    if(empty($email) || empty($password)){
        $error_msg = "Please enter email and password.";
    } else {
        $sql = "SELECT id, fullname, password, role FROM users WHERE email = ?";
        if($stmt = mysqli_prepare($conn, $sql)){
            mysqli_stmt_bind_param($stmt, "s", $email);
            if(mysqli_stmt_execute($stmt)){
                mysqli_stmt_store_result($stmt);
                if(mysqli_stmt_num_rows($stmt) == 1){
                    mysqli_stmt_bind_result($stmt, $id, $fullname, $hashed_password, $role);
                    if(mysqli_stmt_fetch($stmt)){
                        if(password_verify($password, $hashed_password)){
                            $_SESSION["loggedin"] = true;
                            $_SESSION["id"] = $id;
                            $_SESSION["fullname"] = $fullname;
                            $_SESSION["role"] = $role;
                            
                            if($role === 'admin'){
                                header("location: admin_dashboard.php");
                            } elseif($role === 'collector'){ // UPDATED
                                header("location: collector_dashboard.php"); // UPDATED
                            } else {
                                header("location: dashboard.php");
                            }
                        } else { $error_msg = "Invalid password."; }
                    }
                } else { $error_msg = "No account found."; }
            } else { $error_msg = "Oops! Something went wrong."; }
            mysqli_stmt_close($stmt);
        }
    }
    mysqli_close($conn);
}
?>
<!-- The rest of the HTML form remains the same as the previous login.php file -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | GreenCycle</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Poppins:wght@500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="auth.css">
</head>
<body class="auth-body">
    <div class="auth-container">
        <div class="visual-side">
            <div class="visual-content">
                <div class="logo" style="margin-bottom: 40px; color: white; font-size: 2rem;"><i class="fas fa-recycle"></i> GreenCycle</div>
                <h1>Welcome Back</h1>
                <p>Track your pickups and view recycling certificates.</p>
            </div>
            <div class="visual-footer">&copy; 2025 GreenCycle E-Waste Solutions.</div>
        </div>
        <div class="form-side center-content">
            <a href="index.html" class="back-home"><i class="fas fa-arrow-left"></i> Back to Home</a>
            <div class="auth-form-container">
                <div class="section-header"><h2>Login</h2><p>Enter your credentials to access your account.</p></div>
                <?php if(!empty($error_msg)){ echo '<div class="alert alert-error">'.$error_msg.'</div>'; } ?>
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                    <div class="form-group"><label>Email Address</label><div class="input-wrapper"><i class="far fa-envelope"></i><input type="email" name="email" placeholder="name@example.com" required></div></div>
                    <div class="form-group"><label>Password</label><div class="input-wrapper"><i class="fas fa-lock"></i><input type="password" name="password" placeholder="Enter your password" required></div></div>
                    <button type="submit" class="btn btn-primary" style="width:100%;">Sign In</button>
                </form>
                <div style="text-align:center; margin-top:20px;">Don't have an account? <a href="registration.php" style="color:var(--primary); font-weight:600;">Register for free</a></div>
            </div>
        </div>
    </div>
</body>
</html>