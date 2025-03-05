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
        $this->assertSelectorExists('.btn-ajouter-panier');

        // on trouve le lien d'ajout au panier pour le cliquer
        $link = $crawler->filter('.btn-ajouter-panier');
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

    public function testSuppressionDuPanier(): void
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/');
        $this->assertResponseIsSuccessful();

        // on ajoute un produit au panier
        $link = $crawler->filter('.btn-ajouter-panier');
        $client->click($link->link());
        $client->followRedirect();

        // On va sur la page du panier
        $crawler = $client->request('GET', '/panier');
        $this->assertResponseIsSuccessful();

        // la présence du lien de suppression
        $this->assertSelectorExists('.btn-supprimer-panier');

        // on trouve le lien de suppression pour le cliquer
        $link = $crawler->filter('.btn-supprimer-panier');
        $this->assertNotNull($link, 'Le lien de suppression devrait être présent');

        // récupère l'URL du lien
        $url = $link->attr('href');
        $this->assertNotNull($url, 'L\'URL du lien ne devrait pas être vide');

        // on clique sur le lien de suppression
        $client->click($link->link());
        
        // la redirection vers la page du panier
        $this->assertResponseRedirects('/panier');
        
        // on suit la redirection
        $crawler = $client->followRedirect();
        
        // que la page du panier s'affiche correctement
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Votre Panier');
        
        // que le message "panier vide" est présent et n'est pas vide
        $this->assertSelectorExists('.message-panier-vide');
        $message = $crawler->filter('.message-panier-vide');
        $this->assertNotNull($message, 'Le message du panier vide devrait être présent');
        $this->assertNotEmpty(trim($message->text()), 'Le message du panier vide ne devrait pas être vide');
    }

    public function testTotalPanier(): void
    {
        $client = static::createClient();
        
        // On va sur la page d'accueil
        $crawler = $client->request('GET', '/');
        $this->assertResponseIsSuccessful();

        // On ajoute plusieurs produits au panier
        $links = $crawler->filter('.btn-ajouter-panier');
        $this->assertGreaterThan(0, $links->count(), 'Il devrait y avoir des produits à ajouter au panier');

        // On ajoute le premier produit
        $client->click($links->first()->link());
        $client->followRedirect();

        // On retourne sur la page d'accueil pour ajouter un deuxième produit
        $crawler = $client->request('GET', '/');
        $links = $crawler->filter('.btn-ajouter-panier');
        $client->click($links->first()->link());
        $client->followRedirect();

        // On va sur la page du panier
        $crawler = $client->request('GET', '/panier');
        $this->assertResponseIsSuccessful();

        // On vérifie que le tableau des produits est présent
        $this->assertSelectorExists('table');

        // On récupère tous les prix des produits dans le panier
        $prix = $crawler->filter('td.prix')->each(function ($node) {
            return (float) str_replace('€', '', trim($node->text()));
        });

        // On calcule le total attendu
        $totalAttendu = array_sum($prix);

        // On vérifie que le total affiché correspond
        $totalAffiche = (float) str_replace(['€', 'Total : '], '', trim($crawler->filter('#total')->first()->text()));
        $this->assertEquals($totalAttendu, $totalAffiche, 'Le total affiché ne correspond pas à la somme des prix');
    }
}
