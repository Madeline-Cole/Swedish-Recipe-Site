function showToast(message, isSuccess = true) {
  const toast = document.getElementById('toast');
  if (!toast) return;
  toast.textContent = message;
  toast.style.backgroundColor = isSuccess ? '#28a745' : '#dc3545';
  toast.classList.add('show');
  setTimeout(() => toast.classList.remove('show'), 3000);
}

function updateFavoriteCount(count = null) {
  const counter = document.getElementById('favorite-count');
  const navLink = document.querySelector('a[href*="favorites"] span');
  if (count != null) {
    if (counter)  counter.textContent = count;
    if (navLink)  navLink.textContent = `My Favorites (${count})`;
  } else {
    fetch('php/favorites.php?action=list', {
      credentials: 'same-origin'     // ← inside the fetch call
    })
    .then(r => r.json())
    .then(favs => {
      if (counter) counter.textContent = favs.length;
      if (navLink) navLink.textContent = `My Favorites (${favs.length})`;
    })
    .catch(console.error);
  }
}

// ===== entrypoint =====
document.addEventListener('DOMContentLoaded', () => {
      // Always-on
  hydrateUsername();
  setupNavToggle();
  setupLogoSwap();
  checkAuthStatus();
  setupCategoryFiltering();
  if (localStorage.getItem('currentUser')) {
    updateFavoriteCount();
  }

    // —— page-wide elements ——
    const userNameSpan           = document.getElementById('user-name');
    const menuToggle             = document.querySelector('.menu-toggle');
    const navMenu                = document.querySelector('.nav-menu');
    const logoLink               = document.querySelector('.logo a');
    const logoImg                = document.querySelector('.logo img');
    
    // —— home/index only ——
    const filterTogglesContainer = document.getElementById('filter-toggles');
    const clearFiltersBtn        = document.getElementById('clear-filters');
    const searchInput            = document.querySelector('.search-bar input');
    
    // —— recipe-detail only ——
    const decreaseBtn            = document.getElementById('decrease-servings');
    const increaseBtn            = document.getElementById('increase-servings');
    const servingsDisplay        = document.getElementById('servings');
    const ingredientsList        = document.getElementById('ingredients-list');
    
    // —— dynamic-load pages ——
    const recipeContainer        = document.getElementById('recipe-container');
    const recipeDetailSection    = document.querySelector('.recipe-detail');
    
    // initial filter state
    let activeFilter = localStorage.getItem('activeFilter') || null;
    let hideConflicts= localStorage.getItem('hideConflicts') === 'true';
    const userPreferences = window.userPreferences || [];
  
    // home/index
   // —— home/index: filters & search ——
   if (filterTogglesContainer && searchInput) {
    setupFiltersAndSearch({
      filterTogglesContainer,
      clearFiltersBtn,
      searchInput,
      userPreferences,
      getState: () => ({ activeFilter, hideConflicts }),
      setState: (f, h) => { activeFilter = f; hideConflicts = h; }
    });
    setupPerformSearch(searchInput);
  }
  
  // —— recipe detail: servings adjuster ——
  setupServingSizeAdjuster({ decreaseBtn, increaseBtn, servingsDisplay, ingredientsList });

  // —— favorites everywhere ——
  setupFavoritesButtons();
  
// —— dynamic-load listing/detail ——
if (recipeContainer?.dataset.dynamic === 'true') {
    loadRecipes(recipeContainer);
  }
  if (recipeDetailSection?.dataset.dynamic === 'true') {
    const id = new URLSearchParams(location.search).get('id');
    if (id) loadRecipeDetails(id);
  }

});  
//====================================================
//=========== end of DOMContentLoaded ================
//====================================================


