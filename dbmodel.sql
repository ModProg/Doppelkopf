-- ------
-- BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
-- dk implementation : © Roland Fredenhagen roland@van-fredenhagen.de
-- 
-- This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
-- See http://en.boardgamearena.com/#!doc/Studio for more information.
-- -----
-- dbmodel.sql
-- This is the file where you are describing the database schema of your game
-- Basically, you just have to export from PhpMyAdmin your table structure and copy/paste
-- this export here.
-- Note that the database itself and the standard tables ("global", "stats", "gamelog" and "player") are
-- already created and must not be created here
-- Note: The database schema is created from this file when the game starts. If you modify this file,
--       you have to restart a game to see your changes in database.
CREATE TABLE IF NOT EXISTS `card` (
    `card_id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `card_type` VARCHAR(16) NOT NULL,
    `card_type_arg` INT(11) NOT NULL,
    `card_location` VARCHAR(16) NOT NULL,
    `card_location_arg` INT(11) NOT NULL,
    `card_last_changed` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`card_id`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8 AUTO_INCREMENT = 1;

ALTER TABLE
    `player`
ADD
    `player_re` BOOLEAN NOT NULL DEFAULT '0';

CREATE TABLE IF NOT EXISTS `fox`(
    `fox_card` INT(10) UNSIGNED NOT NULL PRIMARY KEY,
    `fox_catcher` INT(10) UNSIGNED NOT NULL,
    `fox_looser` INT(10) UNSIGNED NOT NULL,
    FOREIGN KEY(`fox_catcher`) REFERENCES `player`(`player_id`),
    FOREIGN KEY(`fox_looser`) REFERENCES `player`(`player_id`),
    FOREIGN KEY(`fox_card`) REFERENCES `card`(`card_id`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8 AUTO_INCREMENT = 1;

CREATE TABLE IF NOT EXISTS `doppelkopf`(
    `doppelkopf_card` INT(10) UNSIGNED NOT NULL PRIMARY KEY,
    `doppelkopf_owener` INT(10) UNSIGNED NOT NULL,
    FOREIGN KEY(`doppelkopf_owener`) REFERENCES `player`(`player_id`),
    FOREIGN KEY(`doppelkopf_card`) REFERENCES `card`(`card_id`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8 AUTO_INCREMENT = 1;