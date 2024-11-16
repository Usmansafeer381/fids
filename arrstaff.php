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
require('connect.php');
require('settings.php');

echo '<!DOCTYPE html>';
echo '<html>';
echo '<head>';
echo '<meta http-equiv="refresh" content="10">';
echo '<link rel="stylesheet" type="text/css" href="staff.css">';
echo '<meta name="google" value="notranslate">';
echo '</head>';
echo '<body>';

$tquery = 'SELECT * FROM `arrivals` ORDER BY `arrives`, `arrivestime`';
$ttable = mysqli_query($conn, $tquery);

if (!$ttable) {
    echo('<p>Oops, Try again</p>');
} else {
    echo ('<div style="text-align: left; position:fixed;">Arrivals - Staff</div>');
    echo ('<div style="text-align: right;">Local Time: ' . date("H:i") . ' &nbsp; </div>');
    
    echo '<table border="0" cellpadding="1" cellspacing="10" width="100%">';
    echo '<tr>';
    echo '<td></td>';		
    echo '<td>Flight No</td>';
    echo '<td>FROM</td>';
    echo '<td>SchTime</td>';
    echo '<td>Slot</td>';
    echo '<td>ETA</td>';
    echo '<td>Rego</td>';
    echo '<td>Bay</td>';
    echo '<td>Gate</td>';
    echo '<td>A/C</td>';
    echo '<td>Belt</td>';
    echo '<td>Status</td>';
    echo '<td>TYPE</td>';
    echo '<td>Staff Msg</td>';
    echo '</tr>';

    while ($row = mysqli_fetch_assoc($ttable)) {
        $airlinecode = htmlspecialchars($row['airlinecode']);
        $flightno = htmlspecialchars($row['flightno']);
        $arrives = htmlspecialchars($row['arrives']);
        $arrivestime = htmlspecialchars($row['arrivestime']);
        $airport = htmlspecialchars($row['airport']);
        $registration = htmlspecialchars($row['registration']);
        $slottime = htmlspecialchars($row['slottime']);
        $eta = htmlspecialchars($row['eta']);
        $bay = htmlspecialchars($row['bay']);
        $gate = htmlspecialchars($row['gate']);
        $aircraft = htmlspecialchars($row['aircraft']);
        $belt = htmlspecialchars($row['belt']);
        $type = htmlspecialchars($row['type']);
        $status = htmlspecialchars($row['status']);
        $staffmsg = htmlspecialchars($row['staffmsg']);

        $timescheduled = date('H:i', strtotime($arrivestime));
        $timeactual = date('H:i', strtotime($eta));
        $slot = date('H:i', strtotime($slottime));

        echo '<tr>';
        if (file_exists('airlinelogos/' . $airlinecode . '.png')) {
            echo '<td><img src="airlinelogos/' . $airlinecode . '.png" style="height:20px"></td>';	
        } else {
            echo '<td><img src="airlinelogos/default.png" style="height:20px"></td>';	
        }

        echo '<td>' . $airlinecode . $flightno . '</td>';
        echo '<td>' . $airport . '</td>';
        echo '<td>' . $timescheduled . '</td>';
        echo '<td>' . ($slottime ? $slot : '') . '</td>';
        echo '<td>' . $timeactual . '</td>';
        echo '<td>' . $registration . '</td>';
        echo '<td>' . ($bay == '0' ? '' : $bay) . '</td>';
        echo '<td>' . ($gate == '0' ? '' : $gate) . '</td>';
        echo '<td>' . $aircraft . '</td>';
        echo '<td>' . $belt . '</td>';
        echo '<td>' . $status . '</td>';
        echo '<td>' . ($type == 'd' ? 'Dom' : ($type == 'i' ? 'Intl' : 'Other')) . '</td>';
        echo '<td>' . $staffmsg . '</td>';
        echo '</tr>';
    }
}

echo '</table>';
echo ('</body>');
echo ('</html>');

$conn->close();
?>
