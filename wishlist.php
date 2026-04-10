<?php
// ============================================
// WISHLIST.PHP - LISTA E PRODUKTEVE TË PËLQYERA
// ============================================

session_start();

// Include database connection
require_once 'config/db.php';

// Krijo lidhjen me databazën
$conn = new mysqli('localhost', 'root', '', 'pinguinshop');
if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}
$conn->set_charset('utf8mb4');

// Përcakto variabla për header
$page_title = 'Lista e dëshirave - Pinguin Shop';
$page_description = 'Produktet që ju pëlqejnë, të ruajtura për më vonë';

// Marr produktet nga databaza për t'i shfaqur
$featured_products = [];

try {
    // Merr produktet e rekomanduara (për seksionin "Mund t'ju pëlqejnë")
    $stmt = $conn->query("SELECT * FROM products WHERE featured = 1 AND status = 'active' ORDER BY created_at DESC LIMIT 4");
    $featured_products = $stmt->fetch_all(MYSQLI_ASSOC);
    
    // Nëse nuk ka produkte featured, merr disa produkte të rastit
    if (empty($featured_products)) {
        $stmt = $conn->query("SELECT * FROM products WHERE status = 'active' ORDER BY RAND() LIMIT 4");
        $featured_products = $stmt->fetch_all(MYSQLI_ASSOC);
    }
} catch(Exception $e) {
    error_log("Database error: " . $e->getMessage());
}

// Include header
include 'templates/header.php';
?>

<main>
    <!-- ============================================
         WISHLIST HEADER
         ============================================ -->
    <div class="wishlist-hero">
        <div class="container">
            <div class="wishlist-hero-content">
                <h1><i class="fas fa-heart"></i> Lista e Dëshirave</h1>
                <p>Produktet që i ke ruajtur për më vonë</p>
            </div>
        </div>
    </div>

    <!-- ============================================
         WISHLIST CONTENT
         ============================================ -->
    <div class="container">
        <div id="wishlistContainer" class="wishlist-container">
            <div class="loading-spinner">
                <div class="spinner"></div>
                <p>Duke ngarkuar listën tuaj...</p>
            </div>
        </div>
    </div>

    <!-- ============================================
         RECOMMENDED PRODUCTS
         ============================================ -->
    <?php if (!empty($featured_products)): ?>
    <section class="recommended-section">
        <div class="container">
            <div class="section-header">
                <h2><i class="fas fa-star"></i> Mund t'ju pëlqejnë gjithashtu</h2>
                <a href="products.php" class="view-all">Shiko të gjitha <i class="fas fa-arrow-right"></i></a>
            </div>
            <div class="products-grid" id="recommendedProducts">
                <?php foreach($featured_products as $product): 
                    $image = !empty($product['image']) ? $product['image'] : 'default.jpg';
                    $discount = isset($product['discount_percent']) ? $product['discount_percent'] : 0;
                    $old_price = isset($product['old_price']) ? $product['old_price'] : 0;
                ?>
                <div class="product-card" data-id="<?php echo $product['id']; ?>">
                    <?php if($discount > 0): ?>
                    <div class="discount-badge">-<?php echo $discount; ?>%</div>
                    <?php endif; ?>
                    
                    <div class="product-image">
                        <a href="product.php?id=<?php echo $product['id']; ?>&slug=<?php echo urlencode($product['slug'] ?? $product['name']); ?>">
                            <img src="assets/images/products/<?php echo htmlspecialchars($image); ?>" 
                                 alt="<?php echo htmlspecialchars($product['name']); ?>"
                                 loading="lazy">
                        </a>
                        <button class="wishlist-btn active" 
                                onclick="toggleWishlist(<?php echo $product['id']; ?>, '<?php echo addslashes($product['name']); ?>', <?php echo $product['price']; ?>, '<?php echo addslashes($image); ?>')"
                                title="Hiq nga lista">
                            <i class="fas fa-heart"></i>
                        </button>
                    </div>
                    
                    <div class="product-info">
                        <h3><a href="product.php?id=<?php echo $product['id']; ?>&slug=<?php echo urlencode($product['slug'] ?? $product['name']); ?>"><?php echo htmlspecialchars($product['name']); ?></a></h3>
                        
                        <div class="product-price">
                            <span class="current-price"><?php echo number_format($product['price'], 2); ?> €</span>
                            <?php if($old_price > 0): ?>
                            <span class="old-price"><?php echo number_format($old_price, 2); ?> €</span>
                            <?php endif; ?>
                        </div>
                        
                        <div class="product-actions">
                            <button onclick="addToCart(<?php echo $product['id']; ?>, '<?php echo addslashes($product['name']); ?>', <?php echo $product['price']; ?>)" 
                                    class="add-to-cart-btn">
                                <i class="fas fa-cart-plus"></i> Shto në Shportë
                            </button>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>
