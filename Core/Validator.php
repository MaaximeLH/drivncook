<?php

namespace Core;

class Validator {

    /**
     * Vérifie la validité d'une adresse email
     * @param $email
     * @return bool
     */
    public static function isValidEmail($email) {
        if(filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return true;
        }

        return false;
    }

    /**
     * Vérifie la validité du nom / prénom, tous deux étant basés sur les mêmes règles
     * @param $string
     * @return bool
     */
    public static function isValidName($string) {
        if(strlen($string) >= 2 && strlen($string) <= 75) {
            return true;
        }

        return false;
    }

    /**
     * Vérifie la validité d'un code postal français
     * @param $postalCode
     * @return bool
     */
    public static function isValidPostalCode($postalCode) {
        return strlen($postalCode) == 5;
    }


    /**
     * Vérifie la validité d'une ville
     * @param $city
     * @return bool
     */
    public static function isValidCityName($city) {
        return strlen($city) >= 1 && strlen($city) <= 50;
    }

    /**
     * Retourne un numéro de téléphone valide ou retourne false si numéro invalide
     * @param $phoneNumber
     * @return false|string|string[]|null
     */
    public static function isValidPhoneNumberAndFormatIt($phoneNumber) {
        $phoneNumber = preg_replace('/[^0-9]+/', '', $phoneNumber);
        $phoneNumber = substr($phoneNumber, -9);
        $motif = '+33 (\1) \2 \3 \4 \5';
        $phoneNumber = preg_replace('/(\d{1})(\d{2})(\d{2})(\d{2})(\d{2})/', $motif, $phoneNumber);

        return $phoneNumber;
    }

    /**
     * Vérifie la longueur minimale du password
     * @param $password
     * @return bool
     */
    public static function isGoodPassword($password) {
        if(strlen($password) >= 5) {
            return true;
        }

        return false;
    }

    /**
     * Vérifie la validité d'une plaque d'immatriculation
     * @param $plate
     * @return bool
     */
    public static function isValidLicensePlate($plate) {
        $pattern = '/^[a-np-z]{2}-[0-9]{3}-[a-np-z]{2}$/i';
        if(preg_match($pattern, $plate)) {
            return true;
        }

        return false;
    }

    /**
     * Vérifie la validité d'un SIRET
     * @param $siret
     * @return bool
     */
    public static function isValidSiret($siret) {
        return (strlen(trim($siret)) == 14) ? true : false;
    }

    /**
     * Vérifie l'extension d'un fichier
     * @param $ext
     * @return bool
     */
    public static function isValidFileExtension($ext) {
        return in_array(strtolower($ext), ['png', 'jpeg', 'jpg', 'pdf']);
    }

    public static function isSizeNotTooBig($size) {
        return $size <= 10485760; // 10Mo
    }
}