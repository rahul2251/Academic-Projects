<?php
session_start();
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] !== 'admin'){
    header("location: login.php"); exit;
}

require_once "db_connect.php";
$msg = ""; $msg_type = "";

// Determine Active Section
$active_section = isset($_GET['section']) ? $_GET['section'] : 'dashboard-home';

// --- HANDLERS ---

// 1. Auto-Assign & Accept Request
if(isset($_POST['action']) && $_POST['action'] == 'auto_assign') {
    $req_id = $_POST['request_id'];
    
    // Get Request Details (to find Pincode)
    $req_q = mysqli_query($conn, "SELECT u.pincode FROM pickup_requests r JOIN users u ON r.user_id = u.id WHERE r.id = $req_id");
    $req_data = mysqli_fetch_assoc($req_q);
    $target_pincode = $req_data['pincode'];

    // Find Eligible Collector (Matching Pincode, Active, < 10 tasks today)
    // Note: This is a simplified "First Found" logic.
    $collector_sql = "
        SELECT c.id 
        FROM users c 
        WHERE c.role = 'collector' 
        AND c.status = 'Active' 
        AND c.pincode = '$target_pincode'
        AND (SELECT COUNT(*) FROM pickup_requests pr WHERE pr.collector_id = c.id AND pr.status IN ('Accepted', 'In-Transit')) < 10
        LIMIT 1
    ";
    
    $collector_q = mysqli_query($conn, $collector_sql);
    
    if(mysqli_num_rows($collector_q) > 0) {
        $collector = mysqli_fetch_assoc($collector_q);
        $cid = $collector['id'];
        
        if(mysqli_query($conn, "UPDATE pickup_requests SET status='Accepted', collector_id=$cid WHERE id=$req_id")) {
            $msg = "Request #$req_id Auto-Assigned to Collector #$cid."; $msg_type = "success";
        }
    } else {
        // Fallback: Just mark Accepted
        if(mysqli_query($conn, "UPDATE pickup_requests SET status='Accepted' WHERE id=$req_id")) {
            $msg = "No exact collector match. Request marked Accepted for open pickup."; $msg_type = "warning";
        }
    }
}

// 2. Manual Update (Reject/Cancel)
if(isset($_POST['action']) && $_POST['action'] == 'update_request') {
    $req_id = $_POST['request_id']; 
    $new_status = $_POST['status']; 
    if(mysqli_query($conn, "UPDATE pickup_requests SET status='$new_status', collector_id=NULL WHERE id=$req_id")) {
        $msg = "Request updated to $new_status."; $msg_type = "success";
    }
}

// 3. User/Collector CRUD (Suspend/Restore/Delete)
if(isset($_POST['action']) && $_POST['action'] == 'manage_user') {
    $tid = $_POST['user_id']; $type = $_POST['type'];
    if($type == 'delete') {
        mysqli_query($conn, "DELETE FROM pickup_requests WHERE user_id=$tid");
        mysqli_query($conn, "DELETE FROM feedback WHERE user_id=$tid"); 
        if(mysqli_query($conn, "DELETE FROM users WHERE id=$tid")) { $msg = "User permanently deleted."; $msg_type = "success"; }
    } else {
        $st = ($type == 'suspend') ? 'Suspended' : 'Active';
        if(mysqli_query($conn, "UPDATE users SET status='$st' WHERE id=$tid")) { 
            $msg = "User account $st."; $msg_type = "success"; 
        }
    }
}

// 4. Add Collector
if(isset($_POST['action']) && $_POST['action'] == 'add_collector') {
    $name = trim($_POST['name']); $email = trim(strtolower($_POST['email'])); $mob = trim($_POST['mobile']);
    $pass = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $pin = trim($_POST['pincode']);

    $check = mysqli_query($conn, "SELECT id FROM users WHERE email='$email'");
    if(mysqli_num_rows($check) > 0) { $msg = "Email exists."; $msg_type = "error"; }
    else {
        $sql = "INSERT INTO users (fullname, email, password, mobile, pincode, role, status) VALUES (?, ?, ?, ?, ?, 'collector', 'Active')";
        $stmt = mysqli_prepare($conn, $sql); mysqli_stmt_bind_param($stmt, "sssss", $name, $email, $pass, $mob, $pin);
        if(mysqli_stmt_execute($stmt)) { $msg = "Collector added."; $msg_type = "success"; } 
        else { $msg = "Error adding collector."; $msg_type = "error"; }
    }
}

