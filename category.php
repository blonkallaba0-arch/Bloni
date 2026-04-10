<?php
// ============================================
// CATEGORY.PHP - FAQJA E PRODUKTEVE SIPAS KATEGORIVE
// ============================================

// Session start
session_start();

// Përfshij header template
include 'templates/header.php';

// Merr parametrin e kategorisë nga URL
$category = isset($_GET['cat']) ? htmlspecialchars($_GET['cat']) : 'Elektronike';

// Të gjithë produktet e dispozicionit
$all_products = [
    // ELEKTRONIKE
    ['id' => 1, 'name' => 'Laptop HP i7 16GB', 'image' => 'laptop.jpg', 'price' => 899.99, 'old_price' => 1199.99, 'rating' => 5, 'reviews' => 124, 'category' => 'Elektronike', 'discount' => '-25%'],
    ['id' => 2, 'name' => 'iPhone 14 Pro', 'image' => 'iphone.jpg', 'price' => 1099.99, 'old_price' => 1299.99, 'rating' => 5, 'reviews' => 256, 'category' => 'Elektronike', 'discount' => '-15%'],
    ['id' => 6, 'name' => 'Monitor 4K 32"', 'image' => 'monitor.jpg', 'price' => 449.99, 'old_price' => 599.99, 'rating' => 4, 'reviews' => 102, 'category' => 'Elektronike', 'discount' => '-25%'],
    ['id' => 7, 'name' => 'Mechanical Keyboard', 'image' => 'keyboard.jpg', 'price' => 79.99, 'old_price' => 129.99, 'rating' => 5, 'reviews' => 234, 'category' => 'Elektronike', 'discount' => '-38%'],
    ['id' => 8, 'name' => 'Wireless Mouse', 'image' => 'mouse.jpg', 'price' => 24.99, 'old_price' => 49.99, 'rating' => 4, 'reviews' => 567, 'category' => 'Elektronike', 'discount' => '-50%'],
    ['id' => 9, 'name' => 'USB-C Hub 7-Port', 'image' => 'hub.jpg', 'price' => 34.99, 'old_price' => 59.99, 'rating' => 4, 'reviews' => 198, 'category' => 'Elektronike', 'discount' => '-42%'],
    ['id' => 10, 'name' => 'Phone Stand Adjustable', 'image' => 'stand.jpg', 'price' => 14.99, 'old_price' => 29.99, 'rating' => 4, 'reviews' => 423, 'category' => 'Elektronike', 'discount' => '-50%'],
    
    // KËPUCË
    ['id' => 3, 'name' => 'Nike Air Max 90', 'image' => 'nike.jpg', 'price' => 129.99, 'old_price' => 179.99, 'rating' => 4, 'reviews' => 89, 'category' => 'Këpucë', 'discount' => '-28%'],
    ['id' => 4, 'name' => 'Adidas Ultraboost', 'image' => 'adidas.jpg', 'price' => 149.99, 'old_price' => 199.99, 'rating' => 5, 'reviews' => 145, 'category' => 'Këpucë', 'discount' => '-25%'],
    ['id' => 11, 'name' => 'Puma Running Shoes', 'image' => 'puma.jpg', 'price' => 99.99, 'old_price' => 149.99, 'rating' => 4, 'reviews' => 76, 'category' => 'Këpucë', 'discount' => '-33%'],
    ['id' => 12, 'name' => 'Converse All Star', 'image' => 'converse.jpg', 'price' => 69.99, 'old_price' => 99.99, 'rating' => 4, 'reviews' => 201, 'category' => 'Këpucë', 'discount' => '-30%'],
    
    // MOBILJE
    ['id' => 5, 'name' => 'Gaming Chair RGB', 'image' => 'chair.jpg', 'price' => 299.99, 'old_price' => 399.99, 'rating' => 5, 'reviews' => 78, 'category' => 'Mobilje', 'discount' => '-25%'],
    ['id' => 13, 'name' => 'Office Desk Wood', 'image' => 'desk.jpg', 'price' => 199.99, 'old_price' => 299.99, 'rating' => 4, 'reviews' => 45, 'category' => 'Mobilje', 'discount' => '-33%'],
    ['id' => 14, 'name' => 'Standing Desk', 'image' => 'standing.jpg', 'price' => 349.99, 'old_price' => 499.99, 'rating' => 5, 'reviews' => 92, 'category' => 'Mobilje', 'discount' => '-30%'],
    
    // VESHJE
    ['id' => 15, 'name' => 'T-Shirt Premium Cotton', 'image' => 'tshirt.jpg', 'price' => 19.99, 'old_price' => 39.99, 'rating' => 4, 'reviews' => 156, 'category' => 'Veshje', 'discount' => '-50%'],
    ['id' => 16, 'name' => 'Jeans Blue Slim', 'image' => 'jeans.jpg', 'price' => 49.99, 'old_price' => 79.99, 'rating' => 4, 'reviews' => 234, 'category' => 'Veshje', 'discount' => '-38%'],
    
    // AKSESORE
    ['id' => 17, 'name' => 'Smart Watch', 'image' => 'watch.jpg', 'price' => 149.99, 'old_price' => 249.99, 'rating' => 5, 'reviews' => 178, 'category' => 'Aksesore', 'discount' => '-40%'],
    ['id' => 18, 'name' => 'Wireless Headphones', 'image' => 'headphones.jpg', 'price' => 79.99, 'old_price' => 129.99, 'rating' => 5, 'reviews' => 445, 'category' => 'Aksesore', 'discount' => '-38%'],
    
    // LIBRA
    ['id' => 19, 'name' => 'Think Like a Programmer', 'image' => 'book1.jpg', 'price' => 24.99, 'old_price' => 39.99, 'rating' => 5, 'reviews' => 87, 'category' => 'Libra', 'discount' => '-38%'],
    ['id' => 20, 'name' => 'Clean Code', 'image' => 'book2.jpg', 'price' => 29.99, 'old_price' => 49.99, 'rating' => 5, 'reviews' => 234, 'category' => 'Libra', 'discount' => '-40%'],
];

