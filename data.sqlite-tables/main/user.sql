create table user
(
    id              INT AUTO_INCREMENT
        primary key,
    user            VARCHAR(50)  not null
        unique,
    passwords       VARCHAR(255) not null,
    lockout_time    INT default NULL,
    failed_attempts INT default 0
);

