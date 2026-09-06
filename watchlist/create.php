<?php
require "../includes/auth.php";
require_login();
require "../includes/csrf.php";
require "../includes/db.php";
require "../includes/watchlist.php";
require "../includes/layout.php";

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();

    $title = trim($_POST['title'] ?? '');
    $type = $_POST['type'] ?? '';
    $status = $_POST['status'] ?? '';
    $rating = ($_POST['rating'] ?? '') !== '' ? (int)$_POST['rating'] : null;
    $notes = trim($_POST['notes'] ?? '');
    $poster = sanitize_poster_url($_POST['poster_url'] ?? '');
    $externalId = substr(trim($_POST['external_id'] ?? ''), 0, 64);

    if ($status === 'plan_to_watch') {
        $rating = null;
    }

    $error = validate_watchlist_input($title, $type, $status, $rating) ?? '';
    if ($error === '') {
        $stmt = $pdo->prepare(
            "INSERT INTO watchlist_items (user_id, title, type, status, rating, notes, poster_url, external_id)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
        );
        $stmt->execute([
            $_SESSION['user_id'],
            $title,
            $type,
            $status,
            $rating,
            $notes !== '' ? $notes : null,
            $poster,
            $externalId !== '' ? $externalId : null,
        ]);
        header("Location: index.php");
        exit;
    }
}

page_start('Add to Watchlist', APP_BASE . '/assets/form.js');
?>
    <h2>Add Movie / Show</h2>
    <div class="card">
        <?php if ($error): ?><p class="error"><?= htmlspecialchars($error) ?></p><?php endif; ?>
        <form method="POST">
            <?= csrf_field() ?>
            <label>Type
                <select name="type" id="type">
                    <option value="movie">Movie</option>
                    <option value="show">TV Show</option>
                </select>
            </label>
            <label>Search catalog (IMDb for movies, TVMaze for shows)
                <input type="text" id="media-search" maxlength="100" placeholder="e.g. Inception or Breaking Bad">
            </label>
            <button type="button" id="media-search-btn" class="ghost">Search external API</button>
            <div id="search-results" class="search-results"></div>

            <label>Title <input type="text" name="title" id="title" required maxlength="255"></label>
            <input type="hidden" name="poster_url" id="poster_url">
            <input type="hidden" name="external_id" id="external_id">

            <label>Status
                <select name="status" id="status" data-current="plan_to_watch">
                    <option value="plan_to_watch">Plan to Watch</option>
                    <option value="completed">Completed</option>
                </select>
            </label>
            <div id="rating-wrap" class="hidden">
                <label>Rating (1-10, optional) <input type="number" name="rating" id="rating" min="1" max="10"></label>
            </div>
            <label>Notes<br><textarea name="notes" rows="4" cols="40"></textarea></label>
            <button type="submit">Save</button>
        </form>
        <p><a href="index.php">Back</a></p>
    </div>
<?php page_end(); ?>
