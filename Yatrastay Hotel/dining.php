<?php
session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] != true) {
    header("location: login.php");
    exit;
}


?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>YatraStay Dining</title>
    <!-- <link rel="stylesheet" href="style.css"> -->
    <style>
        /* img {
            border: 5px solid #555
        } */
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        .scroll-container {
            margin: 0% 4% 0% 4%;
            background-color: #333;
            overflow: auto;
            white-space: nowrap;
            padding: 10px;
        }

        body {
            background-image: url("images/bgimg.jpg");
        }

        .slides-container {
            padding: 10px;
        }

        .menu {
            border: 2px solid #333;
            height: 102%;
            margin: 1% 1% 1% 1%;
            border-radius: 10px;
        }

        .imgs {
            height: 50px;
            width: 50px;
            margin: 2.5% 2.5% 2.5% 2.5%
        }

        body {
            font-family: Arial, sans-serif;
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
    </style>

</html>

<body>

    <nav>
        <div class="navbar">
            <div class="navbar-brand">YATRASTAY HOTEL</div>
            <ul class="navbar-menu" id="navbarMenu">
                <li><a href="index.php">HOME</a></li>
                <!-- <li><a href="About_us.php">ABOUT US</a></li> -->
                <li><a href="Accomodation.php">ACCOMMODATION</a></li>
                <li><a href="logout.php">LOGOUT</a></li>
            </ul>
        </div>
    </nav>

    <div class="scroll-container">
        <img src="images/banner1.jpg" style="width:95%">
        <img src="images/banner2.jpg" style="width:95%">
        <img src="images/banner3.jpg" style="width:95%">
    </div>
    <div style="text-align:center; ">
        <h3>Scroll Right Side &#x2192 </h3>
    </div>
    <br><br>
    <div>
        <h1>DINING</h1>
        <p style="color:#333">Known for our scrumptious taste, explore various dining options we offer spread across
            different cities</p>

    </div>
    <div class="menu">
        <div style="display: flex; flex-wrap: wrap; gap: 1%;">
            <img src="images/dining.png" style="height:65vh;width:auto;margin:1% 1% 2% 1%;border: none;">
            <img src="images/menuu.png" style="height:65vh;width:45%;margin:1% 1% 2% 1%;border: none;">

        </div>
        <div style="float: right ;margin:-4% 3% 1% 1%;">
            <button onclick="window.open('Menu.pdf', '_blank')"
                style="background-color: rgba(237, 0, 0, 1); color: white;padding:15% 17% 15% 17%">MENU</button>
        </div>
    </div>
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