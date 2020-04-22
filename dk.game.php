<?php

/**
 *------
 * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
 * dk implementation : © Roland Fredenhagen roland@van-fredenhagen.de
 *
 * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
 * See http://en.boardgamearena.com/#!doc/Studio for more information.
 * -----
 *
 * dk.game.php
 *
 * This is the main file for your game logic.
 *
 * In this PHP file, you are going to defines the rules of the game.
 *
 */

require_once APP_GAMEMODULE_PATH . 'module/table/table.game.php';

class dk extends Table
{
    public function __construct()
    {
        // Your global variables labels:
        //  Here, you can assign labels to global variables you are using for this game.
        //  You can use any number of global variables with IDs between 10 and 99.
        //  If your game has options ( variants ), you also have to associate here a label to
        //  the corresponding ID in gameoptions.inc.php.
        // Note: afterwards, you can get/set the global variables with getGameStateValue/setGameStateInitialValue/setGameStateValue

        parent::__construct();

        self::initGameStateLabels(array(
            'round' => 10,
            'trickSuit' => 11,
            'karlchen' => 12,
            //    'my_first_game_variant' => 100,
            //    'my_second_game_variant' => 101,
            //      ...
        ));

        $this->cards = self::getNew('module.common.deck');
        $this->cards->init('card');
    }

    protected function getGameName()
    {
        // Used for translations and stuff. Please do not modify.
        return 'dk';
    }

    /*
    setupNewGame:

    This method is called only once, when a new game is launched.
    In this method, you must setup the game according to the game rules, so that
    the game is ready to be played.
     */
    protected function setupNewGame($players, $options = array())
    {

        // Set the colors of the players with HTML color code
        // The default below is red/green/blue/orange/brown
        // The number of colors defined here must correspond to the maximum number of players allowed for the gams
        $gameinfos = self::getGameinfos();
        $default_colors = $gameinfos['player_colors'];

        // Create players
        // Note: if you added some extra field on 'player' table in the database ( dbmodel.sql ), you can initialize it there.
        $sql = 'INSERT INTO player (player_id, player_color, player_canal, player_name, player_avatar) VALUES ';
        $values = array();
        foreach ($players as $player_id => $player) {
            $color = array_shift($default_colors);
            $values[] = "('" . $player_id . "','$color','" . $player['player_canal'] . "','" . addslashes($player['player_name']) . "','" . addslashes($player['player_avatar']) . "')";
        }
        $sql .= implode(',', $values);
        self::DBQuery($sql);
        self::reattributeColorsBasedOnPreferences($players, $gameinfos['player_colors']);
        self::reloadPlayersBasicInfos();

        /************ Start the game initialization *****/

        // Init global values with their initial values
        //self::setGameStateInitialValue( 'my_first_global_variable', 0 );

        self::setGameStateInitialValue('round', 1);
        self::setGameStateInitialValue('trickSuit', 1);
        self::setGameStateInitialValue('karlchen', 0);

        // Create cards
        $cards = array();
        $DIAMOND = DIAMOND;
        $HEART = HEART;
        $SPADE = SPADE;
        $CLUB = CLUB;
        $TRUMP = TRUMP;
        $NINE = NINE;
        $JACK = JACK;
        $QUEEN = QUEEN;
        $KING = KING;
        $TEN = TEN;
        $ACE = ACE;

        for ($suit = $DIAMOND; $suit <= $CLUB; $suit++) {
            // spade, heart, diamond, club
            for ($value = $NINE; $value <= $ACE; $value++) {
                //  2, 3, 4, ... K, A
                $cards[] = array('type' => $suit, 'type_arg' => $value, 'nbr' => 1);
                $cards[] = array('type' => $suit, 'type_arg' => $value, 'nbr' => 1);
            }
        }

        $this->cards->createCards($cards, 'deck');

        // Create Trumps
        // TODO Add Trump Change
        self::addTrump($DIAMOND, $NINE);
        self::addTrump($DIAMOND, $KING);
        self::addTrump($DIAMOND, $TEN);
        self::addTrump($DIAMOND, $ACE);
        self::addTrump($DIAMOND, $JACK);
        self::addTrump($HEART, $JACK);
        self::addTrump($SPADE, $JACK);
        self::addTrump($CLUB, $JACK);
        self::addTrump($DIAMOND, $QUEEN);
        self::addTrump($HEART, $QUEEN);
        self::addTrump($SPADE, $QUEEN);
        self::addTrump($CLUB, $QUEEN);
        self::addTrump($HEART, $TEN);

        // Init game statistics
        // ( note: statistics used in this file must be defined in your stats.inc.php file )
        //self::initStat( 'table', 'table_teststat1', 0 );
        // Init a table statistics
        //self::initStat( 'player', 'player_teststat1', 0 );
        // Init a player statistics ( for all players )

        // Activate first player ( which is in general a good idea : ) )
        self::activeNextPlayer();

        /************ End of the game initialization *****/
    }

