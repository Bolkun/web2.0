--database shareposts

CREATE TABLE users (
	id INT(4) unsigned NOT NULL AUTO_INCREMENT,
	name VARCHAR(255),
	email VARCHAR(255),
	password VARCHAR(255),
	created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE posts (
	id INT(4) unsigned NOT NULL AUTO_INCREMENT,
	user_id INT,
	title  VARCHAR(255),
	body TEXT,
	created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO users (name, email, password) VALUES('John Dou', 'john@live.de', '123456');

