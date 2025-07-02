CREATE TABLE comments(
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    article_id BIGINT,
    content TEXT NOT NULL DEFAULT '',
    author_id BIGINT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (article_id) REFERENCES articles(id),
    FOREIGN KEY (author_id) REFERENCES authors(id)
);