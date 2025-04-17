create table logs
(
    id              INT AUTO_INCREMENT
        primary key,
    level           VARCHAR(50) not null,
    input           TEXT        not null,
    timestamp       DATETIME default CURRENT_TIMESTAMP,
    ip_address      VARCHAR(45),
    user_agent      VARCHAR(255),
    suspicious      BOOLEAN  default FALSE,
    failed_attempts INT      default 0,
    user_id         integer
);