// ===== helper functions (always available) =====
function hydrateUsername() {
    const span = document.getElementById('user-name');
    const name = localStorage.getItem('userName');
    if (span && name) span.textContent = name;
  }

  function setupNavToggle() {
    const menuToggle = document.querySelector('.menu-toggle');
    const navMenu    = document.querySelector('.nav-menu');
    if (!menuToggle || !navMenu) return;
  
    menuToggle.addEventListener('click', e => {
      e.stopPropagation();
      navMenu.classList.toggle('active');
      menuToggle.classList.toggle('active');
      document.body.classList.toggle('no-scroll');
    });
  
    document.addEventListener('click', e => {
      if (!e.target.closest('.nav-menu') && navMenu.classList.contains('active')) {
        navMenu.classList.remove('active');
        menuToggle.classList.remove('active');
        document.body.classList.remove('no-scroll');
      }
    });
  }
  
  function setupLogoSwap() {
    const link = document.querySelector('.logo a');
    const img  = document.querySelector('.logo img');
    if (!link || !img) return;
  
    const original = img.src;
    const hoverSrc = 'images/dala-horse-yellow.webp';
  
    link.addEventListener('mouseenter', () => {
      img.style.opacity = 0;
      setTimeout(() => { img.src = hoverSrc; img.style.opacity = 1; }, 150);
    });
    link.addEventListener('mouseleave', () => {
      img.style.opacity = 0;
      setTimeout(() => { img.src = original; img.style.opacity = 1; }, 150);
    });
  }
// Check if user is logged in
function checkAuthStatus() {
    const current = JSON.parse(localStorage.getItem('currentUser') || 'null');
    const navMenu = document.querySelector('.nav-menu');
    const path    = window.location.pathname;
  
    if (current) {
      // Replace “Login” link with user dropdown
      const loginLink = navMenu?.querySelector('a[href="login.php"]');
      if (loginLink) {
        const li = loginLink.parentElement;
        li.classList.add('user-menu');
        li.innerHTML = `
          <a href="#" id="user-dropdown-toggle">
            <i class="fas fa-user"></i>
            <span id="user-name">${current.name}</span>
          </a>
          <ul class="dropdown-menu">
            <li><a href="profile.php"><i class="fas fa-user-circle"></i> My Profile</a></li>
            <li><a href="favorites.php"><i class="fas fa-heart"></i> My Favorites</a></li>
            <li><a href="#" id="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
          </ul>`;
        li.querySelector('#logout-btn')
          .addEventListener('click', e => { e.preventDefault(); logout(); });
      }
    } 
  
    if (localStorage.getItem('currentUser')) {
      updateFavoriteCount();
    }
  }

