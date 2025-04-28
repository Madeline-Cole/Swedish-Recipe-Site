document.addEventListener('DOMContentLoaded', function() {
const urlParams = new URLSearchParams(window.location.search);
const recipeId = urlParams.get('id');
    
    if (!recipeId) {
      document.getElementById('recipe-title').textContent = 'Recipe Not Found';
      return;
    }
  
    fetch(`php/recipes.php?action=get&id=${encodeURIComponent(recipeId)}`)
      .then(res => res.json())
      .then(recipe => {
        if (!recipe || recipe.error) {
          document.getElementById('recipe-title').textContent = 'Recipe Not Found';
          return;
        }
  
        document.title = `${recipe.title} - Swedish Recipes`;
        document.getElementById('recipe-title').textContent = recipe.title;
        document.getElementById('recipe-image').src = recipe.image || 'images/placeholder-recipe.jpg';
        document.getElementById('recipe-image').alt = recipe.title;
  
        const ingredientsList = document.getElementById('recipe-ingredients');
        ingredientsList.innerHTML = `<li>${recipe.description || "Ingredients coming soon."}</li>`;
  
        const instructionsList = document.getElementById('recipe-instructions');
        instructionsList.innerHTML = `<li>${recipe.description || "Instructions coming soon."}</li>`;
  
        const meta = document.getElementById('recipe-meta');
        meta.innerHTML = `
          <div><strong>Category:</strong> ${recipe.category}</div>
          <div><strong>Time:</strong> ${recipe.time} mins</div>
          <div><strong>Calories:</strong> ${recipe.calories} cal</div>
          <div><strong>Servings:</strong> ${recipe.servings}</div>
        `;
        // Load tags now
        loadTags(recipe.tags);
      })
      .catch(error => {
        console.error('Failed to load recipe:', error);
        document.getElementById('recipe-title').textContent = 'Recipe Not Found';
      });
  
    function loadTags(tagsString) {
      if (!tagsString) return;
  
      const tags = tagsString.split(',').map(tag => tag.trim());
      const tagsContainer = document.getElementById('tags-container');
      tagsContainer.innerHTML = '';
  
      // Fetch user preferences (from server)
      fetch('php/auth.php?action=session')
        .then(res => res.json())
        .then(sessionData => {
          if (!sessionData.authenticated) return;
  
          fetch('php/profile-handler.php?action=load')
            .then(res => res.json())
            .then(profile => {
              const userPrefs = profile.preferences ? JSON.parse(profile.preferences) : [];
  
              tags.forEach(tag => {
                let show = false;
  
                if (tag.toLowerCase() === 'from gramma') {
                  show = true; // Always show this
                } else if (userPrefs.includes(tag.toLowerCase())) {
                  show = true; // Only show if user selected matching preference
                }
  
                if (show) {
                  const span = document.createElement('span');
                  span.className = 'tag';
                  span.textContent = tag;
                  tagsContainer.appendChild(span);
                }
              });
            });
        });
    }
  });  