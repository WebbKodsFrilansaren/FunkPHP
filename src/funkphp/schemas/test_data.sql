-- Insert data into the 'authors' table
INSERT INTO authors (name, email, description, longer_description, age, weight, nickname, updated_at) VALUES
('Alice Wonderland', 'alice@example.com', 'A curious writer.', 'Alice is an imaginative individual who loves exploring new concepts and sharing her unique perspectives through her writing.', 30, 65.5, 'WonderWriter', '10:00:00'),
('Bob The Builder', 'bob@example.com', 'Builds things, writes too.', 'Bob is a practical and hands-on person, known for his clear, concise, and actionable articles on various technical topics.', 45, 80.2, NULL, '11:30:00'),
('Charlie Chaplin', 'charlie@example.com', 'Silent film enthusiast.', 'Charlie enjoys expressing complex ideas with minimal words, often relying on visual storytelling and subtle humor in his content.', 60, 72.0, 'The Tramp', '12:45:00'),
('Diana Prince', 'diana@example.com', 'Warrior and writer.', 'Diana focuses on articles about strength, resilience, and empowerment, drawing from her vast experiences and wisdom.', 35, 68.0, 'Amazonian', '14:00:00'),
('Eve Harrington', 'eve@example.com', 'Ambitious and sharp.', 'Eve writes compelling narratives, often with a dramatic flair, exploring themes of ambition and human nature.', 28, 58.7, NULL, '15:15:00');

-- Insert data into the 'articles' table
-- Note: author_id must exist in the 'authors' table
INSERT INTO articles (author_id, title, content, published) VALUES
(1, 'The Secret Garden of Ideas', 'A deep dive into creative thinking and nurturing new concepts.', TRUE),
(1, 'Through the Looking Glass of Code', 'An introductory guide to understanding complex programming paradigms.', TRUE),
(2, 'Building Blocks of Web Development', 'A step-by-step tutorial on constructing robust web applications.', TRUE),
(3, 'The Art of Visual Storytelling', 'Exploring the nuances of conveying messages without words.', FALSE),
(4, 'Unleashing Your Inner Strength', 'A motivational piece on personal growth and resilience.', TRUE),
(5, 'The Rise and Fall of Ambition', 'A fictional short story about the double-edged sword of ambition.', TRUE),
(1, 'A Brief History of Time', 'A theoretical physics article.', TRUE),
(2, 'How to Fix Anything', 'Practical guide to home repairs.', TRUE),
(4, 'Empowering Women in Tech', 'A call to action for gender equality in the technology sector.', TRUE),
(5, 'Shadows of Deception', 'A thrilling mystery novel excerpt.', FALSE);

-- Insert data into the 'comments' table
-- Note: article_id must exist in 'articles' or be NULL (if column allows), and author_id must exist in 'authors'
INSERT INTO comments (article_id, content, author_id) VALUES
(1, 'Absolutely captivating! Loved the insights.', 2),
(1, 'Very thought-provoking. Made me reconsider my approach.', 3),
(2, 'Clear and concise explanation. Perfect for beginners!', 1),
(NULL, 'This comment is not linked to any specific article, but still has an author.', 4), -- Example of a NULL article_id
(3, 'Solid advice, as always. Thanks, Bob!', 1),
(4, 'The imagery in this piece is stunning, even without words.', 5),
(5, 'Truly inspiring! Needed this today.', 3),
(6, 'A gripping read. Eager for the next chapter!', 2),
(1, 'I completely agree with this perspective.', 4),
(2, 'Excellent breakdown of the topic.', 5),
(3, 'Very helpful, thank you!', 4),
(5, 'Powerful message!', 1);