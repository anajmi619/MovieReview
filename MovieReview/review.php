<?php
include 'config.php';
session_start();

// Get movie ID from URL
if (!isset($_GET['movie_id'])) {
    die("Movie ID not specified.");
}

$movie_id = intval($_GET['movie_id']);

// Fetch movie details
$sql_movie = "SELECT * FROM movies WHERE id = $movie_id";
$result_movie = $conn->query($sql_movie);

if ($result_movie->num_rows == 0) {
    die("Movie not found.");
}
$movie = $result_movie->fetch_assoc();

// Handle review form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_SESSION['user_id'])) {
    $rating = $_POST['rating'];
    $comment = $_POST['comment'];
    $user_id = $_SESSION['user_id'];

    $sql = "INSERT INTO reviews (user_id, movie_id, rating, comment) 
            VALUES ($user_id, $movie_id, '$rating', '$comment')";
    if ($conn->query($sql) === TRUE) {
        echo "<script>alert('Review added successfully!'); window.location='review.php?movie_id=$movie_id';</script>";
    } else {
        echo "Database error: " . $conn->error;
    }
}

// Handle delete review
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_review_id'])) {
    $review_id = intval($_POST['delete_review_id']);
    $user_id = $_SESSION['user_id'];

    // ✅ Ensure user can only delete their own review
    $delete_sql = "DELETE FROM reviews WHERE id = $review_id AND user_id = $user_id";
    if ($conn->query($delete_sql) === TRUE) {
        echo "<script>alert('Review deleted successfully!'); window.location='review.php?movie_id=$movie_id';</script>";
    } else {
        echo "<script>alert('Error deleting review: " . $conn->error . "');</script>";
    }
}

// Fetch all reviews for this movie
$sql_reviews = "
    SELECT reviews.*, users.username 
    FROM reviews 
    JOIN users ON reviews.user_id = users.id
    WHERE movie_id = $movie_id
    ORDER BY reviews.created_at DESC
";
$result_reviews = $conn->query($sql_reviews);

// Calculate average rating
$sql_avg = "SELECT AVG(rating) as avg_rating FROM reviews WHERE movie_id = $movie_id";
$result_avg = $conn->query($sql_avg);
$avg = $result_avg->fetch_assoc()['avg_rating'];
?>

<!DOCTYPE html>
<html>
<head>
    <title><?php echo htmlspecialchars($movie['title']); ?> - Reviews</title>
    <style>

        button[type="submit"] {
          background: #d9534f;
          color: white;
          border: none;
          padding: 6px 10px;
          border-radius: 5px;
          cursor: pointer;
        }
        button[type="submit"]:hover {
          background: #c9302c;
        }



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
        }

        /* 🧭 Navbar (optional) */
        nav {
          background: #222;
          color: #fff;
          padding: 15px 20px;
          display: flex;
          justify-content: space-between;
          align-items: center;
          flex-wrap: wrap;
          border-radius: 8px;
          margin-bottom: 20px;
        }

        nav h1 {
          font-size: 1.5em;
        }

        nav a {
          color: #fff;
          text-decoration: none;
          margin-left: 15px;
          transition: 0.3s;
        }

        nav a:hover {
          color: #ffcc00;
        }

        /* 🎬 Movie Card */
        .movie-card {
          display: flex;
          gap: 20px;
          align-items: flex-start;
          background: #fff;
          border-radius: 10px;
          box-shadow: 0 2px 10px rgba(0,0,0,0.1);
          padding: 20px;
          margin-bottom: 30px;
        }

        .movie-card img {
          width: 220px;
          height: 330px;
          border-radius: 10px;
          object-fit: cover;
        }

        .movie-info h2 {
          color: #222;
          margin-bottom: 10px;
        }

        .movie-info p {
          margin-bottom: 10px;
        }

        .rating {
          margin: 15px 0;
        }

        .rating h3 {
          background: #007bff;
          color: #fff;
          display: inline-block;
          padding: 8px 15px;
          border-radius: 6px;
          font-weight: 600;
        }

        /* 💬 Review Section */
        .review-section {
          margin-top: 30px;
          background: #fff;
          padding: 20px;
          border-radius: 10px;
          box-shadow: 0 1px 6px rgba(0,0,0,0.1);
        }

        .review-section h3 {
          border-left: 5px solid #007bff;
          padding-left: 10px;
          margin-bottom: 15px;
        }

        .review-box {
          background: #f9fafb;
          padding: 15px;
          border-radius: 10px;
          margin-bottom: 15px;
          box-shadow: 0 1px 4px rgba(0,0,0,0.08);
        }

        .review-header {
          font-weight: 600;
          color: #007bff;
          margin-bottom: 5px;
        }

        .review-comment {
          font-style: italic;
          margin: 8px 0;
        }

        .review-date {
          font-size: 12px;
          color: #666;
        }

        /* 📝 Review Form */
        .review-form {
          margin-top: 30px;
          background: #fff;
          padding: 20px;
          border-radius: 10px;
          box-shadow: 0 1px 6px rgba(0,0,0,0.1);
        }

        .review-form h3 {
          margin-bottom: 15px;
        }

        .review-form label {
          font-weight: 500;
        }

        .review-form input,
        .review-form textarea {
          width: 100%;
          padding: 10px;
          border: 1px solid #ccc;
          border-radius: 6px;
          margin-top: 5px;
          margin-bottom: 15px;
          font-size: 1em;
        }

        .review-form button {
          padding: 10px 15px;
          background: #007bff;
          color: white;
          border: none;
          border-radius: 6px;
          font-weight: 600;
          cursor: pointer;
          transition: 0.3s;
        }

        .review-form button:hover {
          background: #0056b3;
        }

        /* 🔗 Back Link */
        .back-link {
          margin-top: 30px;
          text-align: center;
        }

        .back-link a {
          color: #007bff;
          text-decoration: none;
          font-weight: 600;
        }

        .back-link a:hover {
          text-decoration: underline;
        }

        /* 📱 Responsive Design */
        @media (max-width: 768px) {
          .movie-card {
            flex-direction: column;
            align-items: center;
            text-align: center;
          }

          .movie-card img {
            width: 100%;
            height: auto;
          }

          .movie-info {
            width: 100%;
          }

          .review-form, .review-section {
            padding: 15px;
          }
        }

         /* 🦶 Footer */
        footer {
          text-align: center;
          background: #222;
          color: #fff;
          padding: 20px;
          margin-top: 40px;
          border-radius: 8px;
          font-size: 0.9em;
        }

        footer a {
          color: #ffcc00;
          text-decoration: none;
        }

        footer a:hover {
          text-decoration: underline;
        }
    </style> 
