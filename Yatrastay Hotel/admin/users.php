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

$sql_user = " SELECT * FROM user";
$result_user = $conn->query($sql_user);

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
            color: rgb(255, 255, 255);

        }

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
            color: #006600;
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
    </style>


</head>

<body>
    <nav>
        <div class="navbar">
            <div class="navbar-brand">ADMIN PANNEL</div>
            <ul class="navbar-menu" id="navbarMenu">
                <li><a href="index.php">Dashboard</a></li>
                <li><a href="booking.php">Booking</a></li>
                <li><a href="#">Users</a></li>
                <li><a href="rooms.php">Rooms</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </div>
    </nav>
    <section>
        <h1>Users Detail</h1><br><br>

        <!-- TABLE CONSTRUCTION -->
        <table>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>E-mail</th>
                <th>Mobile</th>
                <th>Date</th>
            </tr>
            <!-- PHP CODE TO FETCH DATA FROM ROWS -->
            <?php
            // LOOP TILL END OF DATA
            while ($rows = $result_user->fetch_assoc()) {
                ?>
                <tr>
                    <!-- FETCHING DATA FROM EACH
                    ROW OF EVERY COLUMN -->

                    <td><?php echo $rows['id']; ?></td>
                    <td><?php echo $rows['name']; ?></td>
                    <td><?php echo $rows['mobile']; ?></td>
                    <td><?php echo $rows['email']; ?></td>
                    <td><?php echo $rows['date']; ?></td>
                </tr>
                <?php
            }
            ?>
        </table>

    </section>
</body>