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
                        <!-- Hardcoded data for WK-02 -->
                        <tr>
                            <td>Sir Flips-A-Lot</td>
                            <td>coinflip.enthusiast@catmail.net</td>
                            <td>403-HEADS-TAILS</td>
                            <td><span class="badge badge-standard">Standard</span></td>
                            <td>Jan 15, 2026</td>
                            <td>247</td>
                        </tr>
                        <tr>
                            <td>Professor Dice Roller PhD</td>
                            <td>statistical.anomaly@rollmail.biz</td>
                            <td>Does not own a phone</td>
                            <td><span class="badge badge-standard">Standard</span></td>
                            <td>Feb 20, 2026</td>
                            <td>3</td>
                        </tr>
                        <tr>
                            <td>Captain Meeple Bandit</td>
                            <td>stole.ur.components@pirate.net</td>
                            <td>403-ARR-HARR-HARR</td>
                            <td><span class="badge badge-premium">Premium</span></td>
                            <td>Jan 10, 2026</td>
                            <td>1,847</td>
                        </tr>
                        <tr>
                            <td>Toast McBreaderson</td>
                            <td>buttered.side.down@toastville.com</td>
                            <td>N/A (sent via telegram bird)</td>
                            <td><span class="badge badge-standard">Standard</span></td>
                            <td>Mar 05, 2026</td>
                            <td>2</td>
                        </tr>
                        <tr>
                            <td>Dr. Randomize Von Shuffleton III</td>
                            <td>chaos.agent.supreme@entropy.edu</td>
                            <td>403-SHUFFLE-UP</td>
                            <td><span class="badge badge-premium">Premium</span></td>
                            <td>Jan 20, 2026</td>
                            <td>892</td>
                        </tr>
                        <tr>
                            <td>Broken Rules Barry</td>
                            <td>actually.ignored.instructions@rebel.net</td>
                            <td>Unknown (plays own way)</td>
                            <td><span class="badge badge-standard">Standard</span></td>
                            <td>Feb 15, 2026</td>
                            <td>64</td>
                        </tr>
                        <tr>
                            <td>Whiskers the Tabby Cat</td>
                            <td>paw.prints.only@meow.io</td>
                            <td>Meow meow meow meow</td>
                            <td><span class="badge badge-premium">Premium</span></td>
                            <td>Jan 25, 2026</td>
                            <td>15 (knocked pieces off table)</td>
                        </tr>
                        <tr>
                            <td>Potato "The Strategist" Salad</td>
                            <td>starchy.victory.lap@tuber.farm</td>
                            <td>403-MASH-PLAYS</td>
                            <td><span class="badge badge-premium">Premium</span></td>
                            <td>Mar 01, 2026</td>
                            <td>42</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
</body>

</html>