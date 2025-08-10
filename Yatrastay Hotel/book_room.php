<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] != true) {
    header("location: login.php");
    exit;
}

$available=true;
$succAlert = false;
$available_room = 0;
$room_numbers = 0;
$Confirm = false;
$alert = false;
$room_type = $_POST["room_type"] ?? '';
$successAlert = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    include "db.php";

    // Get initial form data
    $check_in = $_POST["check_in"] ?? '';
    $check_out = $_POST["check_out"] ?? '';
    $room_numbers = $_POST["roomsno"] ?? 0; // ✅ Get selected number of rooms

    // Step 1: Get a user's email and name
    $sql = "SELECT mobile, name FROM user LIMIT 1";
    $result = $conn->query($sql);
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $mobile = $row["mobile"];
        $guest_name = $row["name"];
    }

    // Step 2: Count available rooms
    if ($check_in && $check_out && $room_type) {
        $sql = "SELECT COUNT(*) AS available_room FROM rooms 
                WHERE room_type = '$room_type'
                AND id NOT IN (
                    SELECT room_id FROM bookings 
                    WHERE checkin_date < '$check_out' AND checkout_date > '$check_in'
                )";

        $result = $conn->query($sql);
        $row = $result->fetch_assoc();
        $available_room = $row["available_room"];

        // echo "🛏️ Available '$room_type' rooms for your selected dates: <b>$available_room</b><br>";
    }

    // Confirm button for booking
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['confirm_btn'])) {
        $Confirm = true;
    }

    //convert string to date format  
    $checkInDate = new DateTime($check_in);
    $checkOutDate = new DateTime($check_out);
    // Calculate the difference
    $interval = $checkInDate->diff($checkOutDate);
    $days = $interval->days;

    // ✅ You can now use $room_numbers for further logic like booking
    if ($available_room > 0) {


        $sql = "SELECT price FROM rooms WHERE room_type = '$room_type' LIMIT 1";

        $result = $conn->query($sql);
        $row = $result->fetch_assoc();
        $price = $row["price"];
        $total_price = ($price * $days) * $room_numbers;
        $total_price2 = ($price * $days);
        if ($room_numbers > 0) {
            $priceAlert = "Total Amount is <strong>₹$total_price</strong> for $days Night and $room_numbers Rooms. Pay at reception on Check-In day";
            $alert = true;
        }

        if ($Confirm) {

            for ($x = 1; $x <= $room_numbers; $x++) {
                // $succAlert = false;

                // Step 3: Get one available room ID
                $sql = "SELECT id FROM rooms 
                                WHERE room_type = '$room_type'
                                AND id NOT IN (
                            SELECT room_id FROM bookings 
                            WHERE checkin_date < '$check_out' AND checkout_date > '$check_in'
                        )
                        LIMIT 1";

                $result = $conn->query($sql);
                $row = $result->fetch_assoc();
                $room_id = $row["id"];

                // Step 4: Insert the booking
                $insert = "INSERT INTO bookings (room_id, room_type, guest_name, mobile, checkin_date, checkout_date,total_price,date,booking_type)
                           VALUES ('$room_id', '$room_type', '$guest_name', '$mobile', '$check_in', '$check_out', '$total_price2',current_timestamp(),'Oneline')";

                if ($conn->query($insert) === TRUE) {

                    $successAlert .= "✅ Room $x booked successfully! Booking ID: " . $conn->insert_id . " " . "<br>";
                    $succAlert = true;
                } else {
                    echo "❌ Booking failed: " . $conn->error;
                }
            }
        }
    } else {
        $available=false;
    }

    $conn->close();
}
?>

<html>