    /*
    // XXX getAllDatas:

    Gather all informations about current game situation ( visible by the current player ).

    The method is called each time the game interface is displayed to a player, ie:
    _ when the game starts
    _ when a player refreshes the game page ( F5 )
     */
    protected function getAllDatas()
    {
        self::error(self::getCurrentPlayerColor());
        $result = array('players' => array());

        $current_player_id = self::getCurrentPlayerId();
        // !! We must only return informations visible by this player !!

        // Get information about players
        // Note: you can retrieve some extra field you added for 'player' table in 'dbmodel.sql' if you need it.
        $sql = 'SELECT player_id id, player_score score FROM player ';
        $result['players'] = self::getCollectionFromDb($sql);

        $sql = 'SELECT
                    `fox_card` `card`,
                    `card_type` `suit`,
                    `card_type_arg` `value`,
                    `fox_catcher` `catcher`,
                    `fox_looser` `looser`
                FROM
                    `fox`
                JOIN
                    `card` ON `fox`.`fox_card` = `card`.`card_id`;';
        $result['foxes'] = self::getCollectionFromDb($sql);
        $sql = 'SELECT
                    `doppelkopf_card` `card`,
                    `card_type` `suit`,
                    `card_type_arg` `value`,
                    `doppelkopf_owner` `owner`
                FROM
                    `doppelkopf`
                JOIN
                    `card` ON `doppelkopf`.`doppelkopf_card` = `card`.`card_id`;';
        $result['doppelköpfe'] = self::getCollectionFromDb($sql);

        // Cards in player hand
        // $result['hand'] = $this->cards->getCardsInLocation( 'hand', $current_player_id );

        $result['cardSorting'] = self::getObjectListFromDB(
            "SELECT `card`.`card_type` `suit`, `card`.`card_type_arg` `value`, `card`.`card_trump` `trump`
             FROM `card`"
        );

        $result['hand'] = self::getObjectListFromDB(
            "SELECT `card`.`card_id` `id`, `card`.`card_type` `suit`, `card`.`card_type_arg` `value`
            FROM `card`
            WHERE `card`.`card_location` = 'hand' AND `card`.`card_location_arg` = '$current_player_id'"
        );
        // Cards played on the table
        $result['table'] = $this->cards->getCardsInLocation('table');

        return $result;
    }

    /*
    getGameProgression:

    Compute and return the current game progression.
    The number returned must be an integer beween 0 ( = the game just started ) and
    100 ( = the game is finished or almost finished ).

    This method is called each time we are in a game state with the 'updateGameProgression' property set to true
    ( see states.inc.php )
     */

    public function getGameProgression()
    {
        return (self::getUniqueValueFromDB(
            "SELECT
                AVG(mycount)
            FROM
                (
                SELECT
                    `card`.`card_location_arg`,
                    COUNT(`card`.`card_location_arg`) mycount
                FROM
                    `card`
                WHERE
                    `card`.`card_location` = 'hand'
                GROUP BY
                    `card`.`card_location_arg`
            ) test;"
        ) + 12 * self::getGameStateValue('round') * 12) * 100 / (4 * 12);
    }

    //////////////////////////////////////////////////////////////////////////////
    //////////// XXX Utility functions
    ////////////

    /*
    In this space, you can put any utility methods useful for your game logic
     */
    // get score
    public function dbGetScore($player_id)
    {
        return self::getUniqueValueFromDB("SELECT player_score FROM player WHERE player_id='$player_id'");
    }

    // set score
    public function dbSetScore($player_id, $count)
    {
        self::DBQuery("UPDATE player SET player_score='$count' WHERE player_id='$player_id'");
    }

    // set aux score (tie breaker)
    public function dbSetAuxScore($player_id, $score)
    {
        self::DBQuery("UPDATE player SET player_score_aux=$score WHERE player_id='$player_id'");
    }

    public function dbIncScore($player_id, $inc)
    {
        $count = self::dbGetScore($player_id);
        if ($inc != 0) {
            $count += $inc;
            self::dbSetScore($player_id, $count);
        }
        return $count;
    }

    public function getTrump($card)
    {
        $card_id = $card['id'];
        return self::getUniqueValueFromDB(
            "SELECT MAX(`card_trump`)
            FROM `card`
            WHERE
            `card_id` = $card_id;"
        );
        //return array_search( array( $card['type'], $card['type_arg'] ), $this->trumps );
    }

    public function h10($card, $best_card)
    {
        $HEART = HEART;
        $TEN = TEN;
        return $card['type'] == $HEART && $card['type_arg'] == $TEN && $best_card['type'] == $HEART && $best_card['type_arg'] == $TEN;
    }

    public function addTrump($suit, $value)
    {
        self::DBQuery(
            "UPDATE
            `card` AS c1
        JOIN
            (
            SELECT
                MAX(`card`.`card_trump`) AS max_number
            FROM
                `card`
            ) AS m
        SET
            c1.`card_trump` = m.max_number+1
        WHERE
            c1.`card_type` = '$suit' AND c1.`card_type_arg` = '$value';"
        );
    }

