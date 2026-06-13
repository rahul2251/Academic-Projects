<?php
session_start();

// 1. Check if user is already logged in
if(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true){
    header("location: index.html");
    exit;
}

// 2. Database connection file
require_once "db_connect.php";

$email = $password = "";
$error_msg = "";

// 3. Handle Form Submission
if($_SERVER["REQUEST_METHOD"] == "POST"){
    $email = trim($_POST["email"]);
    $password = trim($_POST["password"]);
    
    if(empty($email) || empty($password)){
        $error_msg = "Please enter email and password.";
    } else {
        $sql = "SELECT id, fullname, password FROM users WHERE email = ?";
        
        if($stmt = mysqli_prepare($conn, $sql)){
            mysqli_stmt_bind_param($stmt, "s", $email);
            
            if(mysqli_stmt_execute($stmt)){
                mysqli_stmt_store_result($stmt);
                
                if(mysqli_stmt_num_rows($stmt) == 1){
                    mysqli_stmt_bind_result($stmt, $id, $fullname, $hashed_password);
                    if(mysqli_stmt_fetch($stmt)){
                        if(password_verify($password, $hashed_password)){
                            session_start();
                            $_SESSION["loggedin"] = true;
                            $_SESSION["id"] = $id;
                            $_SESSION["fullname"] = $fullname;
                            
                            header("location: index.php");
                        } else{
                            $error_msg = "Invalid password.";
                        }
                    }
                } else{
                    $error_msg = "No account found with that email.";
                }
            } else{
                $error_msg = "Something went wrong. Please try again.";
            }
            mysqli_stmt_close($stmt);
        }
    }
    mysqli_close($conn);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | GreenCycle E-Waste Solutions</title>

    <!-- Fonts & Icons -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Poppins:wght@500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- External CSS -->
    <link rel="stylesheet" href="login.css">
</head>
<body>

    <div class="login-container">
        <!-- Left Side (Visual) -->
        <div class="visual-side">
            <div class="logo">
                <i class="fas fa-recycle" style="font-size: 2rem;"></i>
            </div>
            <div class="visual-content">
                <h1>Welcome Back to GreenCycle</h1>
                <p>Track your pickups, view recycling certificates, and manage your environmental impact dashboard.</p>
            </div>
            <div class="visual-footer">
                &copy; 2025 GreenCycle E-Waste Solutions. Pune, India.
            </div>
        </div>

        <!-- Right Side (Form) -->
        <div class="form-side">
            <a href="index.html" class="back-home"><i class="fas fa-arrow-left"></i> Back to Home</a>

            <div class="auth-wrapper">
                <div class="form-header">
                    <h2>Login</h2>
                    <p>Enter your credentials to access your account.</p>
                </div>

                <?php if(!empty($error_msg)){ ?>
                    <div class="alert-error"><?php echo $error_msg; ?></div>
                <?php } ?>

                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <div class="input-wrapper">
                            <i class="far fa-envelope"></i>
                            <input type="email" name="email" id="email" placeholder="name@example.com" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="password">Password</label>
                        <div class="input-wrapper">
                            <i class="fas fa-lock"></i>
                            <input type="password" name="password" id="password" placeholder="Enter your password" required>
                        </div>
                    </div>

                    <button type="submit" class="btn-login">Sign In</button>
                </form>

                <div class="register-link">
                    Don't have an account? <a href="registration.php">Register for free</a>
                </div>
            </div>
        </div>
    </div>

</body>
</html>
