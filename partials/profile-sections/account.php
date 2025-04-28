<div id="account-info" class="profile-section active">
    <h2>Account Information</h2>
    <form id="profile-form" class="profile-form">
        <label for="profile-name">Name:</label>
        <input type="text" id="profile-name" name="name" required>

        <label for="profile-email">Email:</label>
        <input type="email" id="profile-email" name="email" readonly disabled>

        <label for="profile-bio">Bio:</label>
        <textarea id="profile-bio" name="bio" placeholder="Tell us about yourself..."></textarea>

        <button type="submit" class="btn btn-primary">Save Changes</button>
    </form>

    <hr>

  <div class="password-change">
    <h3>Change Password</h3>
    <form id="password-form" class="profile-form">
      <div class="form-row">
        <label for="current-password">Current Password:</label>
        <div class="password-input">
        <input type="password" id="current-password" name="current-password" required>
          <button type="button" onclick="togglePassword('current-password', this)">👁️</button>
        </div>
      </div>

      <div class="form-row">
        <label for="new-password">New Password:</label>
        <div class="password-input">
        <input type="password" id="new-password" name="new-password" required>
          <button type="button" onclick="togglePassword('new-password', this)">👁️</button>
        </div>
        <small id="profile-password-requirements" class="form-meta">
          At least 12 characters, 1 uppercase letter &amp; 1 number
        </small>
        <div id="profile-password-strength" class="form-meta"></div>
      </div>

      <div class="form-row">
        <label for="confirm-password">Confirm New Password:</label>
        <div class="password-input">
        <input type="password" id="confirm-password" name="confirm-new-password" required>
          <button type="button" onclick="togglePassword('confirm-password', this)">👁️</button>
        </div>
      </div>

      <button type="submit" class="btn btn-primary">Update Password</button>
    </form>
  </div>

  <hr>

<label class="dark-mode-toggle">
  <input type="checkbox" id="dark-mode-toggle" /> Dark Mode
</label>

<button id="delete-account-btn" class="btn btn-small danger">
  Delete My Account
</button>

<script src="js/profile.js"></script>
