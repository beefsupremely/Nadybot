CREATE TABLE IF NOT EXISTS `quote` (
	`id` INTEGER NOT NULL PRIMARY KEY,
	`poster` VARCHAR(25) NOT NULL,
	`dt` INT NOT NULL,
	`msg` VARCHAR(1000) NOT NULL
);
