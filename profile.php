<?php
session_start();
require_once 'config/init.php';

// Kontrollo nëse përdoruesi është i kyçur
if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$success_message = '';
$error_message = '';

// Përditëso të dhënat personale
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $first_name = $conn->real_escape_string($_POST['first_name']);
    $last_name = $conn->real_escape_string($_POST['last_name']);
    $phone = $conn->real_escape_string($_POST['phone']);
    $address = $conn->real_escape_string($_POST['address']);
    
    $sql = "UPDATE users SET first_name = '$first_name', last_name = '$last_name', phone = '$phone', address = '$address' WHERE id = $user_id";
    
    if ($conn->query($sql)) {
        $_SESSION['user_name'] = $first_name . ' ' . $last_name;
        $success_message = 'Të dhënat u përditësuan me sukses!';
    } else {
        $error_message = 'Gabim gjatë përditësimit: ' . $conn->error;
    }
}

// Ndrysho fjalëkalimin
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Merr fjalëkalimin aktual nga databaza
    $sql = "SELECT password FROM users WHERE id = $user_id";
    $result = $conn->query($sql);
    $user = $result->fetch_assoc();
    
    if (password_verify($current_password, $user['password'])) {
        if ($new_password === $confirm_password) {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $update_sql = "UPDATE users SET password = '$hashed_password' WHERE id = $user_id";
            if ($conn->query($update_sql)) {
                $success_message = 'Fjalëkalimi u ndryshua me sukses!';
            } else {
                $error_message = 'Gabim gjatë ndryshimit të fjalëkalimit!';
            }
        } else {
            $error_message = 'Fjalëkalimi i ri dhe konfirmimi nuk përputhen!';
        }
    } else {
        $error_message = 'Fjalëkalimi aktual është i gabuar!';
    }
}

// Merr të dhënat e përdoruesit
$sql = "SELECT * FROM users WHERE id = $user_id";
$result = $conn->query($sql);
$user = $result->fetch_assoc();

