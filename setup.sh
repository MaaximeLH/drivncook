#!/usr/bin/env bash

composer install
mkdir Documents
mkdir Documents/Invoices
mkdir public/dist/qrcode
chmod 777 public/dist/qrcode
chmod 777 Documents/Invoices

echo 'Setup finished'