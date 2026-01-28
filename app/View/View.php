<?php

namespace Phantom\View;

use Exception;

class View
{
    /**
     * Render a view file.
     *
     * @param  string  $view
     * @param  array   $data
     * @return string
     * @throws Exception
     */
    public static function make($view, $data = [])
    {
        // Convert dot notation to path (e.g., 'auth.login' -> 'auth/login')
        $viewPath = str_replace('.', '/', $view);
        $path = base_path("resources/views/{$viewPath}.php");

        if (!file_exists($path)) {
            throw new Exception("View [{$view}] not found at [{$path}].");
        }

        // Extract data to variables
        extract($data);

        // Start buffering
        ob_start();

        try {
            include $path;
        } catch (Exception $e) {
            ob_end_clean();
            throw $e;
        }

        return ob_get_clean();
    }
}
