<footer>
    <div class="container">
        <div class="footer-content">
            <div class="footer-section">
                <h3>Swedish Recipes</h3>
                <p>Delicious recipes for every occasion.</p>
            </div>
            <div class="footer-section">
                <h3>Categories</h3>
                <ul>
                    <li><a href="category.php?category=breakfast">Breakfast</a></li>
                    <li><a href="category.php?category=lunch">Lunch</a></li>
                    <li><a href="category.php?category=dinner">Dinner</a></li>
                    <li><a href="category.php?category=dessert">Desserts</a></li>
                    <li><a href="category.php?category=drinks">Drinks</a></li>
                    <li><a href="category.php?category=snacks">Snacks</a></li>
                    <li><a href="category.php?category=appetizers">Appetizers</a></li>
                </ul>
            </div>
            <div class="footer-section">
                <h3>Links</h3>
                <ul>
                    <li><a href="top-favorites.php">Top Saved Recipes</a></li>
                    <li><a href="favorites-page.php">My Saved Recipes</a></li>
                    <li>
  <a href="<?php echo isset($_SESSION['user_id']) ? 'import.php' : 'login.php'; ?>">
        <i class="fas fa-upload"></i> Import Recipe</a></li>
        <?php if (isset($_SESSION['user_id'])): ?>
  <li>
    <a href="/php/logout.php">
      <i class="fas fa-sign-out-alt"></i> Logout
    </a>
  </li>
<?php endif; ?>
                    <li><a href="terms.php">Terms & Conditions</a></li>
                </ul>
            </div>
            <div class="footer-section">
                <h3>Connect With Us</h3>
                <div class="social-icons">
                    <a href="#"><i class="fab fa-facebook"></i></a>
                    <a href="#"><i class="fab fa-instagram"></i></a>
                    <a href="#"><i class="fab fa-pinterest"></i></a>
                    <a href="#"><i class="fab fa-youtube"></i></a>
                </div>
                <div class="language-selector">
                    <select aria-label="Change language">
                        <option value="en">English</option>
                        <option value="sv">Svenska</option>
                    </select>
                </div>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; 2025 Swedish Recipes. All rights reserved.</p>
        </div>
    </div>
</footer>