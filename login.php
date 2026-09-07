<?php

require_once __DIR__ . '/includes/db-connect.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $username   = trim($_POST['username'] ?? '');
    $password   = $_POST['password'] ?? '';
    $roleChoice = $_POST['role_choice'] ?? '';

    if ($username === '' || $password === '' || $roleChoice === '') {

        $error = 'Please choose Admin or Staff, then enter your username and password.';

    } else {

        $stmt = $conn->prepare(
            "SELECT UserId, Username, PasswordHash, Role, EmployeeId
             FROM Users
             WHERE Username = ?"
        );

        if (!$stmt) {
            die("SQL Prepare Error: " . $conn->error);
        }

        $stmt->bind_param("s", $username);
        $stmt->execute();

        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        if (!$user) {

            $error = 'User not found.';

        } elseif (!password_verify($password, $user['PasswordHash'])) {

            $error = 'Incorrect username or password.';

        } elseif ($user['Role'] !== $roleChoice) {

            $error = 'This account is registered as ' .
                     ucfirst($user['Role']) .
                     '. Please select ' .
                     ucfirst($user['Role']) .
                     ' and try again.';

        } else {

            // Login successful
            $_SESSION['user_id']     = $user['UserId'];
            $_SESSION['username']    = $user['Username'];
            $_SESSION['role']        = $user['Role'];
            $_SESSION['employee_id'] = $user['EmployeeId'];

            // Redirect according to role
            if ($user['Role'] === 'admin') {

                header('Location: admindashboard.php');
                exit;

            } else {

                header('Location: staffdashboard.php');
                exit;
            }
        }

        $stmt->close();
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Stitch & Co — Sign In</title>
<link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,400;9..144,500;9..144,600&family=Work+Sans:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="style.css?v=3">
</head>
<body>
  <div class="login-wrap">
    <div class="login-card">
      <svg class="thread-icon" viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg">
        <circle cx="20" cy="20" r="16" stroke="url(#g1)" stroke-width="1.6"/>
        <path d="M7 21c6-7 12 7 18 0s7-9 9-3" stroke="url(#g1)" stroke-width="1.4" fill="none"/>
        <defs>
          <linearGradient id="g1" x1="0" y1="0" x2="40" y2="40" gradientUnits="userSpaceOnUse">
            <stop stop-color="#C97C86"/><stop offset="1" stop-color="#C9A66B"/>
          </linearGradient>
        </defs>
      </svg>
      <div class="login-title">Stitch & Co</div>
      <p class="login-sub">Sign in to your dashboard</p>

      <?php if ($error): ?>
        <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
      <?php endif; ?>

      <form method="POST" action="login.php" id="loginForm">
        <input type="hidden" name="role_choice" id="roleChoice" value="admin">

        <div class="role-toggle">
          <button type="button" class="role-btn active" data-role="admin" id="adminBtn">Admin</button>
          <button type="button" class="role-btn" data-role="staff" id="staffBtn">Staff</button>
        </div>

        <div class="field">
          <label for="username">Username</label>
          <input type="text" id="username" name="username" autocomplete="username" required>
        </div>
        <div class="field">
          <label for="password">Password</label>
          <input type="password" id="password" name="password" autocomplete="current-password" required>
        </div>
        <button type="submit" class="login-btn">Sign in as <span id="roleLabel">Admin</span></button>
      </form>

      <script>
        const roleChoiceInput = document.getElementById('roleChoice');
        const roleLabel = document.getElementById('roleLabel');
        const adminBtn = document.getElementById('adminBtn');
        const staffBtn = document.getElementById('staffBtn');

        function selectRole(role) {
          roleChoiceInput.value = role;
          roleLabel.textContent = role === 'admin' ? 'Admin' : 'Staff';
          adminBtn.classList.toggle('active', role === 'admin');
          staffBtn.classList.toggle('active', role === 'staff');
        }

        adminBtn.addEventListener('click', () => selectRole('admin'));
        staffBtn.addEventListener('click', () => selectRole('staff'));
      </script>

      <div class="login-hint">
        Select Admin or Staff above before signing in. The button is just a shortcut — the server always double-checks it against your account's real role, so picking the wrong one will just show an error, not grant access.
      </div>
    </div>
  </div>
</body>
</html>