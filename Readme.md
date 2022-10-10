# Installation

### Run docker container
`docker-compose up -d`

### Run database migration
`docker exec -i social-mysql mysql -uroot -psecret social < datadump.sql`