<?php

function allowed_statuses_for_type(string $type): array {
    if ($type === 'movie') {
        return ['plan_to_watch', 'completed'];
    }
    return ['plan_to_watch', 'watching', 'completed'];
}

function validate_watchlist_input(string $title, string $type, string $status, $rating): ?string {
    if ($title === '') {
        return 'Title is required.';
    }
    if (strlen($title) > 255) {
        return 'Title is too long.';
    }
    if (!in_array($type, ['movie', 'show'], true)) {
        return 'Type must be movie or TV show.';
    }
    if (!in_array($status, allowed_statuses_for_type($type), true)) {
        return $type === 'movie'
            ? 'Movies can only be Plan to Watch or Completed (Watching is for TV shows).'
            : 'Invalid status for this type.';
    }
    if ($status === 'plan_to_watch' && $rating !== null) {
        return 'Rating is only available after you start or finish something.';
    }
    if ($rating !== null && ($rating < 1 || $rating > 10)) {
        return 'Rating must be between 1 and 10.';
    }
    return null;
}

function sanitize_poster_url(?string $url): ?string {
    if ($url === null || $url === '') {
        return null;
    }
    if (strlen($url) > 500) {
        return null;
    }
    if (!filter_var($url, FILTER_VALIDATE_URL)) {
        return null;
    }
    $parts = parse_url($url);
    if (($parts['scheme'] ?? '') !== 'https') {
        return null;
    }
    return $url;
}

function status_label(string $status): string {
    return match ($status) {
        'plan_to_watch' => 'Plan to Watch',
        'watching' => 'Watching',
        'completed' => 'Completed',
        default => $status,
    };
}
