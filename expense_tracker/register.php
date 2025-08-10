
<!DOCTYPE html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Expense Tracker - Login</title>
    <style>
        body {
            height: 100vh;
            margin: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            font-family: Arial, sans-serif;
        }

        .login {
            padding: 20px;
            text-align: center;
            border: 2px solid black;
            border-radius: 15px;
            background: #f9f9f9;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
            width: 300px;
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
    </style>
</head>

<body>
    <div class="login">
        <form method="POST">
            <label for="name">Name :</label>
            <input type="text" id="name" name="name" placeholder="Enter Name" required>
          
            <label for="email">E-mail :</label>
            <input type="email" id="email" name="email" placeholder="Enter E-mail" required>

            <label for="password">Password :</label>
            <input type="password" id="password" name="password" placeholder="Enter Password" required>

            <button type="submit">Login</button>
        </form>
    </div>
</body>

</html>
