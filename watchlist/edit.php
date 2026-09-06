<?php
require "../includes/auth.php";
require_login();
require "../includes/csrf.php";
require "../includes/db.php";
require "../includes/watchlist.php";
require "../includes/layout.php";

$id = $_GET['id'] ?? $_POST['id'] ?? null;
if (!$id) {
    header("Location: index.php");
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM watchlist_items WHERE id = ? AND user_id = ?");
$stmt->execute([$id, $_SESSION['user_id']]);
$item = $stmt->fetch();

if (!$item) {
    http_response_code(404);
    die("Entry not found or you don't have permission to edit it.");
}

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();

    $title = trim($_POST['title'] ?? '');
    $type = $_POST['type'] ?? '';
    $status = $_POST['status'] ?? '';
    $rating = ($_POST['rating'] ?? '') !== '' ? (int)$_POST['rating'] : null;
    $notes = trim($_POST['notes'] ?? '');
    $poster = sanitize_poster_url($_POST['poster_url'] ?? '') ?? sanitize_poster_url($item['poster_url'] ?? null);
    $externalId = substr(trim($_POST['external_id'] ?? ($item['external_id'] ?? '')), 0, 64);

    if ($status === 'plan_to_watch') {
        $rating = null;
    }

    $error = validate_watchlist_input($title, $type, $status, $rating) ?? '';
    if ($error === '') {
        $stmt = $pdo->prepare(
            "UPDATE watchlist_items
             SET title = ?, type = ?, status = ?, rating = ?, notes = ?, poster_url = ?, external_id = ?
             WHERE id = ? AND user_id = ?"
        );
        $stmt->execute([
            $title,
            $type,
            $status,
            $rating,
            $notes !== '' ? $notes : null,
            $poster,
            $externalId !== '' ? $externalId : null,
            $id,
            $_SESSION['user_id'],
        ]);
        header("Location: index.php");
        exit;
    }
}

page_start('Edit Entry', APP_BASE . '/assets/form.js');
?>
    <h2>Edit Entry</h2>
    <div class="card">
        <?php if ($error): ?><p class="error"><?= htmlspecialchars($error) ?></p><?php endif; ?>
        <form method="POST">
            <?= csrf_field() ?>
            <input type="hidden" name="id" value="<?= (int)$item['id'] ?>">
            <label>Type
                <select name="type" id="type">
                    <option value="movie" <?= $item['type'] === 'movie' ? 'selected' : '' ?>>Movie</option>
                    <option value="show" <?= $item['type'] === 'show' ? 'selected' : '' ?>>TV Show</option>
                </select>
            </label>
            <label>Search catalog (IMDb for movies, TVMaze for shows)
                <input type="text" id="media-search" maxlength="100" placeholder="Search to replace title">
            </label>
            <button type="button" id="media-search-btn" class="ghost">Search external API</button>
            <div id="search-results" class="search-results"></div>

            <label>Title <input type="text" name="title" id="title" value="<?= htmlspecialchars($item['title']) ?>" required maxlength="255"></label>
            <input type="hidden" name="poster_url" id="poster_url" value="<?= htmlspecialchars($item['poster_url'] ?? '') ?>">
            <input type="hidden" name="external_id" id="external_id" value="<?= htmlspecialchars($item['external_id'] ?? '') ?>">

            <label>Status
                <select name="status" id="status" data-current="<?= htmlspecialchars($item['status']) ?>">
                    <option value="plan_to_watch">Plan to Watch</option>
                    <option value="watching">Watching</option>
                    <option value="completed">Completed</option>
                </select>
            </label>
            <div id="rating-wrap">
                <label>Rating (1-10, optional) <input type="number" name="rating" id="rating" min="1" max="10" value="<?= htmlspecialchars((string)($item['rating'] ?? '')) ?>"></label>
            </div>
            <label>Notes<br><textarea name="notes" rows="4" cols="40"><?= htmlspecialchars((string)($item['notes'] ?? '')) ?></textarea></label>
            <button type="submit">Update</button>
        </form>
        <p><a href="index.php">Back</a></p>
    </div>
<?php page_end(); ?>