</head>
<body>
  <nav>
  <h1>🎬 CineRate</h1>
  <div>
    <a href="index.php">Home</a>
    <a href="profile.php">Profile</a>
    <a href="logout.php">Logout</a>
  </div>
</nav>

<div class="container">
    <div class="movie-card">
        <img src="uploads/<?php echo htmlspecialchars($movie['image']); ?>" alt="Movie Image" class="movie-image">
        <div class="movie-info">
            <h2><?php echo htmlspecialchars($movie['title']); ?></h2>
            <p class="genre"><strong>Genre:</strong> <?php echo htmlspecialchars($movie['genre']); ?></p>
            <p class="year"><strong>Release Year:</strong> <?php echo htmlspecialchars($movie['release_year']); ?></p>
            <p class="desc"><?php echo nl2br(htmlspecialchars($movie['description'])); ?></p>
            <div class="rating">
                <h3>Average Rating: 
                    <?php echo $avg ? round($avg, 1) . "/5 ⭐" : "No ratings yet"; ?>
                </h3>
            </div>
        </div>
    </div>

    <div class="review-section">
        <h3>User Reviews</h3>
        <div class="review-list">
            <?php
            if ($result_reviews->num_rows > 0) {
                while ($row = $result_reviews->fetch_assoc()) {
                    echo "<div style='border-bottom:1px solid #ccc; margin-bottom:10px;'>";
                    echo "<strong>" . htmlspecialchars($row['username']) . "</strong> rated " . htmlspecialchars($row['rating']) . "/5<br>";
                    echo "<em>" . htmlspecialchars($row['comment']) . "</em><br>";
                    echo "<small>Posted on " . $row['created_at'] . "</small>";

                    // ✅ Show delete button only for the user who wrote the review
                   if (isset($_SESSION['user_id']) && 
                       ($_SESSION['user_id'] == $row['user_id'] || $_SESSION['role'] == 'admin')) {  
                        echo "<form method='POST' action='' style='margin-top:5px;' onsubmit='return confirmDelete()'>
                              <input type='hidden' name='delete_review_id' value='" . $row['id'] . "'>
                              <button type='submit' class='delete-btn'>🗑️ Delete</button>
                          </form>
                          ";}  
 
                    echo "</div>";
                }
            } else {
                echo "<p>No reviews yet. Be the first to review!</p>";
            }

            ?>
        </div>
    </div>

    <div class="review-form">
        <?php if (isset($_SESSION['user_id'])): ?>
        <h3>Leave a Review</h3>
        <form method="POST" action="">
            <label>Rating (1-5):</label>
            <input type="number" name="rating" min="1" max="5" required>

            <label>Comment:</label>
            <textarea name="comment" rows="4" required></textarea>

            <button type="submit">Submit Review</button>
        </form>
        <?php else: ?>
        <p><a href="login.php">Login</a> to leave a review.</p>
        <?php endif; ?>
    </div>

    <div class="back-link">
        <a href="index.php">← Back to Movie List</a>
    </div>
</div>
<script>
function confirmDelete() {
    return confirm("Are you sure you want to delete this review? This action cannot be undone.");
}
</script>

<!-- 🦶 Footer -->
<footer>
  <p>© 2025 Movie Review Hub. All Rights Reserved. | <a href="index.php">Home</a></p>
</footer>

</body>
</html> 