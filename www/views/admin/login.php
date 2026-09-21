<!DOCTYPE html>
<html lang="en">

<?php
view('partials/document-head', ['page_title' => 'Admin Login', 'stylesheet' => 'styles/admin-login.css']);
?>

<body>
    <main class="login-container">
        <div class="login-box">
            <h1>Admin Portal</h1>
            <p class="subtitle">Borad Game Venue Management</p>
</body>

</html>
<!DOCTYPE html>
<html lang="en">

<?php
view('partials/document-head', ['page_title' => 'Admin Login', 'stylesheet' => '/styles/admin-login.css']);
?>

<body>
    <main class="login-container">
        <div class="login-box">


            <form method="POST" action="/admin">
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" required>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>

                <button type="submit" class="btn-login">Log In</button>
            </form>
        </div>
    </main>
</body>

</html>