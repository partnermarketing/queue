FROM php:7.4-cli

COPY bin/install.sh /

RUN /install.sh

WORKDIR /project
