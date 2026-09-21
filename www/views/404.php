<!DOCTYPE html>
<html lang="en">

<?php
// The same document-head partial the app pages use. Reusing it here is the
// whole idea of a partial: one fragment, included by more than one view, each
// call passing in the title and stylesheet that page needs.
view('partials/document-head', [
    'page_title' => 'Page Not Found',
    'stylesheet' => '/styles/404.css',
]);
?>

<body>
    <div class="container">
        <h1>404 - Page Not Found</h1>
        <p>The page you are looking for does not exist.</p>
    </div>
</body>

</html>