    public function hasSuit($player, $suit)
    {
        if ($suit != TRUMP) {
            return 0 < self::getUniqueValueFromDB(
                "SELECT
                    COUNT(`card`.`card_id`)
                FROM
                    `card`
                WHERE
                    `card`.`card_location_arg` = '$player' AND `card`.`card_location` = 'hand' AND `card`.`card_trump` = '0' AND `card`.`card_type` = '$suit';"
            );
        } else {
            return 0 < self::getUniqueValueFromDB(
                "SELECT
                    COUNT(`card`.`card_id`)
                FROM
                    `card`
                WHERE
                    `card`.`card_location_arg` = '$player' AND `card`.`card_location` = 'hand' AND `card`.`card_trump` > '0';"
            );
        }
    }

    public function fox($card)
    {
        return $card['type'] == DIAMOND && $card['type_arg'] == ACE;
    }

    //////////////////////////////////////////////////////////////////////////////
    //////////// Player actions
    ////////////

    /*
    Each time a player is doing some game action, one of the methods below is called.
    ( note: each method below must match an input method in dk.action.php )
     */

    public function playCard($card_id)
    {
        self::checkAction('playCard');
        $player_id = self::getActivePlayerId();
        $currentCard = $this->cards->getCard($card_id);
        if (self::getGameStateValue('trickSuit') == 0) {
            $trick_suit = self::getTrump($currentCard) == 0 ? $currentCard['type'] : 5;
            self::setGameStateValue('trickSuit', $trick_suit);
        } else {
            $trick_suit = self::getGameStateValue('trickSuit');
        }

        if (
            $trick_suit == $currentCard['type'] && self::getTrump($currentCard) == 0 ||
            $trick_suit == 5 && self::getTrump($currentCard) > 0 ||
            !self::hasSuit($player_id, $trick_suit)
        ) {

            $this->cards->moveCard($card_id, 'table', $player_id);
            // TODO  rule variations
            // And notify
            self::notifyAllPlayers('playCard', clienttranslate('${player_name} plays ${value_displayed} of ${suit_displayed}s'), array(
                'i18n' => array('suit_displayed', 'value_displayed'), 'card_id' => $card_id, 'player_id' => $player_id,
                'player_name' => self::getActivePlayerName(), 'value' => $currentCard['type_arg'],
                'value_displayed' => $this->values_label[$currentCard['type_arg']]['name'], 'suit' => $currentCard['type'],
                'suit_displayed' => $this->suits[$currentCard['type']]['name'],
            ));
            // Next player
            $this->gamestate->nextState('playCard');
        } else {
            throw new BgaUserException(sprintf(
                _('You cannot play %s of %ss when %s is played.'),
                $this->values_label[$currentCard['type_arg']]['nametr'],
                $this->suits[$currentCard['type']]['nametr'],
                $this->suits[$trick_suit]['nametr']
            ));

            $this->gamestate->nextState('wrongCard');
        }
    }
    //////////////////////////////////////////////////////////////////////////////
    //////////// Game state arguments
    ////////////

    /*
    Here, you can create methods defined as 'game state arguments' ( see 'args' property in states.inc.php ).
    These methods function is to return some additional information that is specific to the current
    game state.
     */

    /*

    Example for game state 'MyGameState':

    function argMyGameState()
    {
    // Get some values from the current game situation in database...

    // return values:
    return array(
    'variable1' => $value1,
    'variable2' => $value2,
    ...
    );
    }

     */

    //////////////////////////////////////////////////////////////////////////////
    //////////// Game state actions
    ////////////

    /*
    Here, you can create methods defined as 'game state actions' ( see 'action' property in states.inc.php ).
    The action method of state X is called everytime the current game state is set to X.
     */

    public function stNewHand()
    {
        // Take back all cards ( from any location => null ) to deck
        $this->cards->moveAllCardsInLocation(null, 'deck');
        $this->cards->shuffle('deck');
        $players = self::loadPlayersBasicInfos();
        foreach ($players as $player_id => $player) {
            $this->cards->pickCards(12, 'deck', $player_id);
            $cards = $result['hand'] = self::getObjectListFromDB(
                "SELECT `card`.`card_id` `id`, `card`.`card_type` `suit`, `card`.`card_type_arg` `value`, `card`.`card_trump` `trump`
                     FROM `card`
                     WHERE `card`.`card_location` = 'hand' AND `card`.`card_location_arg` = '$player_id'
                     ORDER BY `card`.`card_trump`, `card`.`card_type`, `card_type_arg`"
            );
            // Notify player about his cards
            self::notifyPlayer($player_id, 'newHand', '', array('cards' => $cards));
        }
        $this->gamestate->nextState('');
    }

