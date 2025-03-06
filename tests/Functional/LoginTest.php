<?php

namespace App\Tests\Functional;

use Symfony\Component\Panther\PantherTestCase;

class LoginTest extends PantherTestCase
{
    public function testSuccessfulLogin(): void
    {
        $client = static::createPantherClient();

        $client->getCookieJar()->clear();

        $client->request('GET', '/login');
        // $client->takeScreenshot('var/tests_screenshot.png');
        
        $client->waitFor('#inputEmail');
        $this->assertSelectorTextContains('h1', 'Please sign in');

        $form = $client->getCrawler()->filter('form')->form([
            'email' => 'admin@example.com',
            'password' => 'password123',
        ]);

        $client->submit($form);
        $client->waitFor('.card');
        // $client->takeScreenshot('var/tests_screenshot2.png');

        $this->assertSelectorTextContains('h1', 'Boutique Symfony');
    }
}