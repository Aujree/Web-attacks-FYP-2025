create table level_3
(
    id       INTEGER
        primary key,
    hostname TEXT,
    ip       TEXT,
    port     INTEGER,
    os       TEXT collate NOCASE
);

