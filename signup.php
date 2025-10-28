<?php
session_start();

// Database connection
$servername = "127.0.0.1";
$username = "root"; // your DB username
$password = "";     // your DB password
$dbname = "testdb"; // your database name

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// ---------- PART 1: Check username ----------
if (isset($_POST['check_user'])) {
    $user = trim($_POST['username']);
    $pass = trim($_POST['password']);

    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->bind_param("s", $user);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $message = "<p style='color:red;'>Username already exists!</p>";
    } else {
        $_SESSION['temp_user'] = $user;
        $_SESSION['temp_pass'] = password_hash($pass, PASSWORD_DEFAULT);
        $message = "<p style='color:green;'>Username available! Please complete your profile below.</p>";
        $show_second_form = true;
    }
    $stmt->close();
}

// ---------- PART 2: Save full user info ----------
if (isset($_POST['complete_signup'])) {
    if (isset($_SESSION['temp_user']) && isset($_SESSION['temp_pass'])) {
        $user = $_SESSION['temp_user'];
        $pass = $_SESSION['temp_pass'];
        $name = trim($_POST['fullname']);
        $age = trim($_POST['age']);
        $email = trim($_POST['email']);

        $insert = $conn->prepare("INSERT INTO users (username, password, fullname, age, email) VALUES (?, ?, ?, ?, ?)");
        $insert->bind_param("sssis", $user, $pass, $name, $age, $email);

        if ($insert->execute()) {
            $message = "<p style='color:green;'>Signup complete! Welcome, $name 🎉</p>";
            session_destroy(); // clear temp data
        } else {
            $message = "<p style='color:red;'>Error saving data: " . $conn->error . "</p>";
        }

        $insert->close();
    } else {
        $message = "<p style='color:red;'>Session expired. Please start again.</p>";
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Signup Page</title>
    <style>
        body {
            font-family: Arial;
            background: #f2f2f2;
            display: flex;
            height: 100vh;
            justify-content: center;
            align-items: center;
        }
        form {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px gray;
            width: 300px;
        }
        input {
            margin: 5px 0;
            padding: 10px;
            width: 100%;
        }
        button {
            padding: 10px;
            background: #0277bd;
            border: none;
            color: white;
            border-radius: 5px;
            cursor: pointer;
        }
        button:hover {
            background: #01579b;
        }
        h2 {
            text-align: center;
        }
    </style>
</head>
<body>
    <form method="POST">
        <h2>Sign Up</h2>
        <?php if (isset($message)) echo $message; ?>

        <?php if (empty($show_second_form)): ?>
            <!-- First Form -->
            <input type="text" name="username" placeholder="Enter Username" required>
            <input type="password" name="password" placeholder="Enter Password" required>
            <button type="submit" name="check_user">Next</button>

        <?php else: ?>
            <!-- Second Form -->
            <input type="text" name="fullname" placeholder="Full Name" required>
            <input type="number" name="age" placeholder="Age" required>
            <input type="email" name="email" placeholder="Email" required>
            <button type="submit" name="complete_signup">Complete Signup</button>
        <?php endif; ?>
    </form>
</body>
</html>