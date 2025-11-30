<?php
// Database setup script

include 'config.php';

// Create users table
$sql_users = "CREATE TABLE IF NOT EXISTS users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

// Create scholarships table
$sql_scholarships = "CREATE TABLE IF NOT EXISTS scholarships (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL,
    provider VARCHAR(100) NOT NULL,
    education_level VARCHAR(50) NOT NULL,
    field VARCHAR(100) NOT NULL,
    amount DECIMAL(10, 2) NOT NULL,
    deadline DATE NOT NULL,
    eligibility TEXT NOT NULL,
    application_link VARCHAR(255) NOT NULL,
    scholarship_type VARCHAR(50) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

// Create bookmarks table
$sql_bookmarks = "CREATE TABLE IF NOT EXISTS bookmarks (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    scholarship_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (scholarship_id) REFERENCES scholarships(id) ON DELETE CASCADE,
    UNIQUE KEY unique_bookmark (user_id, scholarship_id)
)";

// Execute table creation
if ($conn->query($sql_users) === TRUE) {
    echo "Users table created successfully\n";
} else {
    echo "Error creating users table: " . $conn->error . "\n";
}

if ($conn->query($sql_scholarships) === TRUE) {
    echo "Scholarships table created successfully\n";
} else {
    echo "Error creating scholarships table: " . $conn->error . "\n";
}

if ($conn->query($sql_bookmarks) === TRUE) {
    echo "Bookmarks table created successfully\n";
} else {
    echo "Error creating bookmarks table: " . $conn->error . "\n";
}

// Insert sample data
$sample_scholarships = [
    [
        'title' => 'Engineering Excellence Award',
        'provider' => 'Tech Foundation',
        'education_level' => 'Bachelor',
        'field' => 'Engineering',
        'amount' => 250000,
        'deadline' => '2025-12-31',
        'eligibility' => 'GPA 3.5+, Engineering major',
        'application_link' => 'https://example.com/eng1',
        'scholarship_type' => 'Merit-based'
    ],
    [
        'title' => 'Business Leaders Fund',
        'provider' => 'Commerce Institute',
        'education_level' => 'Bachelor',
        'field' => 'Business',
        'amount' => 150000,
        'deadline' => '2025-11-30',
        'eligibility' => 'GPA 3.0+, Business major',
        'application_link' => 'https://example.com/bus1',
        'scholarship_type' => 'Merit-based'
    ],
    [
        'title' => 'Science Pioneer Scholarship',
        'provider' => 'Science Academy',
        'education_level' => 'Master',
        'field' => 'Science',
        'amount' => 375000,
        'deadline' => '2025-12-15',
        'eligibility' => 'GPA 3.7+, Science major',
        'application_link' => 'https://example.com/sci1',
        'scholarship_type' => 'Merit-based'
    ],
    [
        'title' => 'Arts & Humanities Grant',
        'provider' => 'Cultural Foundation',
        'education_level' => 'Bachelor',
        'field' => 'Arts',
        'amount' => 125000,
        'deadline' => '2025-11-20',
        'eligibility' => 'GPA 3.0+, Portfolio required',
        'application_link' => 'https://example.com/art1',
        'scholarship_type' => 'Need-based'
    ],
    [
        'title' => 'Computer Science Scholars',
        'provider' => 'Tech Pioneers',
        'education_level' => 'Bachelor',
        'field' => 'Engineering',
        'amount' => 300000,
        'deadline' => '2026-01-31',
        'eligibility' => 'GPA 3.4+, CS major',
        'application_link' => 'https://example.com/cs1',
        'scholarship_type' => 'Merit-based'
    ],
    [
        'title' => 'Graduate Engineering Fellowship',
        'provider' => 'Engineering Society',
        'education_level' => 'Master',
        'field' => 'Engineering',
        'amount' => 400000,
        'deadline' => '2026-02-28',
        'eligibility' => 'GPA 3.8+, Research proposal',
        'application_link' => 'https://example.com/eng2',
        'scholarship_type' => 'Merit-based'
    ]
];

// Check if data already exists
$check = $conn->query("SELECT COUNT(*) as count FROM scholarships");
$row = $check->fetch_assoc();

if ($row['count'] == 0) {
    foreach ($sample_scholarships as $scholarship) {
        $sql = "INSERT INTO scholarships (title, provider, education_level, field, amount, deadline, eligibility, application_link, scholarship_type) 
                VALUES (
                    '" . $conn->real_escape_string($scholarship['title']) . "',
                    '" . $conn->real_escape_string($scholarship['provider']) . "',
                    '" . $conn->real_escape_string($scholarship['education_level']) . "',
                    '" . $conn->real_escape_string($scholarship['field']) . "',
                    " . $scholarship['amount'] . ",
                    '" . $scholarship['deadline'] . "',
                    '" . $conn->real_escape_string($scholarship['eligibility']) . "',
                    '" . $conn->real_escape_string($scholarship['application_link']) . "',
                    '" . $conn->real_escape_string($scholarship['scholarship_type']) . "'
                )";
        
        if ($conn->query($sql) !== TRUE) {
            echo "Error inserting scholarship: " . $conn->error . "\n";
        }
    }
    echo "Sample scholarships inserted\n";
}

$conn->close();
echo "Database setup complete!";
?>
