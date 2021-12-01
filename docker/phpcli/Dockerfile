# PHP dependency manager
FROM composer:2.1.9 AS composer

FROM php:7.4-fpm

ARG NEW_RELIC_AGENT_VERSION
ARG NEW_RELIC_LICENSE_KEY
ARG NEW_RELIC_APPNAME
ARG NEW_RELIC_DAEMON_ADDRESS



# Copies dependency manager into PHP image
COPY --from=composer /usr/bin/composer /usr/local/bin/composer

# Install packages
RUN apt-get -qq update && apt-get -qq -y install \
    git \
    libzip-dev \
    unzip \
    && docker-php-ext-install \
       sockets \
       zip


RUN curl -o newrelic.tar.gz "https://download.newrelic.com/php_agent/archive/${NEW_RELIC_AGENT_VERSION}/newrelic-php5-${NEW_RELIC_AGENT_VERSION}-linux.tar.gz" \
  && gzip -dc newrelic.tar.gz | tar xf - \
  && cd newrelic-php5-${NEW_RELIC_AGENT_VERSION}-linux \
  && NR_INSTALL_USE_CP_NOT_LN=1 NR_INSTALL_SILENT=1 ./newrelic-install install \
  && rm -rf ../newrelic-php5-${NEW_RELIC_AGENT_VERSION}-linux newrelic.tar.gz \
  && sed -i \
           -e 's/"REPLACE_WITH_REAL_KEY"/'"$NEW_RELIC_LICENSE_KEY"'/' \
           -e 's/newrelic.appname = "PHP Application"/newrelic.appname = '"$NEW_RELIC_APPNAME"'/' \
           -e 's/;newrelic.daemon.app_connect_timeout =.*/newrelic.daemon.app_connect_timeout=15s/' \
           -e 's/;newrelic.daemon.start_timeout =.*/newrelic.daemon.start_timeout=5s/' \
           -e 's/;newrelic.daemon.address =.*/newrelic.daemon.address='"$NEW_RELIC_DAEMON_ADDRESS"'/' \
           -e 's/;newrelic.daemon.dont_launch =.*/newrelic.daemon.dont_launch = 3/' \
           /usr/local/etc/php/conf.d/newrelic.ini

#RUN curl -L "https://download.newrelic.com/php_agent/archive/${NEW_RELIC_AGENT_VERSION}/newrelic-php5-${NEW_RELIC_AGENT_VERSION}-linux.tar.gz" | tar -C /tmp -zx \
# && export NR_INSTALL_USE_CP_NOT_LN=1 \
# && export NR_INSTALL_SILENT=1
#RUN /tmp/newrelic-php5-*/newrelic-install install \
# && rm -rf /tmp/newrelic-php5-* /tmp/nrinstall*
#
#RUN sed -i -e s/\"REPLACE_WITH_REAL_KEY\"/${NEW_RELIC_LICENSE_KEY}/ \
# -e s/newrelic.appname[[:space:]]=[[:space:]].\*/newrelic.appname="${NEW_RELIC_APPNAME}"/ \
# -e s/\;newrelic.daemon.address[[:space:]]=[[:space:]].\*/newrelic.daemon.address="${NEW_RELIC_DAEMON_ADDRESS}"/ \
#    /usr/local/etc/php/conf.d/newrelic.ini
