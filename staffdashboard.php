<?php
require_once __DIR__ . '/includes/auth.php';
requireRole('staff');
require_once __DIR__ . '/includes/db-connect.php';
$employeeId = $_SESSION['employee_id'];
$message = '';
if (!$employeeId) {
    die("This staff account isn't linked to an employee record. Ask an admin to fix this in the Users table.");
}
if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['action']) &&
    $_POST['action'] === 'update_status'
) {
    $status     = $_POST['status'];
    $orderId    = $_POST['order_id'];
    $stmt = $conn->prepare("
        UPDATE Orders
        SET Status = ?
        WHERE OrderId = ?
        AND OrderId IN (
            SELECT OrderId
            FROM OrderAssignments
            WHERE EmployeeId = ?
        )
    ");
    if (!$stmt) {
        die("SQL Error: " . $conn->error);
    }
    $stmt->bind_param(
        "sii",
        $status,
        $orderId,
        $employeeId
    );
    if ($stmt->execute()) {
        $message = $stmt->affected_rows > 0
            ? 'Order status updated.'
            : "That order isn't assigned to you, so nothing was changed.";
    } else {
        $message = "Error updating order: " . $stmt->error;
    }
    $stmt->close();
}
$stmt = $conn->prepare("
    SELECT Name, Designation, City
    FROM Employee
    WHERE EmployeeId = ?
");
$stmt->bind_param("i", $employeeId);
$stmt->execute();
$result = $stmt->get_result();
$me = $result->fetch_assoc();
$stmt->close();
$myTasks = [];
$stmt = $conn->prepare("
    SELECT
        o.OrderId,
        o.CustomerName,
        o.Product,
        o.OrderDate,
        o.Status,
        oa.TaskRole
    FROM OrderAssignments oa
    JOIN Orders o
        ON oa.OrderId = o.OrderId
    WHERE oa.EmployeeId = ?
    ORDER BY o.OrderDate DESC
");
$stmt->bind_param("i", $employeeId);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $myTasks[] = $row;
}
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Stitch & Co — My Tasks</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,400;9..144,500;9..144,600&family=Work+Sans:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="style.css">
</head>
<body>
<div class="app">
  <aside class="sidebar">
    <div class="brand">
      <svg class="thread-icon" viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg">
        <circle cx="20" cy="20" r="16" stroke="url(#g3)" stroke-width="1.6"/>
        <path d="M7 21c6-7 12 7 18 0s7-9 9-3" stroke="url(#g3)" stroke-width="1.4" fill="none"/>
        <defs><linearGradient id="g3" x1="0" y1="0" x2="40" y2="40" gradientUnits="userSpaceOnUse">
          <stop stop-color="#C97C86"/><stop offset="1" stop-color="#C9A66B"/></linearGradient></defs>
      </svg>
      <div class="brand-text">
        <div class="logo">Stitch & Co</div>
        <div class="role-tag">Staff view</div>
      </div>
    </div>
    <nav class="nav-links">
      <a class="nav-item active" href="#">My tasks</a>
    </nav>
    <div class="sidebar-footer">
      <div class="signed-in-as">Signed in as <strong><?= htmlspecialchars($_SESSION['username']) ?></strong> (Staff)</div>
      <a href="logout.php" class="btn btn-secondary" style="display:block;text-align:center;text-decoration:none;">Log out</a>
    </div>
  </aside>

  <main>
    <div class="page-header">
      <div>
        <h1>Welcome, <?= htmlspecialchars($me['Name'] ?? $_SESSION['username']) ?></h1>
        <p><?= htmlspecialchars($me['Designation'] ?? '') ?> — you can see and update only the orders assigned to you.</p>
      </div>
    </div>

    <?php if ($message): ?>
      <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <div class="panel">
      <h3>My assigned orders</h3>
      <?php if (empty($myTasks)): ?>
        <p style="color:var(--muted); font-size:13.5px;">No orders are assigned to you yet.</p>
      <?php else: ?>
        <div class="table-wrapper">
          <table>
            <thead><tr><th>Order</th><th>Customer</th><th>Product</th><th>My task</th><th>Status</th><th>Update</th></tr></thead>
            <tbody>
              <?php foreach ($myTasks as $t): ?>
                <tr>
                  <td>#<?= $t['OrderId'] ?></td>
                  <td><?= htmlspecialchars($t['CustomerName']) ?></td>
                  <td><?= htmlspecialchars($t['Product']) ?></td>
                  <td><?= htmlspecialchars($t['TaskRole']) ?></td>
                  <td><span class="pill pill-<?= strtolower($t['Status']) ?>"><?= $t['Status'] ?></span></td>
                  <td>
                    <form method="POST" action="staff_dashboard.php" style="display:flex; gap:6px;">
                      <input type="hidden" name="action" value="update_status">
                      <input type="hidden" name="order_id" value="<?= $t['OrderId'] ?>">
                      <select name="status" style="padding:6px 8px; border-radius:6px; border:1px solid var(--border);">
                        <option value="Pending" <?= $t['Status']==='Pending'?'selected':'' ?>>Pending</option>
                        <option value="Shipped" <?= $t['Status']==='Shipped'?'selected':'' ?>>Shipped</option>
                        <option value="Delivered" <?= $t['Status']==='Delivered'?'selected':'' ?>>Delivered</option>
                      </select>
                      <button type="submit" class="btn" style="padding:6px 10px; font-size:12px;">Save</button>
                    </form>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </div>

    <div class="panel">
      <h3>What you can't see here</h3>
      <p style="color:var(--muted); font-size:13.5px; margin:0;">
        Staff accounts don't have access to other employees' records, salary data, the full order list, or account/settings management —
        those are manager-only. If you need something outside your assigned orders, ask an admin.
      </p>
    </div>
  </main>
</div>
</body>
</html>
