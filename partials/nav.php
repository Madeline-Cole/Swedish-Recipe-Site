<header>
  <div class="container">
    <div class="logo">
      <a href="index.php">
        <img src="images/dala-horse.webp" alt="Dala Horse">
        <h1>Swedish Recipes</h1>
      </a>
    </div>
    <nav>
      <div class="menu-toggle">
        <i class="fas fa-bars"></i>
      </div>
      <ul class="nav-menu">
        <li><a href="index.php">Home</a></li>
        <?php
$currentCategory = $_GET['category'] ?? '';
$navCategories = [
  'breakfast' => 'Breakfast',
  'lunch' => 'Lunch',
  'dinner' => 'Dinner',
  'dessert' => 'Desserts',
  'drinks' => 'Drinks',
  'snacks' => 'Snacks',
  'appetizers' => 'Appetizers'
];
foreach ($navCategories as $slug => $label) {
    $isActive = ($currentCategory === $slug) ? 'active' : '';
    echo "<li><a href=\"category.php?category=$slug\" class=\"$isActive\">$label</a></li>";
}
?>
        <li class="user-menu">
          <a href="profile.php" id="user-dropdown-toggle">
            <img src="images/default-avatar.png" alt="User Avatar" id="user-avatar">
            <span id="user-name">User</span>
          </a>
          <ul class="dropdown-menu">
            <li><a href="profile.php"><i class="fas fa-user-circle"></i> My Profile</a></li>
            <li><a href="favorites.php"><i class="fas fa-heart"></i> Favorites <span id="favorite-count">0</span></a></li>
            <li>
  <a href="<?php echo isset($_SESSION['user_id']) ? 'import.php' : 'login.php'; ?>">
          <i class="fas fa-upload"></i> Import</a></li>
          <li>
  <a href="/php/logout.php" class="logout-link">
    <i class="fas fa-sign-out-alt"></i> Logout
  </a>
</li>
          </ul>
        </li>

        <!-- Mobile-only dropdown items shown inline -->
        <li class="mobile-only"><a href="profile.php"><i class="fas fa-user-circle"></i> My Profile</a></li>
        <li class="mobile-only"><a href="favorites.php"><i class="fas fa-heart"></i> My Favorites</a></li>
        <li class="mobile-only">
  <a href="/php/logout.php" class="logout-link">
    <i class="fas fa-sign-out-alt"></i> Logout
  </a>
</li>
      </ul>
    </nav>
  </div>
</header>