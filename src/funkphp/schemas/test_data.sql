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

-- Additional Insert data into the 'authors' table (IDs will likely be 6 through 15)
INSERT INTO authors (name, email, description, longer_description, age, weight, nickname) VALUES
('Frank Ocean', 'frank.o@example.com', 'Musician and soulful writer.', 'Known for his deeply personal and evocative prose, Frank explores themes of love, loss, and self-discovery.', 38, 75.1, 'Soul Scribbler'),
('Grace Hopper', 'grace.h@example.com', 'Pioneering computer scientist.', 'Grace writes about the evolution of technology and the importance of innovation in a rapidly changing world.', 80, 62.3, 'The Debugger'),
('Harry Potter', 'harry.p@example.com', 'Fantasy fiction expert.', 'Harry crafts magical stories, delving into complex narratives and weaving intricate plotlines that captivate readers.', 27, 70.5, 'The Chosen Writer'),
('Ivy Green', 'ivy.g@example.com', 'Environmental advocate and writer.', 'Ivy is passionate about sustainability, sharing insights on ecological preservation and green living practices.', 32, 60.0, 'EcoPen'),
('Jack Sparrow', 'jack.s@example.com', 'Pirate-turned-blogger.', 'Captain Jack shares swashbuckling tales and unexpected philosophical musings from his adventures on the high seas.', 55, 78.9, 'CapnBlog'),
('Karen Smith', 'karen.s@example.com', 'Customer service evangelist.', 'Karen writes practical guides on communication, conflict resolution, and building strong client relationships.', 42, 68.3, 'The Communicator'),
('Liam Neeson', 'liam.n@example.com', 'Voiceover artist and storyteller.', 'Liam delivers powerful narratives, bringing gravitas and depth to complex subjects with his commanding writing style.', 73, 90.0, 'The Narrator'),
('Mia Thermopolis', 'mia.t@example.com', 'Royal consultant and lifestyle guru.', 'Mia offers charming and relatable advice on navigating life\'s complexities with grace and humor, from a unique perspective.', 29, 57.8, 'Princess Pen'),
('Noah Wyle', 'noah.w@example.com', 'Medical drama writer.', 'Noah provides intriguing fictional medical scenarios, blending scientific accuracy with compelling human stories.', 54, 82.5, 'The Diagnostician'),
('Olivia Wilde', 'olivia.w@example.com', 'Filmmaker and feminist writer.', 'Olivia crafts insightful articles on social justice, empowering women, and the nuances of modern society.', 41, 63.2, 'The Visionary');

-- Additional Insert data into the 'articles' table (IDs will likely be 11 through 20)
-- Reference author_id from 1 to 15 (original 5 + new 10)
INSERT INTO articles (author_id, title, content, published) VALUES
(6, 'The Symphony of Silence', 'An exploration of the power of quiet moments in a noisy world.', TRUE),
(7, 'Beyond the Bug: Future of Computing', 'A forward-looking piece on advancements in computer science.', TRUE),
(8, 'The Wandering Quest', 'Chapter 1: A hero embarks on a perilous journey.', FALSE),
(9, 'Gardening for a Greener Tomorrow', 'Tips and tricks for sustainable urban gardening.', TRUE),
(10, 'A Compass for the Soul: Navigating Life''s Storms', 'Philosophical insights on finding direction amidst chaos.', TRUE),
(1, 'The Zen of PHP', 'Applying mindfulness principles to programming practices.', TRUE),
(11, 'The Art of Not Taking It Personally', 'Strategies for maintaining emotional resilience in challenging interactions.', TRUE),
(12, 'From Screen to Script: Writing for Visual Media', 'A guide to adapting storytelling for film and television.', TRUE),
(13, 'Decoding the Human Heart: A Fictional Case Study', 'A medical drama-inspired narrative about a complex patient case.', FALSE),
(14, 'The Unseen Narratives: Amplifying Marginalized Voices', 'An article advocating for greater representation and authentic storytelling.', TRUE);

-- Additional Insert data into the 'comments' table (IDs will likely be 11 through 20)
-- Reference article_id from 1 to 20 (original 10 + new 10) and author_id from 1 to 15
INSERT INTO comments (article_id, content, author_id) VALUES
(11, 'Deeply moving and beautifully written. Thank you, Frank.', 1),
(12, 'As a developer, I found this incredibly insightful and inspiring!', 8),
(13, 'Eagerly awaiting the next chapter of this epic tale!', 6),
(14, 'Fantastic tips! My balcony garden will thrive now.', 2),
(15, 'Exactly what I needed to read today. So much wisdom.', 7),
(7, 'Grace was truly a visionary. Great article!', 15),
(16, 'A must-read for anyone in customer-facing roles. Spot on!', 3),
(17, 'The pacing and character development are excellent. Impressive!', 9),
(18, 'This article brings so much awareness to important issues.', 10),
(19, 'Absolutely crucial. We need more voices like this.', 14);