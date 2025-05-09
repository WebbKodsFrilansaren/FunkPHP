CREATE TABLE comments(
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    test_number_that_is_unsigned INT UNSIGNED NOT NULL,
    test_number_that_is_signed INT SIGNED NOT NULL,
    article_id INT,
    content TEXT NOT NULL DEFAULT '',
    author_id INT NOT NULL,
    comment_status SET('approved', 'pending', 'spam') DEFAULT 'pending',
    comment_type ENUM('text', 'image', 'video') DEFAULT 'text',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (article_id) REFERENCES articles(id),
    FOREIGN KEY (author_id) REFERENCES authors(id)
);