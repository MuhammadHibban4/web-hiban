<?php
session_start();
require 'db.php';

$customer = $_POST['customer_name'] ?? '';
$phone = $_POST['phone'] ?? '';
$visit = $_POST['visit_date'] ?? '';
$cart = $_SESSION['cart'] ?? [];
$buy_now = $_SESSION['buy_now'] ?? null;

if (!$customer || !$phone || !$visit) {
    die("Data tidak lengkap. Silakan kembali ke halaman checkout.");
}

$total = 0;
if ($buy_now) {
    $total = $buy_now['price'] * $buy_now['qty'];
} else {
    foreach ($cart as $it) $total += $it['price'] * $it['qty'];
}

// Simpan ke database
$stmt = $pdo->prepare("INSERT INTO orders (customer_name, phone, visit_date, total) VALUES (?,?,?,?)");
$stmt->execute([$customer, $phone, $visit, $total]);
$order_id = $pdo->lastInsertId();

if ($buy_now) {
    $stmt = $pdo->prepare("INSERT INTO order_items (order_id, name, price, quantity, subtotal) VALUES (?,?,?,?,?)");
    $stmt->execute([$order_id, $buy_now['name'], $buy_now['price'], $buy_now['qty'], $buy_now['price'] * $buy_now['qty']]);
    unset($_SESSION['buy_now']);
} else {
    $stmt = $pdo->prepare("INSERT INTO order_items (order_id, name, price, quantity, subtotal) VALUES (?,?,?,?,?)");
    foreach ($cart as $it) {
        $stmt->execute([$order_id, $it['name'], $it['price'], $it['qty'], $it['price'] * $it['qty']]);
    }
    unset($_SESSION['cart']);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Pesanan Berhasil | Warung Kreasi</title>
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap">
  <style>
    /* 🌿 WARUNG KREASI - PROCESS CHECKOUT STYLE 🌿 */
    body {
      font-family: "Poppins", sans-serif;
      background: linear-gradient(180deg, #f7fff8 0%, #eafaea 100%);
      color: #264d26;
      text-align: center;
      padding: 50px 20px;
    }

    .navbar {
      background: linear-gradient(90deg, #7dcf88, #56b870);
      color: white;
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 15px 40px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }

    .logo {
      font-size: 1.6em;
      font-weight: 600;
    }

    .container {
      background: #fff;
      max-width: 700px;
      margin: 60px auto;
      padding: 40px;
      border-radius: 18px;
      box-shadow: 0 8px 25px rgba(0,0,0,0.1);
    }

    h2 {
      color: #2f5e35;
      margin-bottom: 20px;
    }

    p {
      font-size: 1.05em;
      margin: 10px 0;
    }

    .btn {
      background: #57b565;
      color: #fff;
      border: none;
      padding: 14px 25px;
      border-radius: 10px;
      font-size: 1em;
      cursor: pointer;
      transition: 0.3s;
      text-decoration: none;
      display: inline-block;
      margin-top: 25px;
      font-weight: 500;
    }

    .btn:hover {
      background: #4da85c;
      transform: scale(1.05);
    }

    footer {
      background: #7dcf88;
      color: white;
      text-align: center;
      padding: 15px;
      margin-top: 50px;
      font-size: 0.9em;
      letter-spacing: 0.5px;
    }
  </style>
</head>
<body>

<nav class="navbar">
  <div class="logo">🍲 Warung Kreasi</div>
  <div class="nav-links">
    <a href="index.php" style="color:#fff;text-decoration:none;">Beranda</a>
  </div>
</nav>

<div class="container">
  <h2>✅ Pesanan Berhasil!</h2>
  <p>Terima kasih, <strong><?= htmlspecialchars($customer) ?></strong>!<br>
  Pesanan kamu sudah kami terima dan akan disiapkan sesuai tanggal kunjungan.</p>

  <p><strong>Total Pembayaran:</strong> Rp<?= number_format($total, 0, ',', '.') ?></p>

  <a href="receipt.php?id=<?= $order_id ?>" class="btn">Lihat Nota</a>
  <a href="index.php" class="btn" style="background:#a2a2a2;">Kembali ke Beranda</a>
</div>

<footer>© <?= date('Y') ?> Warung Kreasi | Nikmati cita rasa lokal 🌿</footer>

</body>
</html>
