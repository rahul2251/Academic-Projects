<?php
session_start();
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] !== 'admin'){
    header("location: login.php"); exit;
}

require_once "db_connect.php";
$msg = ""; $msg_type = "";

// --- HANDLERS ---
// 1. Request Update
if(isset($_POST['action']) && $_POST['action'] == 'update_request') {
    $req_id = $_POST['request_id']; $new_status = $_POST['status'];
    if(mysqli_query($conn, "UPDATE pickup_requests SET status='$new_status' WHERE id=$req_id")) {
        $msg = "Request updated."; $msg_type = "success";
    }
}
// 2. User Management
if(isset($_POST['action']) && $_POST['action'] == 'manage_user') {
    $tid = $_POST['user_id']; $type = $_POST['type'];
    if($type == 'delete') {
        mysqli_query($conn, "DELETE FROM pickup_requests WHERE user_id=$tid");
        mysqli_query($conn, "DELETE FROM feedback WHERE user_id=$tid"); 
        if(mysqli_query($conn, "DELETE FROM users WHERE id=$tid")) { $msg = "User deleted."; $msg_type = "success"; }
    } else {
        $st = ($type == 'suspend') ? 'Suspended' : 'Active';
        if(mysqli_query($conn, "UPDATE users SET status='$st' WHERE id=$tid")) { $msg = "User $st."; $msg_type = "success"; }
    }
}
// 3. Add Collector
if(isset($_POST['action']) && $_POST['action'] == 'add_collector') {
    $name = $_POST['name']; $email = $_POST['email']; $pass = password_hash($_POST['password'], PASSWORD_DEFAULT); $mob = $_POST['mobile'];
    $check = mysqli_query($conn, "SELECT id FROM users WHERE email='$email'");
    if($check && mysqli_num_rows($check) > 0) { $msg = "Email exists."; $msg_type = "error"; }
    else {
        $sql = "INSERT INTO users (fullname, email, password, mobile, role, status) VALUES (?, ?, ?, ?, 'controller', 'Active')";
        $stmt = mysqli_prepare($conn, $sql); mysqli_stmt_bind_param($stmt, "ssss", $name, $email, $pass, $mob);
        if(mysqli_stmt_execute($stmt)) { $msg = "Collector added."; $msg_type = "success"; }
    }
}

// Export CSV
if(isset($_GET['export'])) {
    header('Content-Type: text/csv'); header('Content-Disposition: attachment; filename="report.csv"');
    $out = fopen('php://output', 'w'); fputcsv($out, ['ID', 'User', 'Items', 'Date', 'Status']);
    $res = mysqli_query($conn, "SELECT r.id, u.fullname, r.items, r.scheduled_date, r.status FROM pickup_requests r JOIN users u ON r.user_id=u.id");
    if($res){ while($r = mysqli_fetch_assoc($res)) fputcsv($out, $r); }
    fclose($out); exit;
}

// --- DATA FETCHING WITH ERROR CHECKING ---

// 1. Stats
$stats_q = mysqli_query($conn, "SELECT (SELECT COUNT(*) FROM users WHERE role='User') as total_users, (SELECT COUNT(*) FROM users WHERE role='controller') as total_collectors, (SELECT COUNT(*) FROM pickup_requests WHERE status='Pending') as pending_req, (SELECT COUNT(*) FROM pickup_requests WHERE status='Completed') as completed_req");
if($stats_q === false) die("Stats query failed: " . mysqli_error($conn));
$stats = mysqli_fetch_assoc($stats_q);

// 2. Requests
$requests = []; 
$r_res = mysqli_query($conn, "SELECT r.*, u.fullname, u.mobile FROM pickup_requests r JOIN users u ON r.user_id=u.id ORDER BY r.created_at DESC LIMIT 10");
if($r_res === false) die("Requests query failed: " . mysqli_error($conn));
while($row = mysqli_fetch_assoc($r_res)) $requests[] = $row;

// 3. Users
$users = []; 
$u_res = mysqli_query($conn, "SELECT * FROM users WHERE role != 'admin' ORDER BY created_at DESC");
if($u_res === false) die("Users query failed: " . mysqli_error($conn));
while($row = mysqli_fetch_assoc($u_res)) $users[] = $row;

// 4. Feedback Data
$feedbacks = [];
$fb_res = mysqli_query($conn, "SELECT f.*, u.fullname FROM feedback f JOIN users u ON f.user_id=u.id ORDER BY f.created_at DESC LIMIT 5");
if($fb_res === false) die("Feedback query failed: " . mysqli_error($conn));
while($row = mysqli_fetch_assoc($fb_res)) $feedbacks[] = $row;

// 5. Feedback Stats
$fb_stats_q = mysqli_query($conn, "SELECT AVG(rating) as avg_rating, COUNT(*) as total FROM feedback");
if($fb_stats_q === false) die("Feedback stats query failed: " . mysqli_error($conn));
$fb_stats = mysqli_fetch_assoc($fb_stats_q);
$avg_rating = round($fb_stats['avg_rating'] ?? 0, 1);

mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard | GreenCycle</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Poppins:wght@500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { --primary: #7c3aed; --primary-dark: #6d28d9; --secondary: #1f2937; --bg-light: #f3f4f6; --white: #fff; --radius: 12px; --shadow: 0 1px 3px rgba(0,0,0,0.1); }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background: var(--bg-light); color: var(--secondary); }
        
        .navbar { background: var(--white); height: 70px; padding: 0 30px; display: flex; align-items: center; justify-content: space-between; position: sticky; top: 0; z-index: 100; box-shadow: var(--shadow); }
        .brand { font-family: 'Poppins', sans-serif; font-weight: 700; font-size: 1.4rem; display: flex; gap: 10px; } .brand i { color: var(--primary); }
        .btn-logout { color: #ef4444; border: 1px solid #ef4444; padding: 5px 12px; border-radius: 6px; text-decoration: none; font-size: 0.85rem; transition:0.3s; } .btn-logout:hover { background: #ef4444; color: white; }

        .container { max-width: 1200px; margin: 30px auto; padding: 0 20px; }
        
        .stats-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-bottom: 30px; }
        .stat-card { background: var(--white); padding: 20px; border-radius: var(--radius); box-shadow: var(--shadow); border-left: 4px solid var(--primary); }
        .stat-card h4 { color: #6b7280; font-size: 0.9rem; margin-bottom: 5px; } .stat-card p { font-size: 1.8rem; font-weight: 700; color: var(--secondary); }
        
        .section-card { background: var(--white); border-radius: var(--radius); box-shadow: var(--shadow); overflow: hidden; margin-bottom: 30px; }
        .section-header { padding: 20px; border-bottom: 1px solid #e5e7eb; display: flex; justify-content: space-between; align-items: center; background: #fafafa; }
        .section-header h3 { font-size: 1.1rem; font-weight: 600; }
        
        .table-responsive { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 15px; text-align: left; border-bottom: 1px solid #e5e7eb; font-size: 0.9rem; }
        th { background: #f9fafb; font-weight: 600; color: #374151; }
        
        .badge { padding: 4px 10px; border-radius: 50px; font-size: 0.75rem; font-weight: 600; }
        .bg-green { background: #dcfce7; color: #166534; } .bg-yellow { background: #fef9c3; color: #854d0e; } .bg-red { background: #fee2e2; color: #991b1b; } .bg-gray { background: #f3f4f6; color: #374151; }

        .btn { border: none; padding: 6px 12px; border-radius: 4px; cursor: pointer; font-size: 0.85rem; font-weight: 500; margin-right: 5px; }
        .btn-green { background: #10b981; color: white; } .btn-red { background: #ef4444; color: white; } .btn-purple { background: var(--primary); color: white; } .btn-gray { background: #e5e7eb; color: #374151; }
        
        .add-form { display: grid; grid-template-columns: repeat(4, 1fr) auto; gap: 10px; padding: 20px; background: #fdfdfd; border-bottom: 1px solid #eee; }
        .add-form input { padding: 8px; border: 1px solid #d1d5db; border-radius: 4px; }
        
        .alert { padding: 15px; margin-bottom: 20px; border-radius: 8px; }
        .alert-success { background: #dcfce7; color: #166534; } .alert-error { background: #fee2e2; color: #991b1b; }

        @media(max-width: 900px){ .stats-grid { grid-template-columns: 1fr 1fr; } .add-form { grid-template-columns: 1fr; } }
    </style>
</head>
<body>

<nav class="navbar">
    <div class="brand"><i class="fas fa-user-shield"></i> Admin Dashboard</div>
    <a href="logout.php" class="btn-logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
</nav>

<div class="container">
    <?php if(!empty($msg)): ?><div class="alert alert-<?php echo $msg_type; ?>"><?php echo $msg; ?></div><?php endif; ?>

    <!-- 1. MAIN STATS -->
    <div class="stats-grid">
        <div class="stat-card"><h4>Total Users</h4><p><?php echo $stats['total_users']; ?></p></div>
        <div class="stat-card"><h4>Active Collectors</h4><p><?php echo $stats['total_collectors']; ?></p></div>
        <div class="stat-card"><h4>Pending Requests</h4><p><?php echo $stats['pending_req']; ?></p></div>
        <div class="stat-card"><h4>Completed Pickups</h4><p><?php echo $stats['completed_req']; ?></p></div>
    </div>

    <!-- 2. FEEDBACK SUMMARY -->
    <div class="stats-grid" style="grid-template-columns: 1fr 1fr;">
        <div class="stat-card" style="border-left-color: #f59e0b;">
            <h4><i class="fas fa-star" style="color:#f59e0b"></i> Average User Rating</h4>
            <p><?php echo $avg_rating ?: '0.0'; ?> <span style="font-size:1rem; color:#999;">/ 5.0</span></p>
        </div>
        <div class="stat-card" style="border-left-color: #f59e0b;">
            <h4><i class="fas fa-comments" style="color:#f59e0b"></i> Total Feedbacks</h4>
            <p><?php echo $fb_stats['total']; ?></p>
        </div>
    </div>

    <!-- 3. REQUESTS -->
    <div class="section-card">
        <div class="section-header">
            <h3><i class="fas fa-tasks"></i> Recent Requests</h3>
            <a href="?export=true" class="btn btn-gray"><i class="fas fa-download"></i> Report</a>
        </div>
        <div class="table-responsive">
            <table>
                <thead><tr><th>ID</th><th>User</th><th>Items</th><th>Status</th><th>Actions</th></tr></thead>
                <tbody>
                    <?php foreach($requests as $req): ?>
                    <tr>
                        <td>#<?php echo $req['id']; ?></td>
                        <td><?php echo htmlspecialchars($req['fullname']); ?><br><small class="text-muted"><?php echo $req['mobile']; ?></small></td>
                        <td><?php echo htmlspecialchars($req['items']); ?></td>
                        <td><span class="badge <?php echo $req['status']=='Completed'?'bg-green':($req['status']=='Pending'?'bg-yellow':'bg-red'); ?>"><?php echo $req['status']; ?></span></td>
                        <td>
                            <form method="post" style="display:inline;">
                                <input type="hidden" name="action" value="update_request">
                                <input type="hidden" name="request_id" value="<?php echo $req['id']; ?>">
                                <?php if($req['status'] == 'Pending'): ?>
                                    <button type="submit" name="status" value="Completed" class="btn btn-green"><i class="fas fa-check"></i></button>
                                    <button type="submit" name="status" value="Cancelled" class="btn btn-red"><i class="fas fa-times"></i></button>
                                <?php else: ?><span style="color:#aaa; font-size:0.8rem;">Closed</span><?php endif; ?>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- 4. FEEDBACK LIST -->
    <div class="section-card">
        <div class="section-header"><h3><i class="fas fa-comment-dots"></i> Recent Feedback</h3></div>
        <div class="table-responsive">
            <table>
                <thead><tr><th>User</th><th>Rating</th><th>Comment</th><th>Date</th></tr></thead>
                <tbody>
                    <?php foreach($feedbacks as $fb): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($fb['fullname']); ?></td>
                        <td><span style="color:#f59e0b; font-weight:bold;"><?php echo str_repeat('★', $fb['rating']); ?></span> (<?php echo $fb['rating']; ?>)</td>
                        <td><?php echo htmlspecialchars($fb['comments']); ?></td>
                        <td><?php echo date('d M Y', strtotime($fb['created_at'])); ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if(empty($feedbacks)): ?><tr><td colspan="4" style="text-align:center; color:#999;">No feedback yet.</td></tr><?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- 5. USER MANAGEMENT -->
    <div class="section-card">
        <div class="section-header"><h3><i class="fas fa-users-cog"></i> User & Collector Management</h3></div>
        <form method="post" class="add-form">
            <input type="hidden" name="action" value="add_collector">
            <input type="text" name="name" placeholder="Collector Name" required>
            <input type="email" name="email" placeholder="Email" required>
            <input type="text" name="mobile" placeholder="Mobile" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit" class="btn btn-purple"><i class="fas fa-plus"></i> Add</button>
        </form>
        <div class="table-responsive">
            <table>
                <thead><tr><th>Name</th><th>Role</th><th>Email</th><th>Status</th><th>Manage</th></tr></thead>
                <tbody>
                    <?php foreach($users as $u): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($u['fullname']); ?></td>
                        <td><span class="badge <?php echo $u['role']=='controller'?'bg-purple':'bg-gray'; ?>"><?php echo ucfirst($u['role']); ?></span></td>
                        <td><?php echo htmlspecialchars($u['email']); ?></td>
                        <td><span class="badge <?php echo $u['status']=='Active'?'bg-green':'bg-red'; ?>"><?php echo $u['status']; ?></span></td>
                        <td>
                            <form method="post" onsubmit="return confirm('Are you sure?');" style="display:inline;">
                                <input type="hidden" name="action" value="manage_user">
                                <input type="hidden" name="user_id" value="<?php echo $u['id']; ?>">
                                <?php if($u['status'] == 'Active'): ?>
                                    <button type="submit" name="type" value="suspend" class="btn btn-red" title="Suspend"><i class="fas fa-ban"></i></button>
                                <?php else: ?>
                                    <button type="submit" name="type" value="activate" class="btn btn-green" title="Activate"><i class="fas fa-check-circle"></i></button>
                                <?php endif; ?>
                                <button type="submit" name="type" value="delete" class="btn btn-gray" title="Delete"><i class="fas fa-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>
</body>
</html>S