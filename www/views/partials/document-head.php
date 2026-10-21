<?php

/**
 * A PARTIAL: a view included from inside another view.
 *
 * There's nothing special about this file -- it is an ordinary view, rendered
 * with the same view() function a controller uses. What makes it a "partial"
 * is only how it's used: another view calls
 *     view('partials/document-head', [...]);
 * to drop this reusable fragment into itself, so shared page chrome (here, the
 * document <head>) lives in one place instead of being copied onto every page.
 *
 * Its data is passed in explicitly, exactly like any other view. This partial
 * can see $page_title and $stylesheet ONLY because the view that included it
 * named them in the second argument to view(). It does NOT see whatever other
 * variables the outer view happens to have -- each view() call runs in its own
 * scope. That is the point: a partial's inputs are stated out loud, right where
 * it is used.
 *
 * And, like any view, every value it echoes is escaped with e().
 *
 * @var string $page_title
 * @var string $stylesheet
 */
?>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($page_title) ?></title>
    <link rel="stylesheet" href="<?= e($stylesheet) ?>">
</head>
