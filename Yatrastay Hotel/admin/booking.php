<?php

session_start();

if (!isset($_SESSION['admin_loggedin']) || $_SESSION['admin_loggedin'] != true) {
    header("location: login.php");
    exit;
}

$sort = "id";
include "db.php";
$selectedSort = $_POST['sort']?? 'id';

if (!isset($conn)) {
    die("Database connection not established.");
}

$sql_up_booking = "SELECT * FROM bookings WHERE checkin_date >= CURDATE() ORDER BY checkin_date ASC";
$result_up_booking = $conn->query($sql_up_booking);
?>
<!DOCTYPE html>
<html>

<meta charset="UTF-8">

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>ADMIN</title>
    <!-- <link rel="stylesheet" href="style2.css"> -->
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

        h1 {
            text-align: center;
            /* color: #006600; */
            font-size: xx-large;
            font-family: 'Gill Sans', 'Gill Sans MT',
                ' Calibri', 'Trebuchet MS', 'sans-serif';
        }

        table {
            width: 80%;
            margin: 20px auto;
            border-collapse: collapse;
        }

        th,
        td {
            border: 1px solid #ccc;
            padding: 10px;
            text-align: center;
        }

        th {
            background-color: #f2f2f2;
        }

        .margin-left {
            margin-left: 9%;
        }
    </style>


</head>

<body>
    <nav>
        <div class="navbar">
            <div class="navbar-brand">ADMIN PANNEL</div>
            <ul class="navbar-menu" id="navbarMenu">
                <li><a href="index.php">Dashboard</a></li>
                <li><a href="#">Booking</a></li>
                <li><a href="users.php">Users</a></li>
                <li><a href="rooms.php">Rooms</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </div>
    </nav>
    <section>

        <h1>Upcoming Bookings Guests</h1><br><br>
        <!-- TABLE CONSTRUCTION -->
        <table>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Mobile</th>
                <th>Check-in</th>
                <th>Check-out</th>
                <th>Room Type</th>
                <th>Total Price</th>
                <th>Room ID</th>
                <th>Date & Time</th>
                <th>Booking Type</th>
            </tr>
            <!-- PHP CODE TO FETCH DATA FROM ROWS -->
            <?php
            // LOOP TILL END OF DATA
            while ($rows = $result_up_booking->fetch_assoc()) {
                ?>
                <tr>
                    <!-- FETCHING DATA FROM EACH
                    ROW OF EVERY COLUMN -->

                    <td><?php echo $rows['id']; ?></td>
                    <td><?php echo $rows['guest_name']; ?></td>
                    <td><?php echo $rows['mobile']; ?></td>
                    <td><?php echo $rows['checkin_date']; ?></td>
                    <td><?php echo $rows['checkout_date']; ?></td>
                    <td><?php echo $rows['room_type']; ?></td>
                    <td><?php echo $rows['total_price']; ?></td>
                    <td><?php echo $rows['room_id']; ?></td>
                    <td><?php echo $rows['date']; ?></td>
                    <td><?php echo $rows['booking_type']; ?></td>

                </tr>
                <?php
            }
            ?>
        </table>
        <br><br><br>

    </section>
    <section>

        <h1>Bookings</h1><br><br>

        <form method="POST" action="" class="margin-left">
            <label for="sortt">Sort :</label>
            <select name="sort" id="sortt" onchange="this.form.submit()">
                <option value="" <?= ($selectedSort == '') ? 'selected' : '' ?>>Select Sort</option>
                <option value="id" <?= ($selectedSort == 'id') ? 'selected' : '' ?>>ID</option>
                <option value="checkin" <?= ($selectedSort == 'checkin') ? 'selected' : '' ?>>Check-In</option>
            </select>
        </form>
        <?php
        if (isset($_POST['sort'])) {
            $sort = $_POST['sort']; // 'selectName' is the name attribute of your <select>
        }
        ?>
        <table>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Mobile</th>
                <th>Check-in</th>
                <th>Check-out</th>
                <th>Room Type</th>
                <th>Total Price</th>
                <th>Room ID</th>
                <th>Date & Time</th>
                <th>Booking Type</th>
            </tr>
            <?php
            
            if ($sort == "checkin") {
                $sql_booking = " SELECT * FROM bookings ORDER BY checkin_date ASC";
                $result_booking = $conn->query($sql_booking);
            }
            if ($sort == "id") {
                $sql_booking = " SELECT * FROM bookings ORDER BY id ASC";
                $result_booking = $conn->query($sql_booking);
            }
            
            if ($sort) {
                while ($rows = $result_booking->fetch_assoc()) {
                    ?>
                    <tr>
                        <!-- FETCHING DATA FROM EACH
                    ROW OF EVERY COLUMN -->

                        <td><?php echo $rows['id']; ?></td>
                        <td><?php echo $rows['guest_name']; ?></td>
                        <td><?php echo $rows['mobile']; ?></td>
                        <td><?php echo $rows['checkin_date']; ?></td>
                        <td><?php echo $rows['checkout_date']; ?></td>
                        <td><?php echo $rows['room_type']; ?></td>
                        <td><?php echo $rows['total_price']; ?></td>
                        <td><?php echo $rows['room_id']; ?></td>
                        <td><?php echo $rows['date']; ?></td>
                        <td><?php echo $rows['booking_type']; ?></td>

                    </tr>
                    <?php
                }
            }
            ?>
        </table>
        <br><br><br>

    </section>
</body>