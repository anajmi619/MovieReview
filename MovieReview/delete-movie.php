<?php
include 'config.php';
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    die("Access denied. Admins only.");
}

if (isset($_GET['id'])) {
    $movie_id = intval($_GET['id']);
    
    // Delete movie from database
    $sql = "DELETE FROM movies WHERE id = $movie_id";
    if ($conn->query($sql)) {
        echo "<script>alert('Movie deleted successfully!'); window.location='index.php';</script>";
    } else {
        echo "Error deleting movie: " . $conn->error;
    }
} else {
    echo "Movie ID not provided.";
}
?>
