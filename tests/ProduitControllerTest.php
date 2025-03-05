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

    public function testAjoutAuPanier(): void
    {
        $client = static::createClient();
        
        // on va sur la page /
        $crawler = $client->request('GET', '/');
        $this->assertResponseIsSuccessful();

        // on vérifie la présence du lien d'ajout au panier
        $this->assertSelectorExists('a.btn:contains("Ajouter au panier")');

        // on trouve le lien d'ajout au panier pour le cliquer
        $link = $crawler->filter('a.btn:contains("Ajouter au panier")');
        $this->assertNotNull($link, 'Le lien d\'ajout au panier devrait être présent');

        // on récupère l'URL du lien
        $url = $link->attr('href');
        $this->assertNotNull($url, 'L\'URL du lien ne devrait pas être vide');
        // print_r($url);

        // on clique sur le lien d'ajout au panier
        $client->click($link->link());
        
        // on vérifie la présece de redirection vers /
        $this->assertResponseRedirects('/');
        
        // on suit la redirection
        $crawler = $client->followRedirect();
        
        // On vérifie que la page d'accueil s'affiche correctement
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Boutique Symfony');
        
        // On vérifie que le lien vers le panier est présent
        $this->assertSelectorExists('a[href="/panier"]');
    }
}
