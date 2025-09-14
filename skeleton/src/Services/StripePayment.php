<?php

namespace App\Services;

use App\Entity\Panier;
use App\Entity\PanierProduit;
use Stripe\Checkout\Session;
use Stripe\Stripe;

class StripePayment
{
    public function __construct(private string $stripeSecretKey)
    {
        Stripe::setApiKey($this->stripeSecretKey);
    }

    /**
     * Crée une session Stripe Checkout à partir d’un panier
     */
    public function createCheckoutSession(Panier $panier): Session
    {
        $lineItems = [];

        /** @var PanierProduit $panierProduit */
        foreach ($panier->getProduits() as $panierProduit) {
            $produit = $panierProduit->getProduit();

            $lineItems[] = [
                'price_data' => [
                    'currency'     => 'eur',
                    'product_data' => [
                        'name'   => $produit->getName(),
                        'images' => [$produit->getThumbnail()],
                    ],
                    'unit_amount'  => $produit->getPrix() * 100, // en centimes
                ],
                'quantity' => $panierProduit->getQuantite(),
            ];
        }

        return Session::create([
            'payment_method_types' => ['card'],
            'line_items'           => $lineItems,
            'mode'                 => 'payment',
            'success_url'          => 'http://localhost:8000/success',
            'cancel_url'           => 'http://localhost:8000/cancel',
        ]);
    }
}
