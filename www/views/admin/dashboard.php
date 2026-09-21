<!DOCTYPE html>
<html lang="en">

<?php
view('partials/document-head', ['page_title' => 'Dashboard', 'stylesheet' => '/styles/admin-dashboard.css']);
?>

<body>
    <div class="dashboard-wrapper">
        <!-- Header / Navigation -->
        <header class="admin-header">
            <div class="header-content">
                <h1>MeepleMatchups Admin</h1>
                <nav class="admin-nav">
                    <a href="/admin/dashboard" class="nav-link active">Dashboard</a>
                    <a href="/admin/members" class="nav-link">Members</a>
                    <a href="/admin/venues" class="nav-link">Venues</a>
                    <a href="/admin/logout" class="nav-link logout-link">Log Out</a>
                </nav>
            </div>
        </header>

        <!-- Main Content -->
        <main class="dashboard-content">
            <div class="welcome-card">
                <h2>Welcome, Admin</h2>
                <p>Manage your board game venues and members from here.</p>
            </div>

            <!-- Quick Links -->
            <div class="quick-links">
                <a href="/admin/members" class="quick-link-card">
                    <div class="card-icon">👥</div>
                    <h3>Members</h3>
                    <p>View and manage members</p>
                </a>
                <a href="/admin/venues" class="quick-link-card">
                    <div class="card-icon">📍</div>
                    <h3>Venues</h3>
                    <p>Manage featured games</p>
                </a>
            </div>
        </main>
    </div>
</body>

</html>