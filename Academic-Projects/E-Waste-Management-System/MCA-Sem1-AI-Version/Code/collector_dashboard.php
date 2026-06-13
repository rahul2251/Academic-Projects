<?php
session_start();
// 1. Security Check
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] !== 'collector'){
    header("location: login.php"); exit;
}

require_once "db_connect.php";
$collector_id = $_SESSION["id"];
$collector_name = $_SESSION["fullname"];

// Fetch Collector Details (for Profile & Pincode matching)
$c_query = mysqli_query($conn, "SELECT * FROM users WHERE id = $collector_id");
$collector_data = mysqli_fetch_assoc($c_query);
$my_pincode = $collector_data['pincode'] ?? ''; // Used for auto-assignment

$msg = ""; $msg_type = "";

// --- HANDLERS ---

// 1. Accept Task (Assign to Self)
if(isset($_POST['action']) && $_POST['action'] == 'accept_task') {
    $req_id = $_POST['req_id'];
    
    // Check workload limit (Max 10 active tasks)
    $workload_q = mysqli_query($conn, "SELECT COUNT(*) as count FROM pickup_requests WHERE collector_id = $collector_id AND status IN ('Accepted', 'In-Transit')");
    $workload = mysqli_fetch_assoc($workload_q)['count'];

    if($workload >= 10) {
        $msg = "Daily limit reached (10 tasks). Complete current tasks first.";
        $msg_type = "error";
    } else {
        // Assign task
        $sql = "UPDATE pickup_requests SET status = 'Accepted', collector_id = ? WHERE id = ? AND status = 'Pending'";
        if($stmt = mysqli_prepare($conn, $sql)){
            mysqli_stmt_bind_param($stmt, "ii", $collector_id, $req_id);
            if(mysqli_stmt_execute($stmt) && mysqli_stmt_affected_rows($stmt) > 0) {
                $msg = "Task #$req_id accepted successfully."; $msg_type = "success";
            } else {
                $msg = "Task unavailable or already taken."; $msg_type = "error";
            }
        }
    }
}

// 2. Update Status (In-Transit / Completed)
if(isset($_POST['action']) && $_POST['action'] == 'update_status') {
    $req_id = $_POST['req_id'];
    $new_status = $_POST['status'];
    
    $sql = "UPDATE pickup_requests SET status = ? WHERE id = ? AND collector_id = ?";
    if($stmt = mysqli_prepare($conn, $sql)){
        mysqli_stmt_bind_param($stmt, "sii", $new_status, $req_id, $collector_id);
        if(mysqli_stmt_execute($stmt)) {
            $msg = "Task #$req_id marked as $new_status."; $msg_type = "success";
        }
    }
}

// 3. Update Profile
if(isset($_POST['action']) && $_POST['action'] == 'update_profile') {
    $mob = $_POST['mobile'];
    $pin = $_POST['pincode'];
    $addr = $_POST['address'];
    
    $pass_sql = "";
    if(!empty($_POST['password'])) {
        $hash = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $pass_sql = ", password = '$hash'";
    }

    $sql = "UPDATE users SET mobile='$mob', pincode='$pin', address='$addr' $pass_sql WHERE id=$collector_id";
    if(mysqli_query($conn, $sql)) {
        $msg = "Profile updated."; $msg_type = "success";
        // Refresh data
        $c_query = mysqli_query($conn, "SELECT * FROM users WHERE id = $collector_id");
        $collector_data = mysqli_fetch_assoc($c_query);
        $my_pincode = $collector_data['pincode'];
    }
}

// --- DATA FETCHING ---

// 1. My Active Tasks (Accepted/In-Transit)
$my_tasks = [];
$mt_sql = "SELECT r.*, u.fullname, u.mobile, u.address, u.city 
           FROM pickup_requests r 
           JOIN users u ON r.user_id = u.id 
           WHERE r.collector_id = $collector_id AND r.status IN ('Accepted', 'In-Transit')
           ORDER BY r.scheduled_date ASC";
$mt_res = mysqli_query($conn, $mt_sql);
if($mt_res) while($row = mysqli_fetch_assoc($mt_res)) $my_tasks[] = $row;

// 2. Available Nearby Tasks (Pending & Matching Pincode)
$nearby_tasks = [];
if(!empty($my_pincode)) {
    $nt_sql = "SELECT r.*, u.fullname, u.address, u.pincode 
               FROM pickup_requests r 
               JOIN users u ON r.user_id = u.id 
               WHERE r.status = 'Pending' AND u.pincode = '$my_pincode' 
               LIMIT 5";
    $nt_res = mysqli_query($conn, $nt_sql);
    if($nt_res) while($row = mysqli_fetch_assoc($nt_res)) $nearby_tasks[] = $row;
}

