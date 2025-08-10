<?php
session_start();

if (!isset($_SESSION['admin_loggedin']) || $_SESSION['admin_loggedin'] != true) {
    header("location: login.php");
    exit;
}
include "db.php";

if (!isset($conn)) {
    die("Database connection not established.");
}
$last30revenue = 0;
$last30to60revenue = 0;
$last30bookings = 0;
$last30to60bookings = 0;
$todaycheckin = 0;
// $room_id = $_POST["room_id"] ?? 0;
// CURDATE() - INTERVAL 30 DAY;

$sql_last30day = "SELECT * FROM bookings WHERE checkin_date between CURDATE() - INTERVAL 30 DAY and CURDATE() ORDER BY checkin_date ASC";
$result_last30day = $conn->query($sql_last30day);
while ($rows = $result_last30day->fetch_assoc()) {
    $last30revenue += $rows['total_price'];
    $last30bookings += 1;
}

$sql_last30to60day = "SELECT * FROM bookings WHERE checkin_date between CURDATE() - INTERVAL 60 DAY and CURDATE() - interval 30 day ORDER BY checkin_date ASC";
$result_last30to60day = $conn->query($sql_last30to60day);
while ($rows = $result_last30to60day->fetch_assoc()) {
    $last30to60revenue += $rows['total_price'];
    $last30to60bookings += 1;
}

$sql_today = "SELECT * FROM bookings WHERE checkin_date = CURDATE()";
$result_today = $conn->query($sql_today);
while ($rows = $result_today->fetch_assoc()) {
    // $last30to60revenue += $rows['total_price'];
    $todaycheckin += 1;
}

$sql_totalbookings = "SELECT COUNT(*) AS total_bookings FROM bookings";
$result_totalbookings = $conn->query($sql_totalbookings);
$row = $result_totalbookings->fetch_assoc();
$total_bookings = $row["total_bookings"];

$sql_users = "select count(*) as total_users from user ";
$result_users = $conn->query($sql_users);
$row = $result_users->fetch_assoc();
$total_users = $row["total_users"];

$sql_rooms = "select count(*) as available_rooms from rooms where is_available=1";
$result_rooms = $conn->query($sql_rooms);
$row = $result_rooms->fetch_assoc();
$available_rooms = $row["available_rooms"];
if ($last30to60bookings < 1) {
    $growth = "";
} else {
    $growth = round(($last30bookings * 100) / $last30to60bookings, 2);
}

?>

<!DOCTYPE html>
<html>

<meta charset="UTF-8">

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>ADMIN</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: Arial, sans-serif;
        }

        nav {

            background-color: #0c4074ee;
            border-bottom: 1px solid #ccc;
            padding: 10px 20px;
            margin-bottom: 2%;

            position: sticky;
            top: 0;
            z-index: 1000;

        }


        .navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
        }

        .navbar-brand {
            font-weight: bold;
            font-size: 20px;
            /* font-size: 120%; */
            color: rgb(255, 255, 255);

        }
        /* .navbar-menu {
            display: flex;
            list-style: none;
        }

        .navbar-menu li {
            margin-left: 20px;
            position: relative;
        } */
        .navbar-menu {
            display: flex;
            list-style: none;
            flex-wrap: wrap;

        }

        .navbar-menu li {
            position: relative;
        }



        .navbar-menu a:hover {
            color: #000;
            border-bottom: 2px solid #333;
            transition: 0.3s;
        }


        .navbar-menu a {
            text-decoration: none;
            color: rgb(255, 255, 255);
            padding: 8px;
            display: block;
        }

        .navbar-menu .dropdown:hover .dropdown-menu {
            display: block;
        }

        .text-center {
            text-align: center;
        }

        .text-color {
            color: #0c4074ee;
            /* font-size: 18px; */
        }

        .details {
            padding: 10px;
            text-align: center;
            height: 280px;
            width: 459px;
            background-color: rgba(230, 230, 230, 1);
            font-size: 18px;

        }

        .details-container {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            justify-content: center;
            align-items: stretch
        }
    </style>


</head>

<body>
    <nav>
        <div class="navbar">
            <div class="navbar-brand">ADMIN PANNEL</div>
            <ul class="navbar-menu" id="navbarMenu">
                <li><a href="#">Dashboard</a></li>
                <li><a href="booking.php">Booking</a></li>
                <li><a href="users.php">Users</a></li>
                <li><a href="rooms.php">Rooms</a></li>
                <li><a href="logout.php">Logout</a></li>
                <!-- <li><a href="logout.php">Logout</a></li> -->
            </ul>
        </div>
    </nav>
    <h2 class="text-color text-center"><?php echo date("d M Y"); ?></h2>
    <h1 class="text-color text-center">Welcome , Admin</h1>
    <br><br><br>
    <div class="text-color">
        <div class="details-container">
            <div class="details">
                <br>
                <?php
                echo "📅 Revenue (Last 30 Days): ₹" . $last30revenue . "<br><br>";
                echo "📅 Revenue (30–60 Days Ago): ₹" . $last30to60revenue . "<br><br>";
                echo "🛏️ Rooms Booked (Last 30 Days): " . $last30bookings . "<br><br>";
                echo "🛏️ Rooms Booked (30–60 Days Ago): " . $last30to60bookings . "<br><br>";
                echo "📊 Booking Growth (Last 30 Days vs Previous): " . $growth. " %<br><br>"; ?>

            </div>
            <div class="details">
                <br>
                <?php
                echo "✅ Today's Check-ins: " . $todaycheckin . "<br><br>";
                echo "📖 Total Bookings: " . $total_bookings . "<br><br>";
                echo "👤 Registered Users: " . $total_users . "<br><br>";
                echo "🏨 Available Rooms: " . $available_rooms . "<br><br>";
                ?>

            </div>

        </div>


    </div>
    <br><br><br>
</body>