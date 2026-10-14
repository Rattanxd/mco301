<?php
// Start a PHP session to store movie data across page refreshes
session_start();

if (!isset($_SESSION['movies'])) {
    $_SESSION['movies'] = [];
}

if (!isset($_SESSION['current_filter'])) {
    $_SESSION['current_filter'] = 'All';
}

// --- Handle Form Actions ---

// 1. Add Movie
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    $newMovie = [
        'id' => (int)(microtime(true) * 1000),
        'title' => trim($_POST['title']),
        'genre' => trim($_POST['genre']),
        'year' => (int)$_POST['year'],
        'status' => 'Plan to Watch',
        'rating' => 0
    ];
    $_SESSION['movies'][] = $newMovie;
    header('Location: index.php');
    exit;
}

// 2. Filter Tab Selection
if (isset($_GET['filter'])) {
    $_SESSION['current_filter'] = $_GET['filter'];
    header('Location: index.php');
    exit;
}

// 3. Delete Movie
if (isset($_GET['delete'])) {
    $deleteId = (int)$_GET['delete'];
    $_SESSION['movies'] = array_values(array_filter($_SESSION['movies'], fn($m) => $m['id'] !== $deleteId));
    header('Location: index.php');
    exit;
}

// 4. Mark as Watched
if (isset($_POST['action']) && $_POST['action'] === 'watch') {
    $watchId = (int)$_POST['id'];
    $rating = (int)$_POST['rating'];
    foreach ($_SESSION['movies'] as &$m) {
        if ($m['id'] === $watchId) {
            $m['status'] = 'Watched';
            $m['rating'] = max(1, min($rating, 5)); // Clamp between 1-5
            break;
        }
    }
    header('Location: index.php');
    exit;
}

// --- Filter Records for Rendering ---
$filteredMovies = $_SESSION['movies'];
if ($_SESSION['current_filter'] !== 'All') {
    $filteredMovies = array_values(array_filter($_SESSION['movies'], fn($m) => $m['status'] === $_SESSION['current_filter']));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Media Watchlist (PHP)</title>
  <style>
    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    body {
      background-color: #121214;
      color: #e1e1e6;
      display: flex;
      justify-content: center;
      padding: 40px 20px;
    }

    .container {
      width: 100%;
      max-width: 550px;
      background: #202024;
      padding: 24px;
      border-radius: 12px;
      box-shadow: 0 8px 24px rgba(0, 0, 0, 0.4);
    }

    header h1 {
      font-size: 1.8rem;
      color: #8257e5;
      margin-bottom: 4px;
    }

    header p {
      color: #a8a8b3;
      font-size: 0.9rem;
      margin-bottom: 20px;
    }

    /* Form Styles */
    #movieForm {
      display: flex;
      flex-direction: column;
      gap: 10px;
      margin-bottom: 20px;
    }

    input {
      width: 100%;
      padding: 12px;
      background-color: #121214;
      border: 1px solid #323238;
      border-radius: 6px;
      color: #fff;
      font-size: 0.95rem;
    }

    input:focus {
      outline: none;
      border-color: #8257e5;
    }

    #addBtn {
      padding: 12px;
      background-color: #8257e5;
      color: #fff;
      border: none;
      border-radius: 6px;
      font-weight: bold;
      cursor: pointer;
      transition: background 0.2s;
    }

    #addBtn:hover {
      background-color: #996dff;
    }

    /* Filter Bar */
    .filter-bar {
      display: flex;
      gap: 8px;
      margin-bottom: 20px;
    }

    .filter-btn {
      flex: 1;
      padding: 8px;
      background: #29292e;
      border: 1px solid #323238;
      color: #a8a8b3;
      border-radius: 6px;
      cursor: pointer;
      font-size: 0.85rem;
      text-align: center;
      text-decoration: none;
    }

    .filter-btn.active {
      background: #8257e5;
      color: #fff;
      border-color: #8257e5;
    }

    /* Movie Cards */
    .movie-list {
      list-style: none;
      display: flex;
      flex-direction: column;
      gap: 12px;
    }

    .movie-card {
      background: #29292e;
      padding: 16px;
      border-radius: 8px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      border-left: 4px solid #8257e5;
    }

    .movie-card.watched {
      border-left-color: #04d361;
    }

    .movie-info h3 {
      font-size: 1.05rem;
      margin-bottom: 4px;
    }

    .movie-info p {
      font-size: 0.85rem;
      color: #a8a8b3;
    }

    .tag {
      display: inline-block;
      padding: 2px 6px;
      background: #121214;
      border-radius: 4px;
      font-size: 0.75rem;
      margin-top: 6px;
    }

    /* Actions */
    .actions {
      display: flex;
      align-items: center;
      gap: 8px;
    }

    .action-btn {
      padding: 6px 10px;
      border: none;
      border-radius: 4px;
      cursor: pointer;
      font-size: 0.8rem;
    }

    .watch-btn {
      background: #04d361;
      color: #000;
      font-weight: bold;
    }

    .delete-btn {
      background: #f75a68;
      color: #fff;
    }
  </style>
