
CREATE DATABASE IF NOT EXISTS dialogs;

DROP TABLE IF EXISTS dialogs.history;

CREATE TABLE IF NOT EXISTS dialogs.history
(
    shardId         int unsigned not null,
    userId          int unsigned not null,
    recipientId     int unsigned not null,
    message text    not null,
    created timestamp default CURRENT_TIMESTAMP not null
);

create index history_user_index
    on dialogs.history (userId);

create index history_recipient_index
    on dialogs.history (recipientId);

create index history_created_index
    on dialogs.history (created DESC);

GRANT ALL PRIVILEGES ON dialogs.* TO 'test'@'%'  WITH GRANT OPTION;
FLUSH PRIVILEGES;