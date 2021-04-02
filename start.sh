#! /bin/bash

export USER_ID=$(id -u)
export GROUP_ID=$(id -g)

docker-compose up --build -d
docker-compose exec php7.4 composer install
docker-compose exec php7.4 php /var/www/app/bin/console doctrine:migrations:migrate  --no-interaction --allow-no-migration
docker-compose exec -dT php7.4 php /var/www/app/bin/console app:load-counties
docker-compose exec -dT php7.4 php /var/www/app/bin/console rabbitmq:consumer free_day
docker-compose exec -dT php7.4 php /var/www/app/bin/console rabbitmq:consumer free_day
docker-compose exec -dT php7.4 php /var/www/app/bin/console rabbitmq:consumer free_day
docker-compose exec -dT php7.4 php -S 0.0.0.0:80 -t /var/www/app/public
docker-compose exec php7.4 yarn install
docker-compose exec -dT php7.4 yarn encore production