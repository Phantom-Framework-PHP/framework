<?php

namespace Phantom\View;

class Compiler
{
    protected $content;

    /**
     * Compile the given template content into PHP.
     *
     * @param string $content
     * @return string
     */
    public function compile($content)
    {
        $this->content = $content;

        $this->compileEchoes();
        $this->compileStatements();
        $this->compileInheritance();

        return $this->content;
    }

    protected function compileEchoes()
    {
        // {{ $var }}
        $this->content = preg_replace_callback('/\{\{\s*(.+?)\s*\}\}/', function($m) {
            return "<?php echo htmlspecialchars(" . $m[1] . "); ?>";
        }, $this->content);
        
        // {!! $var !!}
        $this->content = preg_replace_callback('/\{!!\s*(.+?)\s*!!\}/', function($m) {
            return "<?php echo " . $m[1] . "; ?>";
        }, $this->content);
    }

    protected function compileStatements()
    {
        // @if / @else / @endif
        $this->content = preg_replace_callback('/@if\s*\((.+?)\)/', function($m) {
            return "<?php if(" . $m[1] . "): ?>";
        }, $this->content);

        $this->content = str_replace('@else', "<?php else: ?>", $this->content);
        $this->content = str_replace('@endif', "<?php endif; ?>", $this->content);

        // @foreach / @endforeach
        $this->content = preg_replace_callback('/@foreach\s*\((.+?)\)/', function($m) {
            return "<?php foreach(" . $m[1] . "): ?>";
        }, $this->content);

        $this->content = str_replace('@endforeach', "<?php endforeach; ?>", $this->content);
        
        // @can / @elsecan / @endcan
        $this->content = preg_replace_callback('/@can\s*\((.+?)\)/', function($m) {
            return "<?php if(gate()->allows(" . $m[1] . ")): ?>";
        }, $this->content);

        $this->content = str_replace('@endcan', "<?php endif; ?>", $this->content);

        // @include
        $this->content = preg_replace_callback('/@include\s*\(\'(.+?)\'\)/', function($m) {
            return "<?php echo \Phantom\View\View::make('" . $m[1] . "', get_defined_vars())->render(); ?>";
        }, $this->content);
    }

    protected function compileInheritance()
    {
        // @extends
        $this->content = preg_replace_callback('/@extends\s*\(\'(.+?)\'\)/', function($m) {
            return "<?php \$this->extend('" . $m[1] . "'); ?>";
        }, $this->content);
        
        // @section
        $this->content = preg_replace_callback('/@section\s*\(\'(.+?)\'\)/', function($m) {
            return "<?php \$this->startSection('" . $m[1] . "'); ?>";
        }, $this->content);

        $this->content = str_replace('@endsection', "<?php \$this->endSection(); ?>", $this->content);
        
        // @yield
        $this->content = preg_replace_callback('/@yield\s*\(\'(.+?)\'\)/', function($m) {
            return "<?php echo \$this->yield('" . $m[1] . "'); ?>";
        }, $this->content);
    }
}