// 5. Export CSV Reports
if(isset($_GET['export'])) {
    $period = $_GET['period'] ?? 'all';
    $date_condition = "";
    
    if($period == 'daily') $date_condition = "AND DATE(r.created_at) = CURDATE()";
    elseif($period == 'weekly') $date_condition = "AND r.created_at >= DATE_SUB(NOW(), INTERVAL 1 WEEK)";
    elseif($period == 'monthly') $date_condition = "AND r.created_at >= DATE_SUB(NOW(), INTERVAL 1 MONTH)";

    header('Content-Type: text/csv'); 
header('Content-Disposition: attachment; filename="greencycle_'.$period.'_report.csv"');
    $out = fopen('php://output', 'w'); 
    fputcsv($out, ['ID', 'User', 'Mobile', 'Items', 'Pincode', 'Collector', 'Date', 'Status']);
    
    $query = "SELECT r.id, u.fullname, u.mobile, r.items, u.pincode, c.fullname as collector_name, r.scheduled_date, r.status 
              FROM pickup_requests r 
              JOIN users u ON r.user_id = u.id 
              LEFT JOIN users c ON r.collector_id = c.id
              WHERE 1 $date_condition";
              
    $res = mysqli_query($conn, $query);
    while($r = mysqli_fetch_assoc($res)) fputcsv($out, $r); 
    fclose($out); exit;
}

