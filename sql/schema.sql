CREATE DATABASE IF NOT EXISTS security_project
  CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;

USE security_project;

CREATE TABLE users (
  id int(11) NOT NULL AUTO_INCREMENT,
  username varchar(50) NOT NULL,
  password_hash varchar(255) NOT NULL,
  failed_attempts int(11) NOT NULL DEFAULT 0,
  locked_until datetime DEFAULT NULL,
  created_at timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (id),
  UNIQUE KEY username (username)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE watchlist_items (
  id int(11) NOT NULL AUTO_INCREMENT,
  user_id int(11) NOT NULL,
  title varchar(255) NOT NULL,
  type enum('movie','show') NOT NULL DEFAULT 'movie',
  status enum('plan_to_watch','watching','completed') NOT NULL DEFAULT 'plan_to_watch',
  rating tinyint(4) DEFAULT NULL,
  notes text DEFAULT NULL,
  poster_url varchar(500) DEFAULT NULL,
  external_id varchar(64) DEFAULT NULL,
  created_at timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (id),
  KEY user_id (user_id),
  CONSTRAINT watchlist_items_ibfk_1 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
