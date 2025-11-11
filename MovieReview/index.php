<?php
include 'config.php';
session_start();

// Handle search and filter
$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';
$genre = isset($_GET['genre']) ? $conn->real_escape_string($_GET['genre']) : '';

// Fetch all genres for dropdown
$genre_query = $conn->query("SELECT DISTINCT genre FROM movies WHERE genre IS NOT NULL AND genre != ''");

// Handle sorting
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'newest';

$sql = "
    SELECT 
        movies.*, 
        COALESCE(ROUND(AVG(reviews.rating), 1), 0) AS avg_rating,
        COUNT(reviews.id) AS review_count
    FROM movies
    LEFT JOIN reviews ON movies.id = reviews.movie_id
    WHERE 1
";

if ($search) {
    $sql .= " AND (title LIKE '%$search%' OR description LIKE '%$search%')";
}
if ($genre) {
    $sql .= " AND genre = '$genre'";
}

// Handle new sorting options
$sort_year = isset($_GET['sort_year']) ? $_GET['sort_year'] : '';
$sort_rating = isset($_GET['sort_rating']) ? $_GET['sort_rating'] : '';

$order_clause = "";

// Apply sorting priority — rating first if selected
if ($sort_rating == 'highest') {
    $order_clause = " ORDER BY (SELECT AVG(rating) FROM reviews WHERE movie_id = movies.id) DESC";
} elseif ($sort_rating == 'lowest') {
    $order_clause = " ORDER BY (SELECT AVG(rating) FROM reviews WHERE movie_id = movies.id) ASC";
} elseif ($sort_year == 'year_desc') {
    $order_clause = " ORDER BY release_year DESC";
} elseif ($sort_year == 'year_asc') {
    $order_clause = " ORDER BY release_year ASC";
} else {
    // Default order (newest added first)
    $order_clause = " ORDER BY id DESC";
}

$sql .= " GROUP BY movies.id " . $order_clause;



