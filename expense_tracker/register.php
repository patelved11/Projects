<?php
$alert = false;
$succAlert = false;
include "db.php";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST["email"];
    $name = $_POST["name"];
    $password = $_POST["password"];
    $sql_users = "SELECT * FROM users WHERE email='$email'";
    $result_users = mysqli_query($conn, $sql_users);
    $num_users = mysqli_num_rows($result_users);
    if ($num_users > 0) {
        $alert = "You are already registered ..! ";
    } else {
        $sql = "INSERT INTO `users` (`name`, `email`, `password`) VALUES ('$name', '$email', '$password');";
        // MySQLi example
        if (mysqli_query($conn, $sql)) {
            $succAlert = "Registered SuccessFull ....";
            // header("Refresh:3; url=index.php"); 
            // Redirect after 5 seconds
        } else {
            $alert = "Error: " . $sql . "<br>" . mysqli_error($conn);
        }
    }
}

?>
<!DOCTYPE html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Expense Tracker - Login</title>
    <style>
        .login {
            /* padding-top: 10px; */
            padding: 0px 20px 20px 20px;
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

        .alert {
            margin: 0 auto;
            text-align: center;
            width: 300px;
            background-color: #f90707ff;
            color: white;
            font-size: 20px;
            padding: 10px;
            border: 1px;
            border-radius: 12px;
        }
        .success{
            background-color: #45a049;
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
                <li><a href="index.php">Login</a> </li>
            </ul>
        </div>
    </nav>
    <?php
    if ($alert) {
        echo ' <div class="alert">
            '.$alert.'
        </div> ';
    }
    if ($succAlert) {
        echo ' <div class="alert success">
            '.$succAlert.'
        </div> ';
    }
    ?>
    <div class="login">
        <form method="POST" action="register.php">
            <h1>Register</h1>
            <label for="name">Name :</label>
            <input type="text" id="name" name="name" placeholder="Enter Name" required>

            <label for="email">E-mail :</label>
            <input type="email" id="email" name="email" placeholder="Enter E-mail" required>

            <label for="password">Password :</label>
            <input type="password" id="password" name="password" placeholder="Enter Password" required>

            <button type="submit">Register</button>
        </form>
    </div>
</body>

</html>