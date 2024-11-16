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
    echo '<form method="post"><label for="db_password">Password:</label>';
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
    if (isset($_POST['deleteairport'])) {
        $airportcode = $_POST['airportcode'];

        $removesql = "DELETE FROM `fids`.`airports` WHERE `airportcode` = ?";
        if (executeQuery($conn, $removesql, [$airportcode], 's')) {
            header("Location: airports.php");
            exit;
        }
    }

    if (isset($_POST['updateairport'])) {
        $airportcode = $_POST['airportcode'];
        $airportname = $_POST['airportname'];

        $updatesql = "UPDATE `fids`.`airports` SET `airportname` = ? WHERE `airportcode` = ?";
        if (!executeQuery($conn, $updatesql, [$airportname, $airportcode], 'ss')) {
            echo '<p>Error updating airport: ' . $conn->error . '</p>';
        }
    }

    if (isset($_POST['newairport'])) {
        $airportcode = $_POST['airportcode'];
        $airportname = $_POST['airportname'];

        $newsql = "INSERT INTO `fids`.`airports` (`airportcode`, `airportname`) VALUES (?, ?)";
        if (!executeQuery($conn, $newsql, [$airportcode, $airportname], 'ss')) {
            echo '<p>Error adding airport: ' . $conn->error . '</p>';
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

$tquery = 'SELECT * FROM `airports` ORDER BY `airportcode`';
$ttable = mysqli_query($conn, $tquery);

if (!$ttable) {
    echo('<p>Oops, Try again</p>');
} else {
    echo '<div style="text-align: left; position:fixed;">Airports - Admin</div>';
    echo '<div style="text-align: right;">Last Update: ' . date("H:i") . ' &nbsp; </div>';
    echo '<table border="0" cellpadding="1" cellspacing="10" width="100%">';
    echo '<tr><td>Airport Code</td><td>Airport Name</td><td><a href="index.html"><button>Home Page</button></a></td></tr>';

    // New Airport Form
    echo '<tr><form method="post" action="airports.php">';    
    echo '<td><input type="text" id="airportcode" name="airportcode" size="4" minlength="3" maxlength="4" required></td>';
    echo '<td><input type="text" id="airportname" name="airportname" size="25" required></td>';
    echo '<td><input type="submit" value="Add Airport" name="newairport"></td>';
    echo '</form></tr>';

    while ($row = mysqli_fetch_assoc($ttable)) {
        $airportcode = htmlspecialchars($row['airportcode']);
        $airportname = htmlspecialchars($row['airportname']);

        echo '<tr>';
        echo '<form method="post" action="airports.php">';    
        echo '<td>' . $airportcode . '</td>';
        echo '<td><input type="text" id="airportname" name="airportname" size="25" value="' . $airportname . '" required>';
        echo '<input type="hidden" id="airportcode" name="airportcode" value="' . $airportcode . '">';
        echo '<input type="submit" value="Update Airport Name" name="updateairport"></td>';
        echo '</form>';

        echo '<form method="post" action="airports.php">';
        echo '<input type="hidden" name="airportcode" value="' . $airportcode . '">';
        echo '<td><input type="submit" value="Delete Airport" name="deleteairport"></td>';
        echo '</form>';
        echo '</tr>';
    }

    echo '</table>';
}

echo '</body>';
echo '</html>';

$conn->close();
?>
