<?php
$conn = new mysqli("localhost", "root", "", "todo_bd");
$message = "";

if (isset($_POST['register'])) {
    $user = $_POST['user'];
    $pass = password_hash($_POST['pass'], PASSWORD_BCRYPT);
    
    $check = $conn->prepare("SELECT id FROM users WHERE username = ?");
    $check->bind_param("s", $user);
    $check->execute();
    if($check->get_result()->num_rows > 0) {
        $message = "Username already exists!";
    } else {
        $stmt = $conn->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
        $stmt->bind_param("ss", $user, $pass);
        $stmt->execute();
        header("Location: login.php");
    }
}
?>
<?php
// Railway provides these variables automatically once you add a MySQL service
$host = getenv('MYSQLHOST') ?: 'localhost';
$user = getenv('MYSQLUSER') ?: 'root';
$pass = getenv('MYSQLPASSWORD') ?: '';
$db   = getenv('MYSQLDATABASE') ?: 'todo_bd';
$port = getenv('MYSQLPORT') ?: '3306';

$conn = new mysqli($host, $user, $pass, $db, $port);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>    
    
<!DOCTYPE html>
<html lang="en">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-100 flex items-center justify-center min-h-screen p-4">
    <form method="POST" class="bg-white p-8 rounded-2xl shadow-xl w-full max-w-md">
        <h2 class="text-3xl font-bold text-indigo-600 mb-6 text-center">Join TaskFlow</h2>
        <?php if($message) echo "<p class='text-red-500 mb-4 text-center'>$message</p>"; ?>
        <input type="text" name="user" placeholder="Username" class="w-full border p-3 rounded-xl mb-4 outline-none focus:ring-2 focus:ring-indigo-400" required>
        <input type="password" name="pass" placeholder="Password" class="w-full border p-3 rounded-xl mb-6 outline-none focus:ring-2 focus:ring-indigo-400" required>
        <button name="register" class="w-full bg-indigo-600 text-white py-3 rounded-xl font-bold hover:bg-indigo-700 transition">Create Account</button>
        <p class="mt-4 text-center text-sm">Already have an account? <a href="login.php" class="text-indigo-600 font-bold">Login</a></p>
    </form>
</body>

</html>
