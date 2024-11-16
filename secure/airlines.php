<?php
session_start();

// Define your admin password here
define('ADMIN_PASSWORD', 'your_admin_password_here'); // Change this to your desired password

// Check for password submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['admin_password'])) {
    if ($_POST['admin_password'] === ADMIN_PASSWORD) {
        $_SESSION['admin_logged_in'] = true;
    } else {
        echo '<p style="color: red;">Invalid password.</p>';
    }
}

// Redirect if not logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    echo '<form method="post"><label for="admin_password">Admin Password:</label>';
    echo '<input type="password" name="admin_password" required>';
    echo '<input type="submit" value="Login"></form>';
    exit;
}

// Check if the database password is provided
if (!isset($_SESSION['db_password']) && $_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo '<form method="post"><label for="db_password">Database Password:</label>';
    echo '<input type="password" name="db_password" required>';
    echo '<input type="submit" value="Connect to Database"></form>';
    exit;
}

// If the password is provided, store it in the session
if (isset($_POST['db_password'])) {
    $_SESSION['db_password'] = $_POST['db_password'];
}

// Include the database connection
require('connect.php'); // Update connect.php to use the session password

// Function to execute a query
function executeQuery($conn, $sql, $params = [], $types = '') {
    $stmt = $conn->prepare($sql);
    if ($params) {
        $stmt->bind_param($types, ...$params);
    }
    return $stmt->execute();
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['deleteairline'])) {
        $airlinecode = $_POST['airlinecode'];

        $removesql = "DELETE FROM `fids`.`airlines` WHERE `airlinecode` = ?";
        if (executeQuery($conn, $removesql, [$airlinecode], 's')) {
            header("Location: airlines.php");
            exit;
        }
    }

    if (isset($_POST['updateairline'])) {
        $airlinecode = $_POST['airlinecode'];
        $airlinename = $_POST['airlinename'];

        $updatesql = "UPDATE `fids`.`airlines` SET `airlinename` = ? WHERE `airlinecode` = ?";
        if (!executeQuery($conn, $updatesql, [$airlinename, $airlinecode], 'ss')) {
            echo '<p>Error updating airline: ' . $conn->error . '</p>';
        }
    }

    if (isset($_POST['newairline'])) {
        $airlinecode = strtoupper($_POST['airlinecode']);
        $airlinename = $_POST['airlinename'];

        $newsql = "INSERT INTO `fids`.`airlines` (`airlinecode`, `airlinename`) VALUES (?, ?)";
        if (!executeQuery($conn, $newsql, [$airlinecode, $airlinename], 'ss')) {
            echo '<p>Error adding airline: ' . $conn->error . '</p>';
        }
    }
}

echo '<!DOCTYPE html>';
echo '<html>';
echo '<head>';
echo '<link rel="stylesheet" type="text/css" href="staff.css">';
echo '<meta name="google" value="notranslate">';
echo '</head>';
echo '<body>';

$tquery = 'SELECT * FROM `airlines` ORDER BY `airlinecode`';
$ttable = mysqli_query($conn, $tquery);

if (!$ttable) {
    echo('<p>Oops, Try again</p>');
} else {
    echo '<div style="text-align: left; position:fixed;">Airlines - Admin</div>';
    echo '<div style="text-align: right;">Last Update: ' . date("H:i") . ' &nbsp; </div>';
    echo '<table border="0" cellpadding="1" cellspacing="10" width="100%">';
    echo '<tr><td>Airline Code</td><td>Airline Name</td><td><a href="index.html"><button>Home Page</button></a></td></tr>';

    // New Airline Form
    echo '<tr><form method="post" action="airlines.php">';    
    echo '<td><input type="text" id="airlinecode" name="airlinecode" size="3" minlength="2" maxlength="2" required></td>';
    echo '<td><input type="text" id="airlinename" name="airlinename" size="25" required></td>';
    echo '<td><input type="submit" value="Add Airline" name="newairline"></td>';
    echo '</form></tr>';

    while ($row = mysqli_fetch_assoc($ttable)) {
        $airlinecode = htmlspecialchars($row['airlinecode']);
        $airlinename = htmlspecialchars($row['airlinename']);

        echo '<tr>';
        echo '<form method="post" action="airlines.php">';    
        echo '<td>' . $airlinecode . '</td>';
        echo '<td><input type="text" id="airlinename" name="airlinename" size="25" value="' . $airlinename . '" required>';
        echo '<input type="hidden" id="airlinecode" name="airlinecode" value="' . $airlinecode . '">';
        echo '<input type="submit" value="Update Airline Name" name="updateairline"></td>';
        echo '</form>';

        echo '<form method="post" action="airlines.php">';
        echo '<input type="hidden" name="airlinecode" value="' . $airlinecode . '">';
        echo '<td><input type="submit" value="Delete Airline" name="deleteairline"></td>';
        echo '</form>';
        echo '</tr>';
    }

    echo '</table>';
}

echo '</body>';
echo '</html>';

$conn->close();
?>
