CREATE TABLE comments(
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    article_id INT DEFAULT (CURRENT_DATE + INTERVAL 1 YEAR) NOT NULL UNIQUE,
    content TEXT NOT NULL "UNIQUE" DEFAULT (RAND() * RAND()),
    author_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT NOW(),
    FOREIGN KEY (article_id) REFERENCES articles(id),
    FOREIGN KEY (author_id) REFERENCES authors(id)
);