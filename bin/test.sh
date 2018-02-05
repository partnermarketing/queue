#!/bin/sh

docker-compose run php vendor/phpunit/phpunit/phpunit --coverage-html coverage
