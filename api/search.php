<?php
require "../includes/auth.php";
require_login();
require "../includes/http.php";

header('Content-Type: application/json; charset=utf-8');

function is_https_url($url): bool {
    if (!is_string($url) || $url === '') {
        return false;
    }
    $parts = parse_url($url);
    return ($parts['scheme'] ?? '') === 'https';
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$now = time();
$_SESSION['search_window_start'] = $_SESSION['search_window_start'] ?? $now;
$_SESSION['search_count'] = $_SESSION['search_count'] ?? 0;
if ($now - $_SESSION['search_window_start'] > 60) {
    $_SESSION['search_window_start'] = $now;
    $_SESSION['search_count'] = 0;
}
if ($_SESSION['search_count'] >= 20) {
    http_response_code(429);
    echo json_encode(['error' => 'Too many searches. Wait a moment and try again.']);
    exit;
}
$_SESSION['search_count']++;

$query = trim($_GET['q'] ?? '');
$type = $_GET['type'] ?? 'movie';

if (strlen($query) < 2 || strlen($query) > 100) {
    echo json_encode(['results' => []]);
    exit;
}
if (!in_array($type, ['movie', 'show'], true)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid type']);
    exit;
}

$results = [];

if ($type === 'movie') {
    $first = strtolower(substr($query, 0, 1));
    if (!preg_match('/^[a-z0-9]$/', $first)) {
        $first = 'x';
    }
    $url = 'https://v3.sg.media-imdb.com/suggestion/'
        . rawurlencode($first) . '/'
        . rawurlencode(strtolower($query)) . '.json';
    $data = http_get_json($url);
    $seen = [];
    foreach (($data['d'] ?? []) as $item) {
        if (($item['qid'] ?? '') !== 'movie') {
            continue;
        }
        $title = trim((string)($item['l'] ?? ''));
        if ($title === '') {
            continue;
        }
        $year = isset($item['y']) ? (string)$item['y'] : '';
        $dedupe = strtolower($title) . '|' . $year;
        if (isset($seen[$dedupe])) {
            continue;
        }
        $seen[$dedupe] = true;
        $artwork = $item['i']['imageUrl'] ?? '';
        if (!is_https_url($artwork)) {
            $artwork = '';
        }
        $results[] = [
            'title' => substr($title, 0, 255),
            'year' => $year,
            'poster' => $artwork,
            'external_id' => isset($item['id']) ? 'imdb:' . $item['id'] : null,
        ];
        if (count($results) >= 8) {
            break;
        }
    }
} else {
    $url = 'https://api.tvmaze.com/search/shows?' . http_build_query(['q' => $query]);
    $data = http_get_json($url);
    if (is_array($data)) {
        foreach (array_slice($data, 0, 8) as $item) {
            $show = $item['show'] ?? [];
            $title = trim((string)($show['name'] ?? ''));
            if ($title === '') {
                continue;
            }
            $poster = $show['image']['medium'] ?? $show['image']['original'] ?? '';
            if (!is_https_url($poster)) {
                $poster = '';
            }
            $premiered = (string)($show['premiered'] ?? '');
            $results[] = [
                'title' => substr($title, 0, 255),
                'year' => $premiered !== '' ? substr($premiered, 0, 4) : '',
                'poster' => $poster,
                'external_id' => isset($show['id']) ? 'tvmaze:' . $show['id'] : null,
            ];
        }
    }
}

echo json_encode(['results' => $results]);
