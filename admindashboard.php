<?php
require_once __DIR__ . '/includes/auth.php';
requireRole('admin');
require_once __DIR__ . '/includes/db-connect.php';
$message = '';
if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['action']) &&
    $_POST['action'] === 'assign_task'
) {
    $orderId    = $_POST['order_id'];
    $employeeId = $_POST['employee_id'];
    $taskRole   = $_POST['task_role'];
    $stmt = $conn->prepare(
        "INSERT INTO OrderAssignments (OrderId, EmployeeId, TaskRole)
         VALUES (?, ?, ?)"
    );
    if (!$stmt) {
        die("SQL Error: " . $conn->error);
    }
    $stmt->bind_param(
        "iis",
        $orderId,
        $employeeId,
        $taskRole
    );
    if ($stmt->execute()) {
        $message = 'Task assigned successfully.';
    } else {
        $message = 'Error assigning task: ' . $stmt->error;
    }
    $stmt->close();
}
if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['action']) &&
    $_POST['action'] === 'add_employee'
) {
    $name        = $_POST['name'];
    $contacts    = $_POST['contacts'];
    $email       = $_POST['email'];
    $role        = $_POST['role'];
    $address     = $_POST['address'];
    $city        = $_POST['city'];
    $designation = $_POST['designation'];
    $salary      = $_POST['salary'];
    $stmt = $conn->prepare(
        "INSERT INTO Employee
        (Name, Contacts, Email, Role, Address, City, Designation, Salary)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
    );
    if (!$stmt) {
        die("SQL Error: " . $conn->error);
    }
    $stmt->bind_param(
        "sssssssd",
        $name,
        $contacts,
        $email,
        $role,
        $address,
        $city,
        $designation,
        $salary
    );
    if ($stmt->execute()) {
        $message = 'Employee added successfully.';
    } else {
        $message = 'Error adding employee: ' . $stmt->error;
    }
    $stmt->close();
}
$employees = [];
$result = $conn->query(
    "SELECT * FROM Employee ORDER BY EmployeeId"
);
if (!$result) {
    die("Employee Query Error: " . $conn->error);
}
while ($row = $result->fetch_assoc()) {
    $employees[] = $row;
}
$orders = [];
$result = $conn->query(
    "SELECT * FROM Orders ORDER BY OrderId DESC"
);
if (!$result) {
    die("Orders Query Error: " . $conn->error);
}
while ($row = $result->fetch_assoc()) {
    $orders[] = $row;
}
$stats = [
    'employees' => 0,
    'active'    => 0,
    'revenue'   => 0
];
$result = $conn->query(
    "SELECT COUNT(*) AS total FROM Employee"
);
if ($result) {
    $row = $result->fetch_assoc();
    $stats['employees'] = $row['total'];
}
$result = $conn->query(
    "SELECT COUNT(*) AS total
     FROM Orders
     WHERE Status IN ('Pending', 'Shipped')"
);
if ($result) {
    $row = $result->fetch_assoc();
    $stats['active'] = $row['total'];
}
$result = $conn->query(
    "SELECT COALESCE(SUM(Amount), 0) AS total
     FROM Orders
     WHERE Status = 'Delivered'"
);

