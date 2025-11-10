<?php
include 'config.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $query = $conn->prepare("SELECT * FROM users WHERE username = ?");
    $query->bind_param("s", $username);
    $query->execute();
    $result = $query->get_result();

   if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['role'] = $user['role']; // ✅ Store the user role in session
    header("Location: index.php");
    exit();

  
        } else {
            echo "Invalid password.";
        }
    } else {
        echo "User not found.";
    }

?>

<!DOCTYPE html>
<html>
<head>
  <title>Login - MovieReview</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>

<nav>
  <h1>🎬 CineRate</h1>
  <div>
    <a href="index.php">Home</a>
    <a href="register.php">Register</a>
  </div>
</nav>

<div class="container" style="max-width: 400px;">
  <h2>Login</h2>
  <?php if (isset($error)) echo "<p style='color:red;'>$error</p>"; ?>

  <form method="POST" action="">
    <label>Username</label>
    <input type="text" name="username" required>

    <label>Password</label>
    <input type="password" name="password" required>

    <button type="submit">Login</button>
  </form>

  <p>Don’t have an account? <a href="register.php">Register here</a>.</p>
</div>

</body>
</html>
