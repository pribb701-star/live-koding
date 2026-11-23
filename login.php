<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$host = "localhost";
$user = "root";
$pass = "";
$db   = "db_perpus";

$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}

if (isset($_GET['logout'])) {
    $_SESSION = [];
    session_destroy();
    header("Location: login.php");
    exit;
}

function e(?string $str): string {
    return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        $error = 'Email dan password wajib diisi.';
    } else {
        $stmt = mysqli_prepare($conn, "SELECT id_user, email, pass, role, nama, kelas FROM tb_user WHERE email = ? LIMIT 1");
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $userRow = mysqli_fetch_assoc($result);

        if ($userRow && hash_equals($userRow['pass'], md5($password))) {
            $_SESSION['user'] = [
                'id_user' => (int) $userRow['id_user'],
                'email'   => $userRow['email'],
                'role'    => $userRow['role'],
                'nama'    => $userRow['nama'],
                'kelas'   => $userRow['kelas'],
            ];
            header("Location: login.php");
            exit;
        } else {
            $error = 'Email atau password salah.';
        }
    }
}

$currentUser = $_SESSION['user'] ?? null;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 420px; margin: 60px auto; padding: 0 16px; }
        .box { border: 1px solid #ddd; padding: 24px; border-radius: 8px; }
        label { display: block; margin-top: 12px; font-weight: bold; }
        input { width: 100%; padding: 8px; margin-top: 4px; box-sizing: border-box; }
        button { margin-top: 16px; width: 100%; padding: 10px; cursor: pointer; }
        .error { color: #b91c1c; background: #fee2e2; padding: 10px; border-radius: 4px; margin-bottom: 12px; }
        .logout { display: inline-block; margin-top: 16px; background: #dc2626; color: #fff; text-decoration: none; padding: 8px 14px; border-radius: 4px; }
    </style>
</head>
<body>
<div class="box">
    <?php if ($currentUser): ?>
        <h2>Halo, <?= e($currentUser['nama'] ?? '') ?></h2>
        <p><strong>ID User:</strong> <?= e((string) ($currentUser['id_user'] ?? '')) ?></p>
        <p><strong>Email:</strong> <?= e($currentUser['email'] ?? '') ?></p>
        <p><strong>Role:</strong> <?= e($currentUser['role'] ?? '') ?></p>
        <p><strong>Kelas:</strong> <?= e($currentUser['kelas'] ?? '-') ?></p>
        <a class="logout" href="login.php?logout=1">Logout</a>
    <?php else: ?>
        <h2>Login</h2>
        <?php if ($error !== ''): ?>
            <div class="error"><?= e($error) ?></div>
        <?php endif; ?>
        <form method="POST">
            <label>Email</label>
            <input type="email" name="email" required placeholder="rani@siswa.sch.id">

            <label>Password</label>
            <input type="password" name="password" required placeholder="••••••••">

            <button type="submit">Masuk</button>
        </form>
    <?php endif; ?>
</div>
</body>
</html>