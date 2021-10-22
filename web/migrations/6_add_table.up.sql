USE test;
CREATE TABLE IF NOT EXISTS messages
(
    id        INT(11)      NOT NULL AUTO_INCREMENT,
    fullName VARCHAR(255) NOT NULL,
    message VARCHAR(255) NOT NULL,
    PRIMARY KEY (id)
    ) ENGINE = InnoDB;