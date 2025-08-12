<?php
$alert=false;
include "db.php";
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $email = $_POST["email"];
    $password = $_POST["password"];
    $sql_users = "Select * from users where email='$email' AND password='$password'";
    $result_users = mysqli_query($conn, $sql_users);

    // $num_users = mysqli_num_rows($result_users);
    if (mysqli_num_rows($result_users) == 1) {
        session_start();
        $user_data = mysqli_fetch_assoc($result_users); // fetch the matched user row

        $_SESSION['loggedin'] = true;
        $_SESSION['email'] = $email;
        $_SESSION['user_id'] = $user_data['user_id'];
        // echo $user_data['user_id'];
        header("location: books.php");
        exit;
    }else{
        $alert= "Invalid credintials";
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Expense Tracker - Login</title>
    <style>
       

        .login {
            /* padding-top: 10px; */
            padding:0px 20px 20px 20px;
            text-align: center;
            border: 2px solid black;
            border-radius: 15px;
            background: #f9f9f9;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
            width: 300px;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
        }

        label {
            font-size: 20px;
            display: block;
            margin-top: 15px;
        }

        input {
            width: 90%;
            padding: 8px;
            font-size: 18px;
            margin-top: 5px;
            border-radius: 5px;
            border: 1px solid #ccc;
        }

        button {
            margin-top: 15px;
            padding: 10px 20px;
            font-size: 20px;
            border: none;
            border-radius: 5px;
            background-color: #4CAF50;
            color: white;
            cursor: pointer;
        }

        button:hover {
            background-color: #45a049;
        }
        .alert-danger{
            margin:0 auto;
            text-align: center;
            width:300px;
            background-color: #f90707ff;
            color:white;
            font-size: 20px;
            padding: 10px;
            border: 1px;
            border-radius: 12px;
        }
        .navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background-color: #4A90E2;
            margin-bottom: 2%;
            color: white;
        }

        .navbar-brand {
            font-size: 1.38rem;
            font-weight: bold;
            padding-left: 1.5%;
        }

        .navbar-menu {
            list-style: none;
            padding-right: 1.5%;
            font-size: 1.12rem;
            display: flex;
        }
        .navbar-menu li {
            margin-right: 20px;
        }

        .navbar-menu a:hover {
            color: #000;
            border-bottom: 2px solid #333;
            transition: 0.3s;
        }


        .navbar-menu a {
            text-decoration: none;
            /* color: #333; */
            color: white;
            /* padding: 8px; */
            display: block;
        }
        body {
            margin: 0;
        }
    </style>
</head>

<body>
    <nav>
        <div class="navbar">
            <div class="navbar-brand">Expense-Tracker</div>
            <ul class="navbar-menu">
                <li><a href="register.php">Register</a> </li>
            </ul>
        </div>
    </nav>
    <?php  
        if($alert){
            echo ' <div class="alert-danger">
            Invalid credintials !
    </div> ' ;
        }
    ?>
        <div class="login">
            <h1>Login </h1>
            <form method="POST">
                <label for="email">E-mail :</label>
                <input type="email" id="email" name="email" placeholder="Enter E-mail" required>
                
                <label for="password">Password :</label>
                <input type="password" id="password" name="password" placeholder="Enter Password" required>
                
                <button type="submit">Login</button>
            </form>
        </div>
    
</body>

</html>