</main>

<!-- ============================================
     FOOTER
     ============================================ -->
<?php include 'templates/footer.php'; ?>

<!-- ============================================
     JAVASCRIPT - WISHLIST MANAGEMENT
     ============================================ -->
<script>
// ============================================
// WISHLIST FUNCTIONS
// ============================================

// Constants
const WISHLIST_KEY = 'pinguin_wishlist';
const CART_KEY = 'pinguin_cart';

// Initialize wishlist on page load
document.addEventListener('DOMContentLoaded', function() {
    renderWishlist();
    updateWishlistCount();
    updateCartCount();
    
    // Shto event listener për storage change (nëse ndryshon wishlist në tab tjetër)
    window.addEventListener('storage', function(e) {
        if (e.key === WISHLIST_KEY) {
            renderWishlist();
            updateWishlistCount();
        }
    });
});

// Get wishlist from localStorage
function getWishlist() {
    try {
        return JSON.parse(localStorage.getItem(WISHLIST_KEY)) || [];
    } catch (e) {
        console.error('Error parsing wishlist:', e);
        return [];
    }
}

// Save wishlist to localStorage
function saveWishlist(wishlist) {
    localStorage.setItem(WISHLIST_KEY, JSON.stringify(wishlist));
    updateWishlistCount();
    
    // Trigger event për sinkronizim ndërmjet tabs
    window.dispatchEvent(new StorageEvent('storage', {
        key: WISHLIST_KEY,
        newValue: JSON.stringify(wishlist)
    }));
}

// Render wishlist products
function renderWishlist() {
    const wishlist = getWishlist();
    const container = document.getElementById('wishlistContainer');
    
    // Hide loading spinner pas 500ms (për animacion)
    setTimeout(() => {
        container.classList.add('loaded');
    }, 500);
    
    if (wishlist.length === 0) {
        // Empty wishlist - Dizajn i përmirësuar
        container.innerHTML = `
            <div class="empty-wishlist">
                <div class="empty-icon">
                    <i class="far fa-heart"></i>
                </div>
                <h2>Lista e dëshirave është bosh</h2>
                <p>Shto produktet që të pëlqejnë duke klikuar në ikonën <i class="fas fa-heart" style="color: #ff4d4d;"></i> pranë produkteve.</p>
                <div class="empty-actions">
                    <a href="index.php" class="btn-primary">
                        <i class="fas fa-home"></i> Faqja Kryesore
                    </a>
                    <a href="products.php" class="btn-secondary">
                        <i class="fas fa-search"></i> Shfleto Produktet
                    </a>
                </div>
            </div>
        `;
    } else {
        // Display wishlist items me dizajn të ri
        let html = '<div class="wishlist-grid">';
        
        wishlist.forEach((product, index) => {
            // Sigurohu që product ka të gjitha fushat e nevojshme
            const productId = product.id || 0;
            const productName = product.name || 'Produkt';
            const productPrice = product.price || 0;
            const productImage = product.image || 'default.jpg';
            
            html += `
                <div class="wishlist-card" data-id="${productId}" data-index="${index}">
                    <div class="wishlist-card-image">
                        <a href="product.php?id=${productId}">
                            <img src="assets/images/products/${productImage}" 
                                 alt="${productName}"
                                 loading="lazy">
                        </a>
                        <button class="remove-btn" onclick="removeFromWishlist(${index})" 
                                title="Hiq nga lista">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <div class="wishlist-card-content">
                        <h3><a href="product.php?id=${productId}">${productName}</a></h3>
                        <div class="wishlist-card-price">${formatPrice(productPrice)}</div>
                        <div class="wishlist-card-actions">
                            <button onclick="addToCart(${productId}, '${productName.replace(/'/g, "\\'")}', ${productPrice})" 
                                    class="add-to-cart-btn">
                                <i class="fas fa-cart-plus"></i> Shto në Shportë
                            </button>
                        </div>
                    </div>
                </div>
            `;
        });
        
        html += '</div>';
        
        // Shto butonin e pastrimit nëse ka më shumë se 1 produkt
        if (wishlist.length > 1) {
            html += `
                <div class="wishlist-actions-bottom">
                    <button onclick="clearWishlist()" class="btn-clear">
                        <i class="fas fa-trash-alt"></i> Pastro Listën (${wishlist.length} produkte)
                    </button>
                </div>
            `;
        }
        
        container.innerHTML = html;
    }
}

// Add to wishlist
function addToWishlist(productId, productName, productPrice, productImage = 'default.jpg') {
    let wishlist = getWishlist();
    
    // Check if product already exists
    const existingIndex = wishlist.findIndex(item => item.id == productId);
    
    if (existingIndex === -1) {
        // Add new product
        wishlist.push({
            id: productId,
            name: productName,
            price: productPrice,
            image: productImage,
            addedAt: new Date().toISOString()
        });
        
        saveWishlist(wishlist);
        showNotification('✅ ' + productName + ' u shtua në listën e dëshirave!', 'success');
        
        // Ndrysho ikonat në faqe nëse ekzistojnë
        updateWishlistButtons(productId, true);
        
        // If we're on wishlist page, refresh
        if (window.location.pathname.includes('wishlist.php')) {
            renderWishlist();
        }
    } else {
        showNotification('ℹ️ ' + productName + ' është tashmë në listën e dëshirave!', 'info');
    }
    
    updateWishlistCount();
}

// Remove from wishlist
function removeFromWishlist(index) {
    let wishlist = getWishlist();
    const product = wishlist[index];
    
    if (!product) return;
    
    const productId = product.id;
    const productName = product.name;
    
    wishlist.splice(index, 1);
    saveWishlist(wishlist);
    
    showNotification('🗑️ ' + productName + ' u hoq nga lista e dëshirave!', 'info');
    
    // Ndrysho ikonat në faqe nëse ekzistojnë
    updateWishlistButtons(productId, false);
    
    renderWishlist();
}

// Remove by product ID
function removeFromWishlistById(productId) {
    let wishlist = getWishlist();
    const index = wishlist.findIndex(item => item.id == productId);
    
    if (index !== -1) {
        removeFromWishlist(index);
    }
}

// Toggle wishlist (add/remove)
function toggleWishlist(productId, productName, productPrice, productImage) {
    let wishlist = getWishlist();
    const existingIndex = wishlist.findIndex(item => item.id == productId);
    
    if (existingIndex === -1) {
        addToWishlist(productId, productName, productPrice, productImage);
    } else {
        removeFromWishlist(existingIndex);
    }
}

// Update all wishlist buttons on the page
function updateWishlistButtons(productId, isInWishlist) {
    const buttons = document.querySelectorAll(`.wishlist-btn[data-id="${productId}"], .product-card[data-id="${productId}"] .wishlist-btn`);
    
    buttons.forEach(btn => {
        if (isInWishlist) {
            btn.classList.add('active');
            btn.innerHTML = '<i class="fas fa-heart"></i>';
            btn.title = 'Hiq nga lista';
        } else {
            btn.classList.remove('active');
            btn.innerHTML = '<i class="far fa-heart"></i>';
            btn.title = 'Shto në listë';
        }
    });
}

// Clear entire wishlist
function clearWishlist() {
    if (confirm('A jeni i sigurt që doni të pastroni të gjithë listën e dëshirave?')) {
        localStorage.removeItem(WISHLIST_KEY);
        showNotification('🗑️ Lista e dëshirave u pastrua!', 'info');
        renderWishlist();
        updateWishlistCount();
        
        // Përditëso të gjithë butonat në faqe
        document.querySelectorAll('.wishlist-btn').forEach(btn => {
            btn.classList.remove('active');
            btn.innerHTML = '<i class="far fa-heart"></i>';
            btn.title = 'Shto në listë';
        });
    }
}

// ============================================
// CART FUNCTIONS
// ============================================

// Get cart from localStorage
function getCart() {
    try {
        return JSON.parse(localStorage.getItem(CART_KEY)) || [];
    } catch (e) {
        console.error('Error parsing cart:', e);
        return [];
    }
}

// Save cart to localStorage
function saveCart(cart) {
    localStorage.setItem(CART_KEY, JSON.stringify(cart));
    updateCartCount();
}