<head>
    <style>
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            padding: 12px;
            border: 1px solid #c3e6cb;
            border-radius: 5px;
            width: 50%;
            margin: 20px auto;
            text-align: center;
        }

        body {
            background-image: url("images/bgimg.jpg");
            font-family: Arial, sans-serif;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }


        .imgs {
            height: 50px;
            width: 50px;
            margin: 2.5% 2.5% 2.5% 2.5%
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
            color: #333;

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

        nav {

            background-color: rgba(210, 210, 210, 0.8);
            border-bottom: 1px solid #ccc;
            padding: 10px 20px;
            margin-bottom: 2%;

            position: sticky;
            top: 0;
            z-index: 1000;

        }


        .navbar-menu a {
            text-decoration: none;
            color: #333;
            padding: 8px;
            display: block;
        }

        .navbar-menu .dropdown:hover .dropdown-menu {
            display: block;
        }
        .row {
            bottom: 0;
            position: fixed;
            background-color: rgba(210, 210, 210, 0.8);
            height: 250px;
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            /* or space-around / center */
            gap: 10px;
            /* spacing between boxes */
            padding: 0% 2% 0% 2%;
        }

        .box {
            background: #ccc;
            padding: 20px;
            flex: 1;
            /* all divs take equal width */
            text-align: center;
            /* width: 200px; */
            height: auto;
            margin: 2% 1.5% 2% 1.5%;
        }
        .text-size{
            text-align: center;
            font-size: 18px;
        }
    </style>
</head>

<body>
    <nav>
        <div class="navbar">
            <div class="navbar-brand">YATRASTAY HOTEL</div>
            <ul class="navbar-menu" id="navbarMenu">
                <li><a href="index.php">HOME</a></li>
                <li><a href="Accomodation.php">ACCOMMODATION</a></li>
                <li><a href="Dining.php">DINING</a></li>
                <li><a href="logout.php">LOGOUT</a></li>
                <!-- <li><a href="#" class="disabled">Disabled</a></li> -->
            </ul>
        </div>
    </nav>
    <div class="text-size">
    <?php 
    if($available){
        echo "🛏️ Available '$room_type' rooms for your selected dates: <b>$available_room</b><br><br>";
    } else {
        echo "⚠️ Sorry, no available rooms of type '$room_type' for selected dates.<br><br>";
    }
        
    if ($available_room > 0) { ?>
        <form method="POST" action="book_room.php">
            <!-- Retain original values -->
            <input type="hidden" name="check_in" value="<?= $check_in ?>">
            <input type="hidden" name="check_out" value="<?= $check_out ?>">
            <input type="hidden" name="room_type" value="<?= $room_type ?>">

            <label>Select number of rooms</label>
            <select name="roomsno" onchange="this.form.submit()" class="text-size">
                <option value="">-- Please choose --</option>
                <?php for ($i = 1; $i <= min(3, $available_room); $i++) { ?>
                    <option value="<?= $i ?>" <?= ($room_numbers == $i) ? 'selected' : '' ?>><?= $i ?> room<?= $i > 1 ? 's' : '' ?>
                        available</option>
                <?php } ?>
            </select>
        </form>
        <br>
        <?php
        if ($alert) {
            echo '<div >' . $priceAlert . '</div>';
        }
        ?>
        <form method="POST" action="book_room.php">
            <input type="hidden" name="check_in" value="<?= $check_in ?>">
            <input type="hidden" name="check_out" value="<?= $check_out ?>">
            <input type="hidden" name="room_type" value="<?= $room_type ?>">
            <input type="hidden" name="roomsno" value="<?= $room_numbers ?>">
            <br>
            
            <button type="submit" name="confirm_btn" class="text-size">Confirm Booking</button>
            
        </form>

    <?php } ?>
    <?php

    if ($succAlert) {
        echo '<div class="alert-success">'
            . $successAlert . '
    </div>';
    }

    

    ?>

<br>
    <br>
    <br>

    <div class="row">
        <div class="box">
            <h3>Registered Office</h3><br>
            Plot No.380,<br>
            YATRASTAY HOTEL,<br>
            Near Goyal Plaza,
            Bodakdev,Ahmedabad
            Thaltej Road, Ahmedabad,<br><br>
            Ph: 079-26841000
        </div>
        <div class="box">
            <h3>Connect With Us</h3><br>
            Share your experiences,<br> feedback and photos on our social networks.
        </div>
        <div class="box">
            <h3>Contact Us</h3><br>
            Ph: 9999999999 (Ahmedabad)<br>
            contact@yatrastay.com
        </div>
        <div class="box">
            <h3>Follow Us</h3><br>
            <a href="https://www.instagram.com/yatrastay/"><img src="images\logo\ig.png" class="imgs"></a>
            <a href="https://www.facebook.com/yatrastay/"><img src="images\logo\fb.png" class="imgs"></a> <br>
            <a href="https://www.youtube.com/yatrastay/"><img src="images\logo\yt.png" class="imgs"></a>
            <a href="https://www.x.com/yatrastay/"><img src="images\logo\X.png" class="imgs"></a> <br>


        </div>
    </div>

</body>

</html>
