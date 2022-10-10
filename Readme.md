# Installation

### Run docker container
`docker-compose up -d`

### Initial composer
`docker exec -i social-php-fpm composer install`

### Run database migration
`docker exec -i social-mysql mysql -uroot -psecret social < datadump.sql`