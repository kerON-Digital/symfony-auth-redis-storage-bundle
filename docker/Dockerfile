# ~/pe/keron-digital-auth-redis-storage-bundle/Dockerfile

FROM php:8.1-cli

ARG USER_ID=1000
ARG GROUP_ID=1000

WORKDIR /app

RUN apt-get update && apt-get install -y --no-install-recommends \
    git \
    unzip \
    libzip-dev \
    libxml2-dev \
    libicu-dev \
    && docker-php-ext-install -j$(nproc) intl opcache sockets xml zip \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && pecl install pcov \
    && docker-php-ext-enable pcov \
    && apt-get clean && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

RUN groupadd --gid $GROUP_ID nonroot \
    && useradd --uid $USER_ID --gid $GROUP_ID --create-home --shell /bin/bash nonroot
USER nonroot

VOLUME /app

CMD ["sleep", "infinity"]
