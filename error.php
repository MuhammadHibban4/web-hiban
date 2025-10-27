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

// Simpan order utama
$stmt = $pdo->prepare("INSERT INTO orders (customer_name, phone, visit_date, total) VALUES (?,?,?,?)");
$stmt->execute([$customer, $phone, $visit, $total]);
$order_id = $pdo->lastInsertId();

// Simpan item order
$stmt = $pdo->prepare("INSERT INTO order_items (order_id, food_id, name, price, quantity, subtotal) VALUES (?,?,?,?,?,?)");

if ($buy_now) {
    $stmt->execute([
        $order_id,
        $buy_now['food_id'] ?? null,
        $buy_now['name'],
        $buy_now['price'],
        $buy_now['qty'],
        $buy_now['price'] * $buy_now['qty']
    ]);
    unset($_SESSION['buy_now']);
} else {
    foreach ($cart as $it) {
        $stmt->execute([
            $order_id,
            $it['food_id'] ?? null,
            $it['name'],
            $it['price'],
            $it['qty'],
            $it['price'] * $it['qty']
        ]);
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
      border-radius: 12px;
    }
    h1 {
      font-size: 28px;
      margin-top: 30px;
    }
  </style>
</head>
<body>
  <div class="navbar">
    <strong>Warung Kreasi</strong>
    <a href="index.php" style="color:white;text-decoration:none;">Kembali ke Beranda</a>
  </div>
  <h1>ðŸŽ‰ Pesanan Anda Berhasil!</h1>
  <p>Terima kasih, <?= htmlspecialchars($customer) ?>. Pesanan Anda telah disimpan.</p>
</body>
</html>
