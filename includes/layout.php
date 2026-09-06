<?php
function page_start(string $title, ?string $extraScript = null): void {
    $css = APP_BASE . '/assets/style.css';
    ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($title) ?></title>
    <link rel="stylesheet" href="<?= htmlspecialchars($css) ?>">
    <?php if ($extraScript): ?>
    <script src="<?= htmlspecialchars($extraScript) ?>" defer></script>
    <?php endif; ?>
</head>
<body>
<div class="wrap">
    <?php
}

function page_end(): void {
    echo "</div>\n</body>\n</html>\n";
}
