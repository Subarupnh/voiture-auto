-- Create the `categories` table (for brands)
CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL UNIQUE
);

-- Create the `models` table (for models)
CREATE TABLE IF NOT EXISTS models (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    category_id INT NOT NULL,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
);

-- Create the `animes` table (if it doesn't exist)
CREATE TABLE IF NOT EXISTS animes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    description TEXT NOT NULL,
    image LONGBLOB NOT NULL,
    category_id INT NOT NULL,
    model_id INT NOT NULL,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE,
    FOREIGN KEY (model_id) REFERENCES models(id) ON DELETE CASCADE
);

-- Create the `users` table (for authentication)
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('user', 'admin') DEFAULT 'user',
    active BOOLEAN DEFAULT TRUE
);

-- Insert default data for testing
INSERT INTO categories (name) VALUES ('Toyota'), ('Honda'), ('Ford');
INSERT INTO models (name, category_id) VALUES 
    ('Corolla', 1), ('Civic', 2), ('Focus', 3);
-- Insert sample data into the `animes` table
INSERT INTO animes (description, image, category_id, model_id) VALUES 
    ('Sample Anime Description', 0x89504E470D0A1A0A, 1, 1); -- Placeholder image data
INSERT INTO users (username, password, role) VALUES 
    ('admin', '$2y$10$eImiTXuWVxfM37uY4JANjQ==', 'admin'), -- Password: "admin"
    ('user', '$2y$10$eImiTXuWVxfM37uY4JANjQ==', 'user'),   -- Password: "user"
    ('zkry', '$2y$10$J9Qk5FZ8h5F9Z8k5FZ8h5uZ8k5FZ8h5FZ8h5FZ8h5FZ8h5FZ8h5FZ8h5', 'admin'); -- Password: "Zakarya999$"
INSERT INTO users (username, password, role) VALUES 
    ('zkry', '$2y$10$9Qk5FZ8h5F9Z8k5FZ8h5uZ8k5FZ8h5FZ8h5FZ8h5FZ8h5FZ8h5FZ8h5', 'admin'); -- Password: "Zakarya999$"
