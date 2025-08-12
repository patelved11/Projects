<?php
$today = date("Y-m-d");
include "db.php";
$expense = false;
$income = false;
$newcatagory = false;

session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] != true) {
    header("location: index.php");
    exit;
} else {
    $user_id = $_SESSION['user_id'];
}
$bookName = $_SESSION['bookname'];
$book_id = $_SESSION['book_id'];
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $category_name = $_POST['category_name'] ?? '';
    $date = $_POST['date'] ?? $today; // backdated or today
    $description = $_POST['description'] ?? '';
    $amount = $_POST['amount'] ?? 0;
}



if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['income'])) {
    $income = true;
    $expense = false;
}
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['expense'])) {
    $income = false;
    $expense = true;
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['savein'])) {

    // ✅ Insert the new entry FIRST (only once)
    $sql = "INSERT INTO `expenses` 
            (`user_id`, `book_id`, `date`, `description`, `category_name`, `income`, `balance`) 
            VALUES ('$user_id', '$book_id', '$date', '$description', '$category_name', '$amount', '0')";

    if ($conn->query($sql) === TRUE) {
        // ✅ Recalculate all balances
        $balance = 0;
        $fetch_sql = "SELECT * FROM expenses
                      WHERE user_id = $user_id AND book_id = $book_id
                      ORDER BY date, id";
        $result = $conn->query($fetch_sql);

        while ($row = $result->fetch_assoc()) {
            $income = floatval($row['income']);
            $expense = floatval($row['expense']);
            $balance += $income - $expense;

            $update_sql = "UPDATE expenses 
                           SET balance = $balance 
                           WHERE id = " . $row['id'];
            $conn->query($update_sql);
        }

        echo "✅ Income entry added and balances updated.";
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();

    } else {
        echo "❌ Error inserting: " . $conn->error;
    }
}
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['saveout'])) {

    // ✅ Insert the new entry FIRST (only once)
    $sql = "INSERT INTO `expenses` 
            (`user_id`, `book_id`, `date`, `description`, `category_name`, `expense`, `balance`) 
            VALUES ('$user_id', '$book_id', '$date', '$description', '$category_name','$amount','0')";

    if ($conn->query($sql) === TRUE) {
        // ✅ Recalculate all balances
        $balance = 0;
        $fetch_sql = "SELECT * FROM expenses
                      WHERE user_id = $user_id AND book_id = $book_id
                      ORDER BY date, id";
        $result = $conn->query($fetch_sql);

        while ($row = $result->fetch_assoc()) {
            $income = floatval($row['income']);
            $expense = floatval($row['expense']);
            $balance += $income - $expense;

            $update_sql = "UPDATE expenses 
                           SET balance = $balance 
                           WHERE id = " . $row['id'];
            $conn->query($update_sql);
        }

        echo "✅ Expense entry added and balances updated.";
        ;
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();

    } else {
        echo "❌ Error inserting: " . $conn->error;
    }
}

// $user_id = (int)$user_id;

// $sql_category = "SELECT * FROM categories";
$sql_category = "SELECT * FROM categories WHERE users_id IN (1,{$user_id})";
$result_category = $conn->query($sql_category);

if (isset($_GET['username']) && isset($_GET['cancel'])) {
    $expense = false;
    $income = false;
    $newcatagory = false;
}
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['save'])) {
    $newcatagory = true;
}
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['savecategory'])) {
    $new_category = $_POST['category'];
    $sql = "INSERT INTO `categories` (`id`, `categories`, `users_id`) VALUES (NULL, '$new_category', '$user_id');";
    // $sql = "INSERT INTO `categories` (`categories`) VALUES ('$new_category');";
    if (mysqli_query($conn, $sql)) {
        $_SESSION['success'] = "Category added successfully!";
        header("Location: dashboard.php");
        exit;
    } else {
        $_SESSION['error'] = "Error: " . mysqli_error($conn);
        header("Location: dashboard.php");
        exit;
    }
}



$sql_data = "SELECT * FROM expenses WHERE user_id = $user_id AND book_id = $book_id ORDER BY date, id";
$result_data = $conn->query($sql_data);




