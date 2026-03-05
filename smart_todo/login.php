<?php
session_start();
$conn = new mysqli("localhost", "root", "", "todo_bd");
$error = "";

if (isset($_POST['login'])) {
    $user = $_POST['user'];
    $pass = $_POST['pass'];
    $stmt = $conn->prepare("SELECT id, password FROM users WHERE username = ?");
    $stmt->bind_param("s", $user);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($row = $res->fetch_assoc()) {
        if (password_verify($pass, $row['password'])) {
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['username'] = $user;
            header("Location: index.php");
        } else { $error = "Wrong password!"; }
    } else { $error = "User not found!"; }
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
        <h2 class="text-3xl font-bold text-indigo-600 mb-6 text-center">Login</h2>
        <?php if($error) echo "<p class='text-red-500 mb-4 text-center'>$error</p>"; ?>
        <input type="text" name="user" placeholder="Username" class="w-full border p-3 rounded-xl mb-4 outline-none focus:ring-2 focus:ring-indigo-400" required>
        <input type="password" name="pass" placeholder="Password" class="w-full border p-3 rounded-xl mb-6 outline-none focus:ring-2 focus:ring-indigo-400" required>
        <button name="login" class="w-full bg-indigo-600 text-white py-3 rounded-xl font-bold hover:bg-indigo-700 transition">Sign In</button>
        <p class="mt-4 text-center text-sm">New here? <a href="register.php" class="text-indigo-600 font-bold">Register</a></p>
    </form>
</body>
</html>