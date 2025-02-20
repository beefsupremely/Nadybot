CREATE TABLE IF NOT EXISTS tower_attack_<myname> (
	id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
	`time` int,
	`att_guild_name` VARCHAR(50),
	`att_faction` VARCHAR(10),
	`att_player` VARCHAR(50),
	`att_level` int,
	`att_ai_level` int,
	`att_profession` VARCHAR(15),
	`def_guild_name` VARCHAR(50),
	`def_faction` VARCHAR(10),
	`playfield_id` INT,
	`site_number` INT,
	`x_coords` INT,
	`y_coords` INT
);

CREATE TABLE IF NOT EXISTS tower_victory_<myname> (
	id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
	`time` int,
	`win_guild_name` VARCHAR(50),
	`win_faction` VARCHAR(10),
	`lose_guild_name` VARCHAR(50),
	`lose_faction` VARCHAR(10),
	`attack_id` INT
);