// Filtro produktet sipas kategorisë
$products = array_filter($all_products, function($p) use ($category) {
    return $p['category'] === $category;
});

// Rendit produktet në rend alfabetik
usort($products, function($a, $b) {
    return strcmp($a['name'], $b['name']);
});
?>

<main>
    <!-- ============================================
         KATEGORI HEADER - Titulli i faqes
         ============================================ -->
    <div class="container">
        <section class="category-header">
            <h1>📦 <?php echo htmlspecialchars($category); ?></h1>
            <p><?php echo count($products); ?> produktet e disponueshme</p>
            
            <!-- OPCIONE SORTIME -->
            <div class="sort-options">
                <label for="sortSelect">Rendit sipas:</label>
                <select id="sortSelect" class="sort-select">
                    <option value="name-asc">Emri (A-Z)</option>
                    <option value="name-desc">Emri (Z-A)</option>
                    <option value="price-asc">Çmim (Ulët-Lartë)</option>
                    <option value="price-desc">Çmim (Lartë-Ulët)</option>
                    <option value="rating-desc">Vlerësim (Lartë-Ulët)</option>
                </select>
            </div>
        </section>
    </div>

    <!-- ============================================
         PRODUKTET GRID
         ============================================ -->
    <div class="container">
        <?php if (count($products) > 0): ?>
            <div class="products-grid">
                <?php
                // Loop përmes produkteve dhe shfaqi çdo produkt
                foreach ($products as $product) {
                    echo '<a href="product.php?id=' . $product['id'] . '" class="product-card">';
                    echo '  <div class="product-img-wrapper">';
                    echo '    <img src="assets/images/' . htmlspecialchars($product['image']) . '" alt="' . htmlspecialchars($product['name']) . '" class="product-img">';
                    echo '    <span class="product-badge">' . htmlspecialchars($product['discount']) . '</span>';
                    echo '  </div>';
                    echo '  <div class="product-info">';
                    echo '    <h3 class="product-name">' . htmlspecialchars($product['name']) . '</h3>';
                    
                    // Rating
                    echo '    <div class="product-rating">';
                    echo '      <span class="stars">';
                    for ($i = 0; $i < $product['rating']; $i++) {
                        echo '⭐';
                    }
                    echo '      </span>';
                    echo '      <span class="review-count">(' . $product['reviews'] . ')</span>';
                    echo '    </div>';
                    
                    // Çmimet
                    echo '    <div class="product-price">';
                    echo '      <span class="new-price">' . number_format($product['price'], 2) . '€</span>';
                    echo '      <span class="old-price">' . number_format($product['old_price'], 2) . '€</span>';
                    echo '    </div>';
                    
                    // Butoni
                    echo '    <button class="add-to-cart-btn" onclick="addToCart(' . $product['id'] . ', \'' . htmlspecialchars($product['name']) . '\', ' . $product['price'] . ')">Shto në Shportë</button>';
                    echo '  </div>';
                    echo '</a>';
                }
                ?>
            </div>
        <?php else: ?>
            <!-- MESAZH KUR NËNUK KA PRODUKTE -->
            <div class="no-products">
                <h2>Asnjë produkt në këtë kategori</h2>
                <p>Provo kategori tjera ose shko në <a href="index.php">faqen kryesore</a>.</p>
            </div>
        <?php endif; ?>
    </div>