    public function stPreRound()
    {
        $DIAMOND = DIAMOND;
        $HEART = HEART;
        $SPADE = SPADE;
        $CLUB = CLUB;
        $TRUMP = TRUMP;
        $NINE = NINE;
        $JACK = JACK;
        $QUEEN = QUEEN;
        $KING = KING;
        $TEN = TEN;
        $ACE = ACE;

        $round = self::getGameStateValue('round') % self::getPlayersNumber() + 1;
        $this->gamestate->changeActivePlayer(self::getUniqueValueFromDB(
            "SELECT
                    `player_id`
                FROM
                    `player`
                WHERE
                    `player_no` = '$round'"
        ));
        // TODO Hochzeit
        $hochzeit = 1 == self::getUniqueValueFromDB(
            "SELECT
                    COUNT(DISTINCT `card_location_arg`)
                FROM
                    `card`
                WHERE
                    `card_type` = $CLUB AND `card_type_arg` = '$QUEEN';"
        );

        $neunen = 5 <= self::getUniqueValueFromDB(
            "SELECT
                    MAX(mycount)
                FROM
                    (
                    SELECT
                        `card`.`card_location_arg`,
                        COUNT(`card`.`card_location_arg`) mycount
                    FROM
                        `card`
                    WHERE
                        `card`.`card_type_arg` = '$NINE'
                    GROUP BY
                        `card`.`card_location_arg`
                ) test"
        );

        $neunenKönige = 1 <= self::getUniqueValueFromDB(
            "SELECT
                    COUNT(test.`card_location_arg`) `count`
                FROM
                    (
                    SELECT
                        `card`.`card_location_arg`,
                        COUNT(`card`.`card_location_arg`) neunen
                    FROM
                        `card`
                    WHERE
                        `card`.`card_type_arg` = '$NINE'
                    GROUP BY
                        `card`.`card_location_arg`
                ) test
                JOIN
                    (
                    SELECT
                        `card`.`card_location_arg`,
                        COUNT(`card`.`card_location_arg`) koenige
                    FROM
                        `card`
                    WHERE
                        `card`.`card_type_arg` = '$KING'
                    GROUP BY
                        `card`.`card_location_arg`
                ) test2 ON test.`card_location_arg` = test2.`card_location_arg`
                WHERE
                    `neunen` >= 4 AND `koenige` >= 4"
        );
        $neunenFarb = 4 <= self::getUniqueValueFromDB(
            "SELECT
                    MAX(neunenCount)
                FROM
                    (
                    SELECT
                        `card`.`card_location_arg`,
                        COUNT(DISTINCT `card`.`card_type`) neunenCount
                    FROM
                        `card`
                    WHERE
                        `card`.`card_type_arg` = '$NINE'
                    GROUP BY
                        `card`.`card_location_arg`
                ) test"
        );

        $zehnen = 7 <= self::getUniqueValueFromDB(
            "SELECT
                    MAX(neunenCount)
                FROM
                    (
                    SELECT
                        `card`.`card_location_arg`,
                        COUNT(`card`.`card_location_arg`) neunenCount
                    FROM
                        `card`
                    WHERE
                        `card`.`card_type_arg` = '$TEN' OR `card`.`card_type_arg` = '$ACE'
                    GROUP BY
                        `card`.`card_location_arg`
                ) test"
        );
        //TODO ohne h10 / schweinchen
        //TODO Übernahme
        $diamondJack = self::getUniqueValueFromDB(
            "SELECT
                    `card`.`card_trump`
                FROM
                    `card`
                WHERE
                    `card`.`card_type` = $DIAMOND AND `card`.`card_type_arg` = $JACK
                LIMIT 1"
        );
        $goodTrump = 0 == self::getUniqueValueFromDB(
            "SELECT
                    MIN(`count`)
                FROM
                    (
                    SELECT
                    COUNT(`card_location_arg`) `count`
                    FROM
                    `player`
                    LEFT JOIN
                        (
                        SELECT
                            *
                        FROM
                            `card`
                        WHERE
                            `card`.`card_trump` >= '$diamondJack'
                        ) temp2 ON temp2.`card_location_arg` = `player`.`player_id`
                GROUP BY
                    `player`.`player_id`
                ) temp"
        );
        $fox = self::getUniqueValueFromDB(
            "SELECT
                     `card`.`card_trump`
                 FROM
                     `card`
                 WHERE
                     `card`.`card_type` = $DIAMOND AND `card`.`card_type_arg` = $ACE
                 LIMIT 1"
        );

        $trump = 3 >= self::getUniqueValueFromDB(
            "SELECT
                     MIN(`count`)
                 FROM
                     (
                     SELECT
                     COUNT(`card_location_arg`) `count`
                     FROM
                     `player`
                     LEFT JOIN
                         (
                         SELECT
                             *
                         FROM
                             `card`
                         WHERE
                             `card`.`card_trump` != '$fox' AND `card`.`card_trump` > '0'
                         ) temp2 ON temp2.`card_location_arg` = `player`.`player_id`
                 GROUP BY
                     `player`.`player_id`
                 ) temp"
        );

        // XXX Schmeißen
        if ($hochzeit || $neunen || $zehnen || $neunenFarb || $neunenKönige || $goodTrump || $trump) {
            return $this->gamestate->nextState('reshuffle');
        }

        // XXX Set RE
        self::DBQuery(
            "UPDATE `player` SET `player_re` = '0';"
        );
        self::DBQuery(
            "UPDATE `player` p
                JOIN
                    `card` c ON p.`player_id` = c.`card_location_arg` AND c.`card_type` = 4 AND c.`card_type_arg` = $QUEEN
                SET
                    p.`player_re` = '1';"
        );
        self::incGameStateValue('round', 1);
        $this->gamestate->nextState('start');
    }