$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Movie Review Website</title>
    <style>
              /* Dark Mode Toggle */
        #theme-toggle {
          background: transparent;
          color: white;
          border: 1px solid #555;
          border-radius: 8px;
          padding: 6px 12px;
          cursor: pointer;
          transition: 0.3s;
        }
        #theme-toggle:hover {
          background: #444;
        }

        /* Dark Theme */
        body.dark {
          background-color: #121212;
          color: #ddd;
        }
        body.dark header {
          background: #1e1e1e;
        }
        body.dark .filter-bar {
          background: #1e1e1e;
          color: #ccc;
        }
        body.dark .movie-card {
          background: #1f1f1f;
          box-shadow: 0 2px 6px rgba(255,255,255,0.05);
        }
        body.dark .movie-card h3,
        body.dark .movie-card p {
          color: #ddd;
        }
        body.dark a {
          color: #ffcc00;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f9f9f9;
            margin: 0;
            padding: 0;
        }

        header {
            background: #222;
            color: #fff;
            padding: 15px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        header h1 {
            margin: 0;
            font-size: 22px;
        }

        nav a {
            color: white;
            text-decoration: none;
            margin-left: 15px;
            font-size: 16px;
        }

        .filter-bar {
            background: white;
            padding: 20px;
            text-align: center;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        .filter-bar input, .filter-bar select, .filter-bar button {
            padding: 8px 12px;
            margin: 5px;
            border-radius: 8px;
            border: 1px solid #ccc;
            font-size: 14px;
        }

        .movie-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
            padding: 30px;
        }

        .movie-card {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            transition: transform 0.3s;
        }

        .movie-card:hover {
            transform: translateY(-5px);
        }

        .movie-card img {
            width: 100%;
            height: 300px;
            object-fit: cover;
        }

        .movie-card .content {
            padding: 15px;
        }

        .movie-card h3 {
            margin: 0 0 10px;
        }

        .movie-card p {
            font-size: 14px;
            color: #555;
        }

        .movie-card a {
            display: inline-block;
            margin-top: 10px;
            text-decoration: none;
            color: #2196f3;
            font-weight: bold;
        }

        .movie-card .content p strong {
          color: #f39c12;
        }

        /*  Footer */
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

<header>
    <h1>🎬 CineRate</h1> 
   <nav>
    <button id="theme-toggle">🌙 Dark Mode</button>

    <?php if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin'): ?>
        <!-- Show Home only for non-admin users -->
        <a href="index.php">Home</a>
    <?php endif; ?>

    <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
        <a href="index.php">Manage Movies</a>
        <a href="add-movie.php">Add Movie</a>
    <?php endif; ?>

    <a href="profile.php">Profile</a>

    <?php if (isset($_SESSION['user_id'])): ?>
        <a href="logout.php">Logout</a>
    <?php else: ?>
        <a href="login.php">Login</a>
    <?php endif; ?>
</nav>



</header>

<div class="filter-bar">
    <form method="GET" action="index.php">
    <input type="text" name="search" placeholder="Search movie..." value="<?php echo htmlspecialchars($search); ?>">

    <select name="genre">
        <option value="">All Genres</option>
        <?php while($g = $genre_query->fetch_assoc()): ?>
            <option value="<?php echo htmlspecialchars($g['genre']); ?>" 
                <?php if ($genre == $g['genre']) echo 'selected'; ?>>
                <?php echo htmlspecialchars($g['genre']); ?>
            </option>
        <?php endwhile; ?>
    </select>

    <!-- 🔹 Sorting by Release Year -->
    <select name="sort_year">
        <option value="">Sort by Release Year</option>
        <option value="year_desc" <?php if(isset($_GET['sort_year']) && $_GET['sort_year']=='year_desc') echo 'selected'; ?>>Newest</option>
        <option value="year_asc" <?php if(isset($_GET['sort_year']) && $_GET['sort_year']=='year_asc') echo 'selected'; ?>>Oldest</option>
    </select>

    <!-- 🔹 Sorting by Rating -->
    <select name="sort_rating">
        <option value="">Sort by Rating</option>
        <option value="highest" <?php if(isset($_GET['sort_rating']) && $_GET['sort_rating']=='highest') echo 'selected'; ?>>Highest Rated</option>
        <option value="lowest" <?php if(isset($_GET['sort_rating']) && $_GET['sort_rating']=='lowest') echo 'selected'; ?>>Lowest Rated</option>
    </select>

    <button type="submit">Filter</button>
</form>

</div>

<div class="movie-grid">
    <?php while($row = $result->fetch_assoc()): ?>
        <div class="movie-card">
    <img src="uploads/<?php echo htmlspecialchars($row['image']); ?>" alt="Movie Poster">
    <div class="content">
        <h3><?php echo htmlspecialchars($row['title']); ?></h3>
        <p><strong>Genre:</strong> <?php echo htmlspecialchars($row['genre']); ?></p>
        <p><strong>Release Year:</strong> <?php echo htmlspecialchars($row['release_year']); ?></p>
        <p><p class="rating"><strong>Average Rating:</strong> <?php echo $row['avg_rating']; ?>/5⭐</p>
        <p class="review-count">(<?php echo $row['review_count']; ?> reviews)</p>
        <p><?php echo substr(htmlspecialchars($row['description']), 0, 80); ?>...</p>
        <a href="review.php?movie_id=<?php echo $row['id']; ?>">View Reviews →</a>

        <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
            <div style="margin-top:10px;">
                <a href="edit-movie.php?id=<?php echo $row['id']; ?>" style="color:green;">✏️ Edit</a> |
                <a href="delete-movie.php?id=<?php echo $row['id']; ?>" style="color:red;" 
                   onclick="return confirm('Are you sure you want to delete this movie?');">🗑️ Delete</a>
            </div>
        <?php endif; ?>
    </div>
</div>

    <?php endwhile; ?>



</div>
<script>
  const toggleBtn = document.getElementById('theme-toggle');
  const body = document.body;

  // Load saved theme
  if (localStorage.getItem('theme') === 'dark') {
    body.classList.add('dark');
    toggleBtn.textContent = '☀️ Light Mode';
  }

  toggleBtn.addEventListener('click', () => {
    body.classList.toggle('dark');
    const isDark = body.classList.contains('dark');
    toggleBtn.textContent = isDark ? '☀️ Light Mode' : '🌙 Dark Mode';
    localStorage.setItem('theme', isDark ? 'dark' : 'light');
  });
</script>

<!--  Footer -->
<footer>
  <p>© 2025 CineRate Hub. All Rights Reserved. | <a href="index.php">Home</a></p>
</footer>

</body>
</html>

