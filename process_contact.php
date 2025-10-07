<?php
// Enable error reporting for debugging (remove in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database configuration
$dbHost     = 'localhost';
$dbUsername = 'root';
$dbPassword = '';
$dbName     = 'ai_solutions';

// Create database connection
$conn = new mysqli($dbHost, $dbUsername, $dbPassword, $dbName);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Validate and sanitize inputs
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Check if form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate required fields
    $required = ['name', 'email', 'phone', 'company', 'country', 'job_title', 'job_details'];
    $errors = [];
    
    foreach ($required as $field) {
        if (empty($_POST[$field])) {
            $errors[] = "$field is required";
        }
    }
    
    // Validate email format
    if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    }
    
    // If no errors, process the form
    if (empty($errors)) {
        // Sanitize inputs
        $name = sanitizeInput($_POST['name']);
        $email = sanitizeInput($_POST['email']);
        $phone = sanitizeInput($_POST['phone']); // FIXED
        $company = sanitizeInput($_POST['company']);
        $country = sanitizeInput($_POST['country']);
        $job_title = sanitizeInput($_POST['job_title']);
        $job_details = sanitizeInput($_POST['job_details']);
        
        // Prepare and bind
        $stmt = $conn->prepare("INSERT INTO contact_submissions (name, email, phone, company, country, job_title, job_details) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssss", $name, $email, $phone, $company, $country, $job_title, $job_details);
        
        // Execute the statement
        if ($stmt->execute()) {
            // Success - redirect back to contact page with success message
            header("Location: contact.html?status=success");
            exit();
        } else {
            // Log the error for debugging
            error_log("Database error: " . $stmt->error);
            $errors[] = "Database error: " . $stmt->error;
        }
        
        $stmt->close();
    }
    
    // If we got here, there were errors
    header("Location: contact.html?status=error&message=" . urlencode(implode(", ", $errors)));
    exit();
}

$conn->close();
?>
