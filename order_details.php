<?php
session_start();
require_once 'config/init.php';

if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

$order_id = intval($_GET['id'] ?? 0);
$user_id = $_SESSION['user_id'];

// Merr detajet e porosisë
$sql = "SELECT o.*, 
               (SELECT SUM(quantity) FROM order_items WHERE order_id = o.id) as total_items 
        FROM orders o 
        WHERE o.id = $order_id AND o.user_id = $user_id";
$result = $conn->query($sql);

if ($result->num_rows === 0) {
    header('Location: profile.php');
    exit;
}

$order = $result->fetch_assoc();

// Merr produktet e porosisë
$items_sql = "SELECT oi.*, p.name, p.image, p.sku 
              FROM order_items oi 
              LEFT JOIN products p ON oi.product_id = p.id 
              WHERE oi.order_id = $order_id";
$items_result = $conn->query($items_sql);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Detajet e porosisë #<?php echo $order['order_number']; ?></title>
    <!-- Këtu vendos CSS-in tënd -->
</head>
<body>
    <!-- Përmbajtja e faqes -->
</body>
</html>