CREATE TABLE comments(
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    article_id INT,
    content TEXT NOT NULL '',
    author_id INT NOT NULL,
    comment_status SET('approved', 'pending', 'spam') DEFAULT 'pending',
    comment_type ENUM('text', 'image', 'video') DEFAULT 'text',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (article_id) REFERENCES articles(id),
    FOREIGN KEY (author_id) REFERENCES authors(id)
);