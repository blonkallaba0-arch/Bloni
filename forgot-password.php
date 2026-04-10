<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php'; // Sigurohu që PHPMailer është instaluar me Composer

// ===== DATABASE CONNECTION =====
$host = "localhost";
$user = "root";
$pass = "";
$db   = "pinguinshop";
$port = 3307; // vendos portin që përdor XAMPP (zakonisht 3306 ose 3307)

$conn = new mysqli($host, $user, $pass, $db, $port);

if ($conn->connect_error) {
    die("Gabim në lidhje me databazën: " . $conn->connect_error);
}

$message = "";

if(isset($_POST['email'])){

    $email = $conn->real_escape_string($_POST['email']);

    $result = $conn->query("SELECT id, username FROM users WHERE email='$email'");

    if($result->num_rows > 0){

        $row = $result->fetch_assoc();
        $username = $row['username'];
        $token = bin2hex(random_bytes(50));

        // Ruaj token-in në databazë
        $conn->query("UPDATE users SET reset_token='$token' WHERE email='$email'");

        $mail = new PHPMailer(true);

        try {
            // Server settings
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'pinguinshop.com@gmail.com';    // vendos emailin tënd Gmail
            $mail->Password = 'bhff iudg rshw ohiq';       // vendos App Password që krijove
            $mail->SMTPSecure = 'tls';
            $mail->Port = 587;

            // Recipients
            $mail->setFrom('pinguinshop.com@gmail.com', 'Pinguin Shop');
            $mail->addAddress($email, $username);

            // Content
            $link = "http://localhost/pinguinshop/reset-password.php?token=$token";

            $mail->isHTML(true);
            $mail->Subject = "Rikupero Fjalëkalimin - Pinguin Shop 🐧";
            $mail->Body    = "
                <div style='font-family:Poppins,sans-serif; color:#333;'>
                    <h2 style='color:#00e0ff;'>Pershendetje $username 👋</h2>
                    <p>Ne morëm një kërkesë për të ndryshuar fjalëkalimin tuaj në <b>Pinguin Shop</b>.</p>
                    <p>Për të rikuperuar fjalëkalimin tuaj, klikoni butonin më poshtë:</p>
                    <a href='$link' style='display:inline-block; padding:12px 25px; margin:15px 0; background:linear-gradient(90deg,#00e0ff,#007cf0); color:#001f3f; font-weight:600; border-radius:12px; text-decoration:none;'>Ndrysho Fjalëkalimin</a>
                    <p>Nëse nuk kërkuat ndryshimin e fjalëkalimit, thjesht injoroni këtë email.</p>
                    <hr style='margin:20px 0; border:none; border-top:1px solid #eee;'>
                    <p style='font-size:13px; color:#555;'>Pinguin Shop | Mbështetje: pinguinshop.com@gmail.com | +355 4X XXX XXXX</p>
                </div>
            ";

            $mail->send();
            $message = "Linku për rikuperim u dërgua në email! Kontrollo inbox ose spam. 🎉";

        } catch (Exception $e) {
            $message = "Gabim gjatë dërgimit të email-it: {$mail->ErrorInfo}";
        }

    } else {
        $message = "Ky email nuk ekziston! <br><br> <a href='login.php' style='color:#00e0ff; text-decoration:underline;'>Kthehu te logini</a>";
    }
}
?>

<!DOCTYPE html>
<html lang="sq">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Pinguin Shop | Keni harruar fjalëkalimin?</title>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

<style>
*{margin:0;padding:0;box-sizing:border-box;font-family:'Poppins',sans-serif;}
body{
height:100vh;
display:flex;
justify-content:center;
align-items:center;
background: radial-gradient(circle at top left, #2f3f46, #111f34, #041124);
}
.box{
width:420px;
padding:50px 40px;
border-radius:25px;
background:rgba(255,255,255,0.05);
backdrop-filter:blur(30px);
box-shadow:0 30px 60px rgba(0,0,0,0.6);
text-align:center;
color:white;
}
.logo{
width:200px;
margin-bottom:20px;
filter: drop-shadow(0 0 25px #00e0ff);
}
h1{
margin-bottom:25px;
color:#00e0ff;
}
input{
width:100%;
padding:14px;
border-radius:12px;
border:1px solid rgba(255,255,255,0.1);
background:rgba(255,255,255,0.08);
color:white;
margin-bottom:20px;
outline:none;
}
button{
width:100%;
padding:14px;
border-radius:12px;
border:none;
background:linear-gradient(90deg,#00e0ff,#007cf0);
color:#001f3f;
font-weight:600;
cursor:pointer;
transition:0.3s;
margin-top:10px;
}
button:hover{
transform:translateY(-3px);
box-shadow:0 15px 25px #1abcce40;
}
.message{
margin-bottom:15px;
font-size:14px;
color:#00e0ff;
}
.login-btn{
display:inline-block;
margin-top:15px;
padding:10px 20px;
border-radius:10px;
    box-shadow:0 15px 25px #13343840;
color:#00e0ff;
font-weight:600;
text-decoration:none;
transition:0.3s;
}
.login-btn:hover{
background:#007cf0;
color:white;
}
</style>
</head>
<body>

<div class="box">

<img src="assets/images/pinguin.png" class="logo">

<h1>Keni harruar fjalëkalimin?</h1>

<?php if($message != ""): ?>
<div class="message"><?php echo $message; ?></div>
<?php endif; ?>

<form method="POST">
<input type="email" name="email" placeholder="Shkruaj Emailin" required>
<button type="submit">Dërgo </button>
</form>

<!-- Butoni për t’u kthyer gjithmonë tek login -->
<a href="login.php" class="login-btn">Kthehu te logini</a>

</div>

</body>
</html>
