--database mvc

CREATE TABLE posts (
	id INT(4) unsigned NOT NULL AUTO_INCREMENT,
	title VARCHAR(255),
	PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO posts (title) VALUES('Post one');
INSERT INTO posts (title) VALUES('Post two');