if ($result) {
    $row = $result->fetch_assoc();
    $stats['revenue'] = $row['total'];
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Stitch & Co — Admin</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,400;9..144,500;9..144,600&family=Work+Sans:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="style.css">
</head>
<body>
<div class="app">
  <aside class="sidebar">
    <div class="brand">
      <svg class="thread-icon" viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg">
        <circle cx="20" cy="20" r="16" stroke="url(#g2)" stroke-width="1.6"/>
        <path d="M7 21c6-7 12 7 18 0s7-9 9-3" stroke="url(#g2)" stroke-width="1.4" fill="none"/>
        <defs><linearGradient id="g2" x1="0" y1="0" x2="40" y2="40" gradientUnits="userSpaceOnUse">
          <stop stop-color="#C97C86"/><stop offset="1" stop-color="#C9A66B"/></linearGradient></defs>
      </svg>
      <div class="brand-text">
        <div class="logo">Stitch & Co</div>
        <div class="role-tag">Manager view</div>
      </div>
    </div>
    <nav class="nav-links">
      <a class="nav-item active" href="#employees-section">Employees</a>
      <a class="nav-item" href="#orders-section">Orders</a>
      <a class="nav-item" href="#assign-section">Assign task</a>
      <a class="nav-item" href="#add-employee-section">Add employee</a>
    </nav>
    <div class="sidebar-footer">
      <div class="signed-in-as">Signed in as <strong><?= htmlspecialchars($_SESSION['username']) ?></strong> (Manager)</div>
      <a href="logout.php" class="btn btn-secondary" style="display:block;text-align:center;text-decoration:none;">Log out</a>
    </div>
  </aside>

  <main>
    <div class="page-header">
      <div>
        <h1>Admin dashboard</h1>
        <p>Full access — manage employees, orders, and task assignments.</p>
      </div>
    </div>

    <?php if ($message): ?>
      <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <div class="stats-grid">
      <div class="stat-card"><div class="stat-label">Total employees</div><div class="stat-value"><?= $stats['employees'] ?></div></div>
      <div class="stat-card"><div class="stat-label">Active orders</div><div class="stat-value"><?= $stats['active'] ?></div></div>
      <div class="stat-card"><div class="stat-label">Revenue (delivered)</div><div class="stat-value">Rs <?= number_format($stats['revenue']) ?></div></div>
    </div>

    <!-- EMPLOYEES — admin only, staff cannot see this -->
    <div class="panel" id="employees-section">
      <h3>All employees</h3>
      <input class="search-input" id="employeeSearch" placeholder="Search employees...">
      <div class="table-wrapper">
        <table id="employeeTable">
          <thead><tr><th>ID</th><th>Name</th><th>Contacts</th><th>Email</th><th>Role</th><th>City</th><th>Designation</th><th>Salary</th></tr></thead>
          <tbody>
            <?php foreach ($employees as $e): ?>
              <tr>
                <td><?= $e['EmployeeId'] ?></td>
                <td><?= htmlspecialchars($e['Name']) ?></td>
                <td><?= htmlspecialchars($e['Contacts']) ?></td>
                <td><?= htmlspecialchars($e['Email']) ?></td>
                <td><?= htmlspecialchars($e['Role']) ?></td>
                <td><?= htmlspecialchars($e['City']) ?></td>
                <td><?= htmlspecialchars($e['Designation']) ?></td>
                <td><?= number_format($e['Salary']) ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>

    <!-- ORDERS — admin sees all, staff will only see their own -->
    <div class="panel" id="orders-section">
      <h3>All orders</h3>
      <input class="search-input" id="orderSearch" placeholder="Search orders...">
      <div class="table-wrapper">
        <table id="orderTable">
          <thead><tr><th>Order ID</th><th>Customer</th><th>Product</th><th>Date</th><th>Status</th><th>Amount</th></tr></thead>
          <tbody>
            <?php foreach ($orders as $o): ?>
              <tr>
                <td>#<?= $o['OrderId'] ?></td>
                <td><?= htmlspecialchars($o['CustomerName']) ?></td>
                <td><?= htmlspecialchars($o['Product']) ?></td>
                <td><?= $o['OrderDate'] ?></td>
                <td><span class="pill pill-<?= strtolower($o['Status']) ?>"><?= $o['Status'] ?></span></td>
                <td>Rs <?= number_format($o['Amount'], 2) ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>

    <!-- ASSIGN TASK — admin-only capability, staff cannot assign work -->
    <div class="panel" id="assign-section">
      <h3>Assign an employee to an order</h3>
   <form method="POST">
        <input type="hidden" name="action" value="assign_task">
        <div class="form-grid">
          <div class="field">
            <label>Order</label>
            <select name="order_id" required>
              <?php foreach ($orders as $o): ?>
                <option value="<?= $o['OrderId'] ?>">#<?= $o['OrderId'] ?> — <?= htmlspecialchars($o['CustomerName']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="field">
            <label>Employee</label>
            <select name="employee_id" required>
              <?php foreach ($employees as $e): ?>
                <option value="<?= $e['EmployeeId'] ?>"><?= htmlspecialchars($e['Name']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
        <div class="field">
          <label>Task role</label>
          <input type="text" name="task_role" placeholder="e.g. Crocheting, Quality Check, Packing" required>
        </div>
        <button type="submit" class="btn">Assign task</button>
      </form>
    </div>

    <!-- ADD EMPLOYEE — admin-only capability -->
    <div class="panel" id="add-employee-section">
      <h3>Add a new employee</h3>
     <form method="POST">
    <input type="hidden" name="action" value="add_employee">
    <div class="form-grid">
        <div class="field">
            <label>Name</label>
            <input type="text" name="name" required>
        </div>
        <div class="field">
            <label>Contacts</label>
            <input type="text" name="contacts" required>
        </div>
        <div class="field">
            <label>Email</label>
            <input type="email" name="email" required>
        </div>
        <div class="field">
            <label>Department/Role</label>
            <input type="text" name="role" required>
        </div>
        <div class="field">
            <label>Address</label>
            <input type="text" name="address">
        </div>
        <div class="field">
            <label>City</label>
            <input type="text" name="city" required>
        </div>
        <div class="field">
            <label>Designation</label>
            <input type="text" name="designation" required>
        </div>
        <div class="field">
            <label>Salary</label>
            <input type="number" name="salary" min="1" required>
        </div>
    </div>
    <button type="submit" class="btn">Add employee</button>
</form>
    </div>
  </main>
</div>

<script>
  function attachSearch(inputId, tableSelector) {
    const input = document.getElementById(inputId);
    const rows = document.querySelectorAll(tableSelector + ' tbody tr');
    input.addEventListener('input', () => {
      const term = input.value.toLowerCase();
      rows.forEach(row => row.style.display = row.textContent.toLowerCase().includes(term) ? '' : 'none');
    });
  }
  attachSearch('employeeSearch', '#employeeTable');
  attachSearch('orderSearch', '#orderTable');
</script>
</body>
</html>
