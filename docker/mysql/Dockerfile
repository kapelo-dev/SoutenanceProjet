# Image MySQL pour déploiement sur Render (service privé).
# Variables d'env attendues : MYSQL_DATABASE, MYSQL_USER, MYSQL_PASSWORD, MYSQL_ROOT_PASSWORD
# Sur Render : ajouter un disque avec Mount Path = /var/lib/mysql

FROM mysql:8.0

RUN chown -R mysql:mysql /var/lib/mysql

CMD ["mysqld", "--default-authentication-plugin=mysql_native_password"]
