# Installation

### Run docker container
`docker-compose up -d`

### Initial composer
`docker exec -i social-php-fpm composer install`

### Run database migration
`docker exec -i social-mysql mysql -uroot -psecret < datadump.sql`

## Replication

### Run write to table for test DB replication
`docker exec -i social-php-fpm php bin/testWrite.php`

## Dialogs sharding

### Create schema on shards

`docker exec -i social-mysql-shard-1 mysql -uroot -psecret < dialogs_init.sql`

`docker exec -i social-mysql-shard-2 mysql -uroot -psecret < dialogs_init.sql`

#### Post message
`curl --location --request POST 'http://social.dev/user/16/messages' \
--header 'Content-Type: application/json' \
--data-raw '{
"recipientId":9,
"message":"Test message"
}'`

#### Get messages
`curl --location --request GET 'http://social.dev/user/16/messages?recipientId=9'`