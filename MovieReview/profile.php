<?php
include 'config.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user details
$sql = "SELECT * FROM users WHERE id = $user_id";
$result = $conn->query($sql);
$user = $result->fetch_assoc();

// Fetch user reviews
$sql_reviews = "
    SELECT reviews.*, movies.title AS movie_title
    FROM reviews 
    JOIN movies ON reviews.movie_id = movies.id
    WHERE reviews.user_id = $user_id
    ORDER BY reviews.created_at DESC
";
$result_reviews = $conn->query($sql_reviews);
?>
<!DOCTYPE html>
<html>
<head>
  <title>My Profile - MovieReview</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>

<nav>
  <h1>🎬 CineRate</h1>
  <div>
    <?php if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin'): ?>
        <!-- Show Home only for non-admin users -->
        <a href="index.php">Home</a>
    <?php endif; ?>

    <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
        <a href="index.php">Manage Movies</a>
        <a href="add-movie.php">Add Movie</a>
    <?php endif; ?>
    <a href="logout.php">Logout</a>
  </div>
</nav>

<div class="container" style="max-width: 700px;">
  <h2>👤 My Profile</h2>

  <div style="margin-top: 20px;">
    <p><strong>Username:</strong> <?php echo htmlspecialchars($user['username']); ?></p>
    <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
    <?php if (!empty($user['created_at'])): ?>
    <?php endif; ?>
  </div>

  <hr style="margin: 30px 0;">

  <h3>📝 My Reviews</h3>
  <?php
  if ($result_reviews->num_rows > 0) {
      while ($row = $result_reviews->fetch_assoc()) {
          echo "<div class='review-box'>";
          echo "<p><strong>🎞️ " . htmlspecialchars($row['movie_title']) . "</strong></p>";
          echo "<p>⭐ Rating: " . htmlspecialchars($row['rating']) . "/5</p>";
          echo "<p>💬 Comment: " . htmlspecialchars($row['comment']) . "</p>";
          echo "<small>Posted on: " . htmlspecialchars($row['created_at']) . "</small><br>";
          echo "<a href='review.php?movie_id=" . $row['movie_id'] . "'>View Movie</a>";
          echo "</div>";
      }
  } else {
      echo "<p>You haven't written any reviews yet.</p>";
  }
  ?>

  <br><br>
  <a href="logout.php"><button>Logout</button></a>
</div>

<!-- Footer -->
<footer>
  <p>© 2025 CineRate Hub. All Rights Reserved. | <a href="index.php">Home</a></p>

</footer>
</body>
</html>

