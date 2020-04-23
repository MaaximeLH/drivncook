<?php

namespace Core;

use App\Config;

class StripeBck {

    /**
     * Récupère les informations du payeur et créer un customer
     * Exemple :
     * [
         * 'source' => $token,
         * 'name' => 'NOM_DU_PAYEUR',
         * 'email' => 'EMAIL_DU_PAYEUR',
             * 'address' => [
             * 'line1' => 'ligne d'adresse 1',
             * 'city' => 'Ville',
             * 'country' => 'Pays',
             * 'postal_code' => 'Code postal',
             * 'state' => 'Province'
             * ]
     * ]
     * @param array|$data
     * @return \Stripe\Customer
     * @throws \Stripe\Exception\ApiErrorException
     */
    public static function createCustomer($data) {
        \Stripe\Stripe::setApiKey(Config::STRIPE_PRIV_KEY);
        return \Stripe\Customer::create($data);
    }

    /**
     * Récupère les informations pour le paiement, et execute le paiement
     * [
         * 'amount' => 'TOTAL_EN_CENTIME', // Faire * 100 pour le paiement
         * 'currency' => 'DEVISE', // eur en général
         * 'description' => "DESCRIPTION DE LA VENTE",
         * 'customer' => "ID DU CUSTOMER", // ID Du customer
     * ]
     * @param $data
     * @return \Stripe\Charge
     * @throws \Stripe\Exception\ApiErrorException
     */
    public  static function createCharge($data) {
        \Stripe\Stripe::setApiKey(Config::STRIPE_PRIV_KEY);
        return \Stripe\Charge::create($data);
    }
}