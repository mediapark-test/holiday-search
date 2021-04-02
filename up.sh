#! /bin/bash

export USER_ID=$(id -u)
export GROUP_ID=$(id -g)

docker-compose up -d
docker-compose exec -dT php7.4 php /var/www/app/bin/console rabbitmq:consumer free_day
docker-compose exec -dT php7.4 php /var/www/app/bin/console rabbitmq:consumer free_day
docker-compose exec -dT php7.4 php /var/www/app/bin/console rabbitmq:consumer free_day
docker-compose exec -dT php7.4 php -S 0.0.0.0:80 -t /var/www/app/public