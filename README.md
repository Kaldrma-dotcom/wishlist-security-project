# wishlist-security-project

## Topic
Movie/show watchlist. Register, log in, add movies/shows you want to watch,
are watching, or finished, rate them once done, and search real titles
through a couple of free APIs instead of typing everything by hand.

## Stack
- PHP + MySQL/MariaDB
- Auth: PHP sessions (no framework)
- Apache via XAMPP, HTTPS with a self-signed cert
- Movie search: IMDb suggestion API / Show search: TVMaze API (No API keys are needed)

## Database
- `users` — id, username, password_hash, failed_attempts, locked_until, created_at
- `watchlist_items` — id, user_id (FK), title, type, status, rating, notes, poster_url, external_id, created_at 
