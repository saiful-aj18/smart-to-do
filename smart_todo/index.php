<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }
$conn = new mysqli("localhost", "root", "", "todo_bd");
$user_id = $_SESSION['user_id'];

// Actions
if (isset($_POST['add'])) {
    $stmt = $conn->prepare("INSERT INTO tasks (user_id, title, priority, due_date) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isss", $user_id, $_POST['title'], $_POST['priority'], $_POST['due_date']);
    $stmt->execute();
}
if (isset($_GET['del'])) { $conn->query("DELETE FROM tasks WHERE id=".$_GET['del']." AND user_id=$user_id"); }
if (isset($_GET['tog'])) { $conn->query("UPDATE tasks SET status = IF(status='Pending','Completed','Pending') WHERE id=".$_GET['tog']); }

// Stats
$res = $conn->query("SELECT status, COUNT(*) as c FROM tasks WHERE user_id=$user_id GROUP BY status");
$s = ['Pending'=>0, 'Completed'=>0];
while($r = $res->fetch_assoc()) $s[$r['status']] = $r['c'];
$total = $s['Pending'] + $s['Completed'];
$prog = ($total > 0) ? round(($s['Completed'] / $total) * 100) : 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TaskFlow Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        tailwind.config = { darkMode: 'class' }
        if (localStorage.theme === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark')
        }
    </script>
</head>
<body class="bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-slate-100 min-h-screen transition-colors duration-300 pb-10">

<nav class="bg-white dark:bg-slate-800 border-b dark:border-slate-700 p-4 sticky top-0 z-50">
    <div class="max-w-5xl mx-auto flex justify-between items-center">
        <h1 class="text-xl font-bold text-indigo-600 dark:text-indigo-400">TaskFlow</h1>
        <div class="flex gap-2">
            <button onclick="toggleTheme()" class="p-2 bg-slate-100 dark:bg-slate-700 rounded-lg">🌓</button>
            <a href="logout.php" class="bg-red-50 dark:bg-red-900/30 text-red-600 px-3 py-2 rounded-lg text-xs font-bold uppercase">Logout</a>
        </div>
    </div>
</nav>

<main class="max-w-5xl mx-auto p-4 grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="bg-white dark:bg-slate-800 p-5 rounded-2xl shadow-sm lg:order-2">
        <h2 class="font-bold mb-4">Your Progress</h2>
        <canvas id="chart"></canvas>
        <div class="mt-4 text-center font-black text-2xl"><?= $prog ?>% Done</div>
    </div>

    <div class="bg-white dark:bg-slate-800 p-5 rounded-2xl shadow-sm lg:order-1">
        <form method="POST" class="space-y-3">
            <input type="text" name="title" placeholder="What's next?" class="w-full border dark:border-slate-600 dark:bg-slate-700 p-3 rounded-xl outline-none" required>
            <div class="flex gap-2">
                <select name="priority" class="flex-1 border dark:border-slate-600 dark:bg-slate-700 p-3 rounded-xl text-sm">
                    <option>Low</option><option selected>Medium</option><option>High</option>
                </select>
                <input type="date" name="due_date" class="flex-1 border dark:border-slate-600 dark:bg-slate-700 p-3 rounded-xl text-sm">
            </div>
            <button name="add" class="w-full bg-indigo-600 text-white py-3 rounded-xl font-bold active:scale-95 transition">Add Task</button>
        </form>
    </div>

    <div class="lg:col-span-2 space-y-3 lg:order-3">
        <?php
        $tasks = $conn->query("SELECT * FROM tasks WHERE user_id=$user_id ORDER BY status DESC, id DESC");
        while($row = $tasks->fetch_assoc()):
            $done = $row['status'] == 'Completed';
            $pClr = ($row['priority'] == 'High') ? 'text-red-500 bg-red-50 dark:bg-red-900/20' : 'text-emerald-500 bg-emerald-50 dark:bg-emerald-900/20';
        ?>
        <div class="bg-white dark:bg-slate-800 p-4 rounded-xl flex items-center justify-between border dark:border-slate-700 <?= $done ? 'opacity-50' : '' ?>">
            <div class="flex items-center gap-3">
                <a href="?tog=<?= $row['id'] ?>" class="w-6 h-6 rounded-full border-2 flex items-center justify-center <?= $done ? 'bg-indigo-500 border-indigo-500' : 'border-gray-300' ?>">
                    <?php if($done) echo '✓'; ?>
                </a>
                <div>
                    <h3 class="font-medium <?= $done ? 'line-through' : '' ?>"><?= htmlspecialchars($row['title']) ?></h3>
                    <span class="text-[10px] font-bold px-1.5 py-0.5 rounded uppercase <?= $pClr ?>"><?= $row['priority'] ?></span>
                </div>
            </div>
            <a href="?del=<?= $row['id'] ?>" class="text-gray-400 hover:text-red-500">🗑️</a>
        </div>
        <?php endwhile; ?>
    </div>
</main>

<script>
    function toggleTheme() {
        if (document.documentElement.classList.contains('dark')) {
            document.documentElement.classList.remove('dark');
            localStorage.theme = 'light';
        } else {
            document.documentElement.classList.add('dark');
            localStorage.theme = 'dark';
        }
    }

    new Chart(document.getElementById('chart'), {
        type: 'doughnut',
        data: {
            datasets: [{
                data: [<?= $s['Pending'] ?>, <?= $s['Completed'] ?>],
                backgroundColor: ['#e2e8f0', '#6366f1'],
                borderWidth: 0
            }]
        },
        options: { cutout: '80%' }
    });
</script>
</body>
</html>