</main>

<!-- ============================================
     FOOTER
     ============================================ -->
<?php include 'templates/footer.php'; ?>

<!-- ============================================
     JAVASCRIPT - SORTIME
     ============================================ -->
<script>
// Funksioni për të shtuar produktin në shportë
function addToCart(productId, productName, productPrice) {
    // Merr shportën nga localStorage
    let cart = JSON.parse(localStorage.getItem('cart')) || [];
    
    // Kontrollo nëse produkti është tashmë në shportë
    let existingProduct = cart.find(item => item.id === productId);
    
    if (existingProduct) {
        // Nëse ekziston, shto në sasi
        existingProduct.quantity += 1;
    } else {
        // Nëse nuk ekziston, shto produktin
        cart.push({
            id: productId,
            name: productName,
            price: productPrice,
            quantity: 1
        });
    }
    
    // Ruaj shportën
    localStorage.setItem('cart', JSON.stringify(cart));
    
    // Përditëso badge
    updateCartCount();
    
    // Konfirmim
    alert('✅ ' + productName + ' u shtua në shportë!');
}

// Përditëso badge të shportës
function updateCartCount() {
    const cart = JSON.parse(localStorage.getItem('cart')) || [];
    const cartCountEl = document.getElementById('cartCount');
    const totalItems = cart.reduce((total, item) => total + item.quantity, 0);
    cartCountEl.textContent = totalItems;
}

// Sortime produktesh
document.addEventListener('DOMContentLoaded', function() {
    // Merr elementet e produkteve
    const products = Array.from(document.querySelectorAll('.product-card'));
    
    // Event listener për select-in e sortime-s
    document.getElementById('sortSelect').addEventListener('change', function(e) {
        const sortType = e.target.value;
        
        // Sorto produktet bazuar në tipin
        products.sort((a, b) => {
            const nameA = a.querySelector('.product-name').textContent;
            const nameB = b.querySelector('.product-name').textContent;
            const priceA = parseFloat(a.querySelector('.new-price').textContent);
            const priceB = parseFloat(b.querySelector('.new-price').textContent);
            const ratingA = a.querySelector('.stars').textContent.length;
            const ratingB = b.querySelector('.stars').textContent.length;
            
            switch(sortType) {
                case 'name-asc':
                    return nameA.localeCompare(nameB);
                case 'name-desc':
                    return nameB.localeCompare(nameA);
                case 'price-asc':
                    return priceA - priceB;
                case 'price-desc':
                    return priceB - priceA;
                case 'rating-desc':
                    return ratingB - ratingA;
                default:
                    return 0;
            }
        });
        
        // Ri-ndrysho DOM me produktet e sortuara
        const grid = document.querySelector('.products-grid');
        grid.innerHTML = '';
        products.forEach(product => grid.appendChild(product));
    });
    
    // Përditëso cart count
    updateCartCount();
});
</script>

<style>
/* Styling për category page */
.category-header {
    padding: var(--spacing-xl) 0;
    margin-bottom: var(--spacing-xl);
}

.category-header h1 {
    font-size: 36px;
    font-weight: bold;
    margin-bottom: var(--spacing-md);
}

.sort-options {
    display: flex;
    align-items: center;
    gap: var(--spacing-md);
    margin-top: var(--spacing-lg);
}

.sort-select {
    padding: var(--spacing-sm) var(--spacing-md);
    border: 1px solid var(--color-border);
    border-radius: var(--border-radius);
    font-size: 14px;
    cursor: pointer;
}

.no-products {
    text-align: center;
    padding: var(--spacing-xl) var(--spacing-lg);
    background-color: var(--color-gray);
    border-radius: var(--border-radius-lg);
    margin: var(--spacing-xl) 0;
}

.no-products h2 {
    margin-bottom: var(--spacing-md);
}

.no-products a {
    color: var(--color-primary);
    text-decoration: none;
}

.no-products a:hover {
    text-decoration: underline;
}
</style>

</body>
</html>
