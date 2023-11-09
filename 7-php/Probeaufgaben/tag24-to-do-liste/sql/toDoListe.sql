/* drop database `to-do-list`; */

CREATE TABLE `user` (
    id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(15) NOT NULL,
    password VARCHAR(32) NOT NULL
)

CREATE TABLE liste (
    id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `date` Date NOT NULL,
    priority VARCHAR(10) DEFAULT 'mittel',
    text VARCHAR (255),
    `name` VARCHAR(15) NOT NULL
)
