<?php

$type = $_GET['type'] ?? ''; // Use null coalescing operator for default values
$login = $_GET['login'] ?? '';
require('connect.php');
require('settings.php');

function executeQuery($conn, $sql, $params = [], $types = '') {
    $stmt = $conn->prepare($sql);
    if ($params) {
        $stmt->bind_param($types, ...$params);
    }
    return $stmt->execute();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['newarrival'])) {
        $fields = [
            'airlinecode' => $_POST['airlinecode'],
            'flightno' => $_POST['flightno'],
            'arrives' => $_POST['arrives'],
            'arrivestime' => $_POST['arrivestime'],
            'airportcode' => $_POST['airportcode'],
            'registration' => $_POST['registration'],
            'slottime' => $_POST['slottime'],
            'eta' => $_POST['eta'],
            'bay' => $_POST['bay'],
            'gate' => $_POST['gate'],
            'aircraft' => $_POST['aircraft'],
            'belt' => $_POST['belt'],
            'status' => $_POST['status'],
            'type' => $_POST['type'],
            'staffmsg' => $_POST['staffmsg'],
        ];

        $sql = "INSERT INTO `fids`.`arrivals` (`airlinecode`, `flightno`, `arrives`, `arrivestime`, `airport`, `registration`, `slottime`, `eta`, `bay`, `gate`, `aircraft`, `belt`, `status`, `type`, `staffmsg`) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        if (executeQuery($conn, $sql, array_values($fields), 'sssssssssssssss')) {
            echo 'Arrival added successfully.';
        } else {
            echo 'Error adding arrival: ' . $conn->error;
        }
    }

    if (isset($_POST['updateflight'])) {
        $fields = [
            'arrivestime' => $_POST['arrivestime'],
            'airportcode' => $_POST['airportcode'],
            'registration' => $_POST['rego'],
            'slottime' => $_POST['slottime'],
            'eta' => $_POST['eta'],
            'bay' => $_POST['bay'],
            'gate' => $_POST['gate'],
            'aircraft' => $_POST['aircraft'],
            'belt' => $_POST['belt'],
            'status' => $_POST['status'],
            'type' => $_POST['type'],
            'staffmsg' => $_POST['staffmsg'],
            'airlinecode' => $_POST['airlinecode'],
            'flightno' => $_POST['flightno'],
            'arrives' => $_POST['arrives'],
        ];

        $sql = "UPDATE `fids`.`arrivals` SET 
                `arrivestime` = ?, `airport` = ?, `registration` = ?, `slottime` = ?, `eta` = ?, `bay` = ?, `gate` = ?, `aircraft` = ?, `belt` = ?, `status` = ?, `type` = ?, `staffmsg` = ? 
                WHERE `airlinecode` = ? AND `flightno` = ? AND `arrives` = ?";

        if (executeQuery($conn, $sql, array_values($fields), 'sssssssssssss')) {
            echo 'Flight updated successfully.';
        } else {
            echo 'Error updating flight: ' . $conn->error;
        }
    }

    if (isset($_POST['deleteflight'])) {
        $airlinecode = $_POST['airlinecode'];
        $flightno = $_POST['flightno'];
        $arrives = $_POST['arrives'];

        $sql = "DELETE FROM `fids`.`arrivals` WHERE `airlinecode` = ? AND `flightno` = ? AND `arrives` = ?";
        if (executeQuery($conn, $sql, [$airlinecode, $flightno, $arrives], 'sss')) {
            echo 'Flight deleted successfully.';
        } else {
            echo 'Error deleting flight: ' . $conn->error;
        }
    }
}

// Fetch and display arrivals
$query = 'SELECT * FROM `arrivals` ORDER BY `arrives`, `arrivestime`';
$result = mysqli_query($conn, $query);

if (!$result) {
    echo '<p>Oops, try again.</p>';
} else {
    echo '<!DOCTYPE html>';
    echo '<html>';
    echo '<head>';
    echo '<meta http-equiv="refresh" content="10">';
    echo '<link rel="stylesheet" type="text/css" href="staff.css">';
    echo '<meta name="google" value="notranslate">';
    echo '</head>';
    echo '<body>';
    echo '<div style="text-align: left; position:fixed;">Arrivals - Admin</div>';
    echo '<div style="text-align: right;">Local Time: ' . date("H:i") . ' &nbsp;</div>';
    echo '<table border="0" cellpadding="1" cellspacing="10" width="100%">';
    echo '<tr><td></td><td>Flight</td><td>FROM</td><td>Sch</td><td>ETA</td><td>Gate</td><td>Belt</td><td>Status</td><td>TYPE</td><td>Staff Msg</td>';
    echo '<td><a href="arrnewarrival.php"><button>New Arrival</button></a></td>';
    echo '<td><a href="index.html"><button>Home Page</button></a></td></tr>';

    while ($row = mysqli_fetch_assoc($result)) {
        $timescheduled = date('H:i', strtotime($row['arrivestime']));
        $timeactual = date('H:i', strtotime($row['eta']));
        $slot = date('H:i', strtotime($row['slottime']));
        echo '<tr>';

        $logoPath = 'airlinelogos/' . htmlspecialchars($row['airlinecode']) . '.png';
        echo '<td><img src="' . (file_exists($logoPath) ? $logoPath : 'airlinelogos/default.png') . '" style="height:20px"></td>';
        echo '<td>' . htmlspecialchars($row['airlinecode'] . $row['flightno']) . '</td>';
        echo '<td>' . htmlspecialchars($row['airport']) . '</td>';
        echo '<td>' . htmlspecialchars($timescheduled) . '</td>';
        echo '<td>' . htmlspecialchars($timeactual) . '</td>';
        echo '<td>' . ($row['gate'] == '0' ? '' : htmlspecialchars($row['gate'])) . '</td>';
        echo '<td>' . htmlspecialchars($row['belt']) . '</td>';
        echo '<td>' . htmlspecialchars($row['status']) . '</td>';
        echo '<td>' . htmlspecialchars($row['type'] == 'd' ? 'Dom' : ($row['type'] == 'i' ? 'Intl' : 'Other')) . '</td>';
        echo '<td>' . htmlspecialchars($row['staffmsg']) . '</td>';

        // Update Flight Form
        echo '<form method="post" action="arrupdate.php">';
        echo '<input type="hidden" name="airlinecode" value="' . htmlspecialchars($row['airlinecode']) . '">';
        echo '<input type="hidden" name="flightno" value="' . htmlspecialchars($row['flightno']) . '">';
        echo '<input type="hidden" name="arrives" value="' . htmlspecialchars($row['arrives']) . '">';
        echo '<td><input type="submit" value="Update Flight" name="updateflight"></td>';
        echo '</form>';

        // Delete Flight Form
        echo '<form method="post" action="arradmin.php">';
        echo '<input type="hidden" name="airlinecode" value="' . htmlspecialchars($row['airlinecode']) . '">';
        echo '<input type="hidden" name="flightno" value="' . htmlspecialchars($row['flightno']) . '">';
        echo '<input type="hidden" name="arrives" value="' . htmlspecialchars($row['arrives']) . '">';
        echo '<td><input type="submit" value="Delete Flight" name="deleteflight"></td>';
        echo '</form>';

        echo '</tr>';
    }

    echo '</table>';
    echo '</body>';
    echo '</html>';
}

$conn->close();
?>
