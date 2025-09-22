<?php
// Contact form handler
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Configuration
$config = [
    'to_email' => 'keithchristopherpunla.com', 
    'from_name' => 'Portfolio Contact Form',
    'subject_prefix' => '[Portfolio] New Contact Form Submission',
    'use_database' => true, // Set to false if you don't want to store in database
];

// Response function
function sendResponse($success, $message, $data = null) {
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data' => $data
    ]);
    exit;
}

// Check if request is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendResponse(false, 'Only POST requests are allowed');
}

// Get and validate form data
$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$message = trim($_POST['message'] ?? '');

// Validation
if (empty($name) || empty($email) || empty($message)) {
    sendResponse(false, 'All fields are required');
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    sendResponse(false, 'Invalid email address');
}

if (strlen($name) > 100) {
    sendResponse(false, 'Name is too long');
}

if (strlen($message) > 2000) {
    sendResponse(false, 'Message is too long');
}

// Sanitize data
$name = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
$email = htmlspecialchars($email, ENT_QUOTES, 'UTF-8');
$message = htmlspecialchars($message, ENT_QUOTES, 'UTF-8');

// Store in database if enabled
if ($config['use_database']) {
    try {
        $pdo = getDatabaseConnection();
        storeContactSubmission($pdo, $name, $email, $message);
    } catch (Exception $e) {
        error_log('Database error: ' . $e->getMessage());
        // Continue with email sending even if database fails
    }
}

// Send email
$emailSent = sendContactEmail($config, $name, $email, $message);

if ($emailSent) {
    sendResponse(true, 'Thank you for your message! I\'ll get back to you soon.');
} else {
    sendResponse(false, 'Failed to send message. Please try again later.');
}

// Database connection function
function getDatabaseConnection() {
    // Database configuration - Update these with your database details
    $host = 'localhost';
    $dbname = 'portfolio_db';
    $username = 'your_db_username';
    $password = 'your_db_password';
    
    $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";
    
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];
    
    return new PDO($dsn, $username, $password, $options);
}

// Store contact submission in database
function storeContactSubmission($pdo, $name, $email, $message) {
    $sql = "INSERT INTO contact_submissions (name, email, message, submitted_at, ip_address) 
            VALUES (?, ?, ?, NOW(), ?)";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        $name,
        $email,
        $message,
        $_SERVER['REMOTE_ADDR'] ?? 'unknown'
    ]);
}

// Send email function
function sendContactEmail($config, $name, $email, $message) {
    $to = $config['to_email'];
    $subject = $config['subject_prefix'] . ' from ' . $name;
    
    // Email body
    $body = "
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: #004aad; color: white; padding: 20px; border-radius: 5px 5px 0 0; }
            .content { background: #f9f9f9; padding: 20px; border-radius: 0 0 5px 5px; }
            .field { margin-bottom: 15px; }
            .label { font-weight: bold; color: #004aad; }
            .value { margin-top: 5px; padding: 10px; background: white; border-radius: 3px; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h2>New Contact Form Submission</h2>
            </div>
            <div class='content'>
                <div class='field'>
                    <div class='label'>Name:</div>
                    <div class='value'>$name</div>
                </div>
                <div class='field'>
                    <div class='label'>Email:</div>
                    <div class='value'>$email</div>
                </div>
                <div class='field'>
                    <div class='label'>Message:</div>
                    <div class='value'>" . nl2br($message) . "</div>
                </div>
                <div class='field'>
                    <div class='label'>Submitted:</div>
                    <div class='value'>" . date('Y-m-d H:i:s') . "</div>
                </div>
            </div>
        </div>
    </body>
    </html>
    ";
    
    // Email headers
    $headers = [
        'MIME-Version: 1.0',
        'Content-type: text/html; charset=UTF-8',
        'From: ' . $config['from_name'] . ' <noreply@yourdomain.com>',
        'Reply-To: ' . $name . ' <' . $email . '>',
        'X-Mailer: PHP/' . phpversion()
    ];
    
    return mail($to, $subject, $body, implode("\r\n", $headers));
}
?>