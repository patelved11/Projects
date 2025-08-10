<?php
session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] != true) {
    header("location: login.php");
    exit;
}


?>


<!DOCTYPE html>
<html>

<meta charset="UTF-8">

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Yatrastay Hotel</title>
    <!-- <link rel="stylesheet" href="style.css"> -->
    <style>
        p {
            padding: 0% 1% 0% 1%;
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

        h1 {
            font-size: 36px;
            color: #333;
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

        .scroll-container {
            margin: 0% 4% 0% 4%;
            background-color: #333;
            overflow: auto;
            white-space: nowrap;
            padding: 10px;
        }

        .slides-container {
            padding: 10px;
        }

        p {
            font-size: 20px;
            color: #333;
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
</head>

<body>
    <nav>
        <div class="navbar">
            <div class="navbar-brand">YATRASTAY HOTEL</div>
            <ul class="navbar-menu" id="navbarMenu">
                <!-- <li><a href="About_us.php">ABOUT US</a></li> -->
                <li><a href="Accomodation.php">ACCOMMODATION</a></li>
                <li><a href="Dining.php">DINING</a></li>
                <li><a href="logout.php">LOGOUT</a></li>
                <!-- <li><a href="#" class="disabled">Disabled</a></li> -->
            </ul>
        </div>
    </nav>
    <div style="margin:0% 5% 2% 5%;">
        <img src="images/2.PNG" style="width:100%;height:auto;">
    </div>
    <br>
    <div style="text-align: center;margin:0 4% 0 4%">
        <h1>Welcome to YATRASTAY</h1>
        <br>
        <p>
            The YATRASTAY is a hotels known for it’s excellent
            food, distinguished hospitality, magnificent banquet
            space and outdoor catering segment in India.
        </p>
        <p>
            Yatrastay was founded with a bold vision—to redefine the hospitality
            experience with elegance, innovation, and heartfelt service. Conceived
            by a team of passionate creators and led by a spirit of excellence,
            Yatrastay began its journey with a simple idea: to make every stay feel like home.
        </p>
        <p>
            Fueled by creativity and an unwavering commitment to quality, the brand has grown
            from a dream into a name that resonates with comfort and trust. From seamless design
            to immersive online experiences, Yatrastay blends technology with tradition—offering
            guests not just a place to stay, but a memory to cherish.
        </p>
        <p>
            Rooted in values and shaped by modern ambition, Yatrastay continues to rise in the hospitality landscape,
            setting
            new standards in the art of welcoming.
        </p>
    </div>
    <br>
    <br>
    <div style="margin: 0% 1% 0% 1%">
        <div>
            <div class="scroll-container">
                <img src="images/banner1.jpg" style="width:95%">
                <img src="images/banner4.png" style="width:95%">
                <img src="images/banner2.jpg" style="width:95%">
                <img src="images/banner3.jpg" style="width:95%">
            </div>
            <div class="heading"
                style="background-color:rgba(237, 0, 0, 1);float:right;margin-right:5%;margin-top:-80px;font-size:14px;backdrop-filter:blur(8px);">
                <a href="Dining.php" style="margin:0% 5px 0% 5px;color:rgba(10, 9, 9, 1);">EXPLORE</a>
            </div>
        </div>
    </div>
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