    public function stNewTrick()
    {
        // New trick: active the player who wins the last trick, or the player who own the club-2 card
        // Reset trick suit to 0 ( = no suit )
        self::setGameStateValue('trickSuit', 0);
        $this->gamestate->nextState();
    }

    public function stNextPlayer()
    {
        $DIAMOND = DIAMOND;
        $HEART = HEART;
        $SPADE = SPADE;
        $CLUB = CLUB;
        $TRUMP = TRUMP;
        $NINE = NINE;
        $JACK = JACK;
        $QUEEN = QUEEN;
        $KING = KING;
        $TEN = TEN;
        $ACE = ACE;

        // Active next player OR end the trick and go to the next trick OR end the hand
        if ($this->cards->countCardInLocation('table') == 4) {
            // This is the end of the trick
            $cards_on_table = $this->cards->getCardsInLocation('table', null, 'card_last_changed');
            $best_card = null;
            $best_trump = 0;
            $foxes = array();
            foreach ($cards_on_table as $card) {
                if (self::fox($card)) {
                    array_push($foxes, $card);
                }

                self::error('card om table');
                // Note: type = card suit
                $trump = self::getTrump($card);
                self::dump('Card:', $card);
                if ($best_card == null) {
                    $best_card = $card;
                    $best_trump = $trump;
                } else {
                    self::error($best_trump);
                    if ($best_trump == 0) {
                        if ($trump == 0) {
                            if ($best_card['type'] == $card['type'] && $card['type_arg'] > $best_card['type_arg']) {
                                $best_card = $card;
                            }
                        } else {
                            $best_card = $card;
                            $best_trump = $trump;
                        }
                    } else {
                        if ($trump > $best_trump || self::h10($best_card, $card)) {
                            $best_card = $card;
                            $best_trump = $trump;
                        }
                    }
                }
            }
            $players = self::loadPlayersBasicInfos();
            // Active this player => he's the one who starts the next trick
            $this->gamestate->changeActivePlayer($best_card['location_arg']);

            // Notify
            // Note: we use 2 notifications here in order we can pause the display during the first notification
            //  before we move all cards to the winner ( during the second )

            self::notifyAllPlayers('trickWin', clienttranslate('${player_name} wins the trick'), array(
                'player_id' => $best_card['location_arg'],
                'player_name' => $players[$best_card['location_arg']]['player_name'],
            ));

            foreach ($foxes as $fox) {
                if ($fox['location_arg'] != $best_card['location_arg']) {
                    $card_id = $fox['id'];
                    $player_id = $best_card['location_arg'];
                    $victim_id = $fox['location_arg'];
                    self::notifyAllPlayers('giveSpecToPlayer', clienttranslate('${player_name} takes a fox from ${victim_name}'), array(
                        'player_name' => $players[$player_id]['player_name'],
                        'player_id' => $player_id,
                        'victim_name' => $players[$victim_id]['player_name'],
                        'victim_id' => $victim_id,
                        'card_id' => $card_id,
                        'suit' => $DIAMOND,
                        'value' => $ACE,
                    ));
                    self::DBQuery(
                        "INSERT INTO `fox`
                            VALUES ($card_id, $player_id, $victim_id);"
                    );
                }
            }
            //TODO switching FOX
            if (4 == self::getUniqueValueFromDB(
                "SELECT
                        COUNT(`card_id`)
                    FROM
                        `card`
                    WHERE
                        `card`.`card_location` = 'table' AND(
                            `card`.`card_type_arg` = '$ACE' OR `card`.`card_type_arg` = '$TEN'
                        );"
            )) {
                $player_id = $best_card['location_arg'];
                $card = self::getObjectFromDB(
                    "SELECT
                            `card`.`card_id` id,
                            `card`.`card_type` `type`,
                            `card`.`card_type_arg` `type_arg`
                        FROM
                            `card`
                        WHERE
                            `card`.`card_location` = 'table' AND NOT(
                                `card`.`card_type` = 1 AND `card`.`card_type_arg` = 14
                            )
                        LIMIT 1;"
                );
                $card_id = $card['id'];
                self::DBQuery(
                    "INSERT INTO `doppelkopf`
                        VALUES ($card_id, $player_id);"
                );
                self::notifyAllPlayers('giveSpecToPlayer', clienttranslate('${player_name} takes a Doppelkopf'), array(
                    'player_name' => $players[$player_id]['player_name'],
                    'player_id' => $player_id,
                    'card_id' => $card['id'],
                    'suit' => $card['type'],
                    'value' => $card['type_arg'],
                ));
            }

            // Move all cards to "won" of the given player
            $this->cards->moveAllCardsInLocation('table', 'won', null, $best_card['location_arg']);

            self::notifyAllPlayers('giveAllCardsToPlayer', '', array(
                'player_id' => $best_card['location_arg'],
            ));

            if ($this->cards->countCardInLocation('hand') == 0) {
                if ($best_card['type'] == $CLUB && $best_card['type_arg'] == $JACK) {
                    self::setGameStateValue('karlchen', $best_card['location_arg']);
                    self::notifyAllPlayers('giveSpecToPlayer', clienttranslate('${player_name} takes a Karlchen Müller'), array(
                        'player_name' => $players[$best_card['location_arg']]['player_name'],
                        'player_id' => $best_card['location_arg'],
                        'card_id' => $best_card['id'],
                        'suit' => $best_card['type'],
                        'value' => $best_card['type_arg'],
                    ));
                }
                // End of the hand
                $this->gamestate->nextState('endHand');
            } else {
                // End of the trick
                $this->gamestate->nextState('nextTrick');
            }
        } else {
            // Standard case ( not the end of the trick )
            // => just active the next player
            $player_id = self::activeNextPlayer();
            self::giveExtraTime($player_id);
            $this->gamestate->nextState('nextPlayer');
        }
    }

    public function stEndHand()
    {
        $DIAMOND = DIAMOND;
        $HEART = HEART;
        $SPADE = SPADE;
        $CLUB = CLUB;
        $TRUMP = TRUMP;
        $NINE = NINE;
        $JACK = JACK;
        $QUEEN = QUEEN;
        $KING = KING;
        $TEN = TEN;
        $ACE = ACE;

        // #region RE
        $re = array();
        $re['9'] = self::getUniqueValueFromDB(
            "SELECT
                    COUNT(`card_id`)
                FROM
                    `card`
                JOIN
                    `player` ON `card`.`card_location_arg` = `player`.`player_id`
                WHERE
                    `player`.`player_re` AND `card`.`card_type_arg` = '$NINE'"
        );
        $re['10'] = self::getUniqueValueFromDB(
            "SELECT
                    COUNT(`card_id`)
                FROM
                    `card`
                JOIN
                    `player` ON `card`.`card_location_arg` = `player`.`player_id`
                WHERE
                    `player`.`player_re` AND `card`.`card_type_arg` = '$TEN'"
        );
        $re['jacks'] = self::getUniqueValueFromDB(
            "SELECT
                    COUNT(`card_id`)
                FROM
                    `card`
                JOIN
                    `player` ON `card`.`card_location_arg` = `player`.`player_id`
                WHERE
                    `player`.`player_re` AND `card`.`card_type_arg` = '$JACK'"
        );
        $re['queens'] = self::getUniqueValueFromDB(
            "SELECT
                    COUNT(`card_id`)
                FROM
                    `card`
                JOIN
                    `player` ON `card`.`card_location_arg` = `player`.`player_id`
                WHERE
                    `player`.`player_re` AND `card`.`card_type_arg` = '$QUEEN'"
        );
        $re['kings'] = self::getUniqueValueFromDB(
            "SELECT
                    COUNT(`card_id`)
                FROM
                    `card`
                JOIN
                    `player` ON `card`.`card_location_arg` = `player`.`player_id`
                WHERE
                    `player`.`player_re` AND `card`.`card_type_arg` = '$KING'"
        );
        $re['aces'] = self::getUniqueValueFromDB(
            "SELECT
                    COUNT(`card_id`)
                FROM
                    `card`
                JOIN
                    `player` ON `card`.`card_location_arg` = `player`.`player_id`
                WHERE
                    `player`.`player_re` AND `card`.`card_type_arg` = '$ACE'"
        );
        // #region KONTRA
        $kontra = array();
        $kontra['9'] = self::getUniqueValueFromDB(
            "SELECT
                    COUNT(`card_id`)
                FROM
                    `card`
                JOIN
                    `player` ON `card`.`card_location_arg` = `player`.`player_id`
                WHERE
                    `player`.`player_re`=0 AND `card`.`card_type_arg` = '$NINE'"
        );
        $kontra['10'] = self::getUniqueValueFromDB(
            "SELECT
                    COUNT(`card_id`)
                FROM
                    `card`
                JOIN
                    `player` ON `card`.`card_location_arg` = `player`.`player_id`
                WHERE
                    `player`.`player_re`=0 AND `card`.`card_type_arg` = '$TEN'"
        );
        $kontra['jacks'] = self::getUniqueValueFromDB(
            "SELECT
                    COUNT(`card_id`)
                FROM
                    `card`
                JOIN
                    `player` ON `card`.`card_location_arg` = `player`.`player_id`
                WHERE
                    `player`.`player_re`=0 AND `card`.`card_type_arg` = '$JACK'"
        );
        $kontra['queens'] = self::getUniqueValueFromDB(
            "SELECT
                    COUNT(`card_id`)
                FROM
                    `card`
                JOIN
                    `player` ON `card`.`card_location_arg` = `player`.`player_id`
                WHERE
                    `player`.`player_re`=0 AND `card`.`card_type_arg` = '$QUEEN'"
        );
        $kontra['kings'] = self::getUniqueValueFromDB(
            "SELECT
                    COUNT(`card_id`)
                FROM
                    `card`
                JOIN
                    `player` ON `card`.`card_location_arg` = `player`.`player_id`
                WHERE
                    `player`.`player_re`=0 AND `card`.`card_type_arg` = '$KING'"
        );
        $kontra['aces'] = self::getUniqueValueFromDB(
            "SELECT
                    COUNT(`card_id`)
                FROM
                    `card`
                JOIN
                    `player` ON `card`.`card_location_arg` = `player`.`player_id`
                WHERE
                    `player`.`player_re`=0 AND `card`.`card_type_arg` = '$ACE'"
        );
        // #endregion RE
        $re_score = $re['jacks'] * 2 + $re['queens'] * 3 + $re['kings'] * 4 + $re['10'] * 10 + $re['aces'] * 11;
        $kontra_score = $kontra['jacks'] * 2 + $kontra['queens'] * 3 + $kontra['kings'] * 4 + $kontra['10'] * 10 + $kontra['aces'] * 11;
        $re_players = join(", ", self::getCollectionFromDB(
            "SELECT
                `player`.`player_id`,
                `player`.`player_name`
              FROM
                `player`
              WHERE
                `player`.`player_re`",
            true
        ));

        $kontra_players = join(", ", self::getCollectionFromDB(
            "SELECT
                `player`.`player_id`,
                `player`.`player_name`
              FROM
                `player`
              WHERE
                NOT `player`.`player_re`",
            true
        ));
        $re_fox_count = self::getUniqueValueFromDB(
            "SELECT
                    COUNT(`fox_card`)
                FROM
                    `fox` f
                JOIN
                    `player` c ON c.`player_id` = f.`fox_catcher`
                JOIN
                    `player` l ON l.`player_id` = f.`fox_looser`
                WHERE
                    c.`player_re` AND NOT l.`player_re`"
        );
        $re_dk_count = self::getUniqueValueFromDB(
            "SELECT
                    COUNT(`doppelkopf_card`)
                FROM
                    `doppelkopf`
                JOIN
                    `player` ON `doppelkopf`.`doppelkopf_owner`= `player`.`player_id`
                WHERE
                    `player`.`player_re`"
        );
        $kontra_fox_count = self::getUniqueValueFromDB(
            "SELECT
                    COUNT(`fox_card`)
                FROM
                    `fox` f
                JOIN
                    `player` c ON c.`player_id` = f.`fox_catcher`
                JOIN
                    `player` l ON l.`player_id` = f.`fox_looser`
                WHERE
                    l.`player_re` AND NOT c.`player_re`"
        );
        $kontra_dk_count = self::getUniqueValueFromDB(
            "SELECT
                    COUNT(`doppelkopf_card`)
                FROM
                    `doppelkopf`
                JOIN
                    `player` ON `doppelkopf`.`doppelkopf_owner`= `player`.`player_id`
                WHERE
                    NOT `player`.`player_re`"
        );
        $karlchen_id = self::getGameStateValue('karlchen');
        $re_karlchen = self::getUniqueValueFromDB(
            "SELECT
                    COUNT(`player_id`)
                FROM
                    `player`
                WHERE
                    `player`.`player_re` AND
                    `player`.`player_id` = $karlchen_id"
        );
        $kontra_karlchen = self::getUniqueValueFromDB(
            "SELECT
                    COUNT(`player_id`)
                FROM
                    `player`
                WHERE
                    NOT `player`.`player_re` AND
                    `player`.`player_id` = $karlchen_id"
        );

        self::notifyAllPlayers('sumCards', clienttranslate('RE (${re_players}) got ${nines} Nines, ${tens} Tens, ${jacks} Jacks, ${queens} Queens, ${kings} Kings and ${aces} Aces'), array(
            're_players' => $re_players,
            'nines' => $re[9],
            'tens' => $re[10],
            'jacks' => $re['jacks'],
            'queens' => $re['queens'],
            'kings' => $re['kings'],
            'aces' => $re['aces'],
        ));

        self::notifyAllPlayers('sumCards', clienttranslate('KONTRA (${kontra_players}) got ${nines} Nines, ${tens} Tens, ${jacks} Jacks, ${queens} Queens, ${kings} Kings and ${aces} Aces'), array(
            'kontra_players' => $kontra_players,
            'nines' => $kontra[9],
            'tens' => $kontra[10],
            'jacks' => $kontra['jacks'],
            'queens' => $kontra['queens'],
            'kings' => $kontra['kings'],
            'aces' => $kontra['aces'],
        ));

        $re_player_ids = self::getCollectionFromDB(
            "SELECT
                    `player`.`player_id`,
                    `player`.`player_no`
                    FROM
                    `player`
                    WHERE
                    `player`.`player_re`"
        );

        $kontra_player_ids = self::getCollectionFromDB(
            "SELECT
                    `player`.`player_id`,
                    `player`.`player_no`
                    FROM
                    `player`
                    WHERE
                    NOT `player`.`player_re`"
        );

        foreach ($re_player_ids as $key => $value) {
            self::dbIncScore($key, ($re_score > $kontra_score ? 4 - intdiv($kontra_score, 30) : 0) + $re_fox_count + $re_dk_count + $re_karlchen);
        }

        foreach ($kontra_player_ids as $key => $value) {
            self::dbIncScore($key, ($re_score <= $kontra_score ? 5 - intdiv($re_score, 30) : 0) + $kontra_fox_count + $kontra_dk_count + $kontra_karlchen);
        }

        if ($re_score > $kontra_score) {
            self::notifyAllPlayers('winner', clienttranslate('RE won with ${re_score} Augen, ${re_karlchen} Karlchen Müller, ${re_fox_count} Foxes and ${re_doppelkopf_count} Doppelköpfen vs
                ${kontra_score} Augen, ${kontra_karlchen} Karlchen Müller, ${kontra_fox_count} Foxes and ${kontra_doppelkopf_count} Doppelköpfen'), array(
                'kontra_score' => $kontra_score,
                're_score' => $re_score,
                'kontra_fox_count' => $kontra_fox_count,
                're_fox_count' => $re_fox_count,
                'kontra_doppelkopf_count' => $kontra_dk_count,
                're_doppelkopf_count' => $re_dk_count,
                'kontra_karlchen' => $kontra_karlchen,
                're_karlchen' => $re_karlchen,
                'score' => self::getCollectionFromDB(
                    "SELECT
                        `player_id`,
                        `player_score`
                      FROM
                        `player`",
                    true
                ),
            ));
        } else {
            self::notifyAllPlayers('winner', clienttranslate('KONTRA won with ${kontra_score} Augen, ${kontra_karlchen} Karlchen Müller, ${kontra_fox_count} Foxes and ${kontra_doppelkopf_count} Doppelköpfen, vs
                ${re_score} Augen, ${re_karlchen} Karlchen Müller, ${re_fox_count} Foxes and ${re_doppelkopf_count} Doppelköpfen'), array(
                'kontra_score' => $kontra_score,
                're_score' => $re_score,
                'kontra_fox_count' => $kontra_fox_count,
                're_fox_count' => $re_fox_count,
                'kontra_doppelkopf_count' => $kontra_dk_count,
                're_doppelkopf_count' => $re_dk_count,
                'kontra_karlchen' => $kontra_karlchen,
                're_karlchen' => $re_karlchen,
                'score' => self::getCollectionFromDB(
                    "SELECT
                        `player_id`,
                        `player_score`
                      FROM
                        `player`",
                    true
                ),
            ));
        }

        self::DBQuery(
            "DELETE FROM `fox`;" // NOI18N
        );

        self::DBQuery(
            "DELETE FROM `doppelkopf`;" // NOI18N
        );

        if (self::getGameStateValue('round') > 4) {
            $this->gamestate->nextState('endGame');
        } else {
            $this->gamestate->nextState('nextHand');
        }
    }
    //////////////////////////////////////////////////////////////////////////////
    ///////////// Zombie
    ////////////