?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Expence Tracker</title>
    <style>
        body {
            margin: 0;
            background-color: #F5F7FA;
            font-family: Arial, sans-serif;
            color: #333333;
        }

        .text-center {
            text-align: center;
        }

        .text-size {
            /* font-size: 1.25rem; */
            font-size: clamp(1rem, 2vw, 1.3rem);
        }

        .btn-div {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }


        .form {
            align-items: center;
            text-align: center;
            background-color: rgba(229, 234, 227, 1);
            width: fit-content;
            margin: 0 auto;
            border: 2px;
            border-radius: 15px;
            padding: 0 30px;
        }

        .marginn {
            margin: 10px 0 10px 0;
            font-size: clamp(1rem, 2.5vw, 1.9rem);
            /* font-size: 1rem; */
            /* font-size: 1.875rem; */
        }

        .button {
            background-color: #04AA6D;
            /* Green */
            border: none;
            color: white;
            padding: 15px 32px;
            text-align: center;
            text-decoration: none;
            display: inline-block;
            font-size: 1rem;
            margin: 4px 2px;
            cursor: pointer;
        }

        .button2 {
            background-color: #f44336;
        }

        .cancel-btn {
            text-align: right;
            padding-top: 15px;
            padding-right: 15px;
        }

        .table-container {
            width: 100%;
            overflow-x: auto;
        }

        table {
            margin: 10px auto 0 auto;
            width: 80%;
            border-collapse: collapse;
            min-width: 100px;
        }

        th,
        td {
            border: 1px solid #ccc;
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
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

        .navbar-menu button:hover {
            color: #000;
            border-bottom: 2px solid #333;
            transition: 0.3s;
            background: transparent;
            /* optional: prevent background change */
            cursor: pointer;
            /* pointer on hover like links */
        }

        .save-btn {
            font-size: 1.12rem;
            border: none;
            color: white;
            background-color: #4A90E2;
        }

        .add-category {

            /* display: flex; */
            /* justify-content: center; */
            text-align: center;
            background-color: rgba(229, 234, 227, 1);
            border-radius: 15px;
            /* width: 600px; */
            width: fit-content;
            margin: 0 auto;
            border: 2px;
            padding: 5px 30px 30px 30px;

        }

        .scroll-container {
            overflow: auto;
            white-space: nowrap;
            padding: 10px;
        }
    </style>
</head>

<body>
    <nav>
        <div class="navbar">
            <div class="navbar-brand">Expense-Tracker</div>
            <ul class="navbar-menu">
                <li>
                    <form method="POST"><button type="submit" name="save" class="save-btn">Add Category</button></form>
                </li>
                <li><a href="books.php">Books</a> </li>
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
    <?php if ($newcatagory) { ?>
        <div class="add-category">
            <form class="cancel-btn">
                <button type="submit" name="cancel" style="font-size: clamp(.5rem, 1.5vw, 1rem);">Cancel</button>
            </form>
            <h1>Add New Category</h1>
            <form method="POST" style="margin:0 auto;">
                <label for="category">Add New Category :</label><br><br>
                <input type="text" id="category" name="category" placeholder="Category Name"><br><br>
                <button type="submit" name="savecategory">Save</button>
            </form>
        </div>
    <?php } ?>
    <form method="POST">
        <div class="btn-div">
            <div class="in">
                <button type="submit" name="income" class="text-size button">Cash In</button>
            </div>
            <div class="out">
                <button type="submit" name="expense" class="text-size button button2">Cash Out</button>
            </div>
        </div>
    </form>
    <br><br>
    <?php if ($income) { ?>
        <div class="form">
            <form class="cancel-btn">
                <button type="submit" name="cancel" style="font-size:20px;">Cancel</button>
            </form>
            <h3 style="color:#04AA6D;font-size:34px;">Cash In</h3>
            <form method="POST">
                <label for="income" class="marginn">Amount :</label>
                <input type="text" id="income" name="amount" placeholder="Amount" required class="marginn"><br>

                <label for="description" class="marginn">Remark :</label>
                <input type="text" id="description" name="description" placeholder="Remark" required class="marginn"><br>

                <label for="description" class="marginn">Category :</label>
                <select name="category_name" class="marginn" required>
                    <option value="">-- Select category --</option>
                    <?php while ($rows_category = $result_category->fetch_assoc()) { ?>
                        <option value="<?= $rows_category['categories'] ?>"> <?= $rows_category['categories'] ?></option>
                    <?php } ?>
                </select>
                <br>
                <!-- <label for="date">Choose a date:</label> -->
                <input type="date" name="date" id="date" class="marginn" value="<?php echo $today; ?>"><br>

                <button type="submit" name="savein" class="marginn">Save</button>
            </form><br>
        </div>
    <?php } ?>

    <?php
    // Re-query categories before second form
    $result_category = $conn->query($sql_category);

    if ($expense) { ?>
        <div class="form">
            <form class="cancel-btn">
                <button type="submit" name="cancel" style="font-size:20px;">Cancel</button>
            </form>
            <h3 style="color:#f44336;font-size:34px;">Cash Out</h3>
            <form method="POST">
                <label for="expense" class="marginn">Amount :</label>
                <input type="text" id="expense" name="amount" placeholder="Amount" required class="marginn"><br>

                <label for="description" class="marginn">Remark :</label>
                <input type="text" id="description" name="description" placeholder="Remark" required class="marginn"><br>

                <label for="description" class="marginn">Category :</label>
                <select name="category_name" class="marginn" required>
                    <option value="">-- Select category --</option>
                    <?php while ($rows_category = $result_category->fetch_assoc()) { ?>
                        <option value="<?= $rows_category['categories'] ?>"> <?= $rows_category['categories'] ?></option>
                    <?php } ?>
                </select>
                <br>
                <!-- <label for="date">Choose a date:</label> -->
                <input type="date" name="date" id="date" class="marginn" value="<?php echo $today; ?>"><br>

                <button type="submit" name="saveout" class="marginn">Save</button>
            </form><br>
        </div>
    <?php } ?>
    </div>

    <div>
        <h1 style="text-align:center;"><?php echo $bookName." Book";?></h1>
        <div class="scroll-container">
            <table>

                <tr>
                    <th>Date</th>
                    <th>Description</th>
                    <th>Category</th>
                    <th>Expense</th>
                    <th>Income</th>
                    <th>Balance</th>

                </tr>
                <!-- PHP CODE TO FETCH DATA FROM ROWS -->
                <?php
                // LOOP TILL END OF DATA
                while ($rows_data = $result_data->fetch_assoc()) {
                    ?>
                    <tr>
                        <!-- FETCHING DATA FROM EACH
                    ROW OF EVERY COLUMN -->

                        <td><?php echo $rows_data['date']; ?></td>
                        <td><?php echo $rows_data['description']; ?></td>
                        <td><?php echo $rows_data['category_name']; ?></td>
                        <td><?php echo $rows_data['expense']; ?></td>
                        <td><?php echo $rows_data['income']; ?></td>
                        <td><?php echo $rows_data['balance']; ?></td>

                    </tr>
                    <?php
                }
                ?>
            </table>
        </div>
    </div>

</body>

</html>