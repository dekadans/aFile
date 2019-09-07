#!/usr/bin/env bash
composer install
npm install
ln -s ../node_modules/ ./webroot/vendor
php ./bin/afile.php install