function renderPreferenceFilters() {
    filterTogglesContainer.innerHTML = '';
    
    if (!userPreferences || userPreferences.length === 0) {
      return;
    }
    
    if (userPreferences.length === 1) {
      const label = document.createElement('label');
      const checkbox = document.createElement('input');
      checkbox.type = 'checkbox';
      checkbox.id = 'filter-' + userPreferences[0];
      checkbox.checked = (activeFilter === userPreferences[0]);
      checkbox.addEventListener('change', function() {
          activeFilter = this.checked ? userPreferences[0] : null;
          saveFilters();
          filterRecipes();
      });
      label.appendChild(checkbox);
      label.append(' Show Only ' + capitalize(userPreferences[0]));
      filterTogglesContainer.appendChild(label);
    } else {
      const select = document.createElement('select');
      select.id = 'preference-select';
      const optionAll = document.createElement('option');
      optionAll.value = '';
      optionAll.textContent = 'All Recipes';
      select.appendChild(optionAll);
    
      userPreferences.forEach(pref => {
          const option = document.createElement('option');
          option.value = pref;
          option.textContent = 'Only ' + capitalize(pref);
          if (pref === activeFilter) {
              option.selected = true;
          }
          select.appendChild(option);
      });
    
      select.addEventListener('change', function() {
          activeFilter = this.value || null;
          saveFilters();
          filterRecipes();
      });
    
      filterTogglesContainer.appendChild(select);
    }
    
    const hideLabel = document.createElement('label');
    const hideCheckbox = document.createElement('input');
    hideCheckbox.type = 'checkbox';
    hideCheckbox.id = 'hide-conflicts';
    hideCheckbox.checked = hideConflicts;
    hideCheckbox.addEventListener('change', function() {
      hideConflicts = this.checked;
      saveFilters();
      filterRecipes();
    });
    hideLabel.appendChild(hideCheckbox);
    hideLabel.append(' Hide Conflicting Recipes');
    filterTogglesContainer.appendChild(hideLabel);
}
function saveFilters() {
    localStorage.setItem('activeFilter', activeFilter || '');
    localStorage.setItem('hideConflicts', hideConflicts);
    localStorage.setItem('searchQuery', searchInput.value);
    updateClearFiltersVisibility();
}    
function updateClearFiltersVisibility() {
    if (activeFilter || hideConflicts || searchInput.value.trim() !== '') {
    clearFiltersBtn.classList.add('visible');
    } else {
    clearFiltersBtn.classList.remove('visible');
    }
}
function filterRecipes() {
    const query = searchInput.value.toLowerCase();
    localStorage.setItem('searchQuery', searchInput.value);
    
    recipeCards.forEach(card => {
      const title = card.querySelector('h3').textContent.toLowerCase();
      const conflictTags = Array.from(card.querySelectorAll('.conflict-tag')).map(el => el.textContent.toLowerCase());
    
      let matchesSearch = title.includes(query);
      let matchesPreference = true;
      if (activeFilter) {
          matchesPreference = !conflictTags.includes('non-' + activeFilter);
      }
      let matchesConflict = true;
      if (hideConflicts) {
          matchesConflict = conflictTags.length === 0;
      }
    
      if (matchesSearch && matchesPreference && matchesConflict) {
          card.style.display = 'block';
      } else {
          card.style.display = 'none';
      }
    });
}
function clearAllFilters() {
    activeFilter = null;
    hideConflicts = false;
    localStorage.removeItem('activeFilter');
    localStorage.removeItem('hideConflicts');
    localStorage.removeItem('searchQuery');
    searchInput.value = '';
    renderPreferenceFilters();
    filterRecipes();
    updateClearFiltersVisibility(); // 👈 Add this call here too
}
// ===== servings adjuster feature =====
function setupServingSizeAdjuster({ decreaseBtn, increaseBtn, servingsDisplay, ingredientsList }) {
    if (!decreaseBtn || !increaseBtn || !servingsDisplay || !ingredientsList) return;
    let current = parseInt(servingsDisplay.textContent,10);
    const original = current;
    const items = Array.from(ingredientsList.querySelectorAll('.quantity'))
                       .map(el => ({ el, base: parseFloat(el.dataset.base) }));
    const update = () => {
      items.forEach(({el, base}) => {
        let v = (base * current / original).toFixed(2)
                   .replace(/\.0+$/,'').replace(/(\.\d*?)0+$/,'$1');
        el.textContent = v;
      });
    };
    decreaseBtn.addEventListener('click', () => {
      if (current>1) { current--; servingsDisplay.textContent = current; update(); }
    });
    increaseBtn.addEventListener('click', () => {
      current++; servingsDisplay.textContent = current; update();
    });
  }
// ===== favorites feature =====
function setupFavoritesButtons() {
  const popSound = new Audio('sounds/pop.mp3');
  popSound.volume = 0.3;

  document.addEventListener('click', e => {
    const btn = e.target.closest('.favorite-btn');
    if (!btn) return;
    const id = btn.dataset.id;

    fetch('php/favorites.php', {
      method: 'POST',
      credentials: 'same-origin',
      body: new URLSearchParams({ action: 'toggle', recipe_id: id })
    })
    .then(r => {
      if (r.status === 401) {
        // not logged in → send to login page
        window.location.href = 'login.php?redirect='
          + encodeURIComponent(window.location.pathname + window.location.search);
        throw new Error('Unauthorized');
      }
      if (!r.ok) throw new Error('Network error');
      return r.json();
    })
    .then(data => {
      // only run if we really toggled
      btn.classList.toggle('active', data.status === 'added');
      const icon = btn.querySelector('i');
      icon.classList.toggle('fas', data.status === 'added');
      icon.classList.toggle('far', data.status !== 'added');
      icon.classList.add('animate');
      popSound.currentTime = 0;
      popSound.play();
      setTimeout(() => icon.classList.remove('animate'), 400);

      updateFavoriteCount(data.favorite_count);
      showToast(
        data.status === 'added'
          ? 'Added to favorites!'
          : 'Removed from favorites!'
      );
    })
    .catch(err => {
      optional: console.log(err);
    });
  });
}

// ===== dynamic load feature =====
// 6) Dynamic detail
function loadRecipeDetails(id) {
    fetch(`php/recipes.php?action=get&id=${encodeURIComponent(id)}`)
      .then(r => r.json())
      .then(recipe => {
        if (!recipe || recipe.error) return (window.location = 'index.php');
  
        document.title = `${recipe.title} – Swedish Recipes`;
        document.getElementById('recipe-title').textContent = recipe.title;
  
        const img = document.getElementById('recipe-image');
        img.src = recipe.image || 'images/placeholder-recipe.jpg';
        img.alt = recipe.title;
  
        document.getElementById('recipe-description').textContent =
          recipe.description || '';
  
        // ingredients
        const ingList = document.getElementById('recipe-ingredients');
        ingList.innerHTML = '';
        recipe.ingredients?.forEach(item => {
          const li = document.createElement('li');
          li.textContent = item;
          ingList.appendChild(li);
        });
  
        // instructions
        const instList = document.getElementById('recipe-instructions');
        instList.innerHTML = '';
        recipe.instructions?.forEach((step, i) => {
          const li = document.createElement('li');
          li.innerHTML = `<span class="step-number">${i + 1}</span> ${step}`;
          instList.appendChild(li);
        });
  
        // meta
        document.getElementById('recipe-meta').innerHTML = `
          <div class="meta-item"><i class="far fa-clock"></i> <span>${recipe.time} mins</span></div>
          <div class="meta-item"><i class="fas fa-fire"></i> <span>${recipe.calories} cal</span></div>
          <div class="meta-item"><i class="fas fa-utensils"></i> <span>${recipe.servings} servings</span></div>`;
  
        // wire up the single favorite button
        const favBtn = document.getElementById('favorite-btn');
        favBtn.dataset.id = id;
        const icon = favBtn.querySelector('i');
        const isFav = JSON.parse(localStorage.getItem('currentUser'))?.favorites?.includes(
          id
        );
        favBtn.classList.toggle('active', !!isFav);
        icon.classList.toggle('fas', !!isFav);
        icon.classList.toggle('far', !isFav);
      })
      .catch(console.error);
  }
// 1) Filters + search combo
function setupFiltersAndSearch({ filterTogglesContainer, clearFiltersBtn, searchInput, userPreferences, getState, setState }) {
    const capitalize = str =>
        str.charAt(0).toUpperCase() + str.slice(1).replace('-', ' ');
    
      // 1) render the controls
      const render = () => {
        const { activeFilter, hideConflicts } = getState();
        filterTogglesContainer.innerHTML = '';
    
        if (!userPreferences.length) return;
    
        if (userPreferences.length === 1) {
          const pref = userPreferences[0];
          const label = document.createElement('label');
          const cb = document.createElement('input');
          cb.type = 'checkbox';
          cb.checked = activeFilter === pref;
          cb.addEventListener('change', () => {
            setState(cb.checked ? pref : null, hideConflicts);
            render();
            filter();
            save();
          });
          label.appendChild(cb);
          label.append(` Show Only ${capitalize(pref)}`);
          filterTogglesContainer.appendChild(label);
        } else {
          const select = document.createElement('select');
          select.innerHTML = `<option value="">All Recipes</option>` +
            userPreferences.map(pref =>
              `<option value="${pref}"${getState().activeFilter===pref?' selected':''}>
                 Only ${capitalize(pref)}
               </option>`
            ).join('');
          select.addEventListener('change', () => {
            setState(select.value || null, getState().hideConflicts);
            filter();
            save();
          });
          filterTogglesContainer.appendChild(select);
        }
    
        // hide-conflicts checkbox
        const hideLabel = document.createElement('label');
        const hideCb = document.createElement('input');
        hideCb.type = 'checkbox';
        hideCb.checked = getState().hideConflicts;
        hideCb.addEventListener('change', () => {
          setState(getState().activeFilter, hideCb.checked);
          render();
          filter();
          save();
        });
        hideLabel.appendChild(hideCb);
        hideLabel.append(' Hide Conflicting Recipes');
        filterTogglesContainer.appendChild(hideLabel);
      };
    
      // 2) filter the grid
      const filter = () => {
        const q = searchInput.value.toLowerCase();
        const { activeFilter, hideConflicts } = getState();
    
        document.querySelectorAll('.recipe-card').forEach(card => {
          const title = (card.querySelector('h3')?.textContent || '').toLowerCase();
          const tags = Array.from(card.querySelectorAll('.conflict-tag'))
                            .map(el => el.textContent.toLowerCase());
    
          let ok = title.includes(q);
          if (activeFilter)    ok = ok && !tags.includes('non-' + activeFilter);
          if (hideConflicts)   ok = ok && tags.length === 0;
    
          card.style.display = ok ? '' : 'none';
        });
    
        clearFiltersBtn.classList.toggle(
          'visible',
          !!(q || activeFilter || hideConflicts)
        );
      };
    
      // 3) persist UI state
      const save = () => {
        const { activeFilter, hideConflicts } = getState();
        localStorage.setItem('activeFilter', activeFilter || '');
        localStorage.setItem('hideConflicts', hideConflicts);
      };
    
      // 4) clear everything
      const clearAll = () => {
        setState(null, false);
        localStorage.removeItem('activeFilter');
        localStorage.removeItem('hideConflicts');
        localStorage.removeItem('searchQuery');
        searchInput.value = '';
        render();
        filter();
      };
    
      // wire it up
      clearFiltersBtn.addEventListener('click', clearAll);
      searchInput.value = localStorage.getItem('searchQuery') || '';
      render();
      filter();
    
      searchInput.addEventListener('input', () => {
        localStorage.setItem('searchQuery', searchInput.value);
        filter();
        save();
      });

document.addEventListener('keydown', e => {
    if (
      document.activeElement.tagName !== 'INPUT' &&
      /^[a-z0-9]$/i.test(e.key)
    ) {
      searchInput.focus();
      searchInput.value += e.key;
      localStorage.setItem('searchQuery', searchInput.value);
      filter();
      save();
    }
  });
}
// 2) “Enter” & button search
function setupPerformSearch(searchInput) {
    const btn = document.querySelector('.search-bar button');
    btn?.addEventListener('click', () => searchInput.dispatchEvent(new Event('input')));
    searchInput.addEventListener('keypress', e => {
      if (e.key === 'Enter') searchInput.dispatchEvent(new Event('input'));
    });
  }

  function setupCategoryFiltering() {
    const categoryLinks = document.querySelectorAll('.category-link, .category-item');
    const recipeCards   = document.querySelectorAll('.recipe-card');
  
    categoryLinks.forEach(link => {
      link.addEventListener('click', e => {
        e.preventDefault();
        const category = link.getAttribute('data-category');
  
        // toggle active class on links
        categoryLinks.forEach(l => l.classList.remove('active'));
        link.classList.add('active');
  
        // show/hide cards
        recipeCards.forEach(card => {
          const cardCategory = card.getAttribute('data-category');
          card.style.display = (category === 'all' || cardCategory === category)
            ? '' 
            : 'none';
        });
      });
    });
  }
// expose for non-module scripts
window.showToast                = showToast;
window.updateFavoriteCount      = updateFavoriteCount;
