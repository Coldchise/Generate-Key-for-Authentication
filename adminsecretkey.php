<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "keytest"; // Database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Function to generate a random key
function generateRandomKey($length = 6, $withLetters = false): string
{
    $chars = $withLetters ? str_shuffle('0W1E2R3T4Y5U6I7O8P9A0S9D8F7G6H5J4K3L2M1NBVCXZ') : str_shuffle('3759402687094152031368921');
    $chars = str_shuffle(str_repeat($chars, ceil($length / strlen($chars)))); 
    $randomPart = strrev(str_shuffle(substr($chars, mt_rand(0, (strlen($chars) - $length - 1)), $length)));
    return 'engine_' . $randomPart;
}

// Function to insert generated key into the database
function insertKey($conn, $key) {
    // Calculate expiration date (30 days from now)
    $expiration_date = date('Y-m-d H:i:s', strtotime('+30 days'));

    // Insert the key into the access_keys table without marking it as used
    $stmt = $conn->prepare("INSERT INTO access_keys (key_value, expiration_date) VALUES (?, ?)");
    $stmt->bind_param('ss', $key, $expiration_date);
    $stmt->execute();
    $stmt->close();
}

// Function to delete key
function deleteKey($conn, $key) {
    $stmt = $conn->prepare("DELETE FROM access_keys WHERE key_value = ?");
    $stmt->bind_param('s', $key);
    $stmt->execute();
    $stmt->close();
}

// Form for key generation
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // If the button clicked is Generate Key
    if (isset($_POST['generate_key'])) {
        $key = generateRandomKey(6, true); // Generate random key
        insertKey($conn, $key); // Insert the key into the database
    }
    // If the button clicked is Delete Key
    if (isset($_POST['delete_key'])) {
        $key_to_delete = $_POST['key_to_delete'];
        deleteKey($conn, $key_to_delete);
    }
}

// Fetch all keys
$sql_keys = "SELECT * FROM access_keys ORDER BY created_at DESC";
$result_keys = $conn->query($sql_keys);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Generate Access Key</title>
    <link rel="stylesheet" href="admin.css">
</head>
<body>
    <div class="container">
        <h1>Admin - Generate and Manage Access Keys</h1>
        <form method="POST">
            <div class="input-group">
                <input type="text" id="generatedKey" value="<?php echo isset($key) ? $key : ''; ?>" readonly>
                <button type="submit" name="generate_key" class="generate-button">Generate Key</button>
            </div>
        </form>
        <p class="info-text">Copy this key and give it to the client. It is valid for 30 days and can be reused within that period.</p>

        <h2>All Generated Keys</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Key Value</th>
                    <th>Expiration Date</th>
                    <th>Status</th>
                    <th>Delete</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($result_keys->num_rows > 0) {
                    // Output data for each row
                    while ($row = $result_keys->fetch_assoc()) {
                        $expiration_date = $row['expiration_date'];
                        $current_date = date('Y-m-d H:i:s');
                        $is_expired = strtotime($expiration_date) < strtotime($current_date) ? 'Expired' : 'Active';
                        $is_expired_color = strtotime($expiration_date) < strtotime($current_date) ? 'color: red;' : 'color: green;';
                        
                        echo "<tr>";
                        echo "<td>" . $row["id"] . "</td>";
                        echo "<td>" . $row["key_value"] . "</td>";
                        echo "<td style=\"$is_expired_color\">" . $expiration_date . " ($is_expired)</td>";
                        echo "<td>" . $is_expired . "</td>";
                        echo "<td><form method='POST' action=''>
                                <input type='hidden' name='key_to_delete' value='" . $row['key_value'] . "'>
                                <button type='submit' name='delete_key' class='delete-button'>Delete</button>
                              </form></td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='5'>No keys generated yet.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</body>
</html>