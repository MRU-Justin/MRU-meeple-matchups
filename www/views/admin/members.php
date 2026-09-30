<?php

/**
 * @var array $members
 */
?>

<!DOCTYPE html>
<html lang="en">

<?php
view('partials/document-head', ['page_title' => 'Members', 'stylesheet' => '/styles/admin-members.css']);
?>

<body>
    <div class="dashboard-wrapper">
        <!-- Header / Navigation -->
        <header class="admin-header">
            <div class="header-content">
                <h1>MeepleMatchups Admin</h1>
                <nav class="admin-nav">
                    <a href="/admin/dashboard" class="nav-link">Dashboard</a>
                    <a href="/admin/members" class="nav-link active">Members</a>
                    <a href="/admin/venues" class="nav-link">Venues</a>
                    <a href="/admin/logout" class="nav-link logout-link">Log Out</a>
                </nav>
            </div>
        </header>

        <!-- Main Content -->
        <main class="dashboard-content">
            <h1 class="page-title">Member Data</h1>

            <!-- Filter Section -->
            <div class="filter-section">
                <form method="GET" class="filter-form">
                    <label for="plan">Filter by Plan Type:</label>
                    <select name="plan" id="plan">
                        <option value="">All Members</option>
                        <option value="standard">Standard</option>
                        <option value="premium">Premium</option>
                    </select>
                    <button type="submit" class="btn-filter">Apply Filter</button>
                </form>
            </div>

            <!-- Members Table -->
            <div class="card">
                <table class="members-table">
                    <thead>
                        <tr>
                            <th>Full Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Plan Type</th>
                            <th>Enrolled</th>
                            <th>Total Plays</th>
                        </tr>
                    </thead>
                    <tbody>

                        <?php foreach ($members as $member) : ?>
                            <tr>
                                <td><?= e($member['first_name'] . ' ' . $member['last_name']) ?></td>
                                <td><?= e($member['email']) ?></td>
                                <td><?= e($member['cell_phone'] ?? 'Not Provided') ?></td>
                                <td><span class="badge badge-<?= e($member['plan_type']) ?>"><?= e(ucfirst($member['plan_type'])) ?></span></td>
                                <td><?= e(date('M d, Y', strtotime($member['enrolled_on']))) ?></td>
                                <td><?= e($member['total_plays']) ?></td>
                            </tr>
                        <?php endforeach; ?>

                    </tbody>
                </table>
            </div>
        </main>
    </div>
</body>

</html>