<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Phantom\Core\Translator;
use Phantom\Core\Application;
use Phantom\Core\Container;

class LocalizationTest extends TestCase
{
    protected function setUp(): void
    {
        $app = new Application(dirname(__DIR__, 2));
        Container::setInstance($app);
    }

    public function test_translator_can_load_json_files()
    {
        $langPath = base_path('lang');
        if (!file_exists($langPath)) mkdir($langPath, 0755, true);
        
        file_put_contents($langPath . '/fr.json', json_encode(['Welcome' => 'Bienvenue']));

        $translator = new Translator($langPath, 'fr');
        $this->assertEquals('Bienvenue', $translator->get('Welcome'));

        // Cleanup
        unlink($langPath . '/fr.json');
    }

    public function test_dynamic_locale_switching()
    {
        $langPath = base_path('lang');
        file_put_contents($langPath . '/it.json', json_encode(['Hello' => 'Ciao']));
        file_put_contents($langPath . '/de.json', json_encode(['Hello' => 'Hallo']));

        $translator = new Translator($langPath, 'it');
        $this->assertEquals('Ciao', $translator->get('Hello'));

        $translator->setLocale('de');
        $this->assertEquals('Hallo', $translator->get('Hello'));

        // Cleanup
        unlink($langPath . '/it.json');
        unlink($langPath . '/de.json');
    }
}