</head>
<body>
  <div class="container">
    <header>
      <h1>🎬 Media Watchlist (PHP Version)</h1>
      <p>Track movies & TV shows you want to watch or have finished.</p>
    </header>

    <!-- Add Movie Form submitting directly to PHP -->
    <form action="index.php" method="POST" id="movieForm">
      <input type="hidden" name="action" value="add">
      <div class="form-group">
        <input type="text" name="title" placeholder="Movie / Show Title" required />
      </div>
      <div class="form-group">
        <input type="text" name="genre" placeholder="Genre (e.g. Action)" required />
      </div>
      <div class="form-group">
        <input type="number" name="year" placeholder="Release Year" required />
      </div>
      <button type="submit" id="addBtn">+ Add to Watchlist</button>
    </form>

    <!-- Filter Buttons via URL Parameters -->
    <div class="filter-bar">
      <a href="index.php?filter=All" class="filter-btn <?= $_SESSION['current_filter'] === 'All' ? 'active' : '' ?>">All</a>
      <a href="index.php?filter=Plan to Watch" class="filter-btn <?= $_SESSION['current_filter'] === 'Plan to Watch' ? 'active' : '' ?>">Plan to Watch</a>
      <a href="index.php?filter=Watched" class="filter-btn <?= $_SESSION['current_filter'] === 'Watched' ? 'active' : '' ?>">Watched</a>
    </div>

    <!-- Movie List Loop rendered by PHP -->
    <ul class="movie-list">
      <?php if (empty($filteredMovies)): ?>
        <li style="text-align: center; color: #a8a8b3; padding: 12px;">No movies found.</li>
      <?php else: ?>
        <?php foreach ($filteredMovies as $movie): ?>
          <?php $isWatched = $movie['status'] === 'Watched'; ?>
          <li class="movie-card <?= $isWatched ? 'watched' : '' ?>">
            <div class="movie-info">
              <h3><?= htmlspecialchars($movie['title']) ?> (<?= $movie['year'] ?>)</h3>
              <p><?= htmlspecialchars($movie['genre']) ?></p>
              <span class="tag">
                <?= $movie['status'] ?> <?= ($isWatched && $movie['rating'] > 0) ? "• ⭐ {$movie['rating']}/5" : '' ?>
              </span>
            </div>
            <div class="actions">
              <?php if (!$isWatched): ?>
                <form action="index.php" method="POST" style="display:inline;">
                  <input type="hidden" name="action" value="watch">
                  <input type="hidden" name="id" value="<?= $movie['id'] ?>">
                  <input type="number" name="rating" min="1" max="5" value="5" style="width: 50px; padding: 4px; display:inline;">
                  <button type="submit" class="action-btn watch-btn">✓ Watched</button>
                </form>
              <?php endif; ?>
              <a href="index.php?delete=<?= $movie['id'] ?>" class="action-btn delete-btn" style="text-decoration:none;">✕</a>
            </div>
          </li>
        <?php endforeach; ?>
      <?php endif; ?>
    </ul>
  </div>
</body>
</html>