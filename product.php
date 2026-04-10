<?php
// Funksionet per te marre produkte nga databaza

/**
 * Merr produktet qe jane ne zbritje
 * @param object $pdo Lidhja me databazen
 * @param int $limit Numri i produkteve per te marre
 * @return array Lista e produkteve
 */
function getProductsOnSale($pdo, $limit = 8) {
    $stmt = $pdo->prepare("SELECT * FROM products WHERE is_on_sale = 1 ORDER BY id DESC LIMIT :limit");
    $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
}

/**
 * Merr produktet me te kerkuarat (me te klikuarat)
 * @param object $pdo Lidhja me databazen
 * @param int $limit Numri i produkteve per te marre
 * @return array Lista e produkteve
 */
function getMostViewedProducts($pdo, $limit = 8) {
    $stmt = $pdo->prepare("SELECT * FROM products ORDER BY views DESC LIMIT :limit");
    $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
}

/**
 * Merr produktet me te lirat (cmimi me i ulet)
 * @param object $pdo Lidhja me databazen
 * @param int $limit Numri i produkteve per te marre
 * @return array Lista e produkteve
 */
function getCheapestProducts($pdo, $limit = 8) {
    $stmt = $pdo->prepare("SELECT * FROM products ORDER BY price ASC LIMIT :limit");
    $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
}

/**
 * Merr produktet me te reja (te fundit te shtuara)
 * @param object $pdo Lidhja me databazen
 * @param int $limit Numri i produkteve per te marre
 * @return array Lista e produkteve
 */
function getNewestProducts($pdo, $limit = 8) {
    $stmt = $pdo->prepare("SELECT * FROM products ORDER BY created_at DESC LIMIT :limit");
    $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
}

?>