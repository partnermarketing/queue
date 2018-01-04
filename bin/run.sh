#!/bin/sh

docker-compose up -d

if ! test -d vendor
then
    docker-compose run php composer install
fi