    /*
    zombieTurn:

    This method is called each time it is the turn of a player who has quit the game ( = 'zombie' player ).
    You can do whatever you want in order to make sure the turn of this player ends appropriately
    ( ex: pass ).

    Important: your zombie code will be called when the player leaves the game. This action is triggered
    from the main site and propagated to the gameserver from a server, not from a browser.
    As a consequence, there is no current player associated to this action. In your zombieTurn function,
    you must _never_ use getCurrentPlayerId() or getCurrentPlayerName(), otherwise it will fail with a 'Not logged' error message.
     */

    public function zombieTurn($state, $active_player)
    {
        $statename = $state['name'];

        if ($state['type'] === 'activeplayer') {
            switch ($statename) {
                default:
                    $this->gamestate->nextState('zombiePass');
                    break;
            }

            return;
        }

        if ($state['type'] === 'multipleactiveplayer') {
            // Make sure player is in a non blocking status for role turn
            $this->gamestate->setPlayerNonMultiactive($active_player, '');

            return;
        }

        throw new feException('Zombie mode not supported at this game state: ' . $statename);
    }

    ///////////////////////////////////////////////////////////////////////////////////:
    ////////// DB upgrade
    //////////

    /*
    upgradeTableDb:

    You don't have to care about this until your game has been published on BGA.
    Once your game is on BGA, this method is called everytime the system detects a game running with your old
    Database scheme.
    In this case, if you change your Database scheme, you just have to apply the needed changes in order to
    update the game database and allow the game to continue to run with your new version.

     */

    public function upgradeTableDb($from_version)
    {
        // $from_version is the current version of this game database, in numerical form.
        // For example, if the game was running with a release of your game named '140430-1345',
        // $from_version is equal to 1404301345

        // Example:
        //        if ( $from_version <= 1404301345 )
        // {
        //            // ! important ! Use DBPREFIX_<table_name> for all tables
        //
        //            $sql = 'ALTER TABLE DBPREFIX_xxxxxxx ....';
        //            self::applyDbUpgradeToAllDB( $sql );
        //        }
        //        if ( $from_version <= 1405061421 )
        // {
        //            // ! important ! Use DBPREFIX_<table_name> for all tables
        //
        //            $sql = 'CREATE TABLE DBPREFIX_xxxxxxx ....';
        //            self::applyDbUpgradeToAllDB( $sql );
        //        }
        //        // Please add your future database scheme changes here
        //
        //

    }
}