// --- DATA FETCHING ---
$stats_q = mysqli_query($conn, "SELECT 
    (SELECT COUNT(*) FROM users WHERE role='User') as total_users,
    (SELECT COUNT(*) FROM users WHERE role='collector') as total_collectors,
    (SELECT COUNT(*) FROM pickup_requests) as total_requests,
    (SELECT COUNT(*) FROM pickup_requests WHERE status='Pending') as pending_req,
    (SELECT COUNT(*) FROM pickup_requests WHERE status='Completed') as completed_req,
    (SELECT COUNT(*) FROM feedback) as total_feedback,
    (SELECT AVG(rating) FROM feedback) as avg_rating
");
$stats = mysqli_fetch_assoc($stats_q);
$avg_rating = round($stats['avg_rating'] ?? 0, 1);

$chart_data = ['Pending'=>0, 'Accepted'=>0, 'Rejected'=>0, 'In-Transit'=>0, 'Completed'=>0];
$chart_q = mysqli_query($conn, "SELECT status, COUNT(*) as count FROM pickup_requests GROUP BY status");
while($r = mysqli_fetch_assoc($chart_q)) { if(isset($chart_data[$r['status']])) $chart_data[$r['status']] = $r['count']; }

$requests = []; 
$r_res = mysqli_query($conn, "SELECT r.*, u.fullname, u.mobile, u.pincode, c.fullname as collector_name 
                              FROM pickup_requests r 
                              JOIN users u ON r.user_id=u.id 
                              LEFT JOIN users c ON r.collector_id=c.id
                              ORDER BY FIELD(r.status, 'Pending', 'In-Transit', 'Accepted', 'Completed', 'Rejected'), r.created_at DESC LIMIT 50");
if($r_res) while($row = mysqli_fetch_assoc($r_res)) $requests[] = $row;

$users = []; 
$u_res = mysqli_query($conn, "SELECT * FROM users WHERE role = 'User' ORDER BY created_at DESC");
if($u_res) while($row = mysqli_fetch_assoc($u_res)) $users[] = $row;

$collectors = []; 
$c_res = mysqli_query($conn, "SELECT * FROM users WHERE role = 'collector' ORDER BY created_at DESC");
if($c_res) while($row = mysqli_fetch_assoc($c_res)) $collectors[] = $row;

$feedbacks = [];
$fb_res = mysqli_query($conn, "SELECT f.*, u.fullname FROM feedback f JOIN users u ON f.user_id=u.id ORDER BY f.created_at DESC LIMIT 20");
if($fb_res) while($row = mysqli_fetch_assoc($fb_res)) $feedbacks[] = $row;

mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard | GreenCycle</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Poppins:wght@500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
	<link rel="stylesheet" href="admin_style.css">

</head>
<body>

<nav class="navbar">
    <div class="brand"><i class="fas fa-user-shield"></i> Admin Dashboard</div>
    <a href="logout.php" class="btn-logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
</nav>

<div class="container">
    <?php if(!empty($msg)): ?><div class="alert alert-<?php echo $msg_type; ?>"><?php echo $msg; ?></div><?php endif; ?>

    <!-- === DASHBOARD HOME === -->
    <div id="dashboard-home" class="section <?php echo ($active_section == 'dashboard-home') ? 'active' : ''; ?>">
        <h2 style="margin-bottom: 20px;">Dashboard Overview</h2>
        
        <!-- Row 1: User Stats -->
        <div class="stats-grid">
            <div class="stat-card" onclick="showSection('users-section')" style="border-left-color: #3b82f6;">
                <h4 style="color:#3b82f6">Total Users</h4>
                <p><?php echo $stats['total_users']; ?></p>
                <i class="fas fa-users stat-icon-bg"></i>
            </div>
            <div class="stat-card" onclick="showSection('collectors-section')" style="border-left-color: #6366f1;">
                <h4 style="color:#6366f1">Total Collectors</h4>
                <p><?php echo $stats['total_collectors']; ?></p>
                <i class="fas fa-truck stat-icon-bg"></i>
            </div>
            <div class="stat-card" onclick="showSection('requests-section')" style="border-left-color: #f97316;">
                <h4 style="color:#f97316">Total Requests</h4>
                <p><?php echo $stats['total_requests']; ?></p>
                <i class="fas fa-file-alt stat-icon-bg"></i>
            </div>
            <div class="stat-card" onclick="showSection('analytics-section')" style="border-left-color: #10b981;">
                <h4 style="color:#10b981">Completed Pickups</h4>
                <p><?php echo $stats['completed_req']; ?></p>
                <i class="fas fa-check-circle stat-icon-bg"></i>
            </div>
        </div>

        <!-- Row 2: Request & Feedback Stats -->
        <div class="stats-grid">
            <div class="stat-card" onclick="showSection('requests-section')" style="border-left-color: #f59e0b;">
                <h4 style="color:#f59e0b">Pending Requests</h4>
                <p><?php echo $stats['pending_req']; ?></p>
                <i class="fas fa-clock stat-icon-bg"></i>
            </div>
            <div class="stat-card" onclick="showSection('feedback-section')" style="border-left-color: #ec4899;">
                <h4 style="color:#ec4899">Avg Rating</h4>
                <p><?php echo $avg_rating; ?> <span style="font-size:1rem; color:#999;">/ 5.0</span></p>
                <i class="fas fa-star stat-icon-bg"></i>
            </div>
            <div class="stat-card" onclick="showSection('feedback-section')" style="border-left-color: #ec4899;">
                <h4 style="color:#ec4899">Total Feedback</h4>
                <p><?php echo $stats['total_feedback']; ?></p>
                <i class="fas fa-comments stat-icon-bg"></i>
            </div>
            <div class="stat-card" onclick="showSection('ai-dashboard-section')" style="border-left-color: #8b5cf6;">
                <h4 style="color:#8b5cf6">AI Insights</h4>
                <p style="font-size:1.2rem; margin-top:10px;">View Patterns <i class="fas fa-arrow-right" style="font-size:0.9rem;"></i></p>
                <i class="fas fa-brain stat-icon-bg"></i>
            </div>
        </div>

        <!-- Quick Views -->
        <div class="section-header" style="margin-bottom:0; box-shadow:none; border-bottom:1px solid #eee;">
            <h3><i class="fas fa-bolt"></i> Quick Actions & Recent</h3>
        </div>
        
        <div class="table-container">
            <table>
                <thead><tr><th>ID</th><th>User / Pincode</th><th>Status</th><th>Collector</th><th>Action</th></tr></thead>
                <tbody>
                    <?php $c=0; foreach($requests as $req): if($c++>=5) break; 
                        $statusBadge = 'bg-yellow';
                        if($req['status'] == 'Accepted') $statusBadge = 'bg-blue';
                        if($req['status'] == 'In-Transit') $statusBadge = 'bg-purple';
                        if($req['status'] == 'Completed') $statusBadge = 'bg-green';
                        if($req['status'] == 'Rejected') $statusBadge = 'bg-red';
                    ?>
                    <tr>
                        <td>#<?php echo $req['id']; ?></td>
                        <td>
                            <strong><?php echo htmlspecialchars($req['fullname']); ?></strong><br>
                            <small>Pin: <?php echo htmlspecialchars($req['pincode']); ?></small>
                        </td>
                        <td><span class="badge <?php echo $statusBadge; ?>"><?php echo $req['status']; ?></span></td>
                        <td><?php echo $req['collector_name'] ? htmlspecialchars($req['collector_name']) : '<span style="color:#aaa;">Unassigned</span>'; ?></td>
                        <td>
                            <?php if($req['status'] == 'Pending'): ?>
                            <form method="post" style="display:inline;">
                                <input type="hidden" name="action" value="auto_assign">
                                <input type="hidden" name="request_id" value="<?php echo $req['id']; ?>">
                                <button type="submit" class="btn btn-blue" style="padding:4px 8px; font-size:0.75rem;">Auto Assign</button>
                            </form>
                            <?php else: ?> - <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- === SECTION 1: REQUESTS (CRUD + ASSIGN) === -->
    <div id="requests-section" class="section <?php echo ($active_section == 'requests-section') ? 'active' : ''; ?>">
        <div class="section-header">
            <h3><i class="fas fa-tasks"></i> Manage Pickup Requests</h3>
            <div style="display:flex; gap:10px;">
                <!-- Report -->
                <form method="get" action="" style="display:flex; gap:5px;">
                    <select name="period" style="padding:6px; border-radius:4px; border:1px solid #ccc;">
                        <option value="daily">Today</option>
                        <option value="weekly">Last 7 Days</option>
                        <option value="monthly">Last 30 Days</option>
                        <option value="all">All Time</option>
                    </select>
                    <button type="submit" name="export" value="true" class="btn btn-green"><i class="fas fa-download"></i> CSV</button>
                </form>
                <button class="btn btn-back" onclick="showHome()">Back</button>
            </div>
        </div>
        
        <div class="table-container">
            <table>
                <thead><tr><th>ID</th><th>User Info</th><th>Items</th><th>Status</th><th>Assign/Action</th></tr></thead>
                <tbody>
                    <?php foreach($requests as $req): 
                        $statusBadge = 'bg-yellow';
                        if($req['status'] == 'Accepted') $statusBadge = 'bg-blue';
                        if($req['status'] == 'In-Transit') $statusBadge = 'bg-purple';
                        if($req['status'] == 'Completed') $statusBadge = 'bg-green';
                        if($req['status'] == 'Rejected') $statusBadge = 'bg-red';
                    ?>
                    <tr>
                        <td>#<?php echo $req['id']; ?></td>
                        <td>
                            <strong><?php echo htmlspecialchars($req['fullname']); ?></strong><br>
                            Pin: <?php echo htmlspecialchars($req['pincode']); ?>
                        </td>
                        <td><?php echo htmlspecialchars($req['items']); ?></td>
                        <td><span class="badge <?php echo $statusBadge; ?>"><?php echo $req['status']; ?></span></td>
                        <td>
                            <?php if($req['status'] == 'Pending'): ?>
                                <form method="post" style="display:inline;">
                                    <input type="hidden" name="action" value="auto_assign">
                                    <input type="hidden" name="request_id" value="<?php echo $req['id']; ?>">
                                    <button type="submit" class="btn btn-blue" title="Auto-Assign based on Pincode">Auto Assign</button>
                                </form>
                                <form method="post" style="display:inline;">
                                    <input type="hidden" name="action" value="update_request">
                                    <input type="hidden" name="request_id" value="<?php echo $req['id']; ?>">
                                    <button type="submit" name="status" value="Rejected" class="btn btn-red">Reject</button>
                                </form>
                            <?php else: ?>
                                <?php echo $req['collector_name'] ? "Assigned: ".$req['collector_name'] : "Closed/Rejected"; ?>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- === SECTION 2: COLLECTORS (CRUD) === -->
    <div id="collectors-section" class="section <?php echo ($active_section == 'collectors-section') ? 'active' : ''; ?>">
        <div class="section-header">
            <h3><i class="fas fa-truck-loading"></i> Manage Collectors</h3>
            <button class="btn btn-back" onclick="showHome()">Back</button>
        </div>
        <form method="post" class="add-form">
            <input type="hidden" name="action" value="add_collector">
            <input type="text" name="name" placeholder="Name" required>
            <input type="email" name="email" placeholder="Email" required>
            <input type="text" name="mobile" placeholder="Mobile" required>
            <input type="text" name="pincode" placeholder="Pref. Pincode" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit" class="btn btn-blue"><i class="fas fa-plus"></i> Add</button>
        </form>
        <div class="table-container">
            <table>
                <thead><tr><th>Name</th><th>Email</th><th>Pincode</th><th>Status</th><th>Action</th></tr></thead>
                <tbody>
                    <?php foreach($collectors as $c): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($c['fullname']); ?></td>
                        <td><?php echo htmlspecialchars($c['email']); ?></td>
                        <td><?php echo htmlspecialchars($c['pincode']); ?></td>
                        <td><span class="badge bg-green">Active</span></td>
                        <td>
                            <form method="post" onsubmit="return confirm('Delete this collector?');">
                                <input type="hidden" name="action" value="manage_user">
                                <input type="hidden" name="user_id" value="<?php echo $c['id']; ?>">
                                <button type="submit" name="type" value="delete" class="btn btn-red">Delete</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- === SECTION 3: USERS (CRUD) === -->
    <div id="users-section" class="section <?php echo ($active_section == 'users-section') ? 'active' : ''; ?>">
        <div class="section-header">
            <h3><i class="fas fa-users"></i> User Management</h3>
            <button class="btn btn-back" onclick="showHome()">Back</button>
        </div>
        <div class="table-container">
            <table>
                <thead><tr><th>Name</th><th>Email</th><th>Status</th><th>Action</th></tr></thead>
                <tbody>
                    <?php foreach($users as $u): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($u['fullname']); ?></td>
                        <td><?php echo htmlspecialchars($u['email']); ?></td>
                        <td><span class="badge <?php echo $u['status']=='Active'?'bg-green':'bg-red'; ?>"><?php echo $u['status']; ?></span></td>
                        <td>
                            <form method="post" style="display:inline;">
                                <input type="hidden" name="action" value="manage_user">
                                <input type="hidden" name="user_id" value="<?php echo $u['id']; ?>">
                                <?php if($u['status']=='Active'): ?>
                                    <button type="submit" name="type" value="suspend" class="btn btn-red">Suspend</button>
                                <?php else: ?>
                                    <button type="submit" name="type" value="activate" class="btn btn-green">Restore</button>
                                <?php endif; ?>
                                <button type="submit" name="type" value="delete" class="btn btn-red" onclick="return confirm('Permanently delete user?')"><i class="fas fa-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- === SECTION 4: FEEDBACK & AI === -->
    <div id="feedback-section" class="section <?php echo ($active_section == 'feedback-section') ? 'active' : ''; ?>">
        <div class="section-header">
            <h3><i class="fas fa-robot"></i> Feedback & AI Insights</h3>
            <button class="btn btn-back" onclick="showHome()">Back</button>
        </div>
        <div style="margin-bottom:20px; text-align:center;">
            <button class="btn btn-blue" onclick="generateAIInsights()">Run Sentiment Analysis</button>
        </div>
        <div id="ai-summary-box">
            <h4 style="margin-top:0; color:#1e40af;"><i class="fas fa-lightbulb"></i> AI Analysis</h4>
            <p id="ai-text">Click button above to analyze.</p>
        </div>
        <div class="table-container">
            <table>
                <thead><tr><th>User</th><th>Rating</th><th>Comment</th></tr></thead>
                <tbody>
                    <?php foreach($feedbacks as $fb): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($fb['fullname']); ?></td>
                        <td><span style="color:#f59e0b;"><?php echo str_repeat('★', $fb['rating']); ?></span></td>
                        <td class="feedback-comment"><?php echo htmlspecialchars($fb['comments']); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- === SECTION 5: ANALYTICS & AI DASHBOARD === -->
    <div id="analytics-section" class="section">
        <div class="section-header"><h3><i class="fas fa-chart-pie"></i> Analytics & AI Predictions</h3><button class="btn btn-back" onclick="showHome()">Back</button></div>
        <div class="charts-row">
            <div class="chart-box"><h4 style="text-align:center;">Request Status</h4><canvas id="statusChart"></canvas></div>
            <div class="chart-box"><h4 style="text-align:center;">User Breakdown</h4><canvas id="userChart"></canvas></div>
        </div>
    </div>
    
    <!-- AI Dashboard Specific Section -->
    <div id="ai-dashboard-section" class="section">
        <div class="section-header"><h3><i class="fas fa-brain"></i> Advanced AI Metrics</h3><button class="btn btn-back" onclick="showHome()">Back</button></div>
        <div class="charts-row">
            <div class="chart-box" style="grid-column:span 2;">
                <h4 style="text-align:center;">Projected Waste Collection (Tonnes)</h4>
                <canvas id="aiPredictionChart"></canvas>
            </div>
        </div>
        <div class="charts-row">
            <div class="chart-box"><h4 style="text-align:center;">Regional Density</h4><canvas id="aiRegionChart"></canvas></div>
            <div class="chart-box"><h4 style="text-align:center;">Waste Composition</h4><canvas id="aiCompositionChart"></canvas></div>
        </div>
    </div>

</div>

<script>
    function showSection(id) {
        document.querySelectorAll('.section').forEach(el => el.classList.remove('active'));
        document.getElementById(id).classList.add('active');
        window.scrollTo(0,0);
    }
    function showHome() {
        document.querySelectorAll('.section').forEach(el => el.classList.remove('active'));
        document.getElementById('dashboard-home').classList.add('active');
        window.scrollTo(0,0);
    }

    // --- Charts ---
    const statusData = {
        labels: ['Pending', 'Accepted', 'Rejected', 'In-Transit', 'Completed'],
        datasets: [{
            data: [<?php echo $chart_data['Pending']; ?>, <?php echo $chart_data['Accepted']; ?>, <?php echo $chart_data['Rejected']; ?>, <?php echo $chart_data['In-Transit']; ?>, <?php echo $chart_data['Completed']; ?>],
            backgroundColor: ['#f59e0b', '#3b82f6', '#ef4444', '#8b5cf6', '#10b981']
        }]
    };
    new Chart(document.getElementById('statusChart').getContext('2d'), { type: 'doughnut', data: statusData });

    const userCtx = document.getElementById('userChart').getContext('2d');
    new Chart(userCtx, {
        type: 'pie',
        data: {
            labels: ['Users', 'Collectors', 'Admins'],
            datasets: [{
                data: [<?php echo $stats['total_users']; ?>, <?php echo $stats['total_collectors']; ?>, 1],
                backgroundColor: ['#3b82f6', '#8b5cf6', '#10b981']
            }]
        }
    });

    // AI Mock Charts
    new Chart(document.getElementById('aiPredictionChart').getContext('2d'), {
        type: 'line',
        data: {
            labels: ['Nov', 'Dec', 'Jan', 'Feb', 'Mar', 'Apr'],
            datasets: [{ label: 'Predicted Volume', data: [12, 15, 11, 18, 22, 25], borderColor: '#7c3aed', tension: 0.4, fill: true, backgroundColor: 'rgba(124, 58, 237, 0.1)' }]
        }
    });
    new Chart(document.getElementById('aiCompositionChart').getContext('2d'), { type: 'polarArea', data: { labels: ['Laptops', 'Phones', 'Monitors'], datasets: [{ data: [30, 45, 15], backgroundColor: ['#3b82f6', '#10b981', '#f59e0b'] }] } });
    new Chart(document.getElementById('aiRegionChart').getContext('2d'), { type: 'bar', data: { labels: ['North', 'East', 'West', 'South'], datasets: [{ label: 'Density', data: [45, 30, 60, 25], backgroundColor: '#3b82f6' }] } });

    // AI Logic
    function generateAIInsights() {
        const comments = document.querySelectorAll('.feedback-comment');
        let count = 0; let score = 0;
        comments.forEach(c => {
            const t = c.innerText.toLowerCase();
            count++;
            if(t.includes('good') || t.includes('great') || t.includes('fast')) score++;
            if(t.includes('bad') || t.includes('slow') || t.includes('poor')) score--;
        });
        const box = document.getElementById('ai-summary-box');
        const txt = document.getElementById('ai-text');
        box.style.display = 'block';
        let sentiment = score > 0 ? "<strong style='color:green'>Positive</strong>" : (score < 0 ? "<strong style='color:red'>Negative</strong>" : "<strong>Neutral</strong>");
        txt.innerHTML = `Analyzed <strong>${count}</strong> feedbacks. Overall sentiment: ${sentiment}. <br><br><strong>Suggestion:</strong> ` + (score < 0 ? "Investigate delays." : "Maintain current service.");
    }
</script>

</body>
</html>
