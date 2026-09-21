<!DOCTYPE html>
<html lang="en">

<?php
view('partials/document-head', ['page_title' => 'Venues', 'stylesheet' => '/styles/admin-venues.css']);
?>

<body>
    <div class="dashboard-wrapper">
        <!-- Header / Navigation -->
        <header class="admin-header">
            <div class="header-content">
                <h1>MeepleMatchups Admin</h1>
                <nav class="admin-nav">
                    <a href="/admin/dashboard" class="nav-link">Dashboard</a>
                    <a href="/admin/members" class="nav-link">Members</a>
                    <a href="/admin/venues" class="nav-link active">Venues</a>
                    <a href="/admin/logout" class="nav-link logout-link">Log Out</a>
                </nav>
            </div>
        </header>

        <!-- Main Content -->
        <main class="dashboard-content">
            <h1 class="page-title">Venue Listings</h1>

            <!-- Venue Selection -->
            <div class="filter-section">
                <form method="GET" class="filter-form">
                    <label for="venue">Select Venue:</label>
                    <select name="venue" id="venue">
                        <option value="">-- Choose a venue --</option>
                        <option value="1">Meeple Manor (Calgary)</option>
                        <option value="2">The Dice Depot (Edmonton)</option>
                        <option value="3">Cardboard Cathedral (Vancouver)</option>
                        <option value="4">The Tabletop Tavern (Toronto)</option>
                        <option value="5">Stratego Stronghold (Montreal)</option>
                        <option value="6">Roll & Chill (Winnipeg)</option>
                        <option value="7">Gaming Gauntlet (Halifax)</option>
                        <option value="8">Pawn Shop Palace (Ottawa)</option>
                        <option value="9">Token Town (Calgary)</option>
                        <option value="10">The Board Bunker (Toronto)</option>
                    </select>
                    <button type="submit" class="btn-filter">View Venue</button>
                </form>
            </div>

            <!-- Venue Details (hardcoded for WK-02) -->
            <div class="card">
                <h2>Meeple Manor</h2>
                <p class="venue-location">Calgary, Alberta</p>

                <!-- Featured Games Section -->
                <div class="section">
                    <h3>Featured Games (3)</h3>
                    <ul class="featured-list">
                        <li class="featured-item">
                            <span>Catan</span>
                            <span class="featured-badge">Featured</span>
                        </li>
                        <li class="featured-item">
                            <span>Ticket to Ride</span>
                            <span class="featured-badge">Featured</span>
                        </li>
                        <li class="featured-item">
                            <span>Carcassonne</span>
                            <span class="featured-badge">Featured</span>
                        </li>
                    </ul>
                </div>

                <!-- Add Featured Game Section -->
                <div class="section">
                    <p class="info-text">
                        This venue has reached the maximum of 3 featured games.
                    </p>
                </div>
            </div>

            <!-- More Venues Info -->
            <div class="card">
                <h2>Venue Statistics</h2>
                <table class="venues-table">
                    <thead>
                        <tr>
                            <th>Venue Name</th>
                            <th>City</th>
                            <th>Featured Games</th>
                            <th>Total Plays</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Meeple Manor</td>
                            <td>Calgary</td>
                            <td>3</td>
                            <td>47</td>
                        </tr>
                        <tr>
                            <td>The Dice Depot</td>
                            <td>Edmonton</td>
                            <td>2</td>
                            <td>23</td>
                        </tr>
                        <tr>
                            <td>Cardboard Cathedral</td>
                            <td>Vancouver</td>
                            <td>3</td>
                            <td>18</td>
                        </tr>
                        <tr>
                            <td>The Tabletop Tavern</td>
                            <td>Toronto</td>
                            <td>1</td>
                            <td>35</td>
                        </tr>
                        <tr>
                            <td>Stratego Stronghold</td>
                            <td>Montreal</td>
                            <td>3</td>
                            <td>29</td>
                        </tr>
                        <tr>
                            <td>Roll & Chill</td>
                            <td>Winnipeg</td>
                            <td>2</td>
                            <td>14</td>
                        </tr>
                        <tr>
                            <td>Gaming Gauntlet</td>
                            <td>Halifax</td>
                            <td>3</td>
                            <td>11</td>
                        </tr>
                        <tr>
                            <td>Pawn Shop Palace</td>
                            <td>Ottawa</td>
                            <td>0</td>
                            <td>8</td>
                        </tr>
                        <tr>
                            <td>Token Town</td>
                            <td>Calgary</td>
                            <td>3</td>
                            <td>22</td>
                        </tr>
                        <tr>
                            <td>The Board Bunker</td>
                            <td>Toronto</td>
                            <td>2</td>
                            <td>19</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
</body>

</html>