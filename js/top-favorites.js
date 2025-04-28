document.addEventListener('DOMContentLoaded', function () {
    const userNameSpan = document.getElementById('user-name');
const savedUserName = localStorage.getItem('userName');
const logoLink = document.querySelector('.logo a');
    const logoImg = document.querySelector('.logo img');
    const menuToggle = document.querySelector('.menu-toggle');
    const navMenu = document.querySelector('.nav-menu');
  
    if (menuToggle && navMenu) {
      menuToggle.addEventListener('click', () => {
        navMenu.classList.toggle('active');
        menuToggle.classList.toggle('active');
      });
    }

    if (logoLink && logoImg) {
        const originalSrc = logoImg.getAttribute('src');
        const yellowSrc = 'images/dala-horse-yellow.webp'; // Your yellow horse path

        logoLink.addEventListener('mouseenter', function() {
            logoImg.style.opacity = '0'; // fade out
            setTimeout(() => {
                logoImg.setAttribute('src', yellowSrc);
                logoImg.style.opacity = '1'; // fade in
            }, 150); // wait 150ms before swapping src
        });

        logoLink.addEventListener('mouseleave', function() {
            logoImg.style.opacity = '0'; // fade out
            setTimeout(() => {
                logoImg.setAttribute('src', originalSrc);
                logoImg.style.opacity = '1'; // fade in
            }, 150);
        });
    }

if (savedUserName && userNameSpan) {
    userNameSpan.textContent = savedUserName;
}
    fetch('php/top-favorites.php')
        .then(res => res.json())
        .then(topRecipes => {
            const topContainer = document.getElementById('top-favorites-container');
            if (topContainer) {
                topContainer.innerHTML = '';
                topRecipes.forEach((recipe, index) => {
                    const card = document.createElement('div');
                    card.className = 'recipe-card';
                    card.innerHTML = `
                        <div class="rank-badge">#${index + 1}</div>
                        <div class="recipe-image">
                            <img src="${recipe.image}" alt="${recipe.title}">
                        </div>
                        <div class="recipe-content">
                            <h3>${recipe.title}</h3>
                            <div class="recipe-meta">
                                <span><i class="far fa-clock"></i> ${recipe.time} mins</span>
                                <span><i class="fas fa-fire"></i> ${recipe.calories} cal</span>
                            </div>
                            <a href="recipe.php?id=${recipe.id}" class="btn btn-small">View Recipe</a>
                        </div>
                    `;
                    topContainer.appendChild(card);
                });
            }
        });
});