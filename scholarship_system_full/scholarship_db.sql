CREATE DATABASE IF NOT EXISTS scholarship_db;
USE scholarship_db;

-- USERS TABLE
CREATE TABLE IF NOT EXISTS users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin','student') DEFAULT 'student',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- STUDENTS
CREATE TABLE IF NOT EXISTS students (
    student_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    first_name VARCHAR(50),
    last_name VARCHAR(50),
    birthdate DATE,
    gender ENUM('Male','Female','Other'),
    address TEXT,
    contact_number VARCHAR(20),
    email VARCHAR(100),
    school_name VARCHAR(100),
    course VARCHAR(100),
    year_level VARCHAR(20),
    gpa DECIMAL(3,2),
    family_income DECIMAL(10,2),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);

-- SCHOLARSHIPS
CREATE TABLE IF NOT EXISTS scholarships (
    scholarship_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100),
    description TEXT,
    requirements TEXT,
    sponsor VARCHAR(100),
    start_date DATE,
    end_date DATE,
    amount DECIMAL(10,2),
    status ENUM('Open','Closed') DEFAULT 'Open'
);

-- APPLICATIONS
CREATE TABLE IF NOT EXISTS applications (
    application_id INT AUTO_INCREMENT PRIMARY KEY,
    scholarship_id INT,
    student_id INT,
    date_applied DATETIME DEFAULT CURRENT_TIMESTAMP,
    documents TEXT,
    status ENUM('Pending','Approved','Rejected','For Interview') DEFAULT 'Pending',
    remarks TEXT,
    FOREIGN KEY (scholarship_id) REFERENCES scholarships(scholarship_id) ON DELETE CASCADE,
    FOREIGN KEY (student_id) REFERENCES students(student_id) ON DELETE CASCADE
);

-- EVALUATIONS
CREATE TABLE IF NOT EXISTS evaluations (
    evaluation_id INT AUTO_INCREMENT PRIMARY KEY,
    application_id INT,
    evaluator_id INT,
    score_academic DECIMAL(5,2),
    score_income DECIMAL(5,2),
    score_overall DECIMAL(5,2),
    comments TEXT,
    date_evaluated DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (application_id) REFERENCES applications(application_id),
    FOREIGN KEY (evaluator_id) REFERENCES users(user_id)
);

-- ANNOUNCEMENTS
CREATE TABLE IF NOT EXISTS announcements (
    announcement_id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(100),
    content TEXT,
    created_by INT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(user_id)
);

-- default admin (username: admin, password: admin123)
INSERT INTO users (username, password, role) VALUES ('admin', MD5('admin123'), 'admin');
