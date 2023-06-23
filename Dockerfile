# récupération de l'image php-apache
FROM ghcr.io/ld-web/php-apache:8.2

# Activation du module rewrite d'a2enmod pour autoriser la réécriture de requête
RUN a2enmod rewrite