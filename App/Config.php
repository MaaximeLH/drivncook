<?php

namespace App;

class Config
{
    const DB_DRIVER = 'pgsql';
    const ORM_DRIVER = 'pdo_pgsql';
    const DB_HOST = '51.255.173.90';
    const DB_NAME = 'drivncook';
    const DB_USER = 'drivncook';
    const DB_PASSWORD = 'drivncook';
    const DB_CHARSET = 'utf8';
    const SHOW_ERRORS = true;
    const STRIPE_PUB_KEY = 'pk_test_gdlpAM5By6xB9dlhN2T2ztEB00okYDUNYT';
    const STRIPE_PRIV_KEY = 'sk_test_g4MdZMZM22Z9RH3lD6LLXfG900k3UT7S9c';
    const GOOGLE_API_KEY = "AIzaSyDYACOXaDpmyuenuiNTX6J44yEzObcHyZI";
    const DEFAULT_LANGUAGE = "fr_FR";
    const LANGUAGES = ['fr_FR', 'en_US'];
}
