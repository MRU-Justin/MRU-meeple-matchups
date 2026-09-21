<?php
// Stripped down (like, REALLY REALLY) version from Laracasts.

class Router
{
    private $routes = [];

    public function add($method, $resource_path, $controller)
    {
        $new_route = [
            'method' => $method,
            'resource_path' => $resource_path,
            'controller' => $controller,
            'restricted' => false, // need for restricted routes

            // A route is really just a list of path segments. We work those
            // out once, here, instead of re-doing it on every request.
            // (See split_into_segments below.)
            'segments' => $this->split_into_segments($resource_path),
        ];

        array_push($this->routes, $new_route);

        return $this;  // for chaining
    }

    // $redirect_to has no default on purpose: deciding where an unauthorized
    // visitor ends up is a choice every restricted route has to make, so we
    // force it to be made rather than quietly falling back to some fixed path.
    public function restrict($redirect_to)
    {
        // array_key_last is a built-in PHP thing. Look it up!
        $last_routes_index = array_key_last($this->routes);
        $this->routes[$last_routes_index]['restricted'] = true;

        // Where to send an unauthorized visitor is a property of the route,
        // not of the router: two restricted routes might want to bounce people
        // to different places. So we stash it alongside the 'restricted' flag
        // and let authorize() read it back when it needs it.
        $this->routes[$last_routes_index]['redirect_to'] = $redirect_to;

        return $this;  // for chaining, same as add()
    }

    public function route($target_resource_path, $target_method)
    {
        $target_segments = $this->split_into_segments($target_resource_path);

        // Tracks whether some route matched the path but wanted a different
        // method, so we can tell "no such page" apart from "wrong method".
        $methods_allowed_here = [];

        foreach ($this->routes as $current_route) {

            $path_params = $this->match_segments(
                $current_route['segments'],
                $target_segments
            );

            // null means "these paths don't match at all". An empty array
            // means "they match, and there were no path parameters" -- which
            // is why we check against null specifically rather than emptiness.
            if ($path_params === null) {
                continue;
            }

            // The path matches. Note the method this route accepts, in case
            // nothing ends up matching on method as well.
            $methods_allowed_here[] = strtoupper($current_route['method']);

            if (strcasecmp($current_route['method'], $target_method) !== 0) {
                continue;
            }

            if ($current_route['restricted']) {
                $this->authorize($current_route['redirect_to']);
            }

            // Hand any path parameters to the controller through $_GET, so
            // that /venues/42 shows up as $_GET['venue_id'].
            foreach ($path_params as $key => $value) {
                $_GET[$key] = $value;
            }

            return $this->run_controller($current_route['controller']);
        }


        // The path exists, but not for this method -- a POST to a route that
        // only handles GET, say. 405 says exactly that, where a 404 would
        // send you looking for a spelling mistake that isn't there.
        if ($methods_allowed_here !== []) {
            http_response_code(405);
            header('Allow: ' . implode(', ', array_unique($methods_allowed_here)));
            require path_to('views/405.php');
            die();
        }

        http_response_code(404);
        require path_to('views/404.php');
        die();
    }

    // Loads and runs a controller.
    //
    // This looks like a pointless wrapper around require, but it isn't: it
    // controls what the controller can SEE. Requiring a controller at the top
    // level of index.php would put it in the same scope as $router,
    // $resource_path and $method, so a controller that happened to name a
    // variable $router would quietly break routing. In here, all it can reach
    // is $controller_file.
    private function run_controller($controller_file)
    {
        return require path_to("controllers/{$controller_file}");
    }

    private function authorize($redirect_to)
    {
        // A restricted route needs a session in order to check anything. If
        // session_start() hasn't been called yet (see www/public/index.php),
        // say so loudly -- otherwise every visitor silently looks
        // unauthorized and there's nothing to suggest why.
        if (session_status() !== PHP_SESSION_ACTIVE) {
            throw new Exception(
                'A restricted route was reached, but no session has been ' .
                'started. Uncomment session_start() in www/public/index.php.'
            );
        }

        // Send the unauthorized visitor wherever this particular route asked
        // to send them (see restrict()) rather than to one fixed place: the
        // login page lives at a different path in different projects.
        if (!isset($_SESSION['authorized'])) {
            redirect($redirect_to);
        }
    }


    // Compares a route's segments against the segments of the path that was
    // actually requested.
    //
    // Returns an array of any path parameters that were found if the two
    // match, or null if they don't match at all.
    //
    // For example, against a route registered as /venues/:venue_id/games:
    //    /venues/42/games        =>  ["venue_id" => "42"]
    //    /venues/42              =>  null  (wrong number of segments)
    //    /venues/42/photos       =>  null  ("photos" isn't "games")
    private function match_segments($route_segments, $target_segments)
    {
        // Different number of segments means there's no way to match.
        // This is what stops /venues/42/games from matching a route
        // registered as /venues/:venue_id.
        if (count($route_segments) !== count($target_segments)) {
            return null;
        }

        $path_params = [];

        foreach ($route_segments as $index => $route_segment) {
            $target_segment = $target_segments[$index];

            // A segment starting with a colon is a path parameter: it matches
            // whatever happens to be in that position.
            if (str_starts_with($route_segment, ':')) {
                $key = substr($route_segment, 1); // leave off the colon

                // A browser sends spaces and other awkward characters
                // percent-encoded (a space arrives as %20), so decode before
                // handing the value on.
                $path_params[$key] = rawurldecode($target_segment);

                continue;
            }

            // An ordinary segment has to match exactly.
            if ($route_segment !== $target_segment) {
                return null;
            }
        }

        return $path_params;
    }

    // Splits a resource path into the list of segments that make it up,
    // ignoring any leading or trailing slashes.
    //
    // For example:
    //    /venues/42/games        =>  ["venues", "42", "games"]
    //    /venues/                =>  ["venues"]
    //    /                       =>  []
    //
    // Note that we split on "/" ourselves rather than using dirname() and
    // basename(). Those are FILE path functions, and on Windows they treat
    // the backslash as a separator too -- which would make routing behave
    // differently on Windows than it does on macOS. explode() doesn't care
    // what operating system you're on.
    private function split_into_segments($path)
    {
        $trimmed = trim($path, '/');

        // Without this, "/" would come back as [""] -- a single empty
        // segment -- rather than the empty list we want.
        if ($trimmed === '') {
            return [];
        }

        return explode('/', $trimmed);
    }
}
