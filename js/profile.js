document.addEventListener('DOMContentLoaded', function () {
    const profileForm = document.getElementById('profile-form');
    const passwordForm = document.getElementById('password-form');
    const preferencesForm = document.getElementById('preferences-form');
    const userNameSpan = document.getElementById('user-name');
    const profileImage = document.getElementById('profile-image');
    const savedUserAvatar = localStorage.getItem('userAvatar');
    const savedSection = document.getElementById('saved-recipes');
    const container    = document.getElementById('saved-recipes-container');

   // grab the tab controls
  const links     = document.querySelectorAll('.profile-nav a');
  const sections  = document.querySelectorAll('.profile-section');
  const indicator = document.getElementById('tab-indicator');
  const wrapper   = document.querySelector('.nav-wrapper');

  // 1️⃣ Wire up clicks for tabs
  links.forEach(link => {
    link.addEventListener('click', e => {
      e.preventDefault();
      // swap the active class in the sidebar
      const prev = document.querySelector('.profile-nav li.active');
      if (prev) prev.classList.remove('active');
      link.parentElement.classList.add('active');
      // show the matching section
      sections.forEach(s => s.classList.remove('active'));
      document.getElementById(link.getAttribute('href').slice(1))
              .classList.add('active');
      // move the underline
      const r = link.getBoundingClientRect();
      const w = wrapper.getBoundingClientRect();
      indicator.style.width = `${r.width}px`;
      indicator.style.left  = `${r.left - w.left}px`;
    });
  });

  // 2️⃣ On load, position the underline under whichever tab is marked active
  setTimeout(() => {
    const init = document.querySelector('.profile-nav li.active a');
    if (!init) return;
    const r = init.getBoundingClientRect();
    const w = wrapper.getBoundingClientRect();
    indicator.style.width = `${r.width}px`;
    indicator.style.left  = `${r.left - w.left}px`;
  }, 0);
    
    // Load avatar
    if (profileImage) profileImage.src = savedUserAvatar ? savedUserAvatar : 'images/default-avatar.svg';

    function loadUserProfile() {
        fetch('php/profile-handler.php?action=load')
            .then(res => res.json())
            .then(user => {
                document.getElementById('profile-name').value = user.name;
                document.getElementById('profile-email').value = user.email;
                document.getElementById('profile-bio').value = user.bio || '';

                if (user.preferences) {
                    const prefs = JSON.parse(user.preferences);
                    prefs.forEach(pref => {
                        const checkbox = document.querySelector(`input[value="${pref}"]`);
                        if (checkbox) checkbox.checked = true;
                    });
                }
            });
    }

    // ✅ Profile info update
    profileForm.addEventListener('submit', function (e) {
        e.preventDefault();
        const submitBtn = profileForm.querySelector('button[type="submit"]');
        submitBtn.classList.add('btn-saving');

        const formData = new FormData(profileForm);
        formData.append('action', 'update-profile');

        fetch('php/profile-handler.php', { method: 'POST', body: formData })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    submitBtn.classList.add('btn-success-check');
                    setTimeout(() => submitBtn.classList.remove('btn-success-check'), 3000);
                    showToast('Profile updated successfully!');
                    const newName = document.getElementById('profile-name').value;
                    localStorage.setItem('userName', newName);
                    if (userNameSpan) userNameSpan.textContent = newName;
                } else {
                    showToast(data.error || 'Failed to update profile.', false);
                }
            })
            .finally(() => submitBtn.classList.remove('btn-saving'));
    });

    // ✅ Password update
    passwordForm.addEventListener('submit', function (e) {
        e.preventDefault();
        const submitBtn = passwordForm.querySelector('button[type="submit"]');
        submitBtn.classList.add('btn-saving');

        const formData = new FormData(passwordForm);
        formData.append('action', 'change-password');

        fetch('php/profile-handler.php', { method: 'POST', body: formData })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    submitBtn.classList.add('btn-success-check');
                    setTimeout(() => submitBtn.classList.remove('btn-success-check'), 3000);
                    showToast('Password updated successfully!');
                    passwordForm.reset();
                } else {
                    showToast(data.error || 'Failed to update password.', false);
                }
            })
            .finally(() => submitBtn.classList.remove('btn-saving'));
    });

    // Simple: load right away (or trigger on tab click)
  fetch('php/profile-handler.php?action=favorites')
  .then(r => r.json())
  .then(recipes => {
    if (!Array.isArray(recipes)) return;
    if (recipes.length === 0) {
      container.innerHTML = '<p>You haven’t saved any recipes yet.</p>';
      return;
    }
    // build a grid of cards
    const grid = document.createElement('div');
    grid.className = 'recipe-grid';
    recipes.forEach(r => {
      const card = document.createElement('div');
      card.className = 'recipe-card';
      card.innerHTML = `
        <div class="recipe-image">
          <img src="${r.image}" alt="${r.title}">
          <button class="remove-btn" data-id="${r.id}">
            <i class="fas fa-trash"></i>
          </button>
        </div>
        <div class="recipe-content">
          <h3>${r.title}</h3>
          <p class="recipe-meta">
            <span><i class="far fa-clock"></i> ${r.time} mins</span>
            <span><i class="fas fa-utensils"></i> ${r.servings} servings</span>
          </p>
          <a href="recipe.php?slug=${r.slug}" class="btn btn-small">View Recipe</a>
        </div>`;
      grid.appendChild(card);

      // wire up your undo/remove logic exactly as in favorites.js
      card.querySelector('.remove-btn')
          .addEventListener('click', () => handleRemove(card, r.id, r.title));
    });
    container.innerHTML = '';
    container.appendChild(grid);
  })
  .catch(console.error);

    // ✅ Preferences update
    preferencesForm.addEventListener('submit', function (e) {
        e.preventDefault();
        const submitBtn = preferencesForm.querySelector('button[type="submit"]');
        submitBtn.classList.add('btn-saving');

        const dietaryPrefs = Array.from(document.querySelectorAll('input[name="dietary"]:checked')).map(cb => cb.value);
        const notificationPrefs = Array.from(document.querySelectorAll('input[name="notifications"]:checked')).map(cb => cb.value);
        const preferences = [...dietaryPrefs, ...notificationPrefs];

        const formData = new FormData();
        formData.append('action', 'update-preferences');
        formData.append('preferences', JSON.stringify(preferences));

        fetch('php/profile-handler.php', { method: 'POST', body: formData })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    submitBtn.classList.add('btn-success-check');
                    setTimeout(() => submitBtn.classList.remove('btn-success-check'), 3000);
                    showToast('Preferences updated successfully!');
                } else {
                    showToast(data.error || 'Failed to update preferences.', false);
                }
            })
            .finally(() => submitBtn.classList.remove('btn-saving'));
    });
    loadSavedRecipes();
    loadUserProfile();

    function loadSavedRecipes() {
        const container = document.getElementById('saved-recipes-container');
container.innerHTML = '<div class="spinner"></div>';
        fetch('php/profile-handler.php?action=favorites')
            .then(res => res.json())
            .then(data => {
                if (data.length === 0) {
                    container.innerHTML = `
                        <div class="no-content-message">
                            <img src="images/no-favorites.png" alt="No favorites yet!" style="width: 150px; margin-bottom: 20px;">
                            <p>You haven't saved any recipes yet!</p>
                            <a href="index.php" class="btn">Start Browsing</a>
                        </div>
                    `;
                    return;
                }
                data.forEach(recipe => {
                    const recipeCard = document.createElement('div');
                    recipeCard.className = 'recipe-card';
                    recipeCard.innerHTML = `
                        <div class="recipe-image">
                            <img src="images/recipes/${recipe.image}" alt="${recipe.title}">
                        </div>
                        <div class="recipe-content">
                            <h3>${recipe.title}</h3>
                            <p class="recipe-meta">
                                <span><i class="far fa-clock"></i> ${recipe.time}</span>
                                <span><i class="fas fa-utensils"></i> ${recipe.servings} servings</span>
                            </p>
                            <p class="recipe-description">${recipe.description}</p>
                            <a href="${recipe.link}" class="btn">View Recipe</a>
                        </div>
                    `;
                    container.appendChild(recipeCard);
                });
            })
            .catch(() => {
                container.innerHTML = '<p class="error-message">Failed to load your saved recipes. Please try again later.</p>';
            });
    }

    // ✅ Password strength indicator (live for "new password" field)
    const newPasswordInput = document.getElementById('new-password');
    const profilePasswordRequirements = document.getElementById('profile-password-requirements');
    const profilePasswordStrength = document.getElementById('profile-password-strength');

    if (newPasswordInput) {
        newPasswordInput.addEventListener('input', () => {
            const val = newPasswordInput.value;

            let requirementsMet = 0;
            const lengthCheck = val.length >= 12;
            const uppercaseCheck = /[A-Z]/.test(val);
            const numberCheck = /\d/.test(val);

            if (lengthCheck) requirementsMet++;
            if (uppercaseCheck) requirementsMet++;
            if (numberCheck) requirementsMet++;

            profilePasswordStrength.innerHTML = `
                <div class="password-strength-bar">
                    <div class="strength-segment ${requirementsMet >= 1 ? 'met' : ''}"></div>
                    <div class="strength-segment ${requirementsMet >= 2 ? 'met' : ''}"></div>
                    <div class="strength-segment ${requirementsMet >= 3 ? 'met' : ''}"></div>
                </div>
                <div class="strength-text">${requirementsMet === 3 ? '✅ Strong Password' : '❗ Improve Your Password'}</div>
            `;

            profilePasswordRequirements.innerHTML = `
                <ul style="text-align:left; font-size:0.85rem; margin-top:8px;">
                    <li style="color:${lengthCheck ? 'green' : 'red'};">${lengthCheck ? '✅' : '❌'} At least 12 characters</li>
                    <li style="color:${uppercaseCheck ? 'green' : 'red'};">${uppercaseCheck ? '✅' : '❌'} At least 1 uppercase letter</li>
                    <li style="color:${numberCheck ? 'green' : 'red'};">${numberCheck ? '✅' : '❌'} At least 1 number</li>
                </ul>
            `;
        });
    }

    // Avatar upload
const changeAvatarBtn = document.getElementById('change-avatar-btn');
const avatarUploadInput = document.getElementById('avatar-upload');

if (changeAvatarBtn && avatarUploadInput) {
    changeAvatarBtn.addEventListener('click', () => {
        avatarUploadInput.click();
    });

    avatarUploadInput.addEventListener('change', function () {
        const file = this.files[0];
        if (file) {
            const formData = new FormData();
            formData.append('avatar', file);
            formData.append('action', 'upload-avatar');

            fetch('php/profile-handler.php', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success && data.avatarUrl) {
                    profileImage.src = data.avatarUrl + '?v=' + new Date().getTime();
                    localStorage.setItem('userAvatar', data.avatarUrl);
                
                    // Only update user-avatar if it exists (e.g. in the nav)
                    const navAvatar = document.getElementById('user-avatar');
                    if (navAvatar) navAvatar.src = data.avatarUrl;
                
                    showToast('Profile photo updated!');
                }
                else { showToast('Profile photo updated!');
                }                
            })
            .catch(() => {
                showToast('Upload failed. Please try again.', false);
            });
        }
    });
}

// Dark mode toggle
const darkToggle = document.getElementById('dark-mode-toggle');
if (darkToggle) {
  darkToggle.checked = localStorage.getItem('darkMode') === 'true';
  document.body.classList.toggle('dark-mode', darkToggle.checked);
  darkToggle.addEventListener('change', () => {
    document.body.classList.toggle('dark-mode', darkToggle.checked);
    localStorage.setItem('darkMode', darkToggle.checked);
  });
}

// Delete account logic
const deleteBtn = document.getElementById('delete-account-btn');
const modal = document.getElementById('delete-modal');
const confirmDelete = document.getElementById('confirm-delete');
const cancelDelete = document.getElementById('cancel-delete');

profileImage.onerror = () => profileImage.src = 'images/default-avatar.svg';

if (deleteBtn && modal && confirmDelete && cancelDelete) {
  deleteBtn.addEventListener('click', () => modal.classList.add('show'));
  cancelDelete.addEventListener('click', () => modal.classList.remove('show'));

  confirmDelete.addEventListener('click', () => {
    fetch('php/profile-handler.php', {
      method: 'POST',
      body: new URLSearchParams({ action: 'delete-account' })
    })
    .then(res => res.json())
    .then(data => {
      if (data.success) {
        localStorage.clear();
        window.location.href = 'register.php?deleted=1';
      } else {
        showToast(data.error || 'Could not delete account', false);
      }
    });
  });
}

const initialHash = window.location.hash || '#account-info';
const initialLink = document.querySelector(`.profile-nav a[href="${initialHash}"]`);
if (initialLink) {
    initialLink.click(); // Trigger section switch
}

});
