<?php
include 'config.php';
session_start();

// If not logged in or not admin, block access
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $genre = $_POST['genre'];
    $release_year = $_POST['release_year'];

    // Handle image upload
    $target_dir = "uploads/";
    $image_name = basename($_FILES["image"]["name"]);
    $target_file = $target_dir . $image_name;
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    // Validate image
    $check = getimagesize($_FILES["image"]["tmp_name"]);
    if ($check === false) {
        die("File is not an image.");
    }

    // Move uploaded file
    if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
        // Insert movie data into the database
        $stmt = $conn->prepare("INSERT INTO movies (title, description, image, genre, release_year) VALUES (?, ?, ?, ?, ?)");

        $stmt->bind_param("ssssi", $title, $description, $image_name, $genre, $release_year);




        if ($stmt->execute()) {
            echo "<script>alert('Movie added successfully!'); window.location='index.php';</script>";
        } else {
            echo "Error inserting movie: " . $stmt->error;
        }
    } else {
        echo "Error uploading image.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Add New Movie</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>

<!-- 🧭 Navbar -->
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
    <a href="profile.php">Profile</a>
  </div>
</nav>

<!-- 📦 Add Movie Container -->
<div class="review-container">
  <h2>Add New Movie</h2>

  <form method="POST" enctype="multipart/form-data" class="review-form">
      <label>Movie Title:</label>
      <input type="text" name="title" required>

      <label>Genre:</label>
      <input type="text" name="genre" placeholder="e.g. Action, Comedy" required>

      <label>Release Year:</label>
      <input type="number" name="release_year" min="1900" max="2099" required>

      <label>Description:</label>
      <textarea name="description" rows="5" placeholder="Write a short description..." required></textarea>

      <label>Upload Image:</label>
      <input type="file" name="image" accept="image/*" required>

      <button type="submit">Add Movie</button>
  </form>

  <br>
  <a href="index.php">← Back to Home</a>
</div>

<!-- 🦶 Footer -->
<footer>
  <p>© 2025 Movie Review Hub. All Rights Reserved. | <a href="index.php">Home</a></p>
</footer>

</body>
</html>
