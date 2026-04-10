<?php
// login.php - FILLO ME KËTË PJESË PHP

session_start();

// Nëse përdoruesi është tashmë i kyçur, dërgoje te faqja kryesore
if (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true) {
    header('Location: index.php');
    exit;
}

// Përfshi lidhjen me databazën
require_once 'config/init.php';

$error = '';

// Kontrollo nëse forma u dërgua
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if (empty($email) || empty($password)) {
        $error = 'Ju lutem plotësoni të gjitha fushat!';
    } else {
        // Kontrollo në databazë
        $email = $conn->real_escape_string($email);
        $sql = "SELECT * FROM users WHERE email = '$email'";
        $result = $conn->query($sql);
        
        if ($result && $result->num_rows > 0) {
            $user = $result->fetch_assoc();
            
            // Verifiko fjalëkalimin
            if (password_verify($password, $user['password'])) {
                // Kyçja e suksesshme
                $_SESSION['user_logged_in'] = true;
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_role'] = $user['role'];
                
                // Dërgo te faqja kryesore
                header('Location: index.php');
                exit;
            } else {
                $error = 'Email ose fjalëkalim i gabuar!';
            }
        } else {
            $error = 'Email ose fjalëkalim i gabuar!';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="sq">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Pinguin Shop | Login</title>

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

/* Ice Glow Background */
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

.login-box{
    width:420px;
    padding:50px 40px;
    border-radius:25px;
    background:rgba(255,255,255,0.05);
    backdrop-filter:blur(30px);
    box-shadow:0 30px 60px rgba(0,0,0,0.6);
    text-align:center;
    color:white;
    position:relative;
    animation:fadeIn 1s ease;
}

@keyframes fadeIn{
    from{opacity:0; transform:translateY(-30px);}
    to{opacity:1; transform:translateY(0);}
}

/* LOGO SUPER */
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
    margin-bottom:35px;
    letter-spacing:1px;
    color:#00e0ff;
}

.input-group{
    margin-bottom:25px;
    position:relative;
}

.input-group input{
    width:100%;
    padding:14px 45px 14px 15px;
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

.input-group span{
    position:absolute;
    right:15px;
    top:14px;
    cursor:pointer;
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
    margin-top:20px;
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
    margin-bottom:15px;
    padding: 10px;
    background: rgba(255, 92, 92, 0.1);
    border-radius: 8px;
    border-left: 3px solid #ff5c5c;
    text-align: left;
}
</style>
</head>
<body>

<div class="login-box">

    <img src="assets/images/pinguin.png" class="logo" alt="Pinguin Logo">

    <h1>Pinguin Shop</h1>

    <?php if (!empty($error)): ?>
        <div class="error" id="errorMsg">
            <?php echo $error; ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="" onsubmit="return validateLogin()">

        <div class="input-group">
            <input type="email" name="email" id="email" placeholder="Email" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
        </div>

        <div class="input-group">
            <input type="password" name="password" id="password" placeholder="Fjalëkalimi" required>
            <span onclick="togglePassword()">👁</span>
        </div>

        <button type="submit">Kyçu në Dyqan</button>

        <div class="options">
            <p><a href="forgot-password.php">Keni harruar fjalëkalimin?</a></p>
            <p>Nuk keni llogari? <a href="register.php">Regjistrohu</a></p>
        </div>

    </form>
</div>

<script>
function togglePassword(){
    const password = document.getElementById("password");
    password.type = password.type === "password" ? "text" : "password";
}

function validateLogin(){
    const email = document.getElementById("email").value;
    const password = document.getElementById("password").value;
    
    if(email.trim() === "" || password.trim() === ""){
        alert("Ju lutem plotësoni të gjitha fushat!");
        return false;
    }
    
    return true; // Lejo form-in të dërgohet
}
</script>

</body>
</html>