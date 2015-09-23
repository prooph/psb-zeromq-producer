FROM php:5.6.13-cli

MAINTAINER Bradley Weston <me@bweston.me>

RUN apt-get update && \
    apt-get install -yq --no-install-recommends \
                       git \
                       libzmq-dev \
                       php-pear \
                       curl && \
    apt-get clean && \
    rm -rf /var/lib/apt/lists/*

RUN curl -L http://pecl.php.net/get/xdebug-2.3.3.tgz >> /usr/src/php/ext/xdebug.tgz && \
    tar -xf /usr/src/php/ext/xdebug.tgz -C /usr/src/php/ext/ && \
    rm /usr/src/php/ext/xdebug.tgz && \
    docker-php-ext-install xdebug-2.3.3 mbstring

RUN cd /tmp && \
    git clone git://github.com/mkoppanen/php-zmq.git && \
    cd php-zmq && \
    phpize && ./configure && \
    make && make install && \
    docker-php-ext-enable zmq

WORKDIR /tmp

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

RUN composer selfupdate && \
    composer require "phpunit/phpunit:~4.8.5" --prefer-source --no-interaction && \
    ln -s /tmp/vendor/bin/phpunit /usr/local/bin/phpunit

COPY . /app
WORKDIR /app

ENTRYPOINT ["/usr/local/bin/phpunit"]
CMD ["--configuration=phpunit.dist.xml"]
