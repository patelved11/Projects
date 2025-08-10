<?php
$login = false;
$showError = false;
if($_SERVER["REQUEST_METHOD"] == "POST"){
    include 'db.php';
    $email = $_POST["email"];
    $password = $_POST["password"]; 
    
     
    $sql = "Select * from admin_users where (email='$email' OR mobile='$email') AND password='$password'";
    $result = mysqli_query($conn, $sql);
    $num = mysqli_num_rows($result);
    if ($num == 1){
        $login = true;
        session_start();
        $_SESSION['admin_loggedin'] = true;
        $_SESSION['email'] = $email;
        header("location: index.php");

    } 
    else{
        $showError = "Invalid Credentials";
    }
} 
?>

<!DOCTYPE html>
<html>

<head>
    <title>login</title>
    <!-- <link rel="stylesheet" href="style.css"> -->
    <style>
        * {
    box-sizing: border-box;
    margin: 0;
    padding: 0;
}

body {
    font-family: Arial, sans-serif;
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
.marginn {
    margin: 3% 1%;
}
body {
    background-image: url("images/bgimg.jpg");
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
    <div class="form" >
        <form method="POST" class="marginn" action="login.php">
            <h3>Login From</h3>
            <br>
            <label for="email">
                E-mail or Mobile No<br>
            </label>
            <input type="text" id="email" name="email" placeholder="Enter your Email or Mobile " required class="marginn">
            <br>
            <label for="password" class="marginn">
                Password <br>
            </label>
            <input type="password" id="password" name="password" placeholder="Enter password" required class="marginn">
            <br>
            <button type="submit" class="marginn">Login</button>
            <button onclick="window.location.href='register.php';" style="margin-left:39%">Register</button> 
            <!-- <a href="register.php" style="border: 2px solid rgba(0, 0, 0, 0.8);background-color:white;">Register</a>  -->

        </form>
    </div>
</body>

</html>