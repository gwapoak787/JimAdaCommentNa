<?php
/**
 * API Controller
 * Handles all API endpoints for AJAX requests
 */

require_once CONFIG_PATH . '/config.php';
require_once SRC_PATH . '/services/AuthService.php';
require_once SRC_PATH . '/services/ScholarshipService.php';

class ApiController {
    private $authService;
    private $scholarshipService;

    public function __construct() {
        $this->authService = new AuthService();
        $this->scholarshipService = new ScholarshipService();
    }

    /**
     * Handles API requests based on action parameter.
     */
    public function handleRequest() {
        header('Content-Type: application/json');

        $action = $_GET['action'] ?? $_POST['action'] ?? '';

        try {
            switch ($action) {
                case 'get_all':
                    $this->getAllScholarships();
                    break;

                case 'get_filters':
                    $this->getFilters();
                    break;

                case 'search':
                    $this->searchScholarships();
                    break;

                case 'sort_deadline':
                    $this->sortByDeadline();
                    break;

                case 'sort_amount':
                    $this->sortByAmount();
                    break;

                case 'add_bookmark':
                    $this->addBookmark();
                    break;

                case 'remove_bookmark':
                    $this->removeBookmark();
                    break;

                case 'is_bookmarked':
                    $this->isBookmarked();
                    break;

                case 'get_bookmarks':
                    $this->getBookmarks();
                    break;

                default:
                    $this->sendError('Invalid action');
            }
        } catch (Exception $e) {
            $this->sendError($e->getMessage());
        }
    }

    private function getAllScholarships() {
        $scholarships = $this->scholarshipService->getAllScholarships();
        $this->sendSuccess($scholarships);
    }

    private function getFilters() {
        $filters = $this->scholarshipService->getFilterOptions();
        $this->sendSuccess($filters);
    }

    private function searchScholarships() {
        $criteria = [
            'level' => $_GET['level'] ?? '',
            'field' => $_GET['field'] ?? '',
            'deadline' => $_GET['deadline'] ?? '',
            'search_term' => $_GET['search'] ?? ''
        ];

        $results = $this->scholarshipService->searchScholarships($criteria);
        $this->sendSuccess($results);
    }

    private function sortByDeadline() {
        $scholarships = $this->scholarshipService->getAllScholarships('id'); // Get unsorted
        $sorted = $this->scholarshipService->sortByDeadline($scholarships);
        $this->sendSuccess($sorted);
    }

    private function sortByAmount() {
        $scholarships = $this->scholarshipService->getAllScholarships('id'); // Get unsorted
        $sorted = $this->scholarshipService->sortByAmount($scholarships);
        $this->sendSuccess($sorted);
    }

    private function addBookmark() {
        if (!$this->authService->isLoggedIn()) {
            $this->sendError('Please log in to bookmark scholarships');
            return;
        }

        $userId = $this->authService->getCurrentUserId();
        $scholarshipId = $_POST['scholarship_id'] ?? 0;

        if (!$scholarshipId) {
            $this->sendError('Scholarship ID required');
            return;
        }

        // Check if already bookmarked
        $existing = Database::getInstance()->select(
            "SELECT id FROM bookmarks WHERE user_id = ? AND scholarship_id = ?",
            [$userId, $scholarshipId]
        );

        if ($existing) {
            $this->sendError('Already bookmarked');
            return;
        }

        $result = Database::getInstance()->insert(
            "INSERT INTO bookmarks (user_id, scholarship_id) VALUES (?, ?)",
            [$userId, $scholarshipId]
        );

        if ($result) {
            $this->sendSuccess(['message' => 'Bookmark added']);
        } else {
            $this->sendError('Failed to add bookmark');
        }
    }

    private function removeBookmark() {
        if (!$this->authService->isLoggedIn()) {
            $this->sendError('Please log in to manage bookmarks');
            return;
        }

        $userId = $this->authService->getCurrentUserId();
        $scholarshipId = $_POST['scholarship_id'] ?? 0;

        $result = Database::getInstance()->delete(
            "DELETE FROM bookmarks WHERE user_id = ? AND scholarship_id = ?",
            [$userId, $scholarshipId]
        );

        if ($result) {
            $this->sendSuccess(['message' => 'Bookmark removed']);
        } else {
            $this->sendError('Failed to remove bookmark');
        }
    }

    private function isBookmarked() {
        if (!$this->authService->isLoggedIn()) {
            $this->sendSuccess(['is_bookmarked' => false]);
            return;
        }

        $userId = $this->authService->getCurrentUserId();
        $scholarshipId = $_GET['scholarship_id'] ?? 0;

        $result = Database::getInstance()->select(
            "SELECT id FROM bookmarks WHERE user_id = ? AND scholarship_id = ?",
            [$userId, $scholarshipId]
        );

        $this->sendSuccess(['is_bookmarked' => !empty($result)]);
    }

    private function getBookmarks() {
        if (!$this->authService->isLoggedIn()) {
            $this->sendError('Please log in to view bookmarks');
            return;
        }

        $userId = $this->authService->getCurrentUserId();

        $bookmarks = Database::getInstance()->select("
            SELECT s.* FROM scholarships s
            INNER JOIN bookmarks b ON s.id = b.scholarship_id
            WHERE b.user_id = ?
            ORDER BY s.deadline ASC
        ", [$userId]);

        $this->sendSuccess($bookmarks);
    }

    private function sendSuccess($data) {
        echo json_encode(['success' => true, 'data' => $data]);
    }

    private function sendError($message) {
        echo json_encode(['success' => false, 'message' => $message]);
    }
}
?>