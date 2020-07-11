<?php

namespace App;

class Config
{
    const DB_DRIVER = 'pgsql';
    const ORM_DRIVER = 'pdo_pgsql';
    const DB_HOST = 'YOUR_IP';
    const DB_NAME = 'YOUR_DB_NAME';
    const DB_USER = 'YOUR_DB_USER';
    const DB_PASSWORD = 'YOUR_DB_PASSWORD';
    const DB_CHARSET = 'UTF-8';
    const SHOW_ERRORS = false;
    const STRIPE_PUB_KEY = '';
    const STRIPE_PRIV_KEY = '';
    const GOOGLE_API_KEY = "";
    const DEFAULT_LANGUAGE = 'fr_FR';
    const LANGUAGES = ['fr_FR', 'en_US'];
}
