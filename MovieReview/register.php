<?php
include 'config.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $sql = "INSERT INTO users (username, email, password) VALUES ('$username', '$email', '$password')";
    if ($conn->query($sql) === TRUE) {
        echo "<script>alert('Registration successful! You can now login.'); window.location='login.php';</script>";
    } else {
        $error = "Error: " . $conn->error;
    }
}
?>
<!DOCTYPE html>
<html>
<head>
  <title>Register - MovieReview</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>

<nav>
  <h1>🎬 CineRate</h1>
  <div>
    <a href="index.php">Home</a>
    <a href="login.php">Login</a>
  </div>
</nav>

<div class="container" style="max-width: 400px;">
  <h2>Create Account</h2>
  <?php if (isset($error)) echo "<p style='color:red;'>$error</p>"; ?>

  <form method="POST" action="">
    <label>Username</label>
    <input type="text" name="username" required>

    <label>Email</label>
    <input type="email" name="email" required>

    <label>Password</label>
    <input type="password" name="password" required>

    <button type="submit">Register</button>
  </form>

  <p>Already have an account? <a href="login.php">Login here</a>.</p>
</div>

</body>
</html>
