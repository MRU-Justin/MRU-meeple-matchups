<?php

/**
 * This is a PHPDoc comment. It's very similar to the Javadoc comment
 * blocks you've encountered in prog 2 and 3.
 *
 * The following annotation (@var) is here to stop Intelephense from
 * complaining that the variable $framework_pieces used in the foreach
 * loop down below doesn't exist. Remove the line and see what happens!
 *
 * @var iterable $framework_pieces
 */
?>

<!DOCTYPE html>
<html lang="en">

<?php
// The document <head> is the same shape on every page, so it lives in a
// partial. We hand it the two things that change from page to page -- the
// title and which stylesheet to load -- and nothing else.
view('partials/document-head', [
    'page_title' => $page_title,
    'stylesheet' => '/styles/app.css',
]);
?>

<body>
    <main>
        <p class="eyebrow">COMP3512 project template</p>
        <h1><?= e($page_title) ?></h1>

        <p class="lede">
            If you can read this and the page is styled, then PHP is
            installed, the built-in server is serving both this page and its
            stylesheet, and the router found a controller.
        </p>

        <h2>Where things live</h2>
        <dl>
            <?php foreach ($framework_pieces as $piece) : ?>
                <dt><?= e($piece['file']) ?></dt>
                <dd><?= e($piece['role']) ?></dd>
            <?php endforeach; ?>
        </dl>

        <h2>Why every value gets escaped</h2>
        <p>
            The controller for this page set a variable to the following
            text, which is exactly the sort of thing somebody might type
            into one of your forms:
        </p>

        <p class="demo"><?= e($untrusted_value) ?></p>

        <p>
            You are reading that as <em>text</em>, which is the whole point.
            The view wrote it with
            <code>&lt;?= e($untrusted_value) ?&gt;</code>. Had it used
            <code>&lt;?= $untrusted_value ?&gt;</code> instead, your browser
            would have run it as a script rather than shown it to you.
            Try that out and see for yourself!
        </p>

        <p>
            Every value you echo into a page goes through
            <code>e()</code>. See <code>www/coretools.php</code> for what it
            does and the two cases it can't help with.
        </p>

        <h2>Check the other route</h2>
        <p>
            <a href="/no-such-page">Visit a path that doesn't exist</a> to
            see the 404 view in <code>www/views/404.php</code>.
        </p>
    </main>
</body>

</html>