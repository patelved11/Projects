<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] != true) {
    header("location: index.php");
    exit;
} else {
    $id = $_SESSION['user_id'];
}

include "db.php";
$sql = "SELECT * FROM books where user_id='$id' ";
$result = $conn->query($sql);

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['Save'])) {
    $book_name = $_POST["bookname"];

    $sql = "INSERT INTO `books` (`book_name`, `user_id`) VALUES ('$book_name', '$id');";
    if (mysqli_query($conn, $sql)) {
        $_SESSION['success'] = "Book added successfully!";
        header("Location: books.php");
        exit;
    } else {
        $_SESSION['error'] = "Error: " . mysqli_error($conn);
        header("Location: books.php");
        exit;
    }
}
if (isset($_POST['book_btn'])) {
    list($bookName, $book_id) = explode('|', $_POST['book_btn']);
    $_SESSION['bookname'] =$bookName;
    $_SESSION['book_id'] = $book_id;
    header("Location: dashboard.php");
}

?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Expense Tracker Books</title>
    <style>
        .navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background-color: #4A90E2;
            /* padding: 10px 20px; */
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

        .navbar-menu a {
            text-decoration: none;
            color: white;
            display: block;
            text-decoration: none;
        }

        .navbar-menu a:hover {
            color: #000;
            border-bottom: 2px solid #333;
            transition: 0.3s;
        }

        /* .navbar-menu a:hover {
            color: #000;
            border-bottom: 2px solid #333;
            transition: 0.3s;
        }  */

        body {
            margin: 0;
            background-color: #F5F7FA;
            font-family: Arial, sans-serif;
            color: #333333;
        }

        .container {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            align-items: center;
            min-height: calc(100vh - 300px);
            align-items: stretch;
        }


        /* .details {
            width: 300px;
            background-color: #ccc;
            text-align: center;
            padding: 0px 5px 25px 5px;
        } */
        .details {
            width: 300px;
            background-color: white;
            padding: 0 20px 20px 20px;
            margin: 10px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        button {
            margin: 10px;
            /* background-color: #4CAF50; */
            background-color: #4A90E2;
            /* Green */
            color: white;
            height: 50px;
            min-width: 150px;
            /* padding: 10px 20px; */
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: 0.3s ease;
        }

        button:hover {
            /* background-color: #45a049; */
            /* Darker green on hover */
            transform: scale(1.05);
            /* Slight zoom */
        }
    </style>
    </style>
</head>

<body>
    <nav>
        <div class="navbar">
            <div class="navbar-brand">Expense-Tracker</div>
            <ul class="navbar-menu">
                <li><a href="logout.php">Logout</a> </li>
            </ul>
        </div>
    </nav>
    <?php
    if (isset($_SESSION['success'])) {
        echo "<p style='color: green; text-align:center;'>" . $_SESSION['success'] . "</p>";
        unset($_SESSION['success']);
    }
    if (isset($_SESSION['error'])) {
        echo "<p style='color: red; text-align:center;'>" . $_SESSION['error'] . "</p>";
        unset($_SESSION['error']);
    }
    ?>

    <h1 style="text-align:center;">Books</h1>
    <div class="container">
        <div class="details">
            <h2>Select book</h2>
            <form method="POST" action="">
                <?php
                if ($result->num_rows > 0) {
                    while ($rows = $result->fetch_assoc()) {
                        // Combine two values using a separator (like "|")
                        $combinedValue = $rows["book_name"] . "|" . $rows["book_id"];
                        ?>
                        <button type="submit" name="book_btn" value="<?php echo $combinedValue; ?>">
                            <?php echo $rows["book_name"]; ?>
                        </button><br>
                        <?php
                    }
                } else {
                    echo "No books";
                }
                ?>
            </form>
        </div>
        <div class="details">
            <h2>Add new Book</h2>
            <br><br><br>
            <form method="POST">
                <label for="book">New Book Name :</label><br>
                <input type="text" id="book" name="bookname" placeholder="Enter new Book" required>
                <button type="submit" name="Save"
                    style="height:30px; width:100px; background-color:#ccc; color:#000;">Save</button>
            </form>
        </div>
    </div>
    <br><br>
</body>

</html>