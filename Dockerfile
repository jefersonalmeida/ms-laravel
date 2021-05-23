FROM php:7.4-fpm-alpine3.13

RUN apk add --no-cache git \
            shadow \
            openssl \
            bash \
            mysql-client \
            nodejs \
            npm \
            freetype-dev \
            libjpeg-turbo-dev \
            libpng-dev \
            libzip-dev

RUN npm config set cache /var/www/.npm-cache --global
RUN touch /root/.bashrc | echo "PS1='\w\$ '" >> /root/.bashrc
RUN touch /home/www-data/.bashrc | echo "PS1='\w\$ '" >> /home/www-data/.bashrc

RUN docker-php-ext-install pdo pdo_mysql bcmath sockets zip
RUN docker-php-ext-configure gd --with-freetype --with-jpeg
RUN docker-php-ext-install -j$(nproc) gd

ENV DOCKERIZE_VERSION v0.6.1
RUN wget https://github.com/jwilder/dockerize/releases/download/$DOCKERIZE_VERSION/dockerize-linux-amd64-$DOCKERIZE_VERSION.tar.gz \
    && tar -C /usr/local/bin -xzvf dockerize-linux-amd64-$DOCKERIZE_VERSION.tar.gz \
    && rm dockerize-linux-amd64-$DOCKERIZE_VERSION.tar.gz

RUN usermod -u 1000 www-data

WORKDIR /var/www
RUN rm -rf /var/www/html

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

RUN ln -s public html

USER www-data

EXPOSE 9000
