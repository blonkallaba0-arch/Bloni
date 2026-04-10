<?php
session_start();

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php'; // Sigurohu që PHPMailer është instaluar me Composer

// ===== DATABASE CONNECTION =====
$host = "127.0.0.1";
$user = "root";
$pass = "";
$db   = "pinguinshop";
$port = 3307; // porti juaj MySQL

$conn = new mysqli($host, $user, $pass, $db, $port);

if ($conn->connect_error) {
    die("Gabim në lidhje me databazën: " . $conn->connect_error);
}

$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $username = trim($_POST["username"]);
    $email = trim($_POST["email"]);
    $password = $_POST["password"];
    $confirm_password = $_POST["confirm_password"];

    if (empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
        $error = "Ju lutem plotësoni të gjitha fushat!";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Email jo valid!";
    } elseif ($password !== $confirm_password) {
        $error = "Fjalëkalimet nuk përputhen!";
    } else {

        // Kontrollo nëse ekziston email
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $error = "Ky email ekziston!";
        } else {

            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $registration_date = date("d/m/Y");

            $stmt = $conn->prepare("INSERT INTO users (username, email, password, created_at) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $username, $email, $hashed_password, $registration_date);

            if ($stmt->execute()) {
                
                // ===== DËRGO EMAIL NJOFTIMI I PERSONALIZUAR =====
                $mail = new PHPMailer(true);

                try {
                    $mail->isSMTP();
                    $mail->Host = 'smtp.gmail.com';
                    $mail->SMTPAuth = true;
                    $mail->Username = 'pinguinshop.com@gmail.com'; // vendos emailin tënd
                    $mail->Password = 'bhff iudg rshw ohiq';    // vendos App Password
                    $mail->SMTPSecure = 'tls';
                    $mail->Port = 587;

                    $mail->setFrom('pinguinshop.com@gmail.com', 'Pinguin Shop');
                    $mail->addAddress($email, $username);

                    $mail->isHTML(true);
                    $mail->Subject = "Mirësevini në Pinguin Shop 🐧";

                    $mail->Body = "
                    <div style='font-family:Arial,sans-serif; color:#001f3f;'>
                        <img src='http://localhost/pinguinshop/assets/images/pinguin.png' alt='Logo' style='width:150px;'>
                        <h2 style='color:#00e0ff;'>Mirë se vini! 🎉</h2>
                        <p>Faleminderit që u bëtë pjesë e <b>Pinguin Shop</b>!</p>

                        <h3>Përshëndetje $username 👋</h3>
                        <p>Ju faleminderit që keni krijuar një llogari në Pinguin Shop. Jemi të lumtur që jeni me ne!</p>

                        <p>✨ Llogaria juaj është aktivizuar me sukses!</p>

                        <h4>Informacioni i Llogarisë Tuaj:</h4>
                        <ul>
                            <li><b>Username:</b> $username</li>
                            <li><b>Email:</b> $email</li>
                            <li><b>Data e Regjistrimit:</b> $registration_date</li>
                        </ul>

                        <h4>Përfitimet e Pinguin Shop:</h4>
                        <ul>
                            <li>🛍️ Blerje Online: Shfletoni mijëra produkte dhe bëni blerje të sigurta</li>
                            <li>🚚 Dërgesë e Shpejtë: Në të gjithë vendin brenda 1-3 ditëve</li>
                            <li>💳 Pagesa të Sigurta: Metoda të ndryshme pagese me mbrojtje maksimale</li>
                            <li>⭐ Oferta Ekskluzive: Zbritje dhe oferta speciale për anëtarët</li>
                        </ul>

                        <p><a href='http://localhost/pinguinshop/login.php' style='color:#00e0ff; text-decoration:none; font-weight:bold;'>Hyni në Llogarinë Tuaj & Filloni të Blini</a></p>

                        <h4>Këshilla për Sigurinë:</h4>
                        <ul>
                            <li>Mos e ndani fjalëkalimin tuaj me të tjerët</li>
                            <li>Përdorni fjalëkalim të fortë dhe unik</li>
                            <li>Kontrolloni email-in për komunikime të rëndësishme</li>
                            <li>Raportoni çdo aktivitet të dyshimtë menjëherë</li>
                        </ul>

                        <p style='font-size:12px; color:#555;'>Nëse nuk e keni krijuar këtë llogari, ju lutem injoroni këtë email ose kontaktoni mbështetjen tonë.<br>
                        Pinguin Shop - Gjithçka që ju nevojitet... 24/7<br>
                        Email: pinguinshop.com@gmail.com | Telefon: +383 45 464 912</p>
                    </div>
                    ";

                    $mail->send();
                    $success = "Regjistrimi u krye me sukses! Kontrollo email-in 🎉";

                } catch (Exception $e) {
                    $success = "Regjistrimi u krye, por email nuk u dërgua.";
                }

            } else {
                $error = "Gabim gjatë regjistrimit!";
            }
        }

        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="sq">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Pinguin Shop | Regjistrohu</title>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

<style>
*{
    margin:0;
    padding:0;
    box-sizing:border-box;
    font-family:'Poppins',sans-serif;
}

body{
    height:100vh;
    display:flex;
    justify-content:center;
    align-items:center;
    background: radial-gradient(circle at top left, #2f3f46, #111f34, #041124);
    overflow:hidden;
}

body::before{
    content:"";
    position:absolute;
    width:500px;
    height:500px;
    background:#00e0ff;
    filter:blur(180px);
    opacity:0.15;
    top:-150px;
    left:-150px;
    animation: moveGlow 8s infinite alternate ease-in-out;
}

@keyframes moveGlow{
    from{transform:translateY(0);}
    to{transform:translateY(60px);}
}

.register-box{
    width:420px;
    padding:50px 40px;
    border-radius:25px;
    background:rgba(255,255,255,0.05);
    backdrop-filter:blur(30px);
    box-shadow:0 30px 60px rgba(0,0,0,0.6);
    text-align:center;
    color:white;
    animation:fadeIn 1s ease;
}

@keyframes fadeIn{
    from{opacity:0; transform:translateY(-30px);}
    to{opacity:1; transform:translateY(0);}
}

.logo{
    width:220px;
    margin-bottom:20px;
    filter: drop-shadow(0 0 25px #00e0ff);
    animation: floatLogo 4s infinite ease-in-out;
}

@keyframes floatLogo{
    0%{transform:translateY(0);}
    50%{transform:translateY(-15px);}
    100%{transform:translateY(0);}
}

h1{
    font-weight:600;
    margin-bottom:25px;
    color:#00e0ff;
}

.input-group{
    margin-bottom:20px;
}

.input-group input{
    width:100%;
    padding:14px;
    border-radius:12px;
    border:1px solid rgba(255,255,255,0.1);
    background:rgba(255,255,255,0.08);
    color:white;
    font-size:14px;
    outline:none;
    transition:0.3s;
}

.input-group input:focus{
    border:1px solid #135862;
    box-shadow:0 0 15px #00e0ff50;
}

button{
    width:100%;
    padding:14px;
    border-radius:12px;
    border:none;
    background:linear-gradient(90deg,#00e0ff,#007cf0);
    color:#001f3f;
    font-weight:600;
    font-size:15px;
    cursor:pointer;
    transition:0.3s;
}

button:hover{
    transform:translateY(-3px);
    box-shadow:0 15px 25px #13343840;
}

.options{
    margin-top:15px;
    font-size:14px;
}

.options a{
    color:#00e0ff;
    text-decoration:none;
}

.options a:hover{
    text-decoration:underline;
}

.error{
    color:#ff5c5c;
    font-size:13px;
    margin-bottom:10px;
}

.success{
    color:#00ffcc;
    font-size:13px;
    margin-bottom:10px;
}
</style>
</head>
<body>

<div class="register-box">

    <img src="assets/images/pinguin.png" class="logo" alt="Pinguin Logo">

    <h1>Krijo Llogari</h1>

    <?php if($error): ?>
        <div class="error"><?php echo $error; ?></div>
    <?php endif; ?>

    <?php if($success): ?>
        <div class="success"><?php echo $success; ?></div>
    <?php endif; ?>

    <form method="POST" onsubmit="return validateRegister()">

        <div class="input-group">
            <input type="text" name="username" id="username" placeholder="Username">
        </div>

        <div class="input-group">
            <input type="email" name="email" id="email" placeholder="Email">
        </div>

        <div class="input-group">
            <input type="password" name="password" id="password" placeholder="Fjalëkalimi">
        </div>

        <div class="input-group">
            <input type="password" name="confirm_password" id="confirm_password" placeholder="Konfirmo Fjalëkalimin">
        </div>

        <button type="submit">Regjistrohu</button>

        <div class="options">
            <p>Keni llogari? <a href="login.php">Kyçu këtu</a></p>
        </div>

    </form>
</div>

<script>
function validateRegister(){
    const username = document.getElementById("username").value;
    const email = document.getElementById("email").value;
    const password = document.getElementById("password").value;
    const confirm = document.getElementById("confirm_password").value;

    if(username === "" || email === "" || password === "" || confirm === ""){
        alert("Ju lutem plotësoni të gjitha fushat!");
        return false;
    }

    if(password !== confirm){
        alert("Fjalëkalimet nuk përputhen!");
        return false;
    }

    return true;
}
</script>

</body>
</html>
