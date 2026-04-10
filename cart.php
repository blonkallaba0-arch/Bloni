<?php
// ============================================
// CART.PHP - SHPORTA E BLERJES
// ============================================

// Session start
session_start();

// Përfshij header template
include 'templates/header.php';
?>

<main>
    <!-- ============================================
         CART HEADER
         ============================================ -->
    <div class="container">
        <section class="cart-header">
            <h1>🛒 Shporta e Blerjes</h1>
            <p>Shqyrto produktet e zgjedhura dhe vazhdo me pagesen</p>
        </section>
    </div>

    <!-- ============================================
         CART CONTENT
         ============================================ -->
    <div class="container">
        <div class="cart-wrapper">
            <!-- PRODUKTET E SHPORTËS -->
            <div class="cart-items" id="cartItems">
                <!-- Produktet do të renderohen me JavaScript -->
            </div>

            <!-- REZYME E SHPORTËS -->
            <div class="cart-summary">
                <h2>Rezyme e Shportës</h2>
                
                <div class="summary-row">
                    <span>Nënshuma:</span>
                    <span id="subtotal">0€</span>
                </div>
                
                <div class="summary-row">
                    <span>Dërgesa:</span>
                    <span id="shipping">0€</span>
                </div>
                
                <div class="summary-row discount-row" id="discountRow" style="display:none;">
                    <span>Zbritje (50€+):</span>
                    <span id="discount" class="discount-value">-0€</span>
                </div>
                
                <div class="summary-row total-row">
                    <span>Totali:</span>
                    <span id="total">0€</span>
                </div>
                
                <button class="checkout-btn" id="checkoutBtn">Vazhdo me Pagesen</button>
                <a href="index.php" class="continue-shopping-btn">Vazhdo me Blerjen</a>
            </div>
        </div>
    </div>

</main>

<!-- ============================================
     FOOTER
     ============================================ -->
<?php include 'templates/footer.php'; ?>

<!-- ============================================
     JAVASCRIPT - CART MANAGEMENT
     ============================================ -->
<script>
// Merr shportën nga localStorage
function getCart() {
    return JSON.parse(localStorage.getItem('cart')) || [];
}

// Shfaqi produktet e shportës
function renderCart() {
    const cart = getCart();
    const container = document.getElementById('cartItems');
    
    if (cart.length === 0) {
        // Nëse shporta është bosh
        container.innerHTML = `
            <div class="empty-cart">
                <h2>Shporta e blerjes është bosh</h2>
                <p>Shto produktet nga <a href="index.php">faqja kryesore</a>.</p>
            </div>
        `;
        document.getElementById('checkoutBtn').disabled = true;
    } else {
        // Nëse ka produkte
        let html = '<div class="cart-items-list">';
        
        cart.forEach((product, index) => {
            const itemTotal = product.price * product.quantity;
            html += `
                <div class="cart-item">
                    <div class="item-img">
                        <img src="assets/images/placeholder.jpg" alt="${product.name}">
                    </div>
                    <div class="item-info">
                        <h3>${product.name}</h3>
                        <p class="item-price">${product.price}€</p>
                    </div>
                    <div class="item-quantity">
                        <button onclick="decreaseQuantity(${index})">−</button>
                        <input type="number" value="${product.quantity}" class="qty-input" readonly>
                        <button onclick="increaseQuantity(${index})">+</button>
                    </div>
                    <div class="item-total">
                        <span>${itemTotal.toFixed(2)}€</span>
                    </div>
                    <button class="remove-item-btn" onclick="removeFromCart(${index})" title="Hiq produktin">×</button>
                </div>
            `;
        });
        
        html += '</div>';
        container.innerHTML = html;
        document.getElementById('checkoutBtn').disabled = false;
    }
    
    // Përditëso rezumën
    updateSummary();
}

// Shto në sasi
function increaseQuantity(index) {
    let cart = getCart();
    if (cart[index]) {
        cart[index].quantity += 1;
        localStorage.setItem('cart', JSON.stringify(cart));
        renderCart();
    }
}

// Zbut sasina
function decreaseQuantity(index) {
    let cart = getCart();
    if (cart[index] && cart[index].quantity > 1) {
        cart[index].quantity -= 1;
        localStorage.setItem('cart', JSON.stringify(cart));
        renderCart();
    }
}

// Hiq produktin nga shporta
function removeFromCart(index) {
    let cart = getCart();
    cart.splice(index, 1);
    localStorage.setItem('cart', JSON.stringify(cart));
    renderCart();
}

// Përditëso rezumën e shportës
function updateSummary() {
    const cart = getCart();
    
    // Llogarit nënshuma
    const subtotal = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
    
    // Llogarit dërgesa (falas nëse > 50€)
    const shipping = subtotal >= 50 ? 0 : 4.99;
    
    // Zbritje (10% nëse > 100€)
    const discount = subtotal >= 100 ? subtotal * 0.1 : 0;
    
    // Totali
    const total = subtotal + shipping - discount;
    
    // Përditëso DOM
    document.getElementById('subtotal').textContent = subtotal.toFixed(2) + '€';
    document.getElementById('shipping').textContent = shipping.toFixed(2) + '€';
    document.getElementById('total').textContent = total.toFixed(2) + '€';
    
    // Shfaqi/Fshih zbritjen
    const discountRow = document.getElementById('discountRow');
    if (discount > 0) {
        discountRow.style.display = 'flex';
        document.getElementById('discount').textContent = '-' + discount.toFixed(2) + '€';
    } else {
        discountRow.style.display = 'none';
    }
    
    // Përditëso cart count
    const cartCountEl = document.getElementById('cartCount');
    const totalItems = cart.reduce((total, item) => total + item.quantity, 0);
    cartCountEl.textContent = totalItems;
}

// Vazhdo me pagesen
document.addEventListener('DOMContentLoaded', function() {
    renderCart();
    
    document.getElementById('checkoutBtn').addEventListener('click', function() {
        alert('Sistemi i pagesës do të implementohet në të ardhmen!');
    });
});
</script>

<style>
/* Cart Styling */
.cart-header {
    padding: var(--spacing-xl) 0;
    margin-bottom: var(--spacing-xl);
}

.cart-header h1 {
    font-size: 36px;
    font-weight: bold;
    margin-bottom: var(--spacing-md);
}

.cart-wrapper {
    display: grid;
    grid-template-columns: 1fr 350px;
    gap: var(--spacing-xl);
    margin: var(--spacing-xl) 0;
}

.empty-cart {
    background-color: var(--color-gray);
    border-radius: var(--border-radius-lg);
    padding: var(--spacing-xl);
    text-align: center;
    grid-column: 1 / -1;
}

.empty-cart h2 {
    margin-bottom: var(--spacing-md);
}

.empty-cart a {
    color: var(--color-primary);
}

.cart-items-list {
    background: white;
    border: 1px solid var(--color-border);
    border-radius: var(--border-radius-lg);
}

.cart-item {
    display: grid;
    grid-template-columns: 80px 1fr 100px 80px 40px;
    gap: var(--spacing-md);
    align-items: center;
    padding: var(--spacing-md);
    border-bottom: 1px solid var(--color-border);
}

.item-img {
    width: 80px;
    height: 80px;
    background-color: var(--color-gray);
    border-radius: var(--border-radius);
    overflow: hidden;
}

.item-img img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.item-info h3 {
    font-size: 14px;
    font-weight: bold;
    margin-bottom: var(--spacing-xs);
}

.item-price {
    color: var(--color-primary);
    font-weight: bold;
}

.item-quantity {
    display: flex;
    align-items: center;
    gap: var(--spacing-xs);
}

.item-quantity button {
    width: 30px;
    height: 30px;
    border: 1px solid var(--color-border);
    background: white;
    cursor: pointer;
    border-radius: 4px;
}

.qty-input {
    width: 40px;
    text-align: center;
    border: none;
    font-weight: bold;
}

.item-total {
    font-weight: bold;
    text-align: right;
}

.remove-item-btn {
    width: 30px;
    height: 30px;
    background-color: #dc3545;
    color: white;
    border: none;
    border-radius: 50%;
    font-size: 18px;
    cursor: pointer;
}

.cart-summary {
    background: white;
    border: 1px solid var(--color-border);
    border-radius: var(--border-radius-lg);
    padding: var(--spacing-lg);
    height: fit-content;
    position: sticky;
    top: 220px;
}

.cart-summary h2 {
    margin-bottom: var(--spacing-lg);
    font-size: 18px;
}

.summary-row {
    display: flex;
    justify-content: space-between;
    margin-bottom: var(--spacing-sm);
    font-size: 14px;
}

.discount-row {
    color: #28a745;
    font-weight: bold;
}

.total-row {
    border-top: 2px solid var(--color-border);
    padding-top: var(--spacing-md);
    margin-top: var(--spacing-md);
    font-weight: bold;
    font-size: 18px;
}

.checkout-btn {
    width: 100%;
    padding: var(--spacing-md);
    background-color: var(--color-primary);
    color: white;
    border: none;
    border-radius: var(--border-radius);
    font-weight: bold;
    cursor: pointer;
    margin-top: var(--spacing-lg);
    transition: background 0.3s ease;
}

.checkout-btn:hover:not(:disabled) {
    background-color: #005fa3;
}

.checkout-btn:disabled {
    background-color: #ccc;
    cursor: not-allowed;
}

.continue-shopping-btn {
    display: block;
    text-align: center;
    margin-top: var(--spacing-md);
    padding: var(--spacing-sm);
    color: var(--color-primary);
    text-decoration: none;
    font-size: 14px;
}
</style>

</body>
</html>
