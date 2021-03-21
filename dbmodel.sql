-- ------
-- BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
-- dk implementation : © Roland Fredenhagen roland@van-fredenhagen.de
-- 
-- This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
-- See http://en.boardgamearena.com/#!doc/Studio for more information.
-- ------

-- suits:
-- ------
-- DIAMOND: 0
-- HEART:   1
-- SPADE:   2
-- CLUB:    3
-- TRUMP:   4

-- values:
-- ------
-- NINE:    0
-- JACK:    1
-- QUEEN:   2
-- KING:    3
-- TEN:     4
-- ACE:     5

-- gamemodes:
-- NORMAL:      0
-- SOLODIAMOND: 1
-- SOLOHEART:   2
-- SOLOSPADE:   3
-- SOLOCLUB:    4
-- SOLOQUEEN:   5
-- SOLOJACK:    6
-- SOLOACE:     7

CREATE TABLE IF NOT EXISTS `card` (
    `card_id` INT(10) UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    `card_type` VARCHAR(16) NOT NULL,
    `card_type_arg` INT(11) NOT NULL,
    `card_location` VARCHAR(16) NOT NULL,
    `card_location_arg` INT(11) NOT NULL,
    `card_last_changed` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `card_trumpN` INT(2) UNSIGNED AS (
        CASE
            WHEN (`card_type` = 0) THEN 
                CASE
                    WHEN (`card_type_arg` = 0) THEN 1
                    WHEN (`card_type_arg` = 3) THEN 2
                    WHEN (`card_type_arg` = 4) THEN 3
                    WHEN (`card_type_arg` = 5) THEN 4
                    WHEN (`card_type_arg` = 1) THEN 5
                    WHEN (`card_type_arg` = 2) THEN 9
                    ELSE 0
                END
            WHEN (`card_type` = 1) THEN 
                CASE
                    WHEN (`card_type_arg` = 1) THEN 6
                    WHEN (`card_type_arg` = 2) THEN 10
                    WHEN (`card_type_arg` = 4) THEN 13
                    ELSE 0
                END
            WHEN (`card_type` = 2) THEN 
                CASE
                    WHEN (`card_type_arg` = 1) THEN 7
                    WHEN (`card_type_arg` = 2) THEN 11
                    ELSE 0
                END
            WHEN (`card_type` = 3) THEN 
                CASE
                    WHEN (`card_type_arg` = 1) THEN 8
                    WHEN (`card_type_arg` = 2) THEN 12
                    ELSE 0
                END
            ELSE 0
        END
        ) STORED,
    `card_trumpD` INT(2) UNSIGNED AS (`card_trumpN`) STORED,
    `card_trumpH` INT(2) UNSIGNED AS (
        CASE
            WHEN (`card_type` = 0) THEN 
                CASE
                    WHEN (`card_type_arg` = 1) THEN 5
                    WHEN (`card_type_arg` = 2) THEN 9
                    ELSE 0
                END
            WHEN (`card_type` = 1) THEN 
                CASE
                    WHEN (`card_type_arg` = 0) THEN 1
                    WHEN (`card_type_arg` = 3) THEN 2
                    WHEN (`card_type_arg` = 5) THEN 4
                    WHEN (`card_type_arg` = 1) THEN 6
                    WHEN (`card_type_arg` = 2) THEN 10
                    WHEN (`card_type_arg` = 4) THEN 13
                    ELSE 0
                END
            WHEN (`card_type` = 2) THEN 
                CASE
                    WHEN (`card_type_arg` = 1) THEN 7
                    WHEN (`card_type_arg` = 2) THEN 11
                    ELSE 0
                END
            WHEN (`card_type` = 3) THEN 
                CASE
                    WHEN (`card_type_arg` = 1) THEN 8
                    WHEN (`card_type_arg` = 2) THEN 12
                    ELSE 0
                END
            ELSE 0
        END
        ) STORED,
    `card_trumpS` INT(2) UNSIGNED AS (
        CASE
            WHEN (`card_type` = 0) THEN 
                CASE
                    WHEN (`card_type_arg` = 1) THEN 5
                    WHEN (`card_type_arg` = 2) THEN 9
                    ELSE 0
                END
            WHEN (`card_type` = 1) THEN 
                CASE
                    WHEN (`card_type_arg` = 1) THEN 6
                    WHEN (`card_type_arg` = 2) THEN 10
                    WHEN (`card_type_arg` = 4) THEN 13
                    ELSE 0
                END
            WHEN (`card_type` = 2) THEN 
                CASE
                    WHEN (`card_type_arg` = 0) THEN 1
                    WHEN (`card_type_arg` = 3) THEN 2
                    WHEN (`card_type_arg` = 4) THEN 3
                    WHEN (`card_type_arg` = 5) THEN 4
                    WHEN (`card_type_arg` = 1) THEN 7
                    WHEN (`card_type_arg` = 2) THEN 11
                    ELSE 0
                END
            WHEN (`card_type` = 3) THEN 
                CASE
                    WHEN (`card_type_arg` = 1) THEN 8
                    WHEN (`card_type_arg` = 2) THEN 12
                    ELSE 0
                END
            ELSE 0
        END
        ) STORED,
    `card_trumpC` INT(2) UNSIGNED AS (
        CASE
            WHEN (`card_type` = 0) THEN 
                CASE
                    WHEN (`card_type_arg` = 1) THEN 5
                    WHEN (`card_type_arg` = 2) THEN 9
                    ELSE 0
                END
            WHEN (`card_type` = 1) THEN 
                CASE
                    WHEN (`card_type_arg` = 1) THEN 6
                    WHEN (`card_type_arg` = 2) THEN 10
                    WHEN (`card_type_arg` = 4) THEN 13
                    ELSE 0
                END
            WHEN (`card_type` = 2) THEN 
                CASE
                    WHEN (`card_type_arg` = 1) THEN 7
                    WHEN (`card_type_arg` = 2) THEN 11
                    ELSE 0
                END
            WHEN (`card_type` = 3) THEN 
                CASE
                    WHEN (`card_type_arg` = 0) THEN 1
                    WHEN (`card_type_arg` = 3) THEN 2
                    WHEN (`card_type_arg` = 4) THEN 3
                    WHEN (`card_type_arg` = 5) THEN 4
                    WHEN (`card_type_arg` = 1) THEN 8
                    WHEN (`card_type_arg` = 2) THEN 12
                    ELSE 0
                END
            ELSE 0
        END
        ) STORED,
    `card_trumpQ` INT(2) UNSIGNED AS (
        CASE
            WHEN (`card_type` = 0) THEN 
                CASE
                    WHEN (`card_type_arg` = 2) THEN 1
                    ELSE 0
                END
            WHEN (`card_type` = 1) THEN 
                CASE
                    WHEN (`card_type_arg` = 2) THEN 2
                    ELSE 0
                END
            WHEN (`card_type` = 2) THEN 
                CASE
                    WHEN (`card_type_arg` = 2) THEN 3
                    ELSE 0
                END
            WHEN (`card_type` = 3) THEN 
                CASE
                    WHEN (`card_type_arg` = 2) THEN 4
                    ELSE 0
                END
            ELSE 0
        END
        ) STORED,
    `card_trumpJ` INT(2) UNSIGNED AS (
        CASE
            WHEN (`card_type` = 0) THEN 
                CASE
                    WHEN (`card_type_arg` = 3) THEN 1
                    ELSE 0
                END
            WHEN (`card_type` = 1) THEN 
                CASE
                    WHEN (`card_type_arg` = 3) THEN 2
                    ELSE 0
                END
            WHEN (`card_type` = 2) THEN 
                CASE
                    WHEN (`card_type_arg` = 3) THEN 3
                    ELSE 0
                END
            WHEN (`card_type` = 3) THEN 
                CASE
                    WHEN (`card_type_arg` = 3) THEN 4
                    ELSE 0
                END
            ELSE 0
        END
        ) STORED,
    `card_trumpA` INT(2) UNSIGNED AS (0) STORED
) ENGINE = InnoDB DEFAULT CHARSET = utf8 AUTO_INCREMENT = 1;

ALTER TABLE
    `player`
ADD
    `player_re` BOOLEAN NOT NULL DEFAULT '0';

ALTER TABLE
    `player`
ADD 
    `player_reservation` BOOLEAN NOT NULL DEFAULT '0';

ALTER TABLE
    `player`
ADD 
    `player_throw` BOOLEAN NOT NULL DEFAULT '0';

ALTER TABLE
    `player`
ADD 
    `player_poverty` BOOLEAN NOT NULL DEFAULT '0';

ALTER TABLE
    `player`
ADD 
    `player_solo` BOOLEAN NOT NULL DEFAULT '0';

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
    `doppelkopf_owner` INT(10) UNSIGNED NOT NULL,
    FOREIGN KEY(`doppelkopf_owner`) REFERENCES `player`(`player_id`),
    FOREIGN KEY(`doppelkopf_card`) REFERENCES `card`(`card_id`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8 AUTO_INCREMENT = 1;
