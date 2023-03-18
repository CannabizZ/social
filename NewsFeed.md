# News feed

### First project start

    See `Readme.md`

## Start RabbitMQ

#### Start node1

    `docker run -d --hostname node1.rabbit --net social_social-network --name rabbitNode1 -p "15673:15672" -e "RABBITMQ_USE_LONGNAME=true" -e RABBITMQ_ERLANG_COOKIE="cookie" rabbitmq:3-management`
    `docker exec rabbitNode2 rabbitmqctl start_app`

## API requests

### Publication new post

    `curl --location --request PUT 'http://social.dev/user/{userId}/posts' \
    --header 'Content-Type: application/json' \
    --data-raw '{
    "title":"Title new post",
    "message":"Text message"
    }'`

### Get news feed

    `curl --location --request GET 'http://social.dev/user/{userId}/posts'`










