box.cfg {
    listen = 3302;
    memtx_memory = 128 * 1024 * 1024; -- 128Mb
    memtx_min_tuple_size = 16;
    memtx_max_tuple_size = 128 * 1024 * 1024; -- 128Mb
    vinyl_memory = 128 * 1024 * 1024; -- 128Mb
    vinyl_cache = 128 * 1024 * 1024; -- 128Mb
    vinyl_max_tuple_size = 128 * 1024 * 1024; -- 128Mb
    vinyl_write_threads = 2;
    wal_mode = "none";
    wal_max_size = 256 * 1024 * 1024;
    checkpoint_interval = 60 * 60; -- one hour
    checkpoint_count = 6;
    force_recovery = true;

     -- 1 – SYSERROR
     -- 2 – ERROR
     -- 3 – CRITICAL
     -- 4 – WARNING
     -- 5 – INFO
     -- 6 – VERBOSE
     -- 7 – DEBUG
     log_level = 7;
     too_long_threshold = 0.5;
 }

box.schema.user.grant('guest','read,write,execute','universe')

local function bootstrap()

    if not box.space.mysqldaemon then
        s = box.schema.space.create('mysqldaemon')
        s:create_index('primary',
        {type = 'tree', parts = {1, 'unsigned'}, if_not_exists = true})
    end

    if not box.space.user then
        u = box.schema.space.create('user')
        u:format({
                {name = 'id', type = 'unsigned'},
                {name = 'firstName', type = 'string'},
                {name = 'lastName', type = 'string'},
                {name = 'years', type = 'unsigned'},
                {name = 'sex', type = 'string'},
                {name = 'city', type = 'string'},
                {name = 'password', type = 'string'}
            })
        u:create_index('primary', {
            type = 'tree',
            parts = {'id'}
        })
    end

end

bootstrap()