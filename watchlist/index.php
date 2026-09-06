<?php
require "../includes/auth.php";
require_login();
require "../includes/csrf.php";
require "../includes/db.php";
require "../includes/watchlist.php";
require "../includes/layout.php";

$stmt = $pdo->prepare("SELECT * FROM watchlist_items WHERE user_id = ? ORDER BY status, created_at DESC");
$stmt->execute([$_SESSION['user_id']]);
$items = $stmt->fetchAll();

page_start('My Watchlist', APP_BASE . '/assets/confirm.js');
?>
    <h2>Welcome, <?= htmlspecialchars($_SESSION['username']) ?></h2>
    <p class="nav"><a href="../profile.php">My Profile</a> | <a href="../logout.php">Logout</a></p>
    <p><a class="btn" href="create.php">+ Add Movie/Show</a></p>

    <div class="card">
    <table>
        <tr>
            <th></th><th>Title</th><th>Type</th><th>Status</th><th>Rating</th><th>Notes</th><th>Actions</th>
        </tr>
    <?php foreach ($items as $item): ?>
        <tr>
            <td>
                <?php if (!empty($item['poster_url']) && sanitize_poster_url($item['poster_url'])): ?>
                    <img class="poster" alt="" src="<?= htmlspecialchars($item['poster_url']) ?>">
                <?php endif; ?>
            </td>
            <td><?= htmlspecialchars($item['title']) ?></td>
            <td><?= $item['type'] === 'movie' ? 'Movie' : 'TV Show' ?></td>
            <td><?= htmlspecialchars(status_label($item['status'])) ?></td>
            <td><?= $item['rating'] ? htmlspecialchars((string)$item['rating']) . '/10' : '—' ?></td>
            <td><?= htmlspecialchars(substr($item['notes'] ?? '', 0, 40)) ?></td>
            <td>
                <a href="edit.php?id=<?= (int)$item['id'] ?>">Edit</a>
                &nbsp;|&nbsp;
                <form method="POST" action="delete.php" class="inline-form" data-confirm="Delete this entry?">
                    <input type="hidden" name="id" value="<?= (int)$item['id'] ?>">
                    <?= csrf_field() ?>
                    <button type="submit" class="ghost">Delete</button>
                </form>
            </td>
        </tr>
    <?php endforeach; ?>
    <?php if (empty($items)): ?>
        <tr><td colspan="7">Your watchlist is empty. Search a title from an external API when adding an entry.</td></tr>
    <?php endif; ?>
    </table>
    </div>
<?php page_end(); ?>
