<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Phantom\Core\Translator;

class TranslatorTest extends TestCase
{
    protected $translator;

    protected function setUp(): void
    {
        $this->translator = new Translator(dirname(__DIR__, 2) . '/lang', 'es', 'en');
    }

    public function test_can_translate_simple_key()
    {
        $this->assertEquals('Debes iniciar sesión para continuar.', $this->translator->get('messages.login_required'));
    }

    public function test_can_translate_with_replacements()
    {
        $this->assertEquals('Bienvenido al Framework Phantom, Mario!', $this->translator->get('messages.welcome', ['name' => 'Mario']));
    }

    public function test_returns_key_if_not_found()
    {
        $this->assertEquals('messages.non_existent', $this->translator->get('messages.non_existent'));
    }

    public function test_fallback_locale()
    {
        // Assuming lang/en/messages.php doesn't exist or doesn't have a key we can test easily without creating it.
        // Let's create a temporary translator for this.
        $translator = new Translator(dirname(__DIR__, 2) . '/lang', 'fr', 'es');
        $this->assertEquals('Debes iniciar sesión para continuar.', $translator->get('messages.login_required'));
    }
}
