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

## Websocket messages with RabbitMQ

### Start RabbitMQ nodes

#### Start node1
`docker run -d --hostname node1.rabbit --net social_social-network --name rabbitNode1 -p "15673:15672" -e "RABBITMQ_USE_LONGNAME=true" -e RABBITMQ_ERLANG_COOKIE="cookie" rabbitmq:3-management`

#### Start node2
`docker run -d --hostname node2.rabbit --net social_social-network --name rabbitNode2 -p "15674:15672" -e "RABBITMQ_USE_LONGNAME=true" -e RABBITMQ_ERLANG_COOKIE="cookie" rabbitmq:3-management`

#### Make cluster

`docker exec rabbitNode2 rabbitmqctl stop_app`

`docker exec rabbitNode2 rabbitmqctl join_cluster rabbit@node1.rabbit`

`docker exec rabbitNode2 rabbitmqctl start_app`

#### Run websocket server

`docker exec social-php-fpm php ws/server.php`

#### Test send to queue

`docker exec social-php-fpm php ws/sendMessage.php {userId} {message}`

Example (sending message 'hello' by userId = 1): 

`docker exec social-php-fpm php ws/sendMessage.php 1 hello` 

Example HTML file: `examples/websocket.html`

