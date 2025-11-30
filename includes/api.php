<?php
// Scholarship API endpoints

include 'config.php';
include 'auth.php';
include 'scholarship_utils.php';

header('Content-Type: application/json');

$action = $_GET['action'] ?? '';

// Get all scholarships
if ($action === 'get_all') {
    $sql = "SELECT * FROM scholarships WHERE deadline >= CURDATE()";
    $result = $conn->query($sql);
    $scholarships = [];

    while ($row = $result->fetch_assoc()) {
        $scholarships[] = $row;
    }

    echo json_encode(['success' => true, 'data' => $scholarships]);
}

// Search scholarships
else if ($action === 'search') {
    $level = $_GET['level'] ?? '';
    $field = $_GET['field'] ?? '';
    $deadline = $_GET['deadline'] ?? '';

    $sql = "SELECT * FROM scholarships WHERE deadline >= CURDATE()";

    if (!empty($level)) {
        $level = $conn->real_escape_string($level);
        $sql .= " AND education_level = '$level'";
    }

    if (!empty($field)) {
        $field = $conn->real_escape_string($field);
        $sql .= " AND field = '$field'";
    }

    if (!empty($deadline)) {
        $deadline = $conn->real_escape_string($deadline);
        $sql .= " AND deadline <= '$deadline'";
    }

    $result = $conn->query($sql);
    $scholarships = [];

    while ($row = $result->fetch_assoc()) {
        $scholarships[] = $row;
    }

    echo json_encode(['success' => true, 'data' => $scholarships]);
}

// Sort by deadline
else if ($action === 'sort_deadline') {
    $sql = "SELECT * FROM scholarships WHERE deadline >= CURDATE()";
    $result = $conn->query($sql);
    $scholarships = [];

    while ($row = $result->fetch_assoc()) {
        $scholarships[] = $row;
    }

    $sorter = new ScholarshipSorter();
    $scholarships = $sorter->sortByDeadline($scholarships);

    echo json_encode(['success' => true, 'data' => $scholarships]);
}

// Sort by amount
else if ($action === 'sort_amount') {
    $sql = "SELECT * FROM scholarships WHERE deadline >= CURDATE()";
    $result = $conn->query($sql);
    $scholarships = [];

    while ($row = $result->fetch_assoc()) {
        $scholarships[] = $row;
    }

    $sorter = new ScholarshipSorter();
    $scholarships = $sorter->sortByAmount($scholarships);

    echo json_encode(['success' => true, 'data' => $scholarships]);
}

// Add bookmark
else if ($action === 'add_bookmark') {
    if (!isLoggedIn()) {
        echo json_encode(['success' => false, 'message' => 'Not logged in']);
        exit;
    }
    
    $user_id = getCurrentUserId();
    $scholarship_id = $_POST['scholarship_id'] ?? '';
    
    if (empty($scholarship_id)) {
        echo json_encode(['success' => false, 'message' => 'Scholarship ID required']);
        exit;
    }
    
    $sql = "INSERT INTO bookmarks (user_id, scholarship_id) VALUES (?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $user_id, $scholarship_id);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Bookmarked successfully']);
    } else {
        if ($stmt->errno === 1062) {
            echo json_encode(['success' => false, 'message' => 'Already bookmarked']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error adding bookmark']);
        }
    }
}

// Remove bookmark
else if ($action === 'remove_bookmark') {
    if (!isLoggedIn()) {
        echo json_encode(['success' => false, 'message' => 'Not logged in']);
        exit;
    }
    
    $user_id = getCurrentUserId();
    $scholarship_id = $_POST['scholarship_id'] ?? '';
    
    if (empty($scholarship_id)) {
        echo json_encode(['success' => false, 'message' => 'Scholarship ID required']);
        exit;
    }
    
    $sql = "DELETE FROM bookmarks WHERE user_id = ? AND scholarship_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $user_id, $scholarship_id);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Bookmark removed']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error removing bookmark']);
    }
}

// Get user bookmarks
else if ($action === 'get_bookmarks') {
    if (!isLoggedIn()) {
        echo json_encode(['success' => false, 'message' => 'Not logged in']);
        exit;
    }

    $user_id = getCurrentUserId();

    $sql = "SELECT s.* FROM scholarships s
            INNER JOIN bookmarks b ON s.id = b.scholarship_id
            WHERE b.user_id = ? AND s.deadline >= CURDATE()
            ORDER BY b.created_at DESC";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $scholarships = [];
    while ($row = $result->fetch_assoc()) {
        $scholarships[] = $row;
    }

    echo json_encode(['success' => true, 'data' => $scholarships]);
}

// Check if bookmarked
else if ($action === 'is_bookmarked') {
    if (!isLoggedIn()) {
        echo json_encode(['success' => false, 'is_bookmarked' => false]);
        exit;
    }
    
    $user_id = getCurrentUserId();
    $scholarship_id = $_GET['scholarship_id'] ?? '';
    
    if (empty($scholarship_id)) {
        echo json_encode(['success' => false, 'is_bookmarked' => false]);
        exit;
    }
    
    $sql = "SELECT id FROM bookmarks WHERE user_id = ? AND scholarship_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $user_id, $scholarship_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $is_bookmarked = $result->num_rows > 0;
    
    echo json_encode(['success' => true, 'is_bookmarked' => $is_bookmarked]);
}

// Get filter options (levels and fields)
else if ($action === 'get_filters') {
    // Get all unique levels for active scholarships
    $sql = "SELECT DISTINCT education_level FROM scholarships WHERE deadline >= CURDATE() ORDER BY education_level";
    $result = $conn->query($sql);
    $levels = [];

    while ($row = $result->fetch_assoc()) {
        $levels[] = $row['education_level'];
    }

    // Get all unique fields for active scholarships
    $sql = "SELECT DISTINCT field FROM scholarships WHERE deadline >= CURDATE() ORDER BY field";
    $result = $conn->query($sql);
    $fields = [];

    while ($row = $result->fetch_assoc()) {
        $fields[] = $row['field'];
    }

    echo json_encode([
        'success' => true,
        'levels' => $levels,
        'fields' => $fields
    ]);
}

else {
    echo json_encode(['success' => false, 'message' => 'Invalid action']);
}

$conn->close();
?>
