#!/usr/bin/env bash

composer install
mkdir public/dist/qrcode
chmod 777 public/dist/qrcode

echo 'Setup finished'