// Add to cart
function addToCart(productId, productName, productPrice, quantity = 1) {
    let cart = getCart();
    
    let existingProduct = cart.find(item => item.id == productId);
    
    if (existingProduct) {
        existingProduct.quantity += quantity;
        showNotification('➕ ' + productName + ' u shtua në shportë! (' + existingProduct.quantity + ' gjithsej)', 'success');
    } else {
        cart.push({
            id: productId,
            name: productName,
            price: productPrice,
            quantity: quantity
        });
        showNotification('✅ ' + productName + ' u shtua në shportë!', 'success');
    }
    
    saveCart(cart);
    
    // Animate cart icon
    animateCartIcon();
}

// ============================================
// UTILITY FUNCTIONS
// ============================================

// Format price
function formatPrice(price) {
    return new Intl.NumberFormat('sq-AL', {
        style: 'currency',
        currency: 'EUR',
        minimumFractionDigits: 2
    }).format(price);
}

// Update wishlist count in header
function updateWishlistCount() {
    const wishlist = getWishlist();
    const countElements = document.querySelectorAll('.wishlist-count, #wishlistCount');
    
    countElements.forEach(el => {
        if (wishlist.length > 0) {
            el.textContent = wishlist.length;
            el.style.display = 'flex';
            el.style.animation = 'pulse 0.3s ease';
            setTimeout(() => {
                el.style.animation = '';
            }, 300);
        } else {
            el.textContent = '0';
            el.style.display = 'flex';
        }
    });
}

// Update cart count in header
function updateCartCount() {
    const cart = getCart();
    const countElements = document.querySelectorAll('.cart-count, #cartCount');
    const totalItems = cart.reduce((total, item) => total + item.quantity, 0);
    
    countElements.forEach(el => {
        if (totalItems > 0) {
            el.textContent = totalItems;
            el.style.display = 'flex';
        } else {
            el.textContent = '0';
            el.style.display = 'flex';
        }
    });
}

// Animate cart icon
function animateCartIcon() {
    const cartIcon = document.querySelector('.action-link i.fa-shopping-cart, .cart-icon');
    if (cartIcon) {
        cartIcon.classList.add('bounce');
        setTimeout(() => {
            cartIcon.classList.remove('bounce');
        }, 500);
    }
}

// Show notification
function showNotification(message, type = 'info') {
    // Check if notification container exists
    let container = document.querySelector('.notification-container');
    
    if (!container) {
        container = document.createElement('div');
        container.className = 'notification-container';
        document.body.appendChild(container);
    }
    
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.innerHTML = message;
    
    container.appendChild(notification);
    
    // Play sound (optional)
    // if (type === 'success') {
    //     new Audio('assets/sounds/success.mp3').play().catch(e => {});
    // }
    
    // Remove after 3 seconds
    setTimeout(() => {
        notification.classList.add('fade-out');
        setTimeout(() => {
            notification.remove();
        }, 300);
    }, 3000);
}

// Initialize wishlist buttons on page load
function initWishlistButtons() {
    const wishlist = getWishlist();
    const wishlistIds = wishlist.map(item => item.id);
    
    document.querySelectorAll('.wishlist-btn').forEach(btn => {
        const productId = btn.closest('.product-card')?.dataset.id || btn.dataset.id;
        if (productId && wishlistIds.includes(parseInt(productId))) {
            btn.classList.add('active');
            btn.innerHTML = '<i class="fas fa-heart"></i>';
            btn.title = 'Hiq nga lista';
        }
    });
}

// Call init after DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    initWishlistButtons();
});
</script>

<!-- ============================================
     ADDITIONAL STYLES
     ============================================ -->
<style>
/* Variables */
:root {
    --color-primary: #0066cc;
    --color-primary-dark: #0052a3;
    --color-success: #28a745;
    --color-danger: #dc3545;
    --color-warning: #ffc107;
    --color-info: #17a2b8;
    --color-gray: #f8f9fa;
    --text-color: #333;
    --border-radius: 8px;
    --border-radius-lg: 15px;
    --box-shadow: 0 5px 15px rgba(0,0,0,0.08);
    --box-shadow-hover: 0 8px 25px rgba(0,0,0,0.15);
}

/* Notification Container */
.notification-container {
    position: fixed;
    top: 20px;
    right: 20px;
    z-index: 9999;
}

.notification {
    min-width: 320px;
    padding: 15px 20px;
    margin-bottom: 10px;
    border-radius: var(--border-radius);
    box-shadow: 0 5px 20px rgba(0,0,0,0.15);
    animation: slideIn 0.3s cubic-bezier(0.68, -0.55, 0.265, 1.55);
    border-left: 4px solid;
    font-weight: 500;
    display: flex;
    align-items: center;
    gap: 10px;
}

.notification::before {
    font-family: 'Font Awesome 5 Free';
    font-weight: 900;
    font-size: 20px;
}

.notification-success {
    border-left-color: var(--color-success);
    background: linear-gradient(135deg, #d4edda, #c3e6cb);
    color: #155724;
}

.notification-success::before {
    content: '\f058';
    color: var(--color-success);
}

.notification-info {
    border-left-color: var(--color-info);
    background: linear-gradient(135deg, #d1ecf1, #bee5eb);
    color: #0c5460;
}

.notification-info::before {
    content: '\f05a';
    color: var(--color-info);
}

.notification-error {
    border-left-color: var(--color-danger);
    background: linear-gradient(135deg, #f8d7da, #f5c6cb);
    color: #721c24;
}

.notification-error::before {
    content: '\f057';
    color: var(--color-danger);
}

.notification.fade-out {
    animation: fadeOut 0.3s cubic-bezier(0.68, -0.55, 0.265, 1.55) forwards;
}

@keyframes slideIn {
    from {
        transform: translateX(100%) scale(0.8);
        opacity: 0;
    }
    to {
        transform: translateX(0) scale(1);
        opacity: 1;
    }
}

@keyframes fadeOut {
    from {
        transform: translateX(0) scale(1);
        opacity: 1;
    }
    to {
        transform: translateX(100%) scale(0.8);
        opacity: 0;
    }
}

/* Wishlist Hero */
.wishlist-hero {
    background: linear-gradient(135deg, var(--color-primary), #4d94ff);
    color: white;
    padding: 60px 0;
    margin-bottom: 40px;
    text-align: center;
}

.wishlist-hero-content h1 {
    font-size: 42px;
    font-weight: 700;
    margin-bottom: 15px;
    text-shadow: 2px 2px 4px rgba(0,0,0,0.2);
}

.wishlist-hero-content h1 i {
    margin-right: 10px;
    color: #ff4d4d;
}

.wishlist-hero-content p {
    font-size: 18px;
    opacity: 0.95;
}

/* Loading Spinner */
.loading-spinner {
    text-align: center;
    padding: 60px;
}

.spinner {
    width: 50px;
    height: 50px;
    border: 3px solid rgba(0,102,204,0.1);
    border-top-color: var(--color-primary);
    border-radius: 50%;
    animation: spin 1s linear infinite;
    margin: 0 auto 20px;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}

.loading-spinner p {
    color: #666;
    font-size: 16px;
}

.wishlist-container {
    min-height: 400px;
    opacity: 1;
    transition: opacity 0.3s ease;
}

.wishlist-container.loaded .loading-spinner {
    display: none;
}

/* Wishlist Grid */
.wishlist-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 25px;
    margin: 30px 0;
}

/* Wishlist Card */
.wishlist-card {
    background: white;
    border-radius: var(--border-radius-lg);
    overflow: hidden;
    box-shadow: var(--box-shadow);
    transition: all 0.3s cubic-bezier(0.165, 0.84, 0.44, 1);
    position: relative;
    border: 1px solid rgba(0,0,0,0.05);
}

.wishlist-card:hover {
    transform: translateY(-8px);
    box-shadow: var(--box-shadow-hover);
}

.wishlist-card-image {
    position: relative;
    width: 100%;
    height: 220px;
    background: var(--color-gray);
    overflow: hidden;
}

.wishlist-card-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.5s cubic-bezier(0.165, 0.84, 0.44, 1);
}

.wishlist-card:hover .wishlist-card-image img {
    transform: scale(1.08);
}

.remove-btn {
    position: absolute;
    top: 12px;
    right: 12px;
    width: 40px;
    height: 40px;
    background: rgba(255,255,255,0.95);
    color: var(--color-danger);
    border: none;
    border-radius: 50%;
    font-size: 18px;
    cursor: pointer;
    transition: all 0.3s cubic-bezier(0.165, 0.84, 0.44, 1);
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transform: scale(0.8);
    box-shadow: 0 3px 10px rgba(0,0,0,0.1);
    z-index: 10;
}

.wishlist-card:hover .remove-btn {
    opacity: 1;
    transform: scale(1);
}

.remove-btn:hover {
    background: var(--color-danger);
    color: white;
    transform: scale(1.1) !important;
    box-shadow: 0 5px 15px rgba(220,53,69,0.3);
}

.wishlist-card-content {
    padding: 20px;
}

.wishlist-card-content h3 {
    font-size: 18px;
    font-weight: 600;
    margin-bottom: 10px;
    line-height: 1.4;
}

.wishlist-card-content h3 a {
    color: var(--text-color);
    text-decoration: none;
    transition: color 0.3s;
}

.wishlist-card-content h3 a:hover {
    color: var(--color-primary);
}

.wishlist-card-price {
    font-size: 22px;
    font-weight: 700;
    color: var(--color-primary);
    margin-bottom: 15px;
}

.wishlist-card-actions {
    display: flex;
    gap: 10px;
}

.add-to-cart-btn {
    flex: 1;
    padding: 12px;
    background: linear-gradient(135deg, var(--color-primary), var(--color-primary-dark));
    color: white;
    border: none;
    border-radius: var(--border-radius);
    font-weight: 600;
    font-size: 14px;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
}

.add-to-cart-btn:hover {
    background: linear-gradient(135deg, var(--color-primary-dark), #004080);
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(0,102,204,0.3);
}

.add-to-cart-btn i {
    font-size: 16px;
}

/* Empty Wishlist */
.empty-wishlist {
    text-align: center;
    padding: 80px 20px;
    background: white;
    border-radius: var(--border-radius-lg);
    box-shadow: var(--box-shadow);
    margin: 40px 0;
}

.empty-icon {
    width: 120px;
    height: 120px;
    background: linear-gradient(135deg, #ffd4d4, #ffe6e6);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 25px;
}

.empty-icon i {
    font-size: 60px;
    color: #ff4d4d;
    animation: heartbeat 1.5s ease-in-out infinite;
}

@keyframes heartbeat {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.1); }
}

.empty-wishlist h2 {
    font-size: 28px;
    margin-bottom: 15px;
    color: var(--text-color);
}

.empty-wishlist p {
    font-size: 16px;
    color: #666;
    margin-bottom: 30px;
    max-width: 500px;
    margin-left: auto;
    margin-right: auto;
}

.empty-actions {
    display: flex;
    gap: 15px;
    justify-content: center;
    flex-wrap: wrap;
}

.btn-primary, .btn-secondary {
    padding: 14px 35px;
    border-radius: 50px;
    text-decoration: none;
    font-weight: 600;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}

.btn-primary {
    background: linear-gradient(135deg, var(--color-primary), var(--color-primary-dark));
    color: white;
    box-shadow: 0 5px 15px rgba(0,102,204,0.2);
}

.btn-primary:hover {
    background: linear-gradient(135deg, var(--color-primary-dark), #004080);
    transform: translateY(-3px);
    box-shadow: 0 8px 20px rgba(0,102,204,0.3);
}

.btn-secondary {
    background: white;
    color: var(--color-primary);
    border: 2px solid var(--color-primary);
}

.btn-secondary:hover {
    background: var(--color-primary);
    color: white;
    transform: translateY(-3px);
    box-shadow: 0 5px 15px rgba(0,102,204,0.2);
}

/* Wishlist Actions Bottom */
.wishlist-actions-bottom {
    text-align: center;
    margin: 40px 0;
}

.btn-clear {
    padding: 14px 35px;
    background: linear-gradient(135deg, var(--color-danger), #c82333);
    color: white;
    border: none;
    border-radius: 50px;
    font-weight: 600;
    font-size: 16px;
    cursor: pointer;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    gap: 10px;
    box-shadow: 0 5px 15px rgba(220,53,69,0.2);
}

.btn-clear:hover {
    background: linear-gradient(135deg, #c82333, #a71d2a);
    transform: translateY(-3px);
    box-shadow: 0 8px 20px rgba(220,53,69,0.3);
}

/* Recommended Section */
.recommended-section {
    background: var(--color-gray);
    padding: 80px 0;
    margin-top: 60px;
    border-top: 1px solid rgba(0,0,0,0.05);
}

.section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 40px;
}

.section-header h2 {
    font-size: 28px;
    color: var(--text-color);
    display: flex;
    align-items: center;
    gap: 10px;
}

.section-header h2 i {
    color: #ffc107;
}

.view-all {
    color: var(--color-primary);
    text-decoration: none;
    font-weight: 600;
    transition: all 0.3s;
    display: flex;
    align-items: center;
    gap: 5px;
}

.view-all:hover {
    color: var(--color-primary-dark);
    gap: 10px;
}

/* Products Grid */
.products-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap: 25px;
}

.product-card {
    background: white;
    border-radius: var(--border-radius-lg);
    overflow: hidden;
    box-shadow: var(--box-shadow);
    transition: all 0.3s ease;
    position: relative;
}

.product-card:hover {
    transform: translateY(-5px);
    box-shadow: var(--box-shadow-hover);
}

.discount-badge {
    position: absolute;
    top: 12px;
    left: 12px;
    background: linear-gradient(135deg, var(--color-danger), #ff6b6b);
    color: white;
    padding: 5px 12px;
    border-radius: 50px;
    font-size: 14px;
    font-weight: 700;
    z-index: 2;
    box-shadow: 0 3px 10px rgba(220,53,69,0.3);
}

.product-image {
    position: relative;
    width: 100%;
    height: 200px;
    overflow: hidden;
}

.product-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.5s ease;
}

.product-card:hover .product-image img {
    transform: scale(1.08);
}

.wishlist-btn {
    position: absolute;
    top: 12px;
    right: 12px;
    width: 40px;
    height: 40px;
    background: white;
    border: none;
    border-radius: 50%;
    color: #ff4d4d;
    font-size: 18px;
    cursor: pointer;
    transition: all 0.3s cubic-bezier(0.165, 0.84, 0.44, 1);
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 3px 10px rgba(0,0,0,0.1);
    z-index: 10;
}

.wishlist-btn:hover {
    transform: scale(1.1);
    background: #ff4d4d;
    color: white;
}

.wishlist-btn.active {
    background: #ff4d4d;
    color: white;
}

.product-info {
    padding: 20px;
}

.product-info h3 {
    font-size: 16px;
    margin-bottom: 10px;
    line-height: 1.4;
    height: 44px;
    overflow: hidden;
}

.product-info h3 a {
    color: var(--text-color);
    text-decoration: none;
    transition: color 0.3s;
}

.product-info h3 a:hover {
    color: var(--color-primary);
}

.product-price {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 15px;
}

.current-price {
    font-size: 20px;
    font-weight: 700;
    color: var(--color-primary);
}

.old-price {
    font-size: 14px;
    color: #999;
    text-decoration: line-through;
}

.product-actions {
    display: flex;
    gap: 10px;
}

/* Bounce Animation */
@keyframes bounce {
    0%, 100% { transform: scale(1); }
    30% { transform: scale(1.3); }
    50% { transform: scale(0.95); }
    70% { transform: scale(1.1); }
}

.bounce {
    animation: bounce 0.5s cubic-bezier(0.165, 0.84, 0.44, 1);
}

/* Pulse Animation */
@keyframes pulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.2); }
    100% { transform: scale(1); }
}

/* Responsive Design */
@media (max-width: 992px) {
    .wishlist-hero {
        padding: 50px 0;
    }
    
    .wishlist-hero-content h1 {
        font-size: 36px;
    }
}

@media (max-width: 768px) {
    .wishlist-grid {
        grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
        gap: 15px;
    }
    
    .section-header {
        flex-direction: column;
        text-align: center;
        gap: 15px;
    }
    
    .empty-actions {
        flex-direction: column;
        align-items: center;
        gap: 10px;
    }
    
    .btn-primary, .btn-secondary, .btn-clear {
        width: 100%;
        max-width: 280px;
        justify-content: center;
    }
    
    .notification-container {
        left: 20px;
        right: 20px;
    }
    
    .notification {
        min-width: auto;
        width: 100%;
    }
    
    .products-grid {
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        gap: 15px;
    }
}

@media (max-width: 480px) {
    .wishlist-hero-content h1 {
        font-size: 28px;
    }
    
    .wishlist-hero-content p {
        font-size: 16px;
    }
    
    .wishlist-grid {
        grid-template-columns: 1fr;
    }
    
    .wishlist-card-image {
        height: 250px;
    }
    
    .empty-icon {
        width: 100px;
        height: 100px;
    }
    
    .empty-icon i {
        font-size: 50px;
    }
    
    .empty-wishlist h2 {
        font-size: 24px;
    }
    
    .products-grid {
        grid-template-columns: 1fr 1fr;
    }
}
</style>