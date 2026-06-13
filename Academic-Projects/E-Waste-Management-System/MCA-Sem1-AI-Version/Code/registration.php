<?php
require_once "db_connect.php";

$error_msg = "";
$success_msg = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // 1. Sanitize inputs
    $fullname = trim($_POST["fullname"]);
    $email = trim($_POST["email"]);
    $mobile = trim($_POST["mobile"]);
    $password = $_POST["password"];
    $confirm_password = $_POST["confirm_password"];
    $address = trim($_POST["address"]);
    $city = trim($_POST["city"]);
    $state = $_POST["state"];
    $pincode = trim($_POST["pincode"]);

    // 2. Validation
    if ($password !== $confirm_password) {
        $error_msg = "Passwords do not match.";
    } elseif (strlen($password) < 8) {
        $error_msg = "Password must be at least 8 characters.";
    } else {
        // Check if email exists
        $sql_check = "SELECT id FROM users WHERE email = ?";
        if ($stmt = mysqli_prepare($conn, $sql_check)) {
            mysqli_stmt_bind_param($stmt, "s", $email);
            if (mysqli_stmt_execute($stmt)) {
                mysqli_stmt_store_result($stmt);
                if (mysqli_stmt_num_rows($stmt) == 1) {
                    $error_msg = "This email is already registered.";
                }
            }
            mysqli_stmt_close($stmt);
        }

        // Insert User
        if (empty($error_msg)) {
            $sql = "INSERT INTO users (fullname, email, mobile, password, address, city, state, pincode) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            
            if ($stmt = mysqli_prepare($conn, $sql)) {
                $param_password = password_hash($password, PASSWORD_DEFAULT);
                mysqli_stmt_bind_param($stmt, "ssssssss", $fullname, $email, $mobile, $param_password, $address, $city, $state, $pincode);
                
                if (mysqli_stmt_execute($stmt)) {
                    $success_msg = "Registration successful! You can now <a href='login.php'>Login</a>.";
                } else {
                    $error_msg = "Something went wrong. Please try again.";
                }
                mysqli_stmt_close($stmt);
            }
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
    <title>Register | GreenCycle E-Waste Solutions</title>
    
    <!-- Fonts & Icons -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Poppins:wght@500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        /* --- CSS Variables --- */
        :root {
            --primary: #059669;
            --primary-dark: #047857;
            --secondary: #1f2937;
            --text-light: #6b7280;
            --white: #ffffff;
            --bg-light: #f3f4f6;
            --radius: 8px;
            --error: #ef4444;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--white);
            height: 100vh;
            overflow: hidden; 
        }

        .register-container {
            display: grid;
            grid-template-columns: 40% 60%;
            height: 100vh;
            width: 100%;
        }

        /* --- Left Side: Visual --- */
        .visual-side {
            background: linear-gradient(rgba(5, 150, 105, 0.9), rgba(4, 120, 87, 0.9)), 
                        url('https://images.unsplash.com/photo-1542601906990-b4d3fb778b09?ixlib=rb-4.0.3&auto=format&fit=crop&w=1000&q=80');
            background-size: cover;
            background-position: center;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: 60px;
            color: var(--white);
        }

        .visual-content h1 { font-family: 'Poppins', sans-serif; font-size: 2.5rem; line-height: 1.2; margin-bottom: 20px; }
        .visual-content ul { list-style: none; margin-top: 30px; }
        .visual-content li { margin-bottom: 15px; font-size: 1.1rem; display: flex; align-items: center; gap: 12px; }
        .visual-content li i { background: rgba(255,255,255,0.2); padding: 8px; border-radius: 50%; font-size: 0.9rem; }
        .visual-footer { font-size: 0.9rem; opacity: 0.8; }

        /* --- Right Side: Scrollable Form --- */
        .form-side {
            background-color: var(--white);
            overflow-y: auto; /* Allow scrolling */
            padding: 40px 80px;
            position: relative;
            display: block; /* Block for scrolling */
        }

        /* Wrapper to constrain width */
        .auth-wrapper {
            max-width: 550px; /* Slightly wider for 2-col inputs */
            margin: 40px auto 0; /* Top margin for back button clearance */
        }

        .back-home {
            position: absolute; top: 30px; left: 40px;
            color: var(--text-light); text-decoration: none;
            font-weight: 500; font-size: 0.9rem;
            display: flex; align-items: center; gap: 8px;
            transition: 0.3s; z-index: 10;
            background: rgba(255,255,255,0.9);
            padding: 5px 10px; border-radius: 4px;
        }
        .back-home:hover { color: var(--primary); }

        .form-header { margin-bottom: 30px; margin-top: 20px; }
        .form-header h2 { font-size: 2rem; color: var(--secondary); margin-bottom: 8px; }
        .form-header p { color: var(--text-light); }

        /* --- Form Grid Layout --- */
        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .full-width { grid-column: span 2; }

        .form-group { margin-bottom: 5px; }
        .form-group label {
            display: block; margin-bottom: 8px;
            color: var(--secondary); font-weight: 500; font-size: 0.9rem;
        }
        
        .input-wrapper { position: relative; }
        .input-wrapper i {
            position: absolute; left: 16px; top: 50%;
            transform: translateY(-50%); color: #9ca3af;
        }
        
        .form-group input, .form-group select, .form-group textarea {
            width: 100%; padding: 12px 16px 12px 45px;
            border: 1px solid #e5e7eb; border-radius: var(--radius);
            font-size: 0.95rem; transition: 0.3s;
            color: var(--secondary); font-family: 'Inter', sans-serif;
        }
        
        .form-group select.no-icon, .form-group textarea.no-icon { padding-left: 16px; }

        .form-group input:focus, .form-group select:focus, .form-group textarea:focus {
            outline: none; border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(5, 150, 105, 0.1);
        }

        .terms-check {
            margin-top: 10px; display: flex; align-items: start; gap: 10px;
            font-size: 0.9rem; color: var(--text-light);
        }
        .terms-check input { margin-top: 4px; accent-color: var(--primary); }
        .terms-check a { color: var(--primary); font-weight: 600; }

        .btn-register {
            width: 100%; padding: 14px; margin-top: 25px;
            background-color: var(--primary); color: var(--white);
            border: none; border-radius: var(--radius);
            font-size: 1rem; font-weight: 600;
            cursor: pointer; transition: 0.3s;
        }
        .btn-register:hover { background-color: var(--primary-dark); transform: translateY(-2px); }

        .alert-error {
            background-color: #fef2f2; color: var(--error);
            padding: 12px; border-radius: var(--radius);
            border: 1px solid #fecaca; margin-bottom: 20px;
        }
        .alert-success {
            background-color: #ecfdf5; color: var(--primary-dark);
            padding: 12px; border-radius: var(--radius);
            border: 1px solid #d1fae5; margin-bottom: 20px;
        }

        .login-link { text-align: center; margin-top: 20px; font-size: 0.95rem; color: var(--text-light); margin-bottom: 40px; }
        .login-link a { color: var(--primary); font-weight: 600; }

        /* --- Responsive --- */
        @media (max-width: 900px) {
            .register-container { grid-template-columns: 1fr; overflow-y: auto; display: block; }
            .visual-side { display: none; }
            .form-side { padding: 40px 24px; overflow-y: visible; }
            .auth-wrapper { margin: 0 auto; }
        }
        @media (max-width: 600px) {
            .form-grid { grid-template-columns: 1fr; }
            .full-width { grid-column: span 1; }
        }
    </style>
</head>
<body>

    <div class="register-container">
        <!-- Left Side (Visual Info) -->
        <div class="visual-side">
            <div class="logo">
                <i class="fas fa-recycle" style="font-size: 2rem;"></i>
            </div>
            <div class="visual-content">
                <h1>Join GreenCycle Today</h1>
                <p>Create an account to start your journey towards a cleaner environment.</p>
                <ul>
                    <li><i class="fas fa-check"></i> Schedule doorstep pickups easily</li>
                    <li><i class="fas fa-check"></i> Track real-time status of your requests</li>
                    <li><i class="fas fa-check"></i> Earn Green Points for every kg recycled</li>
                </ul>
            </div>
            <div class="visual-footer">
                &copy; 2025 GreenCycle E-Waste Solutions.
            </div>
        </div>

        <!-- Right Side (Registration Form) -->
        <div class="form-side">
            <a href="index.html" class="back-home"><i class="fas fa-arrow-left"></i> Home</a>

            <div class="auth-wrapper">
                <div class="form-header">
                    <h2>Create Account</h2>
                    <p>Fill in your details to register as a new user.</p>
                </div>

                <?php if(!empty($error_msg)){ ?>
                    <div class="alert-error"><?php echo $error_msg; ?></div>
                <?php } ?>
                <?php if(!empty($success_msg)){ ?>
                    <div class="alert-success"><?php echo $success_msg; ?></div>
                <?php } ?>

                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                    <div class="form-grid">
                        
                        <!-- Full Name -->
                        <div class="form-group full-width">
                            <label>Full Name</label>
                            <div class="input-wrapper">
                                <i class="far fa-user"></i>
                                <input type="text" name="fullname" placeholder="Rahul Nishad" required>
                            </div>
                        </div>

                        <!-- Email -->
                        <div class="form-group">
                            <label>Email Address</label>
                            <div class="input-wrapper">
                                <i class="far fa-envelope"></i>
                                <input type="email" name="email" placeholder="name@example.com" required>
                            </div>
                        </div>

                        <!-- Mobile -->
                        <div class="form-group">
                            <label>Mobile Number</label>
                            <div class="input-wrapper">
                                <i class="fas fa-mobile-alt"></i>
                                <input type="tel" name="mobile" placeholder="9876543210" pattern="[0-9]{10}" required>
                            </div>
                        </div>

                        <!-- Password -->
                        <div class="form-group">
                            <label>Password</label>
                            <div class="input-wrapper">
                                <i class="fas fa-lock"></i>
                                <input type="password" name="password" placeholder="Min 8 chars" minlength="8" required>
                            </div>
                        </div>

                        <!-- Confirm Password -->
                        <div class="form-group">
                            <label>Confirm Password</label>
                            <div class="input-wrapper">
                                <i class="fas fa-lock"></i>
                                <input type="password" name="confirm_password" placeholder="Re-enter password" required>
                            </div>
                        </div>

                        <!-- Address Heading -->
                        <div class="full-width" style="margin-top: 10px; margin-bottom: 5px;">
                            <h4 style="color: var(--secondary); border-bottom: 1px solid #e5e7eb; padding-bottom: 5px;">Pickup Address</h4>
                        </div>

                        <!-- Full Address -->
                        <div class="form-group full-width">
                            <label>Full Address</label>
                            <textarea name="address" class="no-icon" rows="2" placeholder="e.g. Flat 101, Green Society, Hinjewadi" required></textarea>
                        </div>

                        <!-- City -->
                        <div class="form-group">
                            <label>City</label>
                            <div class="input-wrapper">
                                <i class="fas fa-map-marker-alt"></i>
                                <input type="text" name="city" value="Pune" required>
                            </div>
                        </div>

                        <!-- State -->
                        <div class="form-group">
                            <label>State</label>
                            <select name="state" class="no-icon" required>
                                <option value="Maharashtra" selected>Maharashtra</option>
                                <option value="Karnataka">Karnataka</option>
                                <option value="Delhi">Delhi</option>
                                <option value="Gujarat">Gujarat</option>
                            </select>
                        </div>

                        <!-- PIN Code -->
                        <div class="form-group">
                            <label>PIN Code</label>
                            <div class="input-wrapper">
                                <i class="fas fa-map-pin"></i>
                                <input type="text" name="pincode" placeholder="411057" pattern="[0-9]{6}" required>
                            </div>
                        </div>
                        
                        <!-- Terms -->
                        <div class="form-group full-width terms-check">
                            <input type="checkbox" id="terms" required>
                            <label for="terms" style="display:inline; margin:0; font-weight:400;">I agree to the <a href="#">Terms & Conditions</a> and <a href="#">Privacy Policy</a>.</label>
                        </div>

                    </div>

                    <button type="submit" class="btn-register">Register Now</button>
                </form>

                <div class="login-link">
                    Already have an account? <a href="login.php">Login here</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>Fjohn