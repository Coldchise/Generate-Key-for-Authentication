<?php
session_start();

if (isset($_SESSION['valid_key'])) {
    header("Location: login.php");
    exit();
}

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "keytest";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

function isValidKey($conn, $key) {
    // Check if the key exists, not expired, and not used
    $stmt = $conn->prepare("SELECT * FROM access_keys WHERE key_value = ? AND expiration_date > NOW()");
    $stmt->bind_param('s', $key);
    $stmt->execute();
    $result = $stmt->get_result();
    if (!$result->fetch_assoc()) return false;

    // Check if the key has been used
    $stmt = $conn->prepare("SELECT * FROM used_keys WHERE key_value = ?");
    $stmt->bind_param('s', $key);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc() === null;
}

function markKeyAsUsed($conn, $key) {
    $stmt = $conn->prepare("INSERT INTO used_keys (key_value) VALUES (?)");
    $stmt->bind_param('s', $key);
    $stmt->execute();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $key = $_POST['key'];

    if (isValidKey($conn, $key)) {
        markKeyAsUsed($conn, $key);
        setcookie('valid_key', $key, time() + (30 * 24 * 60 * 60), '/');
        $_SESSION['valid_key'] = $key;
        header("Location: login.php");
        exit();
    } else {
        $error = 'Invalid, expired, or already used key!';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Client - Enter Access Key</title>
    <link rel="stylesheet" href="client.css">
</head>
<body>
    <div class="container">
        <div class="logo">
            <img src="logo.png" alt="Your Logo" class="logo-img">
        </div>

        <h1>Enter Access Key</h1>
        <form method="POST">
            <input type="text" id="key" name="key" placeholder="Enter your access key" required>
            <button type="submit" class="submit-button">Submit</button>
        </form>
            <!-- If the user has entered a valid key -->
            <a href="login.php">
                <button class="submit-button">Done Entering The Key? Click Here</button>
            </a>
        <p class="info-text">Please enter the key provided to you by the admin. The key will be valid for 30 days.</p>
    </div>
</body>
</html>