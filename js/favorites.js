document.addEventListener('DOMContentLoaded', () => {
  fetch('php/favorites.php?action=list', {
    credentials: 'same-origin'
  })
  .then(r => {
    if (r.status === 401) {
      window.location.href = 'login.php?redirect=favorites.php';
      throw new Error('Unauthorized');
    }
    return r.json();
  })
  .then(favoriteRecipes => {
    const container = document.getElementById('favorites-container');
    const noMsg     = document.getElementById('no-favorites');
    if (!container || !noMsg) return;

    if (favoriteRecipes.length === 0) {
      container.style.display = 'none';
      noMsg.style.display     = 'block';
      return;
    }

    container.style.display = 'grid';
    noMsg.style.display     = 'none';
    container.innerHTML     = '';

    favoriteRecipes.forEach(recipe => {
      // build the correct href, falling back to id
      const href = recipe.slug
        ? `recipe.php?slug=${encodeURIComponent(recipe.slug)}`
        : `recipe.php?id=${encodeURIComponent(recipe.id)}`;

      const card = document.createElement('div');
      card.className = 'recipe-card';
      card.innerHTML = `
        <div class="recipe-image">
    <img src="${recipe.image}" alt="${recipe.title}">
    <button class="remove-btn btn-small danger" data-id="${recipe.id}">
      Remove from Favorites
    </button>
    </div>
        <div class="recipe-content">
          <h3>${recipe.title}</h3>
          <div class="recipe-meta">
            <span><i class="far fa-clock"></i> ${recipe.time} mins</span>
            <span><i class="fas fa-fire"></i> ${recipe.calories} cal</span>
          </div>
          <p>${recipe.description.substring(0,80)}…</p>
          <a href="${href}" class="btn btn-small">View Recipe</a>
        </div>
      `;
      container.appendChild(card);

      // wire up the remove button
      card.querySelector('.remove-btn')
          .addEventListener('click', () => handleRemove(card, recipe.id, recipe.title));
    });
  })
  .catch(console.error);
});

function handleRemove(cardEl, recipeId, title) {
  fetch('php/favorites.php', {
    method: 'POST',
    credentials: 'same-origin',
    body: new URLSearchParams({ action: 'toggle', recipe_id: recipeId })
  })
  .then(r => r.json())
  .then(data => {
    updateFavoriteCount(data.favorite_count);

    const placeholder = document.createElement('div');
    placeholder.className = 'undo-placeholder';
    placeholder.innerHTML = `
      <div class="undo-notice">
        Removed “${title}”.
        <button class="undo-btn">Click to undo</button>
      </div>
    `;
    cardEl.parentNode.insertBefore(placeholder, cardEl);
    cardEl.remove();

    const undoBtn = placeholder.querySelector('.undo-btn');
    const timer = setTimeout(() => placeholder.remove(), 10000);

    undoBtn.addEventListener('click', () => {
      clearTimeout(timer);
      fetch('php/favorites.php', {
        method: 'POST',
        credentials: 'same-origin',
        body: new URLSearchParams({ action: 'toggle', recipe_id: recipeId })
      })
      .then(r => r.json())
      .then(data2 => {
        updateFavoriteCount(data2.favorite_count);
        placeholder.replaceWith(cardEl);
      });
    });
  })
  .catch(console.error);
}