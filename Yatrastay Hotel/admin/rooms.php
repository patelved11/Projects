<?php

session_start();

if (!isset($_SESSION['admin_loggedin']) || $_SESSION['admin_loggedin'] != true) {
    header("location: login.php");
    exit;
}

$priceAlert = false;
$bookingdetail = false;
$Confirm = false;
$bookingAlert = false;
$available_room = -1;
$total_price = 0;
$check_in = $_POST["check_in"] ?? '';
$check_out = $_POST["check_out"] ?? '';
$today = date("Y-m-d"); // PHP gets today's date in YYYY-MM-DD format

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    include "db.php";
    $mobile = $_POST["mobile"] ?? '';
    $guest_name = $_POST["name"] ?? '';
    $room_type = $_POST["room_type"] ?? '';
    $room_id = $_POST["room_id"] ?? 0;

    if ($check_in && $check_out) {
        $sql_rooms = "SELECT * FROM rooms 
                WHERE id NOT IN (
                    SELECT room_id FROM bookings 
                    WHERE checkin_date < '$check_out' AND checkout_date > '$check_in'
                )";

        $result_rooms = $conn->query($sql_rooms);
        $result_roomss = $conn->query($sql_rooms);
        $available_room = $result_rooms->num_rows;    # count rooms
    }
    //convert string to date format  
    $checkInDate = new DateTime($check_in);
    $checkOutDate = new DateTime($check_out);
    // Calculate the difference
    $interval = $checkInDate->diff($checkOutDate);
    $days = $interval->days;

}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['confirm_btn2'])) {
    $bookingdetail = true;
    if ($available_room > 0) {
        
        $sql = "SELECT price FROM rooms WHERE room_type = '$room_type' LIMIT 1";
        
        $result = $conn->query($sql);
        $row = $result->fetch_assoc();
        $price = $row["price"];
        $total_price = ($price * $days);
        
        $priceAlert = "Total Amount is <strong>₹$total_price</strong> for $days Night . Pay at reception on Check-In day";
        $alert = true;
    }
    
    // Confirm button for booking
}
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['confirm_btn'])) {
        //     $Confirm = true;
        // }
        $sql = "SELECT price FROM rooms WHERE room_type = '$room_type' LIMIT 1";
        
        $result = $conn->query($sql);
        $row = $result->fetch_assoc();
        $price = $row["price"];
        $total_price = ($price * $days);
        
        $priceAlert = "Total Amount is <strong>₹$total_price</strong> for $days Night . Pay at reception on Check-In day";
        $alert = true;
        
        // if ($Confirm) {
            // Step 4: Insert the booking
            $insert = "INSERT INTO bookings (room_id, room_type, guest_name, mobile, checkin_date, checkout_date,total_price,date,booking_type)
                           VALUES ('$room_id', '$room_type', '$guest_name', '$mobile', '$check_in', '$check_out', '$total_price',current_timestamp(),'Offline')";

        if ($conn->query($insert) === TRUE) {

            $bookingAlert = "✅ Room booked successfully! Booking ID: " . $conn->insert_id . " " . "<br>";
            // $succAlert = true;
        } else {
            $bookingAlert = "❌ Booking failed: " . $conn->error;
        }
    }




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

        .text-center {
            text-align: center;
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

        .overlay-form {
            background-color: rgba(100, 97, 97, 0.3);
            position: absolute;
            left: 50%;
            transform: translate(-50%, -50%);
            backdrop-filter: blur(7px);
            padding: 10px;
            border-radius: 8px;
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

        .center-btn {
            display: block;
            margin: 20px auto;            /* centers it horizontally */
            font-size: 18px;
            width: 200px;
        
            padding: 10px;
        
            background-color: #0c4074ee;
            color: white;
        
            border-radius: 5px;
            cursor: pointer;
        }
        
    </style>


</head>

<body>
    <nav>
        <div class="navbar">
            <div class="navbar-brand">ADMIN PANNEL</div>
            <ul class="navbar-menu" id="navbarMenu">
                <li><a href="dashboard.php">Dashboard</a></li>
                <li><a href="booking.php">Booking</a></li>
                <li><a href="users.php">Users</a></li>
                <li><a href="rooms.php">Rooms</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </div>
    </nav>
    
        <h3 class="text-center">Fill Detail to check Availability</h3>
        <br><br>
        <form class="overlay-form" method="POST" action="">
            <label>Check-in:</label>
            <input type="date" name="check_in" min="<?= $today ?>"required>

            <label>Check-out:</label>
            <input type="date" name="check_out" min="<?= $today ?>"required>
            <button type="submit">Check</button>
        </form><br><br>
    
        <div class="confirm-btn">
    <?php
    if ($bookingdetail) {
        if ($priceAlert) {
            echo '<div class="text-center"><br>' . $priceAlert . '</div>';
        }
        ?>

        <form method="POST" action="">
            <input type="hidden" name="check_in" value="<?= $check_in ?>">
            <input type="hidden" name="check_out" value="<?= $check_out ?>">
            <input type="hidden" name="room_type" value="<?= $room_type ?>">
            <input type="hidden" name="room_id" value="<?= $room_id ?>">
            <input type="hidden" name="name" value="<?= $guest_name ?>">
            <input type="hidden" name="mobile" value="<?= $mobile ?>">

            <button type="submit" name="confirm_btn" class="center-btn">Confirm Booking</button>
        </form>

    <?php }
    ?>
</div>

<?php
    
        if ($bookingAlert) {
            echo '<div class="text-center"><br>' . $bookingAlert . '</div>';
        }
        ?>

    <!-- TABLE CONSTRUCTION -->
    <?php if ($available_room > 0) { ?>
        <h1 class="text-center">Rooms Detail</h1>
        <table>
            <tr>
                <th>Room ID</th>
                <th>Room Type</th>
                <th>Price</th>
                <th>Room Number</th>

            </tr>
            <!-- PHP CODE TO FETCH DATA FROM ROWS -->
            <?php
            // LOOP TILL END OF DATA
            while ($rows = $result_rooms->fetch_assoc()) {
                ?>
                <tr>
                    <!-- FETCHING DATA FROM EACH
                    ROW OF EVERY COLUMN -->

                    <td><?php echo $rows['id']; ?></td>
                    <td><?php echo $rows['room_type']; ?></td>
                    <td><?php echo $rows['price']; ?></td>
                    <td><?php echo $rows['room_number']; ?></td>
                </tr>
                <?php
            }
            ?>
        </table>



    <?php }
    if ($available_room == 0) {
        echo "<h3>No Rooms are available for $check_in to $check_out </h3>";
    } ?>

    <?php if ($available_room > 0) { ?>
        <h3 class="text-center">Fill Form for booking</h3>
        <br><br><br><br><br><br>
        <form class="overlay-form" method="POST" action="">
            <input type="hidden" name="check_in" value="<?= $check_in ?>">
            <input type="hidden" name="check_out" value="<?= $check_out ?>">
            <label for="name" class="marginn">
                Name
            </label>
            <input type="text" id="name" name="name" placeholder="Enter Customer Name" required class="marginn">
            <br>
            <label for="mobile" class="marginn">
                Mobile
            </label>
            <input type="tel" id="mobile" name="mobile" placeholder="Enter Customer Mobile " required class="marginn">
            <br>
            <label>Room Type</label>
            <select name="room_type">
                <option value="">-- Please choose --</option>
                <option value="RoyalSuite">Royal Suite Room
                </option>
                <option value="LuxuaryGarden">Luxuary Garden
                    Room</option>
                <option value="Delux">Delux Room</option>
            </select>
            <br>
            <label>Room ID</label>
            <select name="room_id" required>
                <option value="">-- Select Available Room ID --</option>
                <?php while ($rows = $result_roomss->fetch_assoc()) { ?>
                    <option value="<?= $rows['id'] ?>">Room ID: <?= $rows['id'] ?></option>
                <?php } ?>
            </select>
            <br>
            <button type="submit" name="confirm_btn2">Book Now</button>
        </form>

    <?php } ?>
    <br><br><br><br><br>
</body>

</html>