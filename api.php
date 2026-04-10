<?php
session_start();
require_once 'config/init.php';

header('Content-Type: application/json');

// Kontrollo nëse databaza është e lidhur
if ($conn === null) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

$action = $_GET['action'] ?? '';

switch($action) {
    case 'get_products':
        $category = $_GET['category'] ?? '';
        $subcategory = $_GET['subcategory'] ?? '';
        $limit = intval($_GET['limit'] ?? 0);
        $type = $_GET['type'] ?? '';
        
        $sql = "SELECT p.*, c.name as category_name, c.slug as category_slug,
                       s.name as subcategory_name, s.slug as subcategory_slug
                FROM products p 
                LEFT JOIN categories c ON p.category_id = c.id 
                LEFT JOIN subcategories s ON p.subcategory_id = s.id
                WHERE 1=1";
        
        if ($category) {
            $category = $conn->real_escape_string($category);
            $sql .= " AND c.slug = '$category'";
        }
        
        if ($subcategory) {
            $subcategory = $conn->real_escape_string($subcategory);
            $sql .= " AND s.slug = '$subcategory'";
        }
        
        switch($type) {
            case 'discount':
                $sql .= " AND p.discount_percent > 0 ORDER BY p.discount_percent DESC";
                break;
            case 'cheapest':
                $sql .= " ORDER BY p.price ASC";
                break;
            case 'new':
                $sql .= " ORDER BY p.created_at DESC";
                break;
            case 'popular':
                $sql .= " ORDER BY p.views DESC";
                break;
            case 'featured':
                $sql .= " AND p.featured = 1 ORDER BY p.created_at DESC";
                break;
        }
        
        if ($limit > 0) {
            $sql .= " LIMIT $limit";
        }
        
        $result = $conn->query($sql);
        $products = [];
        
        if ($result) {
            while($row = $result->fetch_assoc()) {
                $products[] = $row;
            }
        }
        
        echo json_encode(['success' => true, 'data' => $products]);
        break;
        
    case 'get_product':
        $id = intval($_GET['id'] ?? 0);
        $slug = $_GET['slug'] ?? '';
        
        if ($id <= 0 && empty($slug)) {
            echo json_encode(['success' => false, 'message' => 'ID ose slug e produktit është e nevojshme']);
            break;
        }
        
        $sql = "SELECT p.*, c.name as category_name, c.slug as category_slug,
                       s.name as subcategory_name, s.slug as subcategory_slug
                FROM products p 
                LEFT JOIN categories c ON p.category_id = c.id 
                LEFT JOIN subcategories s ON p.subcategory_id = s.id 
                WHERE ";
        
        if ($id > 0) {
            $sql .= "p.id = $id";
        } else {
            $slug = $conn->real_escape_string($slug);
            $sql .= "p.slug = '$slug'";
        }
        
        $result = $conn->query($sql);
        
        if ($result && $result->num_rows > 0) {
            $product = $result->fetch_assoc();
            
            // Rrit numrin e shikimeve
            $updateSql = "UPDATE products SET views = views + 1 WHERE id = " . $product['id'];
            $conn->query($updateSql);
            
            echo json_encode(['success' => true, 'data' => $product]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Produkti nuk u gjet']);
        }
        break;
        
    case 'save_order':
        // Merr të dhënat nga POST
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input) {
            echo json_encode(['success' => false, 'message' => 'Nuk u dërguan të dhëna']);
            break;
        }
        
        // Gjenero një numër porosie unik
        $order_number = 'ORD-' . date('Ymd') . '-' . rand(1000, 9999);
        
        // Kontrollo nëse përdoruesi është i kyçur
        $user_id = 0;
        if (isset($_SESSION['user_id'])) {
            $user_id = $_SESSION['user_id'];
        }
        
        // Kontrollo nëse user_id ekziston në tabelën users
        $userCheckSql = "SELECT id FROM users WHERE id = $user_id";
        $userCheckResult = $conn->query($userCheckSql);
        if (!$userCheckResult || $userCheckResult->num_rows === 0) {
            // Krijo një përdorues të ri nëse user_id nuk ekziston
            $newUserSql = "INSERT INTO users (username, email, password, role, created_at, updated_at) VALUES ('anonymous', 'anonymous@example.com', 'password', 'user', NOW(), NOW())";
            if ($conn->query($newUserSql)) {
                $user_id = $conn->insert_id; // Merr ID-në e përdoruesit të ri
                error_log("Krijuar përdorues i ri me ID: $user_id");
            } else {
                error_log("Dështoi krijimi i përdoruesit të ri: " . $conn->error);
                echo json_encode(['success' => false, 'message' => 'Dështoi krijimi i përdoruesit të ri.']);
                break;
            }
        }
        
        $user_name = $conn->real_escape_string($input['name'] ?? '');
        $user_email = $conn->real_escape_string($input['email'] ?? '');
        $user_phone = $conn->real_escape_string($input['phone'] ?? '');
        $shipping_address = $conn->real_escape_string($input['address'] ?? '');
        $billing_address = $shipping_address; // Përdor të njëjtën adresë për faturim
        $payment_method = $conn->real_escape_string($input['payment'] ?? '');
        $notes = $conn->real_escape_string($input['notes'] ?? '');
        $products = $input['products'] ?? [];
        $total_amount = floatval($input['total'] ?? 0);
        
        // Validimi i të dhënave
        if (empty($user_name) || empty($user_email) || empty($user_phone) || empty($shipping_address) || empty($payment_method)) {
            echo json_encode(['success' => false, 'message' => 'Të dhënat e detyrueshme mungojnë']);
            break;
        }
        
        if (empty($products)) {
            echo json_encode(['success' => false, 'message' => 'Nuk ka produkte në porosi']);
            break;
        }
        
        // Krijo porosinë
        $orderSql = "INSERT INTO orders (
            order_number, user_id, user_name, user_email, user_phone,
            total_amount, status, payment_method, payment_status,
            shipping_address, billing_address, notes, created_at, updated_at
        ) VALUES (
            '$order_number', $user_id, '$user_name', '$user_email', '$user_phone',
            $total_amount, 'pending', '$payment_method', 'pending',
            '$shipping_address', '$billing_address', '$notes', NOW(), NOW()
        )";

        if (!$conn->query($orderSql)) {
            error_log("Order insertion failed: " . $conn->error);
            echo json_encode(['success' => false, 'message' => 'Order creation failed.']);
            break;
        }
        
        $orderId = $conn->insert_id;
        
        // Shto produktet në porosi
        $itemsAdded = 0;
        foreach ($products as $item) {
            $productId = intval($item['id']);
            $quantity = intval($item['quantity']);
            $price = floatval($item['price']);
            $subtotal = $price * $quantity;
            
            $itemSql = "INSERT INTO order_items (order_id, product_id, quantity, price, subtotal) 
                        VALUES ($orderId, $productId, $quantity, $price, $subtotal)";
            if ($conn->query($itemSql)) {
                $itemsAdded++;
                
                // Zvogëlo stokun e produktit
                $updateStockSql = "UPDATE products SET stock = stock - $quantity WHERE id = $productId";
                $conn->query($updateStockSql);
            }
        }
        
        // Fshi shportën e përdoruesit nëse është i kyçur
        if ($user_id > 0) {
            $clearCartSql = "DELETE FROM cart WHERE user_id = $user_id";
            $conn->query($clearCartSql);
        }
        
        echo json_encode([
            'success' => true, 
            'message' => 'Porosia u regjistrua me sukses', 
            'order_id' => $orderId,
            'order_number' => $order_number,
            'items_added' => $itemsAdded
        ]);
        break;
        
    case 'get_orders':
        // Kontrollo nëse përdoruesi është admin
        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
            echo json_encode(['success' => false, 'message' => 'Nuk keni autorizim']);
            break;
        }
        
        $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 0;
        $status = isset($_GET['status']) ? $conn->real_escape_string($_GET['status']) : '';
        
        $sql = "SELECT o.*, 
                       (SELECT COUNT(*) FROM order_items WHERE order_id = o.id) as total_items 
                FROM orders o 
                WHERE 1=1";
        
        if ($status) {
            $sql .= " AND o.status = '$status'";
        }
        
        $sql .= " ORDER BY o.created_at DESC";
        
        if ($limit > 0) {
            $sql .= " LIMIT $limit";
        }
        
        $result = $conn->query($sql);
        $orders = [];
        
        if ($result) {
            while($row = $result->fetch_assoc()) {
                // Merr produktet për këtë porosi
                $itemsSql = "SELECT oi.*, p.name, p.image, p.sku 
                             FROM order_items oi 
                             LEFT JOIN products p ON oi.product_id = p.id 
                             WHERE oi.order_id = " . $row['id'];
                $itemsResult = $conn->query($itemsSql);
                $items = [];
                
                if ($itemsResult) {
                    while($item = $itemsResult->fetch_assoc()) {
                        $items[] = $item;
                    }
                }
                
                $row['items'] = $items;
                $orders[] = $row;
            }
        }
        
        echo json_encode(['success' => true, 'data' => $orders]);
        break;
        
    case 'get_user_orders':
        // Merr porositë e përdoruesit të kyçur
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'message' => 'Ju duhet të jeni të kyçur']);
            break;
        }
        
        $user_id = $_SESSION['user_id'];
        
        $sql = "SELECT o.*, 
                       (SELECT COUNT(*) FROM order_items WHERE order_id = o.id) as total_items 
                FROM orders o 
                WHERE o.user_id = $user_id 
                ORDER BY o.created_at DESC";
        
        $result = $conn->query($sql);
        $orders = [];
        
        if ($result) {
            while($row = $result->fetch_assoc()) {
                // Merr produktet për këtë porosi
                $itemsSql = "SELECT oi.*, p.name, p.image 
                             FROM order_items oi 
                             LEFT JOIN products p ON oi.product_id = p.id 
                             WHERE oi.order_id = " . $row['id'];
                $itemsResult = $conn->query($itemsSql);
                $items = [];
                
                if ($itemsResult) {
                    while($item = $itemsResult->fetch_assoc()) {
                        $items[] = $item;
                    }
                }
                
                $row['items'] = $items;
                $orders[] = $row;
            }
        }
        
        echo json_encode(['success' => true, 'data' => $orders]);
        break;
        
    case 'update_order_status':
        // Kontrollo nëse përdoruesi është admin
        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
            echo json_encode(['success' => false, 'message' => 'Nuk keni autorizim']);
            break;
        }
        
        $orderId = intval($_POST['order_id'] ?? 0);
        $status = $conn->real_escape_string($_POST['status'] ?? '');
        
        $validStatuses = ['pending', 'processing', 'shipped', 'delivered', 'cancelled'];
        
        if (!in_array($status, $validStatuses)) {
            echo json_encode(['success' => false, 'message' => 'Status i pavlefshëm']);
            break;
        }
        
        $sql = "UPDATE orders SET status = '$status', updated_at = NOW() WHERE id = $orderId";
        
        if ($conn->query($sql)) {
            echo json_encode(['success' => true, 'message' => 'Statusi u përditësua']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Gabim gjatë përditësimit']);
        }
        break;
        
    case 'update_payment_status':
        // Kontrollo nëse përdoruesi është admin
        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
            echo json_encode(['success' => false, 'message' => 'Nuk keni autorizim']);
            break;
        }
        
        $orderId = intval($_POST['order_id'] ?? 0);
        $payment_status = $conn->real_escape_string($_POST['payment_status'] ?? '');
        
        $validStatuses = ['pending', 'paid', 'failed'];
        
        if (!in_array($payment_status, $validStatuses)) {
            echo json_encode(['success' => false, 'message' => 'Status i pagesës i pavlefshëm']);
            break;
        }
        
        $sql = "UPDATE orders SET payment_status = '$payment_status', updated_at = NOW() WHERE id = $orderId";
        
        if ($conn->query($sql)) {
            echo json_encode(['success' => true, 'message' => 'Statusi i pagesës u përditësua']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Gabim gjatë përditësimit']);
        }
        break;
        
    case 'get_categories':
        $sql = "SELECT * FROM categories ORDER BY name ASC";
        $result = $conn->query($sql);
        $categories = [];
        
        if ($result) {
            while($row = $result->fetch_assoc()) {
                $categories[] = $row;
            }
        }
        
        echo json_encode(['success' => true, 'data' => $categories]);
        break;
        
    case 'get_subcategories':
        $category_id = intval($_GET['category_id'] ?? 0);
        
        $sql = "SELECT * FROM subcategories";
        if ($category_id > 0) {
            $sql .= " WHERE category_id = $category_id";
        }
        $sql .= " ORDER BY name ASC";
        
        $result = $conn->query($sql);
        $subcategories = [];
        
        if ($result) {
            while($row = $result->fetch_assoc()) {
                $subcategories[] = $row;
            }
        }
        
        echo json_encode(['success' => true, 'data' => $subcategories]);
        break;
        
    default:
        echo json_encode(['success' => false, 'message' => 'Veprim i panjohur: ' . $action]);
}
?>