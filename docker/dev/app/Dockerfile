FROM ubuntu:xenial
MAINTAINER jacobs@gmail.com <jacobs@gmail.com>

ENV NGINX_CONF_DIR="/etc/nginx"
ENV OS_LOCALE="en_US.UTF-8"

ENV PHP_RUN_DIR=/run/php \
    PHP_LOG_DIR=/var/log/php \
    PHP_CONF_DIR=/etc/php/7.1 \
    PHP_DATA_DIR=/var/lib/php \
    RUNLEVEL=1


# dpkg-preconfigure error messages fix
RUN echo 'debconf debconf/frontend select Noninteractive' | debconf-set-selections

# Let's start with some basic stuff.
RUN apt-get update -qq && apt-get install -qqy \
    locales \
    apt-transport-https \
    ca-certificates \
    lxc \
    curl \
    apt-utils \
    wget \
    nano \
    cron \
    iptables \
    && locale-gen ${OS_LOCALE}

ENV LANG=${OS_LOCALE} \
    LANGUAGE=en_US:en \
    LC_ALL=${OS_LOCALE} \
    DEBIAN_FRONTEND=noninteractive

# Add a non-root user to prevent files being created with root permissions on host machine.
ARG PUID=1000
ARG PGID=1000

ENV PUID ${PUID}
ENV PGID ${PGID}

RUN groupadd -g ${PGID} test && useradd -u ${PUID} -g test -m test

# Avoid ERROR: invoke-rc.d: policy-rc.d denied execution of start.
RUN sed -i "s/^exit 101$/exit 0/" /usr/sbin/policy-rc.d

RUN \
    buildDeps='software-properties-common' \
    # Install common libraries
    && apt-get install --no-install-recommends -y $buildDeps \
    && wget -O - http://nginx.org/keys/nginx_signing.key | apt-key add - \
	&& echo "deb http://nginx.org/packages/ubuntu/ xenial nginx" | tee -a /etc/apt/sources.list \
	&& echo "deb-src http://nginx.org/packages/ubuntu/ xenial nginx" | tee -a /etc/apt/sources.list \
    && add-apt-repository -y ppa:ondrej/php \
    && apt-get update \
    # Install PHP libraries
    && apt-get install -y php7.1-fpm php7.1-cli php7.1-readline php7.1-mbstring php7.1-zip php7.1-intl php7.1-json php7.1-xml php7.1-curl php7.1-mcrypt php7.1-gd php7.1-pgsql php7.1-mysql php7.1-mongodb php-pear php7.1-bcmath php7.1-gmp \
    && phpenmod mcrypt \
    # Install composer
    && curl -sS https://getcomposer.org/installer | php -- --version=1.4.1 --install-dir=/usr/local/bin --filename=composer \
    && mkdir -p ${PHP_LOG_DIR} ${PHP_RUN_DIR} \
    # Install nginx
	&& apt-get install -y nginx \
	&& rm -rf ${NGINX_CONF_DIR}/sites-enabled/* ${NGINX_CONF_DIR}/sites-available/* \
	&& rm ${NGINX_CONF_DIR}/conf.d/default.conf \
	# Install supervisor
	&& apt-get install -y supervisor && mkdir -p /var/log/supervisor \
    # make /var/www
    && mkdir -p /var/www \
    && chown -R test:test /var/www \
	# Cleaning
    && apt-get purge -y --auto-remove $buildDeps locales \
    && apt-get autoremove -y && apt-get clean \
    && rm -rf /var/lib/apt/lists/* \
	# Forward request and error logs to docker log collector
	&& ln -sf /dev/stdout /var/log/nginx/access.log \
	&& ln -sf /dev/stderr /var/log/nginx/error.log

COPY ./configs/php-fpm.conf ${PHP_CONF_DIR}/fpm/php-fpm.conf
COPY ./configs/www.conf ${PHP_CONF_DIR}/fpm/pool.d/www.conf
COPY ./configs/php.ini ${PHP_CONF_DIR}/fpm/conf.d/custom.ini
COPY ./configs/php.ini ${PHP_CONF_DIR}/cli/conf.d/custom.ini

COPY ./configs/supervisord.conf /etc/supervisor/conf.d/

COPY ./configs/nginx.conf ${NGINX_CONF_DIR}/nginx.conf

RUN sed -i "s~PHP_RUN_DIR~${PHP_RUN_DIR}~g" ${PHP_CONF_DIR}/fpm/php-fpm.conf \
    && sed -i "s~PHP_LOG_DIR~${PHP_LOG_DIR}~g" ${PHP_CONF_DIR}/fpm/php-fpm.conf \
    && chown test:test ${PHP_DATA_DIR} -Rf

# Set timezone
ENV TZ=Europe/Kiev
RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone

# copy GeoIP
#RUN mkdir -p /usr/share/GeoIP
#COPY ./GeoIP /usr/share/GeoIP

#RUN chown -R test:test /usr/share/GeoIP && chmod 755 /usr/share/GeoIP && chmod -R 644 /usr/share/GeoIP/*

#crontab
RUN echo "* * * * * test php /var/www/test/artisan schedule:run >> /dev/null 2>&1" >> /etc/crontab

WORKDIR /var/www

EXPOSE 9000 80 443

# PHP_DATA_DIR store sessions
VOLUME ["${PHP_RUN_DIR}", "${PHP_DATA_DIR}"]

RUN version=$(php -r "echo PHP_MAJOR_VERSION.PHP_MINOR_VERSION;") \
    && curl -A "Docker" -o /tmp/blackfire-probe.tar.gz -D - -L -s https://blackfire.io/api/v1/releases/probe/php/linux/amd64/$version \
    && tar zxpf /tmp/blackfire-probe.tar.gz -C /tmp \
    && mv /tmp/blackfire-*.so $(php -r "echo ini_get('extension_dir');")/blackfire.so \
    && printf "extension=blackfire.so\nblackfire.agent_socket=tcp://blackfire:8707\n" > ${PHP_CONF_DIR}/fpm/conf.d/blackfire.ini

CMD ["/usr/bin/supervisord"]