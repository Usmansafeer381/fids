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

// Include the database connection
require('connect.php'); // Update connect.php to use session-based password handling

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
    if (isset($_POST['newflight'])) {
        $params = [
            $_POST['airlinecode'],
            $_POST['flightno'],
            $_POST['departs'],
            $_POST['departstime'],
            $_POST['airportcode'],
            $_POST['registration'],
            $_POST['slottime'],
            $_POST['edt'],
            $_POST['bay'],
            $_POST['gate'],
            $_POST['aircraft'],
            $_POST['checkin'],
            $_POST['status'],
            $_POST['type'],
            $_POST['staffmsg'],
        ];

        $newsql = "INSERT INTO `fids`.`departures` (`airlinecode`, `flightno`, `departs`, `departstime`, `airport`, `registration`, `slottime`, `edt`, `bay`, `gate`, `aircraft`, `checkin`, `status`, `type`, `staffmsg`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        if (executeQuery($conn, $newsql, $params, str_repeat('s', count($params)))) {
            echo '<p>Flight added successfully.</p>';
        } else {
            echo '<p>Error adding flight: ' . $conn->error . '</p>';
        }
    }

    if (isset($_POST['updateflight'])) {
        $params = [
            $_POST['departstime'],
            $_POST['airportcode'],
            $_POST['registration'],
            $_POST['slottime'],
            $_POST['edt'],
            $_POST['bay'],
            $_POST['gate'],
            $_POST['aircraft'],
            $_POST['checkin'],
            $_POST['status'],
            $_POST['type'],
            $_POST['staffmsg'],
            $_POST['airlinecde'],
            $_POST['flightnumber'],
            $_POST['date'],
        ];

        $sql = "UPDATE `fids`.`departures` SET `departstime` = ?, `airport` = ?, `registration` = ?, `slottime` = ?, `edt` = ?, `bay` = ?, `gate` = ?, `aircraft` = ?, `checkin` = ?, `status` = ?, `type` = ?, `staffmsg` = ? WHERE `airlinecode` = ? AND `flightno` = ? AND `departs` = ?";
        if (executeQuery($conn, $sql, $params, 'sssssssssssss')) {
            echo '<p>Flight updated successfully.</p>';
        } else {
            echo '<p>Error updating flight: ' . $conn->error . '</p>';
        }
    }

    if (isset($_POST['deleteflight'])) {
        $params = [$_POST['airlinecode'], $_POST['flightno'], $_POST['departs']];
        $removesql = "DELETE FROM `fids`.`departures` WHERE `airlinecode` = ? AND `flightno` = ? AND `departs` = ?";
        if (executeQuery($conn, $removesql, $params, 'sss')) {
            echo '<p>Flight deleted successfully.</p>';
        } else {
            echo '<p>Error deleting flight: ' . $conn->error . '</p>';
        }
    }
}

echo '<!DOCTYPE html>';
echo '<html>';
echo '<head>';
echo '<meta http-equiv="refresh" content="10">';
echo '<link rel="stylesheet" type="text/css" href="staff.css">';
echo '<meta name="google" value="notranslate">';
echo '</head>';
echo '<body>';

$tquery = 'SELECT * FROM `departures` ORDER BY `departs`, `departstime`';
$ttable = mysqli_query($conn, $tquery);

if (!$ttable) {
    echo('<p>Oops, Try again</p>');
} else {
    echo '<div style="text-align: left; position:fixed;">Departures - Admin</div>';
    echo '<div style="text-align: right;">Local Time: ' . date("H:i") . ' &nbsp; </div>';
    echo '<table border="0" cellpadding="1" cellspacing="10" width="100%">';
    echo '<tr><td></td><td>Flight</td><td>TO</td><td>Sch</td><td>EDT</td><td>Gate</td><td>CheckIn</td><td>Status</td><td>TYPE</td><td>Staff Msg</td>';
    echo '<td><a href="depnewdeparture.php"><button>New Departure</button></a></td>';
    echo '<td><a href="index.html"><button>Home Page</button></a></td></tr>';

    while ($row = mysqli_fetch_assoc($ttable)) {
        $airlinecode = htmlspecialchars($row['airlinecode']);
        $flightno = htmlspecialchars($row['flightno']);
        $departs = htmlspecialchars($row['departs']);
        $departstime = htmlspecialchars($row['departstime']);
        $airport = htmlspecialchars($row['airport']);
        $registration = htmlspecialchars($row['registration']);
        $slottime = htmlspecialchars($row['slottime']);
        $edt = htmlspecialchars($row['edt']);
        $bay = htmlspecialchars($row['bay']);
        $gate = htmlspecialchars($row['gate']);
        $aircraft = htmlspecialchars($row['aircraft']);
        $checkin = htmlspecialchars($row['checkin']);
        $status = htmlspecialchars($row['status']);
        $type = htmlspecialchars($row['type']);
        $staffmsg = htmlspecialchars($row['staffmsg']);

        $timescheduled = date('H:i', strtotime($departstime));
        $timeactual = date('H:i', strtotime($edt));
        $slot = date('H:i', strtotime($slottime));

        echo '<tr>';
        echo file_exists('airlinelogos/' . $airlinecode . '.png') ? '<td><img src="airlinelogos/' . $airlinecode . '.png" style="height:20px"></td>' : '<td><img src="airlinelogos/default.png" style="height:20px"></td>';
        echo '<td>' . $airlinecode . $flightno . '</td>';
        echo '<td>' . $airport . '</td>';
        echo '<td>' . $timescheduled . '</td>';
        echo '<td>' . ($gate == '0' ? '' : $gate) . '</td>';
        echo '<td>' . $checkin . '</td>';
        echo '<td>' . $status . '</td>';
        echo '<td>' . ($type == 'd' ? 'Dom' : ($type == 'i' ? 'Intl' : 'Other')) . '</td>';
        echo '<td>' . $staffmsg . '</td>';

        // Update Flight Form
        echo '<form method="post" action="depupdate.php">';
        echo '<input type="hidden" name="airlinecode" value="' . $airlinecode . '">';
        echo '<input type="hidden" name="flightno" value="' . $flightno . '">';
        echo '<input type="hidden" name="departs" value="' . $departs . '">';
        echo '<td><input type="submit" value="Update Flight" name="updateflight"></td>';
        echo '</form>';

        // Delete Flight Form
        echo '<form method="post" action="depadmin.php">';
        echo '<input type="hidden" name="airlinecode" value="' . $airlinecode . '">';
        echo '<input type="hidden" name="flightno" value="' . $flightno . '">';
        echo '<input type="hidden" name="departs" value="' . $departs . '">';
        echo '<td><input type="submit" value="Delete Flight" name="deleteflight"></td>';
        echo '</form>';
        echo '</tr>';
    }
}

echo '</table>';
echo '</body>';
echo '</html>';

$conn->close();
?>