// Merr porositë e përdoruesit
$orders_sql = "SELECT * FROM orders WHERE user_id = $user_id ORDER BY created_at DESC LIMIT 5";
$orders_result = $conn->query($orders_sql);
?>
<!DOCTYPE html>
<html lang="sq">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profili im - Pinguin Shop</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }
        
        body {
            background: linear-gradient(135deg, #2d1b4e, #3d1e5e);
            color: white;
        }
        
        .container {
            max-width: 1200px;
            margin: 50px auto;
            padding: 0 20px;
        }
        
        .profile-header {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 30px;
            margin-bottom: 30px;
            border: 1px solid rgba(255, 215, 0, 0.2);
        }
        
        .profile-header h1 {
            font-size: 32px;
            margin-bottom: 10px;
            color: #ffd966;
        }
        
        .profile-header p {
            color: rgba(255, 255, 255, 0.7);
        }
        
        .profile-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 30px;
        }
        
        .profile-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 30px;
            border: 1px solid rgba(255, 215, 0, 0.2);
        }
        
        .profile-card h2 {
            color: #ffd966;
            margin-bottom: 20px;
            font-size: 20px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 5px;
            color: rgba(255, 255, 255, 0.8);
            font-size: 14px;
        }
        
        .form-group input {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid rgba(255, 215, 0, 0.2);
            border-radius: 10px;
            background: rgba(255, 255, 255, 0.05);
            color: white;
            font-size: 14px;
            transition: all 0.3s;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: #ffd966;
            box-shadow: 0 0 10px rgba(255, 215, 0, 0.3);
        }
        
        .form-group input[readonly] {
            background: rgba(255, 255, 255, 0.02);
            cursor: not-allowed;
        }
        
        .btn {
            padding: 12px 25px;
            border: none;
            border-radius: 10px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            font-size: 14px;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #ffd966, #ffb347);
            color: #2d1b4e;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255, 215, 0, 0.3);
        }
        
        .btn-danger {
            background: linear-gradient(135deg, #ff6b6b, #ff4757);
            color: white;
        }
        
        .message {
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
        }
        
        .message.success {
            background: rgba(39, 174, 96, 0.2);
            border: 1px solid #27ae60;
            color: #27ae60;
        }
        
        .message.error {
            background: rgba(255, 71, 87, 0.2);
            border: 1px solid #ff4757;
            color: #ff4757;
        }
        
        .orders-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .orders-table th {
            text-align: left;
            padding: 12px;
            background: rgba(255, 215, 0, 0.1);
            color: #ffd966;
            font-weight: 600;
            font-size: 14px;
        }
        
        .orders-table td {
            padding: 12px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .status {
            padding: 4px 8px;
            border-radius: 5px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .status-pending {
            background: rgba(255, 193, 7, 0.2);
            color: #ffc107;
        }
        
        .status-processing {
            background: rgba(0, 123, 255, 0.2);
            color: #007bff;
        }
        
        .status-delivered {
            background: rgba(40, 167, 69, 0.2);
            color: #28a745;
        }
        
        .status-cancelled {
            background: rgba(220, 53, 69, 0.2);
            color: #dc3545;
        }
        
        .back-home {
            display: inline-block;
            margin-top: 20px;
            color: #ffd966;
            text-decoration: none;
        }
        
        .back-home:hover {
            text-decoration: underline;
        }
        
        .tab-container {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }
        
        .tab {
            padding: 10px 20px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .tab.active {
            background: #ffd966;
            color: #2d1b4e;
        }
        
        .tab:hover {
            background: rgba(255, 215, 0, 0.3);
        }
        
        .tab-content {
            display: none;
        }
        
        .tab-content.active {
            display: block;
        }
        
        @media (max-width: 768px) {
            .profile-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="profile-header">
            <h1>Profili im</h1>
            <p>Mirë se vini, <?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?>! 👋</p>
        </div>
        
        <?php if ($success_message): ?>
            <div class="message success"><?php echo $success_message; ?></div>
        <?php endif; ?>
        
        <?php if ($error_message): ?>
            <div class="message error"><?php echo $error_message; ?></div>
        <?php endif; ?>
        
        <div class="tab-container">
            <div class="tab active" onclick="showTab('profile')">Të dhënat personale</div>
            <div class="tab" onclick="showTab('orders')">Porositë e mia</div>
            <div class="tab" onclick="showTab('password')">Ndrysho fjalëkalimin</div>
        </div>
        
        <!-- Tab: Të dhënat personale -->
        <div id="profile-tab" class="tab-content active">
            <div class="profile-card">
                <h2><i class="fas fa-user-edit"></i> Ndrysho të dhënat personale</h2>
                
                <form method="POST">
                    <div class="form-group">
                        <label>Email (nuk ndryshohet)</label>
                        <input type="email" value="<?php echo htmlspecialchars($user['email']); ?>" readonly>
                    </div>
                    
                    <div class="form-group">
                        <label>Emri</label>
                        <input type="text" name="first_name" value="<?php echo htmlspecialchars($user['first_name']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Mbiemri</label>
                        <input type="text" name="last_name" value="<?php echo htmlspecialchars($user['last_name']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Telefoni</label>
                        <input type="tel" name="phone" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label>Adresa</label>
                        <input type="text" name="address" value="<?php echo htmlspecialchars($user['address'] ?? ''); ?>">
                    </div>
                    
                    <button type="submit" name="update_profile" class="btn btn-primary">
                        <i class="fas fa-save"></i> Ruaj ndryshimet
                    </button>
                </form>
            </div>
        </div>
        
        <!-- Tab: Porositë e mia -->
        <div id="orders-tab" class="tab-content">
            <div class="profile-card">
                <h2><i class="fas fa-shopping-bag"></i> Porositë e mia</h2>
                
                <?php if ($orders_result && $orders_result->num_rows > 0): ?>
                    <table class="orders-table">
                        <thead>
                            <tr>
                                <th>Nr. Porosie</th>
                                <th>Data</th>
                                <th>Totali</th>
                                <th>Statusi</th>
                                <th>Pagesa</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($order = $orders_result->fetch_assoc()): ?>
                                <tr>
                                    <td>#<?php echo $order['order_number']; ?></td>
                                    <td><?php echo date('d.m.Y', strtotime($order['created_at'])); ?></td>
                                    <td><?php echo number_format($order['total_amount'], 2); ?> €</td>
                                    <td>
                                        <span class="status status-<?php echo $order['status']; ?>">
                                            <?php 
                                                $statuses = [
                                                    'pending' => 'Në pritje',
                                                    'processing' => 'Në përpunim',
                                                    'shipped' => 'Nisur',
                                                    'delivered' => 'Dorëzuar',
                                                    'cancelled' => 'Anuluar'
                                                ];
                                                echo $statuses[$order['status']] ?? $order['status'];
                                            ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="status status-<?php echo $order['payment_status']; ?>">
                                            <?php 
                                                $payments = [
                                                    'pending' => 'Në pritje',
                                                    'paid' => 'Paguar',
                                                    'failed' => 'Dështoi'
                                                ];
                                                echo $payments[$order['payment_status']] ?? $order['payment_status'];
                                            ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="order-details.php?id=<?php echo $order['id']; ?>" style="color: #ffd966;">
                                            <i class="fas fa-eye"></i> Shiko
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p style="text-align: center; padding: 30px; color: rgba(255,255,255,0.5);">
                        <i class="fas fa-box-open" style="font-size: 48px; margin-bottom: 15px; display: block;"></i>
                        Nuk keni asnjë porosi ende.
                    </p>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Tab: Ndrysho fjalëkalimin -->
        <div id="password-tab" class="tab-content">
            <div class="profile-card">
                <h2><i class="fas fa-key"></i> Ndrysho fjalëkalimin</h2>
                
                <form method="POST">
                    <div class="form-group">
                        <label>Fjalëkalimi aktual</label>
                        <input type="password" name="current_password" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Fjalëkalimi i ri</label>
                        <input type="password" name="new_password" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Konfirmo fjalëkalimin e ri</label>
                        <input type="password" name="confirm_password" required>
                    </div>
                    
                    <button type="submit" name="change_password" class="btn btn-primary">
                        <i class="fas fa-key"></i> Ndrysho fjalëkalimin
                    </button>
                </form>
            </div>
        </div>
        
        <a href="index.php" class="back-home">
            <i class="fas fa-arrow-left"></i> Kthehu në faqen kryesore
        </a>
    </div>
    
    <script>
        function showTab(tabName) {
            // Fshij klasën active nga të gjitha tab-at
            document.querySelectorAll('.tab').forEach(tab => {
                tab.classList.remove('active');
            });
            
            // Fshij klasën active nga të gjitha përmbajtjet
            document.querySelectorAll('.tab-content').forEach(content => {
                content.classList.remove('active');
            });
            
            // Aktivizo tab-in e klikuar
            document.querySelector(`.tab[onclick="showTab('${tabName}')"]`).classList.add('active');
            
            // Aktivizo përmbajtjen e duhur
            document.getElementById(`${tabName}-tab`).classList.add('active');
        }
    </script>
</body>
</html>