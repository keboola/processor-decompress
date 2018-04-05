FROM php:7-cli

ENV DEBIAN_FRONTEND noninteractive
ENV COMPOSER_ALLOW_SUPERUSER 1

WORKDIR /tmp/

RUN apt-get update && apt-get install -y --no-install-recommends \
		git \
		libsnappy-dev \
        unzip \
        zlib1g-dev \
	&& rm -r /var/lib/apt/lists/* \
	&& docker-php-ext-install -j$(nproc) zip \
	&& curl -sS --fail https://getcomposer.org/installer | php \
	&& mv /tmp/composer.phar /usr/local/bin/composer 


RUN git clone --recursive https://github.com/kjdev/php-ext-snappy.git \
	&& cd php-ext-snappy \
	&& git checkout tags/0.1.9 \
	&& phpize \
	&& ./configure \
	&& make \
	&& make install

COPY . /code/
COPY /docker/php.ini /usr/local/etc/php/php.ini
WORKDIR /code/
RUN composer install --no-interaction
CMD ["php", "/code/main.php"]
