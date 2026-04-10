<?php
// Nisim sesionin per te perdorur variabla si 'user' ne rast se perdoruesi eshte loguar
session_start();
?>
<!DOCTYPE html>
<html lang="sq">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pinguin Shop - Blerje Online</title>
    <!-- Linku i CSS -->
    <link rel="stylesheet" href="css/style.css">
    <!-- Google Fonts per nje dizajn modern -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>

<!-- ===== TOP BANNER LEVIZES ===== -->
<div class="top-banner">
    <div class="marquee">
        <!-- KERKESA 1: Teksti i ndryshuar dhe levizja -->
        <span>🐧 Mire se vini ne Pinguin Shop! 🐧 Mire se vini ne Pinguin Shop! 🐧 Mire se vini ne Pinguin Shop! 🐧</span>
    </div>
</div>

<!-- ===== HEADERI KRYESOR ===== -->
<!-- KERKESA 6: Header-i eshte fiks. Klasa 'sticky-header' e ben kete ne CSS -->
<header class="sticky-header">
    <div class="container header-container">
        <!-- Logo -->
        <div class="logo">
            <a href="index.php">
                <!-- KERKESA 2: Logoja e re -->
                <img src="images/pinguin.png" alt="Pinguin Shop Logo">
                <span>PinguinShop</span>
            </a>
        </div>

        <!-- Slogani -->
        <div class="slogan">
            <span>Blerje online të shpejta dhe të sigurta</span>
        </div>

        <!-- Ikonat e perdoruesit -->
        <div class="user-actions">
            <!-- KERKESA 3: Butoni Llogaria -->
            <a href="login.php" class="action-btn">Llogaria</a>
            <!-- KERKESA 4: Butoni Lista (Wishlist) -->
            <a href="wishlist.php" class="action-btn">Lista <span class="badge">❤️</span></a>
            <!-- KERKESA 5: Butoni Shporta -->
            <a href="cart.php" class="action-btn">Shporta <span class="badge">🛒</span></a>
        </div>
    </div>
</header>

<!-- Nis pjesa kryesore e faqes -->
<main></main>