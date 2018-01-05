FROM alpine

COPY bin/install.sh /

RUN /install.sh

WORKDIR /project
