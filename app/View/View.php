<?php

namespace Phantom\View;

use Exception;

class View
{
    protected $view;
    protected $data;
    protected $sections = [];
    protected $sectionStack = [];
    protected $layout;
    protected $pushes = [];
    protected $pushStack = [];

    public function __construct($view, $data = [])
    {
        $this->view = $view;
        $this->data = $data;
    }

    /**
     * Create a new view instance.
     *
     * @param  string  $view
     * @param  array   $data
     * @return static
     */
    public static function make($view, $data = [])
    {
        return new static($view, $data);
    }

    /**
     * Render the view.
     *
     * @return string
     */
    public function render()
    {
        $content = $this->renderView($this->view, $this->data);

        if ($this->layout) {
            $content = $this->renderView($this->layout, array_merge($this->data, ['__content' => $content]));
        }

        return $content;
    }

    protected function renderView($view, $data)
    {
        $viewPath = str_replace('.', '/', $view);
        $path = base_path("resources/views/{$viewPath}.php");

        if (!file_exists($path)) {
            throw new Exception("View [{$view}] not found.");
        }

        $compiledPath = storage_path("compiled/" . md5($view) . ".php");

        if (!file_exists($compiledPath) || filemtime($path) > filemtime($compiledPath)) {
            $compiler = new Compiler();
            $compiledContent = $compiler->compile(file_get_contents($path));

            if (!file_exists(dirname($compiledPath))) {
                mkdir(dirname($compiledPath), 0755, true);
            }
            file_put_contents($compiledPath, $compiledContent);
        }

        extract($data);
        ob_start();

        try {
            include $compiledPath;
        } catch (Exception $e) {
            ob_end_clean();
            throw $e;
        }

        return ob_get_clean();
    }

    public function extend($layout)
    {
        $this->layout = $layout;
    }

    public function startSection($name)
    {
        ob_start();
        $this->sectionStack[] = $name;
    }

    public function endSection()
    {
        $name = array_pop($this->sectionStack);
        $this->sections[$name] = ob_get_clean();
    }

    public function yield($name)
    {
        return $this->sections[$name] ?? '';
    }

    public function startPush($name)
    {
        ob_start();
        $this->pushStack[] = $name;
    }

    public function endPush()
    {
        $name = array_pop($this->pushStack);
        if (!isset($this->pushes[$name])) {
            $this->pushes[$name] = [];
        }
        $this->pushes[$name][] = ob_get_clean();
    }

    public function stack($name)
    {
        return implode('', $this->pushes[$name] ?? []);
    }

    public function __toString()
    {
        return $this->render();
    }
}