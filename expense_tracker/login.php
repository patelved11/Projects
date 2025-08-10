<?php
$alert=false;
include "db2.php";
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $email = $_POST["email"];
    $password = $_POST["password"];
    $sql_users = "Select * from users where email='$email' AND password='$password'";
    $result_users = mysqli_query($conn, $sql_users);
    $num_users = mysqli_num_rows($result_users);
    if ($num_users > 0) {
        session_start();
        
        session_start();
        $_SESSION['loggedin'] = true;
        $_SESSION['email'] = $email;
        header("location: index.php");
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
    </style>
</head>

<body>
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