# Tarantool examples

## Configure tarantool

    docker exec social-tarantool cp /usr/local/etc/tarantool/instances.available/user.lua /usr/local/etc/tarantool/instances.enabled/
    chown -R tarantool /var/log/tarantool/
    docker exec social-tarantool tarantoolctl start user.lua


## Configuration in user.lua

### Create new space
    u = box.schema.space.create('user')

### Create space format
    u:format({
        {name = 'id', type = 'unsigned'},
        {name = 'firstName', type = 'string'},
        {name = 'lastName', type = 'string'},
        {name = 'years', type = 'unsigned'},
        {name = 'sex', type = 'string'},
        {name = 'city', type = 'string'},
        {name = 'password', type = 'string'}
    })

### Create index
`u:create_index('primary', {
    type = 'tree',
    parts = {'id'}
})`

## Configure MySQL

For example using default root access

## Replication

Using daemon tool https://github.com/tarantool/mysql-tarantool-replication

https://www.tarantool.io/en/doc/latest/how-to/sql/improving_mysql/


### Cloning mysql replication tool

    git clone https://github.com/tarantool/mysql-tarantool-replication.git`
    cd mysql-tarantool-replication
    git submodule update --init --recursive
    cmake .
    make

#### An error was received while executing the command `make` on Ubuntu 20

    error: ‘vector’ in namespace ‘std’ does not name a template type

This error has been fixed adding string `#include <vector>` in file mysql-tarantool-replication/lib/libslave/field.cpp on line 18

### Replication configuration file

    `replicator/replicatord.yml`



