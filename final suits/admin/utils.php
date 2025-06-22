<?php
/**
 * Admin utility functions for handling common operations
 */

// Ensure this file is included within the application
if (!defined('ADMIN_UTILS')) {
    define('ADMIN_UTILS', true);
}

/**
 * Handle image upload with validation and processing
 * 
 * @param string $fileInputName The name of the file input field
 * @param string $destination The destination directory
 * @param array $allowedTypes Allowed MIME types
 * @param int $maxSize Maximum file size in bytes
 * @return array ['success' => bool, 'message' => string, 'path' => string]
 */
function handleImageUpload($fileInputName, $destination = null, $allowedTypes = ['image/jpeg', 'image/png', 'image/webp'], $maxSize = 5242880) {
    if ($destination === null) {
        $destination = UPLOAD_PATH . 'products/';
    }
    
    try {
        // Check if file was uploaded
        if (!isset($_FILES[$fileInputName]) || $_FILES[$fileInputName]['error'] !== UPLOAD_ERR_OK) {
            throw new Exception('File upload failed with error code: ' . ($_FILES[$fileInputName]['error'] ?? 'No file uploaded'));
        }
        
        $file = $_FILES[$fileInputName];
        
        // Validate file size
        if ($file['size'] > $maxSize) {
            throw new Exception('File size exceeds limit of ' . ($maxSize / 1024 / 1024) . 'MB');
        }

        // Validate file type
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mimeType, $allowedTypes)) {
            throw new Exception('Invalid file type. Allowed types: ' . implode(', ', $allowedTypes));
        }

        // Create destination directory if it doesn't exist
        if (!file_exists($destination)) {
            mkdir($destination, 0755, true);
        }

        // Generate unique filename
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = uniqid('img_') . '.' . $extension;
        $filepath = $destination . $filename;

        // Move uploaded file
        if (!move_uploaded_file($file['tmp_name'], $filepath)) {
            throw new Exception('Failed to move uploaded file');
        }

        return [
            'success' => true,
            'message' => 'File uploaded successfully',
            'path' => 'uploads/products/' . $filename
        ];
    } catch (Exception $e) {
        return [
            'success' => false,
            'message' => $e->getMessage(),
            'path' => null
        ];
    }
}

/**
 * Sanitize and validate input data
 * 
 * @param mixed $input The input to sanitize
 * @param string $type The type of data (string, email, int, float, url)
 * @return mixed The sanitized input
 */
function sanitizeInput($input, $type = 'string') {
    if ($input === null || $input === '') {
        return $type === 'int' ? 0 : ($type === 'float' ? 0.0 : '');
    }
    
    $input = trim($input);
    
    switch ($type) {
        case 'email':
            $input = filter_var($input, FILTER_SANITIZE_EMAIL);
            if (!filter_var($input, FILTER_VALIDATE_EMAIL)) {
                return false;
            }
            break;
            
        case 'int':
            $input = filter_var($input, FILTER_SANITIZE_NUMBER_INT);
            $input = filter_var($input, FILTER_VALIDATE_INT);
            if ($input === false) {
                return 0;
            }
            break;
            
        case 'float':
            $input = filter_var($input, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
            $input = filter_var($input, FILTER_VALIDATE_FLOAT);
            if ($input === false) {
                return 0.0;
            }
            break;
            
        case 'url':
            $input = filter_var($input, FILTER_SANITIZE_URL);
            if (!filter_var($input, FILTER_VALIDATE_URL)) {
                return false;
            }
            break;
            
        default: // string
            $input = htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
            break;
    }
    
    return $input;
}

/**
 * Generate CSRF token
 * @return string The CSRF token
 */
function generateCSRFToken() {
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Validate CSRF token
 * @param string $token The token to validate
 * @return bool True if valid, false otherwise
 */
function validateCSRFToken($token) {
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    
    if (!isset($_SESSION['csrf_token']) || empty($token)) {
        return false;
    }
    
    return hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Log error messages to file
 * @param string $message The error message
 * @param array $context Additional context information
 * @param string $level Error level (error, warning, info)
 */
function logError($message, $context = [], $level = 'error') {
    $logDir = __DIR__ . '/../logs';
    if (!file_exists($logDir)) {
        mkdir($logDir, 0755, true);
    }
    
    $logFile = $logDir . '/admin_' . date('Y-m-d') . '.log';
    $timestamp = date('Y-m-d H:i:s');
    $contextStr = !empty($context) ? json_encode($context) : '';
    
    $logMessage = "[$timestamp] [$level] $message $contextStr\n";
    error_log($logMessage, 3, $logFile);
}

/**
 * Format currency amount
 * @param float $amount The amount to format
 * @param string $currency The currency code
 * @return string Formatted currency
 */
function formatCurrency($amount, $currency = 'DA') {
    return number_format($amount, 2, '.', ',') . ' ' . $currency;
}

/**
 * Generate pagination data
 * @param int $total Total number of items
 * @param int $perPage Items per page
 * @param int $currentPage Current page number
 * @param string $urlPattern URL pattern for links
 * @return array Pagination data
 */
function generatePagination($total, $perPage, $currentPage, $urlPattern) {
    $totalPages = ceil($total / $perPage);
    $currentPage = max(1, min($currentPage, $totalPages));
    
    $pages = [];
    $startPage = max(1, $currentPage - 2);
    $endPage = min($totalPages, $currentPage + 2);
    
    for ($i = $startPage; $i <= $endPage; $i++) {
        $pages[] = [
            'number' => $i,
            'url' => str_replace('{page}', $i, $urlPattern),
            'isCurrent' => $i === $currentPage
        ];
    }
    
    return [
        'currentPage' => $currentPage,
        'totalPages' => $totalPages,
        'pages' => $pages,
        'hasNext' => $currentPage < $totalPages,
        'hasPrev' => $currentPage > 1,
        'nextUrl' => str_replace('{page}', $currentPage + 1, $urlPattern),
        'prevUrl' => str_replace('{page}', $currentPage - 1, $urlPattern)
    ];
}

/**
 * Check if user has admin privileges
 * @return bool True if user is admin, false otherwise
 */
function checkAdminAccess() {
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    
    if (!isLoggedIn() || !isAdmin()) {
        header('Location: ../login.php');
        exit();
    }
}

/**
 * Display success or error messages
 * @param string $message The message to display
 * @param string $type The type of message (success, error, warning, info)
 */
function displayMessage($message, $type = 'info') {
    if (!empty($message)) {
        echo "<div class='alert alert-{$type}'>" . htmlspecialchars($message) . "</div>";
    }
}
?>