// 3. Completed History
$history = [];
$h_sql = "SELECT r.*, u.fullname FROM pickup_requests r JOIN users u ON r.user_id = u.id WHERE r.collector_id = $collector_id AND r.status = 'Completed' ORDER BY r.scheduled_date DESC LIMIT 20";
$h_res = mysqli_query($conn, $h_sql);
if($h_res) while($row = mysqli_fetch_assoc($h_res)) $history[] = $row;

// 4. Feedback on My Tasks (Approximation: Feedback from users I've served)
$feedbacks = [];
$f_sql = "SELECT f.*, u.fullname FROM feedback f 
          JOIN users u ON f.user_id = u.id 
          WHERE f.user_id IN (SELECT user_id FROM pickup_requests WHERE collector_id = $collector_id AND status = 'Completed')
          ORDER BY f.created_at DESC LIMIT 5";
$f_res = mysqli_query($conn, $f_sql);
if($f_res) while($row = mysqli_fetch_assoc($f_res)) $feedbacks[] = $row;

// Calculate Workload Percentage
$active_count = count($my_tasks);
$workload_pct = ($active_count / 10) * 100;

mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Collector Dashboard | GreenCycle</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Poppins:wght@500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { --primary: #ea580c; --primary-dark: #c2410c; --secondary: #1f2937; --bg-light: #fff7ed; --white: #fff; --radius: 12px; --shadow: 0 1px 3px rgba(0,0,0,0.1); }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background: var(--bg-light); color: var(--secondary); margin: 0; }
        
        /* Navbar */
        .navbar { background: var(--white); height: 70px; padding: 0 30px; display: flex; align-items: center; justify-content: space-between; position: sticky; top: 0; z-index: 100; box-shadow: var(--shadow); }
        .brand { font-family: 'Poppins', sans-serif; font-weight: 700; font-size: 1.4rem; color: var(--secondary); display: flex; gap: 10px; } .brand i { color: var(--primary); }
        .btn-logout { color: #ef4444; border: 1px solid #ef4444; padding: 5px 12px; border-radius: 6px; text-decoration: none; font-weight: 500; transition:0.3s; } .btn-logout:hover { background: #ef4444; color: white; }

        .container { max-width: 1200px; margin: 40px auto; padding: 0 20px; }
        
        /* Grid Layout */
        .dashboard-grid { display: grid; grid-template-columns: 280px 1fr; gap: 30px; }
        
        /* Sidebar Cards */
        .profile-card { background: var(--white); padding: 25px; border-radius: var(--radius); box-shadow: var(--shadow); text-align: center; margin-bottom: 25px; }
        .avatar { width: 80px; height: 80px; background: #ffedd5; color: var(--primary); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 2.5rem; margin: 0 auto 15px; }
        .workload-bar { height: 8px; background: #e5e7eb; border-radius: 4px; margin: 15px 0; overflow: hidden; }
        .workload-fill { height: 100%; background: var(--primary); width: <?php echo $workload_pct; ?>%; transition: width 0.3s; }
        
        /* Main Content */
        .section-card { background: var(--white); padding: 25px; border-radius: var(--radius); box-shadow: var(--shadow); margin-bottom: 30px; }
        .section-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; padding-bottom: 15px; border-bottom: 1px solid #eee; }
        .section-header h3 { margin: 0; font-size: 1.2rem; display: flex; align-items: center; gap: 10px; }
        
        /* Task Cards */
        .task-list { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px; }
        .task-card { border: 1px solid #eee; border-radius: 8px; padding: 20px; background: #fafafa; border-left: 4px solid var(--primary); transition: transform 0.2s; }
        .task-card:hover { transform: translateY(-3px); box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
        .task-meta { display: flex; justify-content: space-between; font-size: 0.85rem; color: #6b7280; margin-bottom: 10px; }
        .task-user { font-weight: 600; font-size: 1.05rem; margin-bottom: 5px; }
        .task-address { font-size: 0.9rem; color: #4b5563; margin-bottom: 10px; line-height: 1.4; }
        .task-items { background: #fff; padding: 8px; border-radius: 4px; font-size: 0.85rem; border: 1px solid #e5e7eb; margin-bottom: 15px; }
        
        /* Buttons & Badges */
        .btn { border: none; padding: 8px 16px; border-radius: 6px; cursor: pointer; font-size: 0.85rem; font-weight: 500; width: 100%; transition: 0.2s; }
        .btn-primary { background: var(--primary); color: white; } .btn-primary:hover { background: var(--primary-dark); }
        .btn-green { background: #10b981; color: white; } .btn-green:hover { background: #059669; }
        .btn-group { display: flex; gap: 10px; }
        
        .badge { padding: 3px 8px; border-radius: 4px; font-size: 0.75rem; font-weight: 600; }
        .bg-yellow { background: #fef9c3; color: #854d0e; } .bg-blue { background: #dbeafe; color: #1e40af; } .bg-green { background: #dcfce7; color: #166534; }

        /* Table */
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #eee; font-size: 0.9rem; }
        th { background: #f9fafb; font-weight: 600; color: #374151; }

        /* Forms */
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-size: 0.9rem; font-weight: 500; }
        .form-group input, .form-group textarea { width: 100%; padding: 10px; border: 1px solid #d1d5db; border-radius: 6px; }

        /* Tabs */
        .tabs { display: flex; gap: 20px; margin-bottom: 20px; border-bottom: 1px solid #e5e7eb; }
        .tab { padding: 10px 0; cursor: pointer; color: #6b7280; font-weight: 500; border-bottom: 2px solid transparent; }
        .tab.active { color: var(--primary); border-bottom-color: var(--primary); }
        .tab-content { display: none; }
        .tab-content.active { display: block; }

        .alert { padding: 15px; border-radius: 8px; margin-bottom: 20px; }
        .alert-success { background: #dcfce7; color: #166534; } .alert-error { background: #fee2e2; color: #991b1b; }

        @media(max-width: 900px) { .dashboard-grid { grid-template-columns: 1fr; } }
    </style>
</head>
<body>

<nav class="navbar">
    <div class="brand"><i class="fas fa-shipping-fast"></i> Collector Portal</div>
    <a href="logout.php" class="btn-logout">Logout</a>
</nav>

<div class="container">
    <?php if(!empty($msg)): ?><div class="alert alert-<?php echo $msg_type; ?>"><?php echo $msg; ?></div><?php endif; ?>

    <div class="dashboard-grid">
        
        <!-- SIDEBAR: Profile & Workload -->
        <aside>
            <div class="profile-card">
                <div class="avatar"><i class="fas fa-user"></i></div>
                <h3><?php echo htmlspecialchars($collector_name); ?></h3>
                <p style="color:#6b7280; font-size:0.9rem;">Pincode: <strong><?php echo $my_pincode ?: 'Not Set'; ?></strong></p>
                
                <div style="margin-top: 20px; text-align: left;">
                    <small>Daily Workload (<?php echo $active_count; ?>/10)</small>
                    <div class="workload-bar"><div class="workload-fill"></div></div>
                </div>
                
                <button onclick="openTab('profile')" class="btn btn-primary" style="margin-top:10px; background:#374151;">Edit Profile</button>
            </div>

            <!-- Nearby Available Tasks (Mini View) -->
            <div class="section-card" style="padding: 20px;">
                <h4 style="margin-top:0; margin-bottom:15px; color:var(--primary);">Available Nearby</h4>
                <?php if(empty($nearby_tasks)): ?>
                    <p style="font-size:0.85rem; color:#666;">No pending tasks in <?php echo $my_pincode; ?>.</p>
                <?php else: ?>
                    <ul style="list-style:none; padding:0;">
                        <?php foreach($nearby_tasks as $nt): ?>
                        <li style="padding:10px 0; border-bottom:1px solid #eee; font-size:0.85rem;">
                            <strong><?php echo htmlspecialchars($nt['fullname']); ?></strong><br>
                            <?php echo htmlspecialchars($nt['address']); ?>
                            <form method="post" style="margin-top:5px;">
                                <input type="hidden" name="action" value="accept_task">
                                <input type="hidden" name="req_id" value="<?php echo $nt['id']; ?>">
                                <button type="submit" class="btn btn-green" style="padding:4px; font-size:0.75rem;">Accept Job</button>
                            </form>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </aside>

        <!-- MAIN CONTENT -->
        <main>
            <div class="tabs">
                <div class="tab active" onclick="openTab('tasks')">My Tasks</div>
                <div class="tab" onclick="openTab('history')">Completed History</div>
                <div class="tab" onclick="openTab('feedback')">Feedbacks</div>
                <div class="tab" onclick="openTab('profile')">Settings</div>
            </div>

            <!-- TAB 1: MY ACTIVE TASKS -->
            <div id="tasks" class="tab-content active">
                <div class="section-card">
                    <div class="section-header">
                        <h3><i class="fas fa-tasks"></i> Active Pickups (<?php echo $active_count; ?>)</h3>
                    </div>
                    
                    <?php if(empty($my_tasks)): ?>
                        <p style="text-align:center; padding:20px; color:#666;">You have no active tasks. Accept nearby tasks to start.</p>
                    <?php else: ?>
                        <div class="task-list">
                            <?php foreach($my_tasks as $task): ?>
                            <div class="task-card">
                                <div class="task-meta">
                                    <span>#<?php echo $task['id']; ?></span>
                                    <span class="badge <?php echo $task['status']=='Accepted'?'bg-blue':'bg-yellow'; ?>"><?php echo $task['status']; ?></span>
                                </div>
                                <div class="task-user"><?php echo htmlspecialchars($task['fullname']); ?></div>
                                <div class="task-address">
                                    <i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($task['address']); ?><br>
                                    <span style="color:#6b7280;"><?php echo htmlspecialchars($task['city']); ?></span>
                                    <a href="tel:<?php echo $task['mobile']; ?>" style="display:block; margin-top:5px; color:var(--primary); text-decoration:none;"><i class="fas fa-phone"></i> <?php echo $task['mobile']; ?></a>
                                </div>
                                <div class="task-items">
                                    <strong>Items:</strong> <?php echo htmlspecialchars($task['items']); ?>
                                </div>
                                
                                <form method="post">
                                    <input type="hidden" name="action" value="update_status">
                                    <input type="hidden" name="req_id" value="<?php echo $task['id']; ?>">
                                    
                                    <?php if($task['status'] == 'Accepted'): ?>
                                        <button type="submit" name="status" value="In-Transit" class="btn btn-primary">Start Journey <i class="fas fa-arrow-right"></i></button>
                                    <?php elseif($task['status'] == 'In-Transit'): ?>
                                        <button type="submit" name="status" value="Completed" class="btn btn-green"><i class="fas fa-check"></i> Mark Completed</button>
                                    <?php endif; ?>
                                </form>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- TAB 2: HISTORY -->
            <div id="history" class="tab-content">
                <div class="section-card">
                    <h3><i class="fas fa-history"></i> Completed Jobs</h3>
                    <table>
                        <thead><tr><th>Date</th><th>User</th><th>Items</th><th>Status</th></tr></thead>
                        <tbody>
                            <?php foreach($history as $h): ?>
                            <tr>
                                <td><?php echo date('d M Y', strtotime($h['scheduled_date'])); ?></td>
                                <td><?php echo htmlspecialchars($h['fullname']); ?></td>
                                <td><?php echo htmlspecialchars($h['items']); ?></td>
                                <td><span class="badge bg-green">Completed</span></td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if(empty($history)): ?><tr><td colspan="4">No history yet.</td></tr><?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- TAB 3: FEEDBACK -->
            <div id="feedback" class="tab-content">
                <div class="section-card">
                    <h3><i class="fas fa-star"></i> User Feedback on My Pickups</h3>
                    <table>
                        <thead><tr><th>User</th><th>Rating</th><th>Comment</th></tr></thead>
                        <tbody>
                            <?php foreach($feedbacks as $fb): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($fb['fullname']); ?></td>
                                <td><span style="color:#f59e0b;"><?php echo str_repeat('★', $fb['rating']); ?></span></td>
                                <td><?php echo htmlspecialchars($fb['comments']); ?></td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if(empty($feedbacks)): ?><tr><td colspan="3">No feedback yet.</td></tr><?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- TAB 4: PROFILE SETTINGS -->
            <div id="profile" class="tab-content">
                <div class="section-card">
                    <h3><i class="fas fa-user-cog"></i> Account Settings</h3>
                    <form method="post">
                        <input type="hidden" name="action" value="update_profile">
                        
                        <div style="display:grid; grid-template-columns: 1fr 1fr; gap:20px;">
                            <div class="form-group">
                                <label>Mobile Number</label>
                                <input type="text" name="mobile" value="<?php echo htmlspecialchars($collector_data['mobile']); ?>" required>
                            </div>
                            <div class="form-group">
                                <label>Preferred Pincode (For Auto-Assign)</label>
                                <input type="text" name="pincode" value="<?php echo htmlspecialchars($collector_data['pincode']); ?>" required>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Full Address</label>
                            <textarea name="address" rows="2"><?php echo htmlspecialchars($collector_data['address']); ?></textarea>
                        </div>

                        <div class="form-group">
                            <label>New Password (Leave blank to keep current)</label>
                            <input type="password" name="password" placeholder="Min 8 chars">
                        </div>

                        <button type="submit" class="btn btn-primary" style="width:auto;">Save Changes</button>
                    </form>
                </div>
            </div>

        </main>
    </div>
</div>

<script>
    function openTab(tabId) {
        // Hide all contents
        document.querySelectorAll('.tab-content').forEach(el => el.classList.remove('active'));
        // Remove active class from all tabs
        document.querySelectorAll('.tab').forEach(el => el.classList.remove('active'));
        
        // Show selected
        document.getElementById(tabId).classList.add('active');
        // Highlight tab (simple logic finding tab by text/order or using event target)
        event.target.classList.add('active');
    }
</script>

</body>
</html>