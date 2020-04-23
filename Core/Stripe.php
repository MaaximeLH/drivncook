<?php

namespace Core;

use App\Config;

class Stripe {

    private $source;

    /**
     * Customers attributs
     */

    private $customer_name;
    private $customer_email;
    private $customer_address_line;
    private $customer_address_city;
    private $customer_address_country;
    private $customer_address_postal_code;
    private $customer_address_state;

    private $customer;

    /**
     * Charge attributs
     */
    private $charge_amount;
    private $charge_currency = 'eur';
    private $charge_description = "Vente Driv'n'Cook";

    public function __construct()
    {
        \Stripe\Stripe::setApiKey(Config::STRIPE_PRIV_KEY);
    }

    /**
     * @return mixed
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * @param mixed $source
     * @return Stripe
     */
    public function setSource($source)
    {
        $this->source = $source;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getCustomerName()
    {
        return $this->customer_name;
    }

    /**
     * @param mixed $customer_name
     * @return Stripe
     */
    public function setCustomerName($customer_name)
    {
        $this->customer_name = $customer_name;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getCustomerEmail()
    {
        return $this->customer_email;
    }

    /**
     * @param mixed $customer_email
     * @return Stripe
     */
    public function setCustomerEmail($customer_email)
    {
        $this->customer_email = $customer_email;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getCustomerAddressLine()
    {
        return $this->customer_address_line;
    }

    /**
     * @param mixed $customer_address_line
     * @return Stripe
     */
    public function setCustomerAddressLine($customer_address_line)
    {
        $this->customer_address_line = $customer_address_line;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getCustomerAddressCity()
    {
        return $this->customer_address_city;
    }

    /**
     * @param mixed $customer_address_city
     * @return Stripe
     */
    public function setCustomerAddressCity($customer_address_city)
    {
        $this->customer_address_city = $customer_address_city;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getCustomerAddressCountry()
    {
        return $this->customer_address_country;
    }

    /**
     * @param mixed $customer_address_country
     * @return Stripe
     */
    public function setCustomerAddressCountry($customer_address_country)
    {
        $this->customer_address_country = $customer_address_country;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getCustomerAddressPostalCode()
    {
        return $this->customer_address_postal_code;
    }

    /**
     * @param mixed $customer_address_postal_code
     * @return Stripe
     */
    public function setCustomerAddressPostalCode($customer_address_postal_code)
    {
        $this->customer_address_postal_code = $customer_address_postal_code;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getCustomerAddressState()
    {
        return $this->customer_address_state;
    }

    /**
     * @param mixed $customer_address_state
     * @return Stripe
     */
    public function setCustomerAddressState($customer_address_state)
    {
        $this->customer_address_state = $customer_address_state;
        return $this;
    }

    /**
     * @return null|object
     */
    public function getCustomer()
    {
        return $this->customer;
    }

    /**
     * @param null $customer
     * @return Stripe
     */
    private function setCustomer($customer)
    {
        $this->customer = $customer;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getChargeAmount()
    {
        return $this->charge_amount;
    }

    /**
     * @param mixed $charge_amount
     * @return Stripe
     */
    public function setChargeAmount($charge_amount)
    {
        $this->charge_amount = $charge_amount * 100;
        return $this;
    }

    /**
     * @return string
     */
    public function getChargeCurrency()
    {
        return $this->charge_currency;
    }

    /**
     * @param string $charge_currency
     * @return Stripe
     */
    public function setChargeCurrency($charge_currency)
    {
        $this->charge_currency = $charge_currency;
        return $this;
    }

    /**
     * @return string
     */
    public function getChargeDescription()
    {
        return $this->charge_description;
    }

    /**
     * @param string $charge_description
     * @return Stripe
     */
    public function setChargeDescription($charge_description)
    {
        $this->charge_description = $charge_description;
        return $this;
    }

    /**
     * @throws \Stripe\Exception\ApiErrorException
     */
    public function createCustomer() {
        $attributs = [];

        if(is_null($this->getSource())) {
            throw new \Exception("You need to set Stripe Source");
        }

        $attributs['source'] = $this->getSource();
        if(!is_null($this->getCustomerName())) {
            $attributs['name'] = $this->getCustomerName();
        }

        if(!is_null($this->getCustomerEmail())) {
            $attributs['email'] = $this->getCustomerEmail();
        }

        $address = [];

        if(!is_null($this->getCustomerAddressLine())) {
            $address['line1'] = $this->getCustomerAddressLine();
        }

        if(!is_null($this->getCustomerAddressCity())) {
            $address['city'] = $this->getCustomerAddressCity();
        }

        if(!is_null($this->getCustomerAddressCountry())) {
            $address['country'] = $this->getCustomerAddressCity();
        }

        if(!is_null($this->getCustomerAddressPostalCode())) {
            $address['postal_code'] = $this->getCustomerAddressPostalCode();
        }

        if(!is_null($this->getCustomerAddressState())) {
            $address['state'] = $this->getCustomerAddressState();
        }

        if(!empty($address)) {
            $attributs['address'] = $address;
        }

        $this->setCustomer(\Stripe\Customer::create($attributs));
        return $this->getCustomer();
    }

    /**
     * @throws \Exception
     */
    public function createCharge() {
        $attributs = [];

        if(is_null($this->getSource())) {
            throw new \Exception("You need to set Stripe Source");
        }

        if(is_null($this->getChargeAmount())) {
            throw new \Exception("You need to set amount.");
        } else {
            $attributs['amount'] = $this->getChargeAmount();
        }

        if(is_null($this->getChargeCurrency())) {
            throw new \Exception("You need to set currency.");
        } else {
            $attributs['currency'] = $this->getChargeCurrency();
        }

        if(is_null($this->getCustomer())) {
            $attributs['source'] = $this->getSource();
        } else {
            $attributs['customer'] = $this->getCustomer()->id;
        }

        if(!is_null($this->getChargeCurrency())) {
            $attributs['description'] = $this->getChargeDescription();
        }

        return \Stripe\Charge::create($attributs);
    }
}