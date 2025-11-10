<?php
include 'config.php';
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    die("Access denied. Admins only.");
}

if (!isset($_GET['id'])) {
    die("Movie ID not provided.");
}

$movie_id = intval($_GET['id']);

// Fetch existing movie data
$result = $conn->query("SELECT * FROM movies WHERE id = $movie_id");
if ($result->num_rows === 0) {
    die("Movie not found.");
}
$movie = $result->fetch_assoc();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $conn->real_escape_string($_POST['title']);
    $description = $conn->real_escape_string($_POST['description']);
    $genre = $conn->real_escape_string($_POST['genre']);
    $release_year = intval($_POST['release_year']);

    // Update query
    $sql = "UPDATE movies 
            SET title='$title', description='$description', genre='$genre', release_year='$release_year'
            WHERE id=$movie_id";

    if ($conn->query($sql)) {
        echo "<script>alert('✅ Movie updated successfully!'); window.location='index.php';</script>";
    } else {
        echo "Error updating movie: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Edit Movie</title>
  <style>
    /* 🎬 General Reset */
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: "Poppins", sans-serif;
    }

    body {
      background-color: #f4f6f8;
      color: #222;
      line-height: 1.6;
      padding: 20px;
      display: flex;
      justify-content: center;
      align-items: center;
      min-height: 100vh;
    }

    .container {
      background: #fff;
      padding: 30px 40px;
      border-radius: 12px;
      box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
      max-width: 500px;
      width: 100%;
      text-align: center;
    }

    h2 {
      margin-bottom: 20px;
      color: #007bff;
    }

    form {
      display: flex;
      flex-direction: column;
      gap: 15px;
      text-align: left;
    }

    label {
      font-weight: 500;
      color: #333;
    }

    input, textarea {
      width: 100%;
      padding: 10px 12px;
      border: 1px solid #ccc;
      border-radius: 6px;
      font-size: 1em;
    }

    textarea {
      resize: vertical;
    }

    button {
      padding: 10px;
      font-size: 1em;
      background-color: #007bff;
      color: white;
      border: none;
      border-radius: 6px;
      cursor: pointer;
      transition: 0.3s;
    }

    button:hover {
      background-color: #0056b3;
    }

    a {
      display: inline-block;
      margin-top: 15px;
      text-decoration: none;
      color: #007bff;
      transition: 0.3s;
    }

    a:hover {
      text-decoration: underline;
    }

    /* 📱 Responsive */
    @media (max-width: 600px) {
      .container {
        padding: 25px;
      }
    }
  </style>
</head>
<body>
  <div class="container">
    <h2>Edit Movie</h2>

    <form method="POST">
      <label>Movie Title:</label>
      <input type="text" name="title" value="<?php echo htmlspecialchars($movie['title']); ?>" required>

      <label>Description:</label>
      <textarea name="description" rows="4"><?php echo htmlspecialchars($movie['description']); ?></textarea>

      <label>Genre:</label>
      <input type="text" name="genre" value="<?php echo htmlspecialchars($movie['genre']); ?>">

      <label>Release Year:</label>
      <input type="number" name="release_year" value="<?php echo htmlspecialchars($movie['release_year']); ?>">

      <button type="submit">💾 Update Movie</button>
    </form>

    <a href="index.php">← Back to Home</a>
  </div>
</body>
</html>
