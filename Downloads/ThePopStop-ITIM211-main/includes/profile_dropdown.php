<?php

if (!isset($user_data)) {
    $user_data = ['profile_photo' => null];
}
?>

<style>
    /* Profile Dropdown Styles */
    .profile-dropdown {
        position: relative;
    }
    
    .profile-trigger {
        display: flex !important;
        align-items: center;
        gap: 0.5rem;
        cursor: pointer;
    }
    
    .profile-icon {
        width: 35px;
        height: 35px;
        border-radius: 50%;
        object-fit: cover;
        border: 2px solid var(--primary);
    }
    
    .profile-icon-placeholder {
        width: 35px;
        height: 35px;
        border-radius: 50%;
        background: var(--primary);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        font-size: 1.1rem;
    }
    
    .dropdown-menu {
        position: absolute;
        top: 100%;
        right: 0;
        background: white;
        border-radius: 10px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        min-width: 200px;
        margin-top: 0.5rem;
        display: none;
        z-index: 1000;
    }
    
    .dropdown-menu.show {
        display: block;
    }
    
    .dropdown-item {
        display: flex !important;
        align-items: center;
        gap: 0.8rem;
        padding: 0.8rem 1.2rem !important;
        color: var(--dark-brown) !important;
        text-decoration: none;
        transition: background 0.3s;
        border-bottom: 1px solid var(--light-beige);
    }
    
    .dropdown-item:first-child {
        border-radius: 10px 10px 0 0;
    }
    
    .dropdown-item:last-child {
        border-bottom: none;
        border-radius: 0 0 10px 10px;
    }
    
    .dropdown-item:hover {
        background: var(--light-beige);
    }
    
    .dropdown-item span {
        font-size: 1.2rem;
    }
</style>

<!-- Profile Dropdown -->
<li class="profile-dropdown">
    <a href="#" class="profile-trigger" onclick="toggleProfileMenu(event)">
        <?php if (!empty($user_data['profile_photo']) && file_exists($user_data['profile_photo'])): ?>
            <img src="<?php echo htmlspecialchars($user_data['profile_photo']); ?>" alt="Profile" class="profile-icon">
        <?php else: ?>
            <div class="profile-icon-placeholder"><?php echo strtoupper(substr($_SESSION['full_name'], 0, 1)); ?></div>
        <?php endif; ?>
        <span>My Account</span>
    </a>
    <div class="dropdown-menu" id="profileDropdown">
        <a href="profile.php" class="dropdown-item">
            <span>👤</span> My Profile
        </a>
        <a href="logout.php" class="dropdown-item">
            <span>🚪</span> Logout
        </a>
    </div>
</li>

<script>
    function toggleProfileMenu(event) {
        event.preventDefault();
        const dropdown = document.getElementById('profileDropdown');
        dropdown.classList.toggle('show');
    }
    
    document.addEventListener('click', function(event) {
        const dropdown = document.getElementById('profileDropdown');
        const trigger = document.querySelector('.profile-trigger');
        
        if (dropdown && trigger && !trigger.contains(event.target) && !dropdown.contains(event.target)) {
            dropdown.classList.remove('show');
        }
    });
</script>
