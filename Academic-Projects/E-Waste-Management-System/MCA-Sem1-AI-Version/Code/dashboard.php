<?php
session_start();

// 1. Security
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
}
if($_SESSION["role"] === 'admin') { header("location: admin_dashboard.php"); exit; }
if($_SESSION["role"] === 'collector') { header("location: collector_dashboard.php"); exit; }

require_once "db_connect.php";
$user_id = $_SESSION["id"];
$fullname = $_SESSION["fullname"];

$msg = ""; $msg_type = "";

// --- HANDLERS ---

// A. Handle "New Pickup"
if($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'schedule'){
    $date = $_POST['pickup_date'];
    $items = $_POST['items'];
    $sql = "INSERT INTO pickup_requests (user_id, scheduled_date, items, status) VALUES (?, ?, ?, 'Pending')";
    if($stmt = mysqli_prepare($conn, $sql)){
        mysqli_stmt_bind_param($stmt, "iss", $user_id, $date, $items);
        if(mysqli_stmt_execute($stmt)) { $msg = "Pickup scheduled successfully!"; $msg_type = "success"; }
        else { $msg = "Error scheduling pickup."; $msg_type = "error"; }
        mysqli_stmt_close($stmt);
    }
}

// B. Handle "Cancel Request"
if($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'cancel'){
    $req_id = $_POST['request_id'];
    $sql = "DELETE FROM pickup_requests WHERE id = ? AND user_id = ? AND status = 'Pending'";
    if($stmt = mysqli_prepare($conn, $sql)){
        mysqli_stmt_bind_param($stmt, "ii", $req_id, $user_id);
        if(mysqli_stmt_execute($stmt)) { $msg = "Request cancelled."; $msg_type = "success"; }
        else { $msg = "Error cancelling request."; $msg_type = "error"; }
        mysqli_stmt_close($stmt);
    }
}

// C. Handle "Update Profile & Password" (UPDATED with Pincode)
if($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'update_profile'){
    $new_mobile = $_POST['mobile'];
    $new_pincode = $_POST['pincode']; // New Pincode Field
    $new_address = $_POST['address'];
    $new_pass = $_POST['password'];
    
    // Default Update Query (Mobile, Pincode, Address)
    $sql = "UPDATE users SET mobile = ?, pincode = ?, address = ? WHERE id = ?";
    $types = "sssi";
    $params = array($new_mobile, $new_pincode, $new_address, $user_id);

    // If password is provided, update it too
    if(!empty($new_pass)){
        $sql = "UPDATE users SET mobile = ?, pincode = ?, address = ?, password = ? WHERE id = ?";
        $hash = password_hash($new_pass, PASSWORD_DEFAULT);
        $types = "ssssi";
        $params = array($new_mobile, $new_pincode, $new_address, $hash, $user_id);
    }

    if($stmt = mysqli_prepare($conn, $sql)){
        mysqli_stmt_bind_param($stmt, $types, ...$params);
        if(mysqli_stmt_execute($stmt)) { $msg = "Profile details updated!"; $msg_type = "success"; }
        else { $msg = "Update failed."; $msg_type = "error"; }
        mysqli_stmt_close($stmt);
    }
}

// D. Submit Feedback
if($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'submit_feedback'){
    $rating = $_POST['rating'];
    $comment = trim($_POST['comment']);
    $sql = "INSERT INTO feedback (user_id, rating, comments) VALUES (?, ?, ?)";
    if($stmt = mysqli_prepare($conn, $sql)){
        mysqli_stmt_bind_param($stmt, "iis", $user_id, $rating, $comment);
        if(mysqli_stmt_execute($stmt)) { $msg = "Thank you for your feedback!"; $msg_type = "success"; }
        else { $msg = "Error submitting feedback."; $msg_type = "error"; }
        mysqli_stmt_close($stmt);
    }
}

// --- DATA FETCHING ---

// 1. User Details (Fetch Pincode too)
$user_q = mysqli_query($conn, "SELECT mobile, address, pincode FROM users WHERE id = $user_id");
$user_data = mysqli_fetch_assoc($user_q);

// 2. Stats
$stats = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total, SUM(CASE WHEN status='Pending' THEN 1 ELSE 0 END) as pending, SUM(CASE WHEN status='Completed' THEN 1 ELSE 0 END) as completed FROM pickup_requests WHERE user_id = $user_id"));
$total_requests = $stats['total']; 
$pending_requests = $stats['pending'] ?? 0; 
$completed_requests = $stats['completed'] ?? 0;

// 3. Request History (Joined with Collector info)
$requests = [];
$sql_hist = "SELECT r.*, c.fullname as collector_name, c.mobile as collector_mobile 
             FROM pickup_requests r 
             LEFT JOIN users c ON r.collector_id = c.id 
             WHERE r.user_id = $user_id 
             ORDER BY r.created_at DESC LIMIT 10";
$res_hist = mysqli_query($conn, $sql_hist);
if($res_hist) while($row = mysqli_fetch_assoc($res_hist)) $requests[] = $row;

mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Dashboard | GreenCycle</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Poppins:wght@500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- TF.js -->
    <script src="https://cdn.jsdelivr.net/npm/@tensorflow/tfjs@latest"></script>
    <script src="https://cdn.jsdelivr.net/npm/@tensorflow-models/mobilenet"></script>

    <style>
        :root { --primary: #059669; --primary-dark: #047857; --secondary: #1f2937; --bg-light: #f3f4f6; --white: #ffffff; --radius: 12px; --shadow: 0 4px 6px -1px rgba(0,0,0,0.1); }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background: var(--bg-light); color: var(--secondary); }
        
        /* Layout */
        .navbar { background: var(--white); height: 70px; padding: 0 30px; display: flex; align-items: center; justify-content: space-between; position: sticky; top: 0; z-index: 100; box-shadow: var(--shadow); }
        .brand { font-family: 'Poppins', sans-serif; font-weight: 700; font-size: 1.4rem; color: var(--secondary); display: flex; gap: 10px; } .brand i { color: var(--primary); }
        .btn-logout { color: #ef4444; border: 1px solid #ef4444; padding: 6px 14px; border-radius: 6px; font-size: 0.85rem; text-decoration: none; transition:0.3s;} .btn-logout:hover { background: #ef4444; color: white; }
        
        .dashboard-grid { max-width: 1200px; margin: 30px auto; padding: 0 20px; display: grid; grid-template-columns: 350px 1fr; gap: 30px; }
        
        /* Cards */
        .card { background: var(--white); padding: 25px; border-radius: var(--radius); box-shadow: var(--shadow); margin-bottom: 30px; }
        .card h3 { margin-bottom: 20px; font-family: 'Poppins', sans-serif; border-bottom: 1px solid #e5e7eb; padding-bottom: 10px; display: flex; align-items: center; gap: 10px; }
        
        /* Inputs & Buttons */
        .form-group { margin-bottom: 15px; } .form-group label { display: block; margin-bottom: 5px; font-weight: 500; font-size: 0.9rem; } 
        .form-group input, .form-group textarea, .form-group select { width: 100%; padding: 10px; border: 1px solid #d1d5db; border-radius: 8px; font-family: 'Inter', sans-serif; }
        .btn-primary { width: 100%; padding: 12px; background: var(--primary); color: white; border: none; border-radius: 8px; cursor: pointer; font-weight: 600; }
        .btn-danger { background: #ef4444; color: white; border: none; padding: 5px 10px; border-radius: 4px; cursor: pointer; font-size: 0.8rem; }

        /* AI Scanner */
        #ai-preview { width: 100%; height: 200px; border-radius: 8px; object-fit: contain; background: #f9fafb; border: 2px dashed #d1d5db; margin-bottom: 15px; display: flex; align-items: center; justify-content: center; }
        #ai-result { background: #ecfdf5; padding: 15px; border-radius: 8px; margin-top: 10px; display: none; }
        .confidence-bar { height: 6px; background: #d1fae5; border-radius: 3px; margin-top: 5px; overflow: hidden; }
        .confidence-fill { height: 100%; background: var(--primary); width: 0%; transition: width 0.5s; }

        /* Stats & Table */
        .stats-row { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-bottom: 30px; }
        .stat-box { background: var(--white); padding: 20px; border-radius: var(--radius); text-align: center; box-shadow: var(--shadow); }
        .stat-box h4 { font-size: 0.9rem; color: #6b7280; } .stat-box p { font-size: 1.8rem; font-weight: 700; color: var(--secondary); }
        
        table { width: 100%; border-collapse: collapse; } th, td { padding: 12px; text-align: left; border-bottom: 1px solid #e5e7eb; font-size: 0.9rem; }
        .status-badge { padding: 4px 10px; border-radius: 20px; font-size: 0.75rem; font-weight: 600; }
        .status-pending { background: #fff7ed; color: #c2410c; } .status-accepted { background: #dbeafe; color: #1e40af; } .status-in-transit { background: #ede9fe; color: #5b21b6; } .status-completed { background: #ecfdf5; color: #047857; } .status-rejected { background: #fee2e2; color: #991b1b; }

        /* Timeline Styles */
        .timeline-container { margin-top: 10px; }
        .timeline { display: flex; justify-content: space-between; position: relative; margin-bottom: 20px; }
        .timeline::before { content: ''; position: absolute; top: 14px; left: 0; right: 0; height: 2px; background: #e5e7eb; z-index: 0; }
        .timeline-step { position: relative; z-index: 1; text-align: center; background: var(--white); padding: 0 5px; }
        .step-circle { width: 30px; height: 30px; background: #e5e7eb; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 5px; font-size: 0.8rem; color: #6b7280; font-weight: bold; transition: 0.3s; }
        .step-label { font-size: 0.75rem; color: #6b7280; font-weight: 500; }
        
        /* Active Step Styling */
        .timeline-step.active .step-circle { background: var(--primary); color: white; box-shadow: 0 0 0 4px #d1fae5; }
        .timeline-step.active .step-label { color: var(--primary); font-weight: 700; }
        .timeline-step.completed .step-circle { background: var(--primary); color: white; }

        .collector-info { background: #f0fdf4; border: 1px solid #bbf7d0; padding: 10px; border-radius: 8px; font-size: 0.85rem; margin-top: 5px; display: flex; align-items: center; gap: 10px; color: #166534; }

        .msg { padding: 10px; border-radius: 6px; margin-bottom: 15px; text-align: center; }
        .msg-success { background: #d1fae5; color: #065f46; } .msg-error { background: #fee2e2; color: #991b1b; }

        @media(max-width: 900px){ .dashboard-grid { grid-template-columns: 1fr; } }
    </style>
</head>
<body>

<nav class="navbar">
    <div class="brand"><i class="fas fa-recycle"></i> GreenCycle</div>
    <div><span style="margin-right: 15px; font-weight: 600;">Hi, <?php echo htmlspecialchars($fullname); ?></span><a href="logout.php" class="btn-logout">Logout</a></div>
</nav>

<div class="dashboard-grid">
    <aside>
        <!-- AI SCANNER -->
        <div class="card">
            <h3><i class="fas fa-camera"></i> AI E-Waste Scanner</h3>
            <input type="file" id="image-upload" accept="image/*" onchange="previewImage(event)">
            <div id="ai-preview"><span style="color:#aaa">Image Preview</span></div>
            <button onclick="identifyImage()" class="btn-primary" id="scan-btn" style="background: #4f46e5;">Scan Image</button>
            <div id="loading" style="display:none; text-align:center; margin-top:10px;">Analyzing...</div>
            <div id="ai-result">
                <strong>Detected:</strong> <span id="label-name">--</span><br>
                <strong>Confidence:</strong> <span id="conf-score">--</span>
                <div class="confidence-bar"><div class="confidence-fill" id="conf-fill"></div></div>
                <p id="suggestion-text" style="font-size: 0.85rem; margin-top: 8px; color: #047857;"></p>
                <button onclick="addToRequest()" style="margin-top:10px; font-size:0.8rem; background:transparent; border:1px solid var(--primary); color:var(--primary); padding:5px; border-radius:4px; cursor:pointer;">+ Add to Pickup</button>
            </div>
        </div>

        <!-- PROFILE & PASSWORD -->
        <div class="card">
            <h3><i class="fas fa-user-edit"></i> Profile & Security</h3>
            <form method="post">
                <input type="hidden" name="action" value="update_profile">
                <div class="form-group"><label>Mobile</label><input type="text" name="mobile" value="<?php echo htmlspecialchars($user_data['mobile']); ?>" required></div>
                
                <!-- Added Pincode Field -->
                <div class="form-group">
                    <label>Pincode</label>
                    <input type="text" name="pincode" value="<?php echo htmlspecialchars($user_data['pincode']); ?>" placeholder="e.g. 411057" required>
                </div>

                <div class="form-group"><label>Address</label><textarea name="address" rows="2" required><?php echo htmlspecialchars($user_data['address']); ?></textarea></div>
                
                <div class="form-group">
                    <label>Change Password <small>(Optional)</small></label>
                    <input type="password" name="password" placeholder="New Password (Min 8 chars)">
                </div>
                <button type="submit" class="btn-primary" style="background:#374151;">Update Details</button>
            </form>
        </div>

        <!-- FEEDBACK -->
        <div class="card">
            <h3><i class="fas fa-star"></i> Give Feedback</h3>
            <form method="post">
                <input type="hidden" name="action" value="submit_feedback">
                <div class="form-group">
                    <label>Rate Us</label>
                    <select name="rating" required>
                        <option value="5">⭐⭐⭐⭐⭐ (Excellent)</option>
                        <option value="4">⭐⭐⭐⭐ (Good)</option>
                        <option value="3">⭐⭐⭐ (Average)</option>
                        <option value="2">⭐⭐ (Poor)</option>
                        <option value="1">⭐ (Bad)</option>
                    </select>
                </div>
                <div class="form-group"><label>Comments</label><textarea name="comment" rows="3" placeholder="Your suggestions..." required></textarea></div>
                <button type="submit" class="btn-primary" style="background:#ea580c;">Submit Feedback</button>
            </form>
        </div>
    </aside>

    <main>
        <?php if(!empty($msg)): ?><div class="msg msg-<?php echo $msg_type; ?>"><?php echo $msg; ?></div><?php endif; ?>

        <!-- STATS -->
        <div class="stats-row">
            <div class="stat-box"><h4>Total Requests</h4><p><?php echo $total_requests; ?></p></div>
            <div class="stat-box"><h4>Pending</h4><p style="color:#f97316"><?php echo $pending_requests; ?></p></div>
            <div class="stat-box"><h4>Recycled</h4><p style="color:#059669"><?php echo $completed_requests; ?></p></div>
        </div>

        <!-- SCHEDULE -->
        <div class="card">
            <h3><i class="fas fa-calendar-plus"></i> Schedule New Pickup</h3>
            <form method="post">
                <input type="hidden" name="action" value="schedule">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    <div class="form-group"><label>Date</label><input type="date" name="pickup_date" required min="<?php echo date('Y-m-d'); ?>"></div>
                    <div class="form-group"><label>Items</label><input type="text" name="items" id="items-input" placeholder="e.g. 1 Monitor" required></div>
                </div>
                <button type="submit" class="btn-primary">Confirm Pickup Request</button>
            </form>
        </div>

        <!-- HISTORY & TRACKING -->
        <div class="card">
            <h3><i class="fas fa-history"></i> Request History & Tracking</h3>
            <?php if(count($requests) > 0): ?>
            
            <!-- Loop through requests -->
            <?php foreach($requests as $req): 
                // Status Logic for Timeline
                $s = $req['status'];
                $step1 = 'completed'; // Placed
                $step2 = ($s=='Accepted' || $s=='In-Transit' || $s=='Completed') ? 'active' : ''; 
                $step3 = ($s=='In-Transit' || $s=='Completed') ? 'active' : '';
                $step4 = ($s=='Completed') ? 'active' : '';
                
                // Override for Rejected
                if($s == 'Rejected') { $step2 = $step3 = $step4 = ''; }
            ?>
            <div style="border:1px solid #eee; border-radius:8px; padding:15px; margin-bottom:20px;">
                <div style="display:flex; justify-content:space-between; margin-bottom:15px;">
                    <div>
                        <strong>Pickup Date:</strong> <?php echo date("d M Y", strtotime($req['scheduled_date'])); ?><br>
                        <small style="color:#666"><?php echo htmlspecialchars($req['items']); ?></small>
                    </div>
                    <div style="text-align:right;">
                        <span class="status-badge status-<?php echo strtolower(str_replace(' ', '-', $s)); ?>"><?php echo $s; ?></span>
                        <?php if($s == 'Pending'): ?>
                        <form method="post" onsubmit="return confirm('Cancel this request?');" style="display:inline-block; margin-left:10px;">
                            <input type="hidden" name="action" value="cancel">
                            <input type="hidden" name="request_id" value="<?php echo $req['id']; ?>">
                            <button type="submit" class="btn-danger" style="padding:2px 8px; font-size:0.7rem;">Cancel</button>
                        </form>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Status Timeline (Only if not Rejected) -->
                <?php if($s != 'Rejected'): ?>
                <div class="timeline">
                    <div class="timeline-step <?php echo $step1; ?>">
                        <div class="step-circle"><i class="fas fa-clipboard-check"></i></div>
                        <div class="step-label">Placed</div>
                    </div>
                    <div class="timeline-step <?php echo $step2; ?>">
                        <div class="step-circle"><i class="fas fa-user-check"></i></div>
                        <div class="step-label">Accepted</div>
                    </div>
                    <div class="timeline-step <?php echo $step3; ?>">
                        <div class="step-circle"><i class="fas fa-truck"></i></div>
                        <div class="step-label">In-Transit</div>
                    </div>
                    <div class="timeline-step <?php echo $step4; ?>">
                        <div class="step-circle"><i class="fas fa-box-open"></i></div>
                        <div class="step-label">Completed</div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Assigned Collector Info -->
                <?php if(!empty($req['collector_name'])): ?>
                <div class="collector-info">
                    <i class="fas fa-id-card"></i>
                    <div>
                        <strong>Collector Assigned:</strong> <?php echo htmlspecialchars($req['collector_name']); ?>
                        <?php if($req['status']!='Completed'): ?>
                             • <a href="tel:<?php echo $req['collector_mobile']; ?>" style="color:#166534; text-decoration:none; font-weight:600;"><i class="fas fa-phone"></i> <?php echo $req['collector_mobile']; ?></a>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>

            <?php else: ?><p style="text-align:center; color:#999;">No requests found.</p><?php endif; ?>
        </div>
    </main>
</div>

<script>
    // AI Logic
    let net; const imgEl = document.createElement('img');
    async function loadModel() { net = await mobilenet.load(); } loadModel();
    function previewImage(event) {
        const reader = new FileReader();
        reader.onload = function(){
            document.getElementById('ai-preview').innerHTML = `<img src="${reader.result}" id="uploaded-img-tag" style="max-height:100%; max-width:100%;">`;
            imgEl.src = reader.result;
        }; reader.readAsDataURL(event.target.files[0]);
    }
    async function identifyImage() {
        if(!imgEl.src) { alert("Upload image first."); return; }
        document.getElementById('loading').style.display = 'block'; document.getElementById('ai-result').style.display = 'none';
        try {
            const result = await net.classify(imgEl);
            document.getElementById('loading').style.display = 'none';
            if(result.length > 0) {
                const best = result[0]; const confidence = Math.round(best.probability * 100);
                document.getElementById('label-name').innerText = best.className;
                document.getElementById('conf-score').innerText = confidence + "%";
                document.getElementById('conf-fill').style.width = confidence + "%";
                document.getElementById('ai-result').style.display = 'block';
                
                const eWasteKeywords = ['monitor','screen','keyboard','mouse','computer','laptop','phone','printer', 'electronic'];
                let isEWaste = eWasteKeywords.some(k => best.className.toLowerCase().includes(k));
                const sug = document.getElementById('suggestion-text');
                if(isEWaste) { sug.innerText = "✅ E-Waste detected. We recycle this!"; sug.style.color = "#047857"; }
                else { sug.innerText = "⚠️ Might not be e-waste. Check accepted items."; sug.style.color = "#c2410c"; }
            }
        } catch (e) { alert("Error scanning."); document.getElementById('loading').style.display = 'none'; }
    }
    function addToRequest() {
        const cat = document.getElementById('label-name').innerText;
        const val = document.getElementById('items-input').value;
        document.getElementById('items-input').value = val ? val + ", 1 " + cat : "1 " + cat;
    }
</script>
</body>
</html>