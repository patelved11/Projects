<?php
$showAlert = false;
$showError = false;
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    include "db.php";
    $name = $_POST["name"];
    $mobile = $_POST["mobile"];
    $email = $_POST["email"];
    $password = $_POST["password"];

    $exists = false;
    // Check whether this username exists
    $existSql = "SELECT * FROM user WHERE email = '$email' OR mobile = '$mobile'";
    $result = mysqli_query($conn, $existSql);
    $numExistRows = mysqli_num_rows($result);
    if ($numExistRows > 0) {
        // $exists = true;
        // $showError = "E-mail Already Exists";
        $showError = true;
    } else {
        // $exists = false; 
            $sql = "INSERT INTO `user` (`name`, `mobile`, `email`, `password`, `date`) VALUES ('$name', '$mobile', '$email', '$password', current_timestamp());";
            $result = mysqli_query($conn, $sql);
            if ($result) {
                $showAlert = true;
            }
        
    }
}
?>
<!DOCTYPE html>
<html>

<head>
    <title>
        Registration
    </title>
    <!-- <link rel="stylesheet" href="style.css"> -->
    <style>
        * {
    box-sizing: border-box;
    margin: 0;
    padding: 0;
}
.marginn {
    margin: 3% 1%;
}
body {
    background-image: url("images/bgimg.jpg");
}

.form {
    border: 2px solid #000000;
    border-radius: 15px;
    padding: 1.5% 2% 1.5% 2%;
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    background-color: rgba(210, 210, 210, 0.8);
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
}

.navbar-menu li {
    margin-left: 20px;
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

        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            padding: 12px;
            border: 1px solid #f5c6cb;
            border-radius: 5px;
            width: 50%;
            margin: 20px auto;
            text-align: center;
        }
    </style>

</head>

<body>
    <?php
    if ($showAlert) {
        echo '<div class="alert-success">
        ✅ Registered successfully!
    </div>';
    }
    if ($showError) {
            echo ' <div class="alert-danger">
            E-mail or Mobile Number Already Exists
    </div> ';
        }
    ?>

    <div class="form">

        <form method="POST" action="">
            <h3>Register From</h3>
            <br>
            <label for="name" class="marginn">
                Name <br>
            </label>
            <input type="text" id="name" name="name" placeholder="Enter Your Name" required class="marginn">
            <br>
            <label for="mobile" class="marginn">
                Mobile Number<br>
            </label>
            <input type="tel" id="mobile" name="mobile" placeholder="Enter Your Mobile Number" required class="marginn">
            <br>
            <label for="email" class="marginn">
                E-mail <br>
            </label>
            <input type="email" id="email" name="email" placeholder="Enter your E-mail" required class="marginn">
            <br>
            <label for="password" class="marginn">
                Password <br>
            </label>
            <input type="password" id="password" name="password" placeholder="Enter Your Password" required
                class="marginn">
            <br>
            <button type="submit" class="marginn">Register</button>
            <button onclick="window.location.href='login.php';" style="margin-left:40%">Login</button>


        </form>

    </div>
</body>

</html>