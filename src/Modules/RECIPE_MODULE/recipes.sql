DROP TABLE IF EXISTS `recipes`;
CREATE TABLE `recipes` (
	`id`     INT NOT NULL PRIMARY KEY,
	`name`   VARCHAR(50) NOT NULL,
	`author` VARCHAR(50) NOT NULL,
	`recipe` TEXT NOT NULL,
	`date`   INT NOT NULL
);
