USE test;
CREATE TABLE IF NOT EXISTS messages
(
    id        INT(11)      NOT NULL AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    message VARCHAR(15000) NOT NULL,
    time VARCHAR(255) NOT NULL,
    PRIMARY KEY (id)
    ) ENGINE = InnoDB;