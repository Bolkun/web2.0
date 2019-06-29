--database_name chat

CREATE TABLE IF NOT EXISTS `chat` (
	id BIGINT unsigned NOT NULL auto_increment,
	`name` CHAR(25) NOT NULL DEFAULT '',
	msg CHAR(255) NOT NULL DEFAULT '',
	`date` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO chat (`name`, msg) VALUES ('Sara', 'Hello World');
INSERT INTO chat (`name`, msg) VALUES ('Jordon', 'Hey there!');
