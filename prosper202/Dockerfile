FROM ulsmith/alpine-apache-php7
MAINTAINER You <you@youremail.com>

ENV PROSPER_DB_HOST galera
ENV PROSPER_DB_USER root
ENV PROSPER_DB_PASSWORD Y2hhbmdlaXQ=
ENV PROSPER_DB_NAME prosper

ADD /public /app/public
RUN chown -R apache:apache /app