<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ProduitControllerTest extends WebTestCase
{
    public function testPageAccueilAfficheProduit(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/');

        $this->assertResponseIsSuccessful();

        $this->assertSelectorTextContains('h1', 'Boutique Symfony');
    }

    public function testPanierPageIsSuccessfull(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/panier');

        $this->assertResponseIsSuccessful();

        $this->assertTrue(
            $crawler->filter('table')->count() > 0 || 
            $crawler->filter('div.alert:contains("Votre panier est vide")')->count() > 0,
            'Le panier doit contenir un tableau de produits ou un message indiquant que le panier est vide'
        );
    }
}
