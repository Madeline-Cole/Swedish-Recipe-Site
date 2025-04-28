document.addEventListener('DOMContentLoaded', function () {
    const registerForm = document.getElementById('register-form');
    const loginForm = document.getElementById('login-form');
    const logoutBtn = document.getElementById('logout-btn');
    const userNameSpan = document.getElementById('user-name');
    const savedUserName = localStorage.getItem('userName');
    const logoLink = document.querySelector('.logo a');
    const logoImg = document.querySelector('.logo img');

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
    const userAvatarImg = document.getElementById('user-avatar');
    const savedUserAvatar = localStorage.getItem('userAvatar');
    if (userAvatarImg) {
        userAvatarImg.src = savedUserAvatar ? savedUserAvatar : 'images/default-avatar.svg';
    }    

    if (logoutBtn) {
        logoutBtn.addEventListener('click', function (e) {
            e.preventDefault();
            fetch('php/auth.php?action=logout')
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            localStorage.removeItem('userName');
            window.location.href = 'login.php';
        } else {
            alert('Logout failed.');
        }
    })
        });
    }

    if (registerForm) {
        registerForm.addEventListener('submit', async function (e) {
            e.preventDefault();
            const formData = new FormData(registerForm);
            formData.append('action', 'register');

            try {
                const res = await fetch('php/auth.php', {
                    method: 'POST',
                    body: formData
                });
                const data = await res.json();

                if (res.ok && data.user) {
                    showToast('🎉 Registration successful! Please log in.');
                    setTimeout(() => {
                        window.location.href = 'login.php';
                    }, 1500);
                } else {
                    showErrorToast(data.error || "Registration failed.");
                }
            } catch (err) {
                showErrorToast("Server error during registration.");
            }
        });
    }

    if (loginForm) {
        loginForm.addEventListener('submit', async function (e) {
            e.preventDefault();
            const formData = new FormData(loginForm);
            formData.append('action', 'login');
    
            try {
                const res = await fetch('php/auth.php', {
                    method: 'POST',
                    body: formData
                });
                const data = await res.json();
    
                if (res.ok && data.user) {
                    localStorage.setItem('userName', data.user.name); // save name for nav bar
                    //
                    localStorage.setItem('currentUser', JSON.stringify(data.user));
                    showToast('✅ Login successful!');
                    setTimeout(() => {
                        window.location.href = 'index.php';
                    }, 1200);
                } else {
                    showErrorToast(data.error || "Incorrect email or password.");
                }
            } catch (err) {
                showErrorToast("Server error during login.");
            }
        });
    }    

    function showToast(message) {
        const toast = document.getElementById('toast');
        if (!toast) return;
        toast.textContent = message;
        toast.classList.add('show');
        toast.style.backgroundColor = "#28a745"; // green
        setTimeout(() => {
            toast.classList.remove('show');
        }, 3000);
    }

    function showErrorToast(message) {
        const toast = document.getElementById('toast');
        if (!toast) return;
        toast.textContent = message;
        toast.classList.add('show');
        toast.style.backgroundColor = "#dc3545"; // red
        setTimeout(() => {
            toast.classList.remove('show');
        }, 3000);
    }
});