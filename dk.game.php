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

    // XXX global vars
    $this->initGameStateLabels(array(
      VAR_ROUND => 10,
      VAR_TRICK_SUIT => 11,
      VAR_CHARLIE => 12,
      VAR_WEDDING => 13,
      VAR_RES2 => 14,
      VAR_CHOSEN => 15,
      VAR_SOLO => 16,
      VAR_SOLOS => 17,
      OPT_AUTO_THROW => OPT_AUTO_THROW_ID,
      OPT_SOLO => OPT_SOLO_ID,
      OPT_ROUNDS => OPT_ROUNDS_ID
    ));

    $this->cards = $this->getNew('module.common.deck');
    $this->cards->init('card');
  }

  protected function getGameName()
  {
    // Used for translations and stuff. Please do not modify.
    return 'dk';
  }

  protected function setupNewGame($players, $options = array())
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

    // Set the colors of the players with HTML color code
    // The default below is red/green/blue/orange/brown
    // The number of colors defined here must correspond to the maximum number of players allowed for the gams
    $gameinfos = $this->getGameinfos();
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
    $this->DBQuery($sql);
    $this->reattributeColorsBasedOnPreferences($players, $gameinfos['player_colors']);
    $this->reloadPlayersBasicInfos();

    /************ Start the game initialization *****/

    // Init global values with their initial values
    //$this->setGameStateInitialValue( 'my_first_global_variable', 0 );

    $this->setGameStateInitialValue(VAR_ROUND, 0);
    $this->setGameStateInitialValue(VAR_TRICK_SUIT, -1);
    $this->setGameStateInitialValue(VAR_CHARLIE, 0);
    $this->setGameStateInitialValue(VAR_WEDDING, 0);
    $this->setGameStateInitialValue(VAR_RES2, 0);
    $this->setGameStateInitialValue(VAR_CHOSEN, 0);
    $this->setGameStateInitialValue(VAR_SOLO, 0);
    $this->setGameStateInitialValue(VAR_SOLOS, 0);

    // Create cards
    $cards = array();

    for ($suit = $DIAMOND; $suit <= $CLUB; $suit++) {
      // spade, heart, diamond, club
      for ($value = $NINE; $value <= $ACE; $value++) {
        //  2, 3, 4, ... K, A
        $cards[] = array('type' => $suit, 'type_arg' => $value, 'nbr' => 1);
        $cards[] = array('type' => $suit, 'type_arg' => $value, 'nbr' => 1);
      }
    }

    $this->cards->createCards($cards, 'deck');

    // Init game statistics
    // ( note: statistics used in this file must be defined in your stats.inc.php file )
    //$this->initStat( 'table', 'table_teststat1', 0 );
    // Init a table statistics
    //$this->initStat( 'player', 'player_teststat1', 0 );
    // Init a player statistics ( for all players )

    // Activate first player ( which is in general a good idea : ) )
    $this->activeNextPlayer();

    /************ End of the game initialization *****/
  }

  protected function getAllDatas()
  {
    $this->getCurrentPlayerColor();
    $result = array('players' => array());

    if ($this->getGameStateValue(OPT_SOLO) != OFF)
      $result["solo_allowed"] = true;

    $current_player_id = $this->getCurrentPlayerId();
    // !! We must only return informations visible by this player !!

    // Get information about players
    // Note: you can retrieve some extra field you added for 'player' table in 'dbmodel.sql' if you need it.
    $sql = 'SELECT player_id id, player_score score FROM player ';
    $result['players'] = $this->getCollectionFromDb($sql);

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
    $result['foxes'] = $this->getCollectionFromDb($sql);
    $sql = 'SELECT
          `doppelkopf_card` `card`,
          `card_type` `suit`,
          `card_type_arg` `value`,
          `doppelkopf_owner` `owner`
        FROM
          `doppelkopf`
        JOIN
          `card` ON `doppelkopf`.`doppelkopf_card` = `card`.`card_id`;';
    $result['doppelköpfe'] = $this->getCollectionFromDb($sql);

    // Cards in player hand
    // $result['hand'] = $this->cards->getCardsInLocation( 'hand', $current_player_id );

    $result['cardSorting'] = $this->getCurrentCardSorting();

    $result['hand'] = $this->getHand($current_player_id);

    // Cards played on the table
    $result['table'] = $this->cards->getCardsInLocation('table');

    if ($this->getGameStateValue(VAR_WEDDING)) {
      $result["wedding"] = $this->getGameStateValue(VAR_WEDDING);
    }

    if ($this->getGameStateValue(VAR_RES2) == $current_player_id) {
      $result["res2"] = true;
    }

    $result['throw'] = $this->getUniqueValueFromDB(
      "SELECT `player_reservation`
      FROM `player`
      WHERE `player_id` = '$current_player_id'"
    );

    return $result;
  }


  public function getGameProgression()
  {
    return ($this->getUniqueValueFromDB(
      "SELECT
        AVG(cardsinhand)
      FROM
        (
        SELECT
          `card`.`card_location_arg`,
          COUNT(`card`.`card_location_arg`) cardsinhand
        FROM
          `card`
        WHERE
          `card`.`card_location` = 'hand'
        GROUP BY
          `card`.`card_location_arg`
      ) test;"
    ) + 12 * $this->getGameStateValue(VAR_ROUND) * 12) * 100 / (4 * 12);
  }

  //////////////////////////////////////////////////////////////////////////////
  //////////// XXX Utility functions
  ////////////
  #region Util

  public function canThrow($player_id)
  {
    return false;
  }

  public function getSoloPlayer()
  {
    return $this->getUniqueValueFromDB(
      "SELECT
        `player_id`
      FROM
        `player`
      WHERE
        `player_solo` = 1"
    );
  }

  public function getStartPlayer($offset = 0)
  {
    $soloplayer = $this->getSoloPlayer();
    if ($soloplayer)
      return $soloplayer;

    $player_idx = ($this->getGameStateValue(VAR_ROUND) - $this->getGameStateValue(VAR_SOLOS) + $this->getPlayersNumber() + $offset) % $this->getPlayersNumber() + 1;
    $this->error("Set Start player to: $player_idx");
    return $this->getUniqueValueFromDB(
      "SELECT
          `player_id`
        FROM
          `player`
        WHERE
          `player_no` = '$player_idx'"
    );
  }

  public function getCurrentCardSorting()
  {
    $sql = $this->getTrumpSH();

    return $this->getObjectListFromDB(
      "SELECT `card`.`card_type` `suit`, `card`.`card_type_arg` `value`, `card`.`card_trump$sql` `trump`
       FROM `card`"
    );
  }

  public function  getHand($player_id)
  {
    return $this->getObjectListFromDB(
      "SELECT `card`.`card_id` `id`, `card`.`card_type` `suit`, `card`.`card_type_arg` `value`
      FROM `card`
      WHERE `card`.`card_location` = 'hand' AND `card`.`card_location_arg` = '$player_id'"
    );
  }

  public function loadPlayersBasicInfos()
  {
    return $this->getCollectionFromDB(
      "SELECT `player_id`, `player_name`, `player_no`, `player_re`, `player_color`, `player_canal`
      FROM `player`"
    );
  }

  // get score
  public function dbGetScore($player_id)
  {
    return $this->getUniqueValueFromDB("SELECT player_score FROM player WHERE player_id='$player_id'");
  }

  // set score
  public function dbSetScore($player_id, $count)
  {
    $this->DBQuery("UPDATE player SET player_score='$count' WHERE player_id='$player_id'");
  }

  // set aux score (tie breaker)
  public function dbSetAuxScore($player_id, $score)
  {
    $this->DBQuery("UPDATE player SET player_score_aux=$score WHERE player_id='$player_id'");
  }

  public function dbIncScore($player_id, $inc)
  {
    $count = $this->dbGetScore($player_id);
    if ($inc != 0) {
      $count += $inc;
      $this->dbSetScore($player_id, $count);
    }
    return $count;
  }

  public function getTrump($card)
  {
    $sql = $this->getTrumpSH();
    return $this->getUniqueValueFromDB("SELECT `card_trump$sql` FROM `card` WHERE `card`.`card_id` = '{$card['id']}'");
  }

  public function h10($best_card, $card)
  {
    return $card['type'] == HEART && $card['type_arg'] == TEN && $best_card['type'] == HEART && $best_card['type_arg'] == TEN;
  }

  public function getTrumpSH()
  {
    switch ($this->getGameStateValue(VAR_SOLO)) {
      case SOLODIAMOND:
        return "D";
      case SOLOHEART:
        return "H";
      case SOLOSPADE:
        return "S";
      case SOLOCLUB:
        return "C";
      case SOLOQUEEN:
        return "Q";
      case SOLOJACK:
        return "J";
      case SOLOACE:
        return "A";
      default:
        return "N";
    }
  }

  public function hasSuit($player, $suit)
  {
    $sql = $this->getTrumpSH();
    if ($suit != TRUMP) {
      return 0 < $this->getUniqueValueFromDB(
        "SELECT
          COUNT(`card`.`card_id`)
        FROM
          `card` WHERE `card`.`card_location_arg` = '$player' AND `card`.`card_location` = 'hand' AND `card`.`card_trump$sql` = '0' AND `card`.`card_type` = '$suit';"
      );
    } else {
      return 0 < $this->getUniqueValueFromDB(
        "SELECT COUNT(`card`.`card_id`) FROM `card` WHERE
          `card`.`card_location_arg` = '$player' AND `card`.`card_location` = 'hand' AND `card`.`card_trump$sql` > '0';"
      );
    }
  }

  public function fox($card)
  {
    return $card['type'] == DIAMOND && $card['type_arg'] == ACE;
  }




  public function beatsNormal($firstCard, $secondCard)
  {
    return $firstCard["type"] == $secondCard["type"] && $secondCard["type_arg"] > $firstCard["type_arg"];
  }


  public function beats($firstCard, $secondCard)
  {
    //TODO heart 10
    if ($this->getTrump($secondCard) > $this->getTrump($firstCard)) {
      return true;
    }
    if ($this->beatsNormal($firstCard, $secondCard)) {
      return $this->getTrump($firstCard) == 0;
    }
  }
  #endregion

  #region SpecialCardConfs
  #endregion
  //////////////////////////////////////////////////////////////////////////////
  //////////// Player actions
  ////////////

  /*
  Each time a player is doing some game action, one of the methods below is called.
  ( note: each method below must match an input method in dk.action.php )
   */

  public function playCard($card_id)
  {
    $this->checkAction('playCard');
    $player_id = $this->getActivePlayerId();
    $currentCard = $this->cards->getCard($card_id);
    if ($this->getGameStateValue(VAR_TRICK_SUIT) == -1) {
      $trick_suit = $this->getTrump($currentCard) == 0 ? $currentCard['type'] : TRUMP;
      $this->setGameStateValue(VAR_TRICK_SUIT, $trick_suit);
    } else {
      $trick_suit = $this->getGameStateValue(VAR_TRICK_SUIT);
    }

    if (
      $trick_suit == $currentCard['type'] && $this->getTrump($currentCard) == 0 ||
      $trick_suit == TRUMP && $this->getTrump($currentCard) > 0 ||
      !$this->hasSuit($player_id, $trick_suit)
    ) {

      $this->cards->moveCard($card_id, 'table', $player_id);
      // TODO  rule variations
      $this->notifyAllPlayers('playCard', clienttranslate('${player_name} plays ${value_displayed} of ${suit_displayed}s'), array(
        'i18n' => array('suit_displayed', 'value_displayed'), 'card_id' => $card_id, 'player_id' => $player_id,
        'player_name' => $this->getActivePlayerName(), 'value' => $currentCard['type_arg'],
        'value_displayed' => $this->values_label[$currentCard['type_arg']]['name'], 'suit' => $currentCard['type'],
        'suit_displayed' => $this->suits[$currentCard['type']]['name'],
      ));
      // Next player
      $this->gamestate->nextState();
    } else {
      throw new BgaUserException(sprintf(
        _('You cannot play %s of %ss when %s is played.'),
        $this->values_label[$currentCard['type_arg']]['nametr'],
        $this->suits[$currentCard['type']]['nametr'],
        $this->suits[$trick_suit]['nametr']
      ));
    }
  }

  public function gesund()
  {
    $this->checkAction('gesund');
    $player_id = $this->getActivePlayerId();

    $this->notifyAllPlayers('gesund', '${player_name} is GESUND.', array(
      'player_name' => $this->getActivePlayerName(),
      'player_id' => $player_id
    ));

    $this->gamestate->nextState();
  }

  public function reservation()
  {
    $this->checkAction('reservation');
    $player_id = $this->getActivePlayerId();

    $this->DbQuery(
      "UPDATE `player`
      SET `player_reservation` = 1
      WHERE `player_id` = $player_id"
    );

    $this->notifyAllPlayers('reservation', '${player_name} has VORBEHALT.', array(
      'player_name' => $this->getActivePlayerName(),
      'player_id' => $player_id
    ));

    $this->gamestate->nextState();
  }

  public function no()
  {
    $this->checkAction('no');
    $this->gamestate->nextState(('nextPlayer'));
  }

  public function mandatorySolo($solo)
  {
    $this->checkAction('mandatorySolo');
    $player_id = $this->getActivePlayerId();

    // Unset re
    $this->DBQuery(
      "UPDATE `player` SET `player_re` = '0';"
    );
    $this->DbQuery(
      "UPDATE `player`
      SET `player_solo` = 1,
        `player_re` = 1
      WHERE `player_id` = $player_id"
    );

    $this->setGameStateValue(VAR_SOLO, $solo);

    $this->notifyAllPlayers(VAR_SOLO, '${player_name} plays a mandatory ${solo_name}.', array(
      'player_name' => $this->getActivePlayerName(),
      'player_id' => $player_id,
      'solo_name' =>  $this->suits[$solo]['nametr'],
      'cardSorting' => $this->getCurrentCardSorting()
    ));

    $this->DbQuery("UPDATE `player` SET `player_reservation` = 0");
    $this->setGameStateValue(VAR_CHOSEN, 0);
    $this->incGameStateValue(VAR_SOLOS, 1);

    $this->gamestate->nextState();
  }

  public function playerSolo($solo)
  {
    $this->checkAction('playerSolo');
    $player_id = $this->getActivePlayerId();
    // Unset re
    $this->DBQuery(
      "UPDATE `player` SET `player_re` = '0';"
    );
    $this->DbQuery(
      "UPDATE `player`
      SET `player_re` = 1
      WHERE `player_id` = $player_id"
    );

    $this->setGameStateValue(VAR_SOLO, $solo);

    $this->notifyAllPlayers("solo", '${player_name} plays a voluntary ${solo_name}.', array(
      'player_name' => $this->getActivePlayerName(),
      'player_id' => $player_id,
      'solo_name' =>  $this->solos[$solo]['nametr'],
      'cardSorting' => $this->getCurrentCardSorting()
    ));

    $this->DbQuery("UPDATE `player` SET `player_reservation` = 0");
    $this->setGameStateValue(VAR_CHOSEN, 0);

    $this->gamestate->nextState();
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
    // XXX reset Vars:
    $this->setGameStateValue(VAR_SOLO, 0);
    $this->setGameStateValue(VAR_CHARLIE, 0);

    // Take back all cards ( from any location => null ) to deck
    $this->cards->moveAllCardsInLocation(null, 'deck');
    $this->cards->shuffle('deck');
    $players = $this->loadPlayersBasicInfos();
    foreach ($players as $player_id => $player) {
      $this->cards->pickCards(12, 'deck', $player_id);

      // Notify player about his cards
      $this->notifyPlayer($player_id, 'newHand', '', array(
        'hand' => $this->getHand($player_id),
        'cardSorting' => $this->getCurrentCardSorting()
      ));
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

    //XXX RESET ALL PLAYERS
    $this->DbQuery(
      "UPDATE `player`
      SET `player_reservation` = 0,
      `player_re` = 0,
      `player_reservation` = 0,
      `player_throw` = 0,
      `player_poverty` = 0,
      `player_solo` = 0"
    );

    $this->gamestate->changeActivePlayer($this->getStartPlayer());

    // TODO wedding
    $wedding = 1 == $this->getUniqueValueFromDB(
      "SELECT
          COUNT(DISTINCT `card_location_arg`)
        FROM
          `card`
        WHERE
          `card_type` = $CLUB AND `card_type_arg` = '$QUEEN';"
    );

    $neunen = 5 <= $this->getUniqueValueFromDB(
      "SELECT
          MAX(nines)
        FROM
          (
          SELECT
            `card`.`card_location_arg`,
            COUNT(`card`.`card_location_arg`) nines
          FROM
            `card`
          WHERE
            `card`.`card_type_arg` = '$NINE'
          GROUP BY
            `card`.`card_location_arg`
        ) test"
    );

    $neunenKönige = 1 <= $this->getUniqueValueFromDB(
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
            COUNT(`card`.`card_location_arg`) kings
          FROM
            `card`
          WHERE
            `card`.`card_type_arg` = '$KING'
          GROUP BY
            `card`.`card_location_arg`
        ) test2 ON test.`card_location_arg` = test2.`card_location_arg`
        WHERE
          `neunen` >= 4 AND `kings` >= 4"
    );
    $neunenFarb = 4 <= $this->getUniqueValueFromDB(
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

    $tens = 7 <= $this->getUniqueValueFromDB(
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

    $diamondJack = $this->getUniqueValueFromDB(
      "SELECT
          `card`.`card_trumpN`
        FROM
          `card`
        WHERE
          `card`.`card_type` = $DIAMOND AND `card`.`card_type_arg` = $JACK
        LIMIT 1"
    );
    $goodTrump = 0 == $this->getUniqueValueFromDB(
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
              `card`.`card_trumpN` >= '$diamondJack'
            ) temp2 ON temp2.`card_location_arg` = `player`.`player_id`
        GROUP BY
          `player`.`player_id`
        ) temp"
    );
    $fox = $this->getUniqueValueFromDB(
      "SELECT
           `card`.`card_trumpN`
         FROM
           `card`
         WHERE
           `card`.`card_type` = $DIAMOND AND `card`.`card_type_arg` = $ACE
         LIMIT 1"
    );

    $trump = 3 >= $this->getUniqueValueFromDB(
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
               `card`.`card_trumpN` != '$fox' AND `card`.`card_trumpN` > '0'
             ) temp2 ON temp2.`card_location_arg` = `player`.`player_id`
         GROUP BY
           `player`.`player_id`
         ) temp"
    );

    // TODO AutoThrow

    if ($this->getGameStateValue(OPT_AUTO_THROW) && ($neunen || $tens || $neunenFarb || $neunenKönige || $goodTrump || $trump)) {
      return $this->gamestate->nextState('reshuffle');
    }

    // XXX Set RE
    $this->DBQuery(
      "UPDATE `player` SET `player_re` = '0';"
    );
    $this->DBQuery(
      "UPDATE `player` p
        JOIN
  `card` c ON p.`player_id` = c.`card_location_arg` 
     AND c.`card_type` = $CLUB AND c.`card_type_arg` = $QUEEN
        SET
          p.`player_re` = '1';"
    );
    $res = $this->getObjectListFromDB(
      "SELECT
        `player_id`
      FROM
        `player`
      WHERE
        `player`.`player_re` = 1;",
      true
    );
    if (count($res) == 1) {
      $this->notifyPlayer($res[0], "res2", "", array());
      $this->setGameStateValue(VAR_RES2, $res[0]);
    }
    // XXX FORCE WEDDING
    // else {
    //   return $this->gamestate->nextState("reshuffle");
    // }

    // TODO Mandtory Solo (vorgeführt)
    // if ($this->getGameStateValue(OPT_ROUNDS) - $this->getGameStateValue(VAR_ROUND) <= $this->getUniqueValueFromDB(
    //   "SELECT COUNT(`player_id`)
    //   FROM `player`
    //   WHERE `player_solo` = 2"
    // )) {
    //   $player_id = $this->getStartPlayer();
    //   while ($this->getGameStateValue(OPT_SOLO) == ON) {
    //     if ($this->getUniqueValueFromDB("SELECT `player_solo` FROM `player` WHERE `player_id` = $player_id") != 2) {
    //       $player_id = $this->activeNextPlayer();
    //     } else {
    //       $this->gamestate->nextState('startGame');
    //       return;
    //     }
    //   }
    // }
    $this->setGameStateValue(VAR_CHOSEN, 4);
    $this->gamestate->changeActivePlayer($this->getStartPlayer(-1));
    $this->gamestate->nextState('playerChoose');
  }

  public function stNextReservation()
  {
    if ($this->getGameStateValue(VAR_CHOSEN) == 0) {
      // End of the trick

      $this->setGameStateValue(
        VAR_CHOSEN,
        4
      );
      $this->gamestate->changeActivePlayer($this->getStartPlayer(-1));
      $this->gamestate->nextState('mandatorySolo');
    } else {
      // Standard case ( not the end of the trick )
      // => just active the next player
      $this->incGameStateValue(VAR_CHOSEN, -1);
      $player_id = $this->activeNextPlayer();
      $this->giveExtraTime($player_id);
      $this->gamestate->nextState('nextPlayer');
    }
  }

  public function stNextMandatory()
  {
    if ($this->getGameStateValue(VAR_CHOSEN) == 0 || $this->getGameStateValue(OPT_SOLO) == OFF || $this->getGameStateValue(OPT_SOLO) == SOLO_ONLY_VOL) {
      // End of the trick
      $this->setGameStateValue(
        VAR_CHOSEN,
        4
      );
      $this->gamestate->changeActivePlayer($this->getStartPlayer(-1));
      $this->gamestate->nextState('normalSolo');
    } else {
      // Standard case ( not the end of the trick )
      // => just active the next player

      $this->incGameStateValue(VAR_CHOSEN, -1);
      $player_id = $this->activeNextPlayer();

      if ($this->getUniqueValueFromDB(
        "SELECT `player_reservation`
        FROM `player`
        WHERE `player_id` = $player_id"
      ) == 0)
        return $this->stNextMandatory();

      $this->giveExtraTime($player_id);
      $this->gamestate->nextState('nextPlayer');
    }
  }

  public function stNextSolo()
  {

    if ($this->getGameStateValue(VAR_CHOSEN) == 0 || $this->getGameStateValue(OPT_SOLO) == OFF) {
      // End of the trick
      $this->setGameStateValue(
        VAR_CHOSEN,
        4
      );
      $this->gamestate->changeActivePlayer($this->getStartPlayer(-1));
      $this->gamestate->nextState('throwing');
    } else {
      // Standard case ( not the end of the trick )
      // => just active the next player
      $this->incGameStateValue(VAR_CHOSEN, -1);

      $player_id = $this->activeNextPlayer();

      if ($this->getUniqueValueFromDB(
        "SELECT `player_reservation`
        FROM `player`
        WHERE `player_id` = $player_id"
      ) == 0)
        return $this->stNextSolo();
      $this->giveExtraTime($player_id);
      $this->gamestate->nextState('nextPlayer');
    }
  }
  public function stNextThrowing()
  {

    if ($this->getGameStateValue(VAR_CHOSEN) == 0) {
      // End of the trick
      $this->setGameStateValue(
        VAR_CHOSEN,
        4
      );
      $this->gamestate->changeActivePlayer($this->getStartPlayer(-1));
      $this->gamestate->nextState('poverty');
    } else {
      $this->incGameStateValue(VAR_CHOSEN, -1);
      // Standard case ( not the end of the trick )
      // => just active the next player
      $player_id = $this->activeNextPlayer();
      $this->giveExtraTime($player_id);
      $this->gamestate->nextState('nextPlayer');
    }
  }
  public function stNextPoverty()
  {

    if ($this->getGameStateValue(VAR_CHOSEN) == 0) {
      // End of the trick
      //'nextPlayer' => 29,'startGame' => 40, 'reshuffle' => 10, 'takeOver' => 31
      $this->setGameStateValue(
        VAR_CHOSEN,
        $this->getUniqueValueFromDB(
          "SELECT COUNT(`player_id`)
            FROM `player`
            WHERE `player_reservation` = 1"
        )
      );
      if ($this->getGameStateValue(VAR_CHOSEN) == 0) {
        $this->gamestate->changeActivePlayer($this->getStartPlayer());
        $this->gamestate->nextState('startGame');
      } else if ($this->getGameStateValue(VAR_CHOSEN) == 1) {
        $this->gamestate->changeActivePlayer($this->getStartPlayer(-1));
        $this->gamestate->nextState('takeOver');
      } else
        $this->gamestate->nextState('reshuffle');
    } else {
      $this->incGameStateValue(VAR_CHOSEN, -1);
      // Standard case ( not the end of the trick )
      // => just active the next player
      $player_id = $this->activeNextPlayer();
      $this->giveExtraTime($player_id);
      $this->gamestate->nextState('nextPlayer');
    }
  }
  public function stNewTrick()
  {
    // New trick: activate the player who won the last trick or the starting player
    // Reset trick suit to 0 ( = no suit )
    if ($this->cards->countCardInLocation('hand') == 48) {
      $this->error($this->getGameStateValue(VAR_RES2));
      $wed_id = $this->getObjectFromDB("SELECT 
      `player`.`player_id`,
      `player`.`player_name` FROM `player` WHERE `player_reservation` = 1 AND `player_id` = {$this->getGameStateValue(VAR_RES2)}");
      if ($wed_id) {
        $this->setGameStateValue(VAR_WEDDING, $wed_id['player_id']);
        $this->notifyAllPlayers("wedding", clienttranslate('Wedding by ${player_name}.'), array(
          'player_id' => $wed_id['player_id'],
          'player_name' => $wed_id['player_name'],
        ));
      }
      $player_id = $this->getStartPlayer();
      $this->gamestate->changeActivePlayer($player_id);
      $this->giveExtraTime($player_id);
    }

    $this->setGameStateValue(VAR_TRICK_SUIT, -1);
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
      $foxes = array();
      foreach ($cards_on_table as $card) {
        if ($this->fox($card)) {
          array_push($foxes, $card);
        }
        if ($best_card == null) {
          $best_card = $card;
        } else {
          if ($this->beats($best_card, $card)) {
            $best_card = $card;
          }
        }
      }
      $players = $this->loadPlayersBasicInfos();

      // Activate this player => he's the one who starts the next trick
      $this->gamestate->changeActivePlayer($best_card['location_arg']);

      // Notify
      // Note: we use 2 notifications here in order we can pause the display during the first notification
      //  before we move all cards to the winner ( during the second )

      $this->notifyAllPlayers('trickWin', clienttranslate('${player_name} wins the trick'), array(
        'player_id' => $best_card['location_arg'],
        'player_name' => $players[$best_card['location_arg']]['player_name'],
      ));

      // XXX Doppelkopf und Fox
      if ($this->getGameStateValue(VAR_SOLO) == 0)
        foreach ($foxes as $fox) {
          if ($fox['location_arg'] != $best_card['location_arg']) {
            $card_id = $fox['id'];
            $player_id = $best_card['location_arg'];
            $victim_id = $fox['location_arg'];
            $this->notifyAllPlayers('giveSpecToPlayer', clienttranslate('${player_name} takes a fox from ${victim_name}'), array(
              'player_name' => $players[$player_id]['player_name'],
              'player_id' => $player_id,
              'victim_name' => $players[$victim_id]['player_name'],
              'victim_id' => $victim_id,
              'card_id' => $card_id,
              'suit' => $DIAMOND,
              'value' => $ACE,
            ));
            $this->DBQuery(
              "INSERT INTO `fox`
              VALUES ($card_id, $player_id, $victim_id);"
            );
          }
        }

      if (($this->getGameStateValue(VAR_SOLO) == 0) && 4 == $this->getUniqueValueFromDB(
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
        $card = $this->getObjectFromDB(
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
        $this->DBQuery(
          "INSERT INTO `doppelkopf`
            VALUES ($card_id, $player_id);"
        );
        $this->notifyAllPlayers('giveSpecToPlayer', clienttranslate('${player_name} takes a Doppelkopf'), array(
          'player_name' => $players[$player_id]['player_name'],
          'player_id' => $player_id,
          'card_id' => $card['id'],
          'suit' => $card['type'],
          'value' => $card['type_arg'],
        ));
      }

      // Move all cards to "won" of the given player
      $this->cards->moveAllCardsInLocation('table', 'won', null, $best_card['location_arg']);

      $this->notifyAllPlayers('giveAllCardsToPlayer', '', array(
        'player_id' => $best_card['location_arg'],
      ));

      if ($this->getGameStateValue(VAR_WEDDING) != 0) {
        if ($this->cards->countCardInLocation('won') <= 4 * 3 && $players[$best_card['location_arg']]['player_re'] == 0) {
          $this->DbQuery(
            "UPDATE `player` SET `player_re` = '1' WHERE `player_id` = " . $best_card['location_arg']
          );
          $this->setGameStateValue(VAR_WEDDING, 0);
          $this->notifyAllPlayers('weddingComplete', clienttranslate('${player_name} married and is now RE as well.'), array(
            'player_name' => $players[$best_card['location_arg']]['player_name']
          ));
        } else if ($this->cards->countCardInLocation('won') >= 4 * 3) {
          $this->setGameStateValue(VAR_WEDDING, 0);
          $this->notifyAllPlayers('weddingComplete', clienttranslate('${player_name} was unable to find a partner. Playing Solo now.'), array(
            'player_name' => $this->getUniqueValueFromDB(
              "SELECT `player_name`
                FROM `player`
                WHERE `player_re` = 1"
            )
          ));
        }
      }

      if ($this->cards->countCardInLocation('hand') == 0) {
        if (($this->getGameStateValue(VAR_SOLO) == 0) && $best_card['type'] == $CLUB && $best_card['type_arg'] == $JACK) {
          $this->setGameStateValue(VAR_CHARLIE, $best_card['location_arg']);
          $this->notifyAllPlayers('giveSpecToPlayer', clienttranslate('${player_name} takes a Charlie Miller'), array(
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
      $player_id = $this->activeNextPlayer();
      $this->giveExtraTime($player_id);
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
    $re['9'] = $this->getUniqueValueFromDB(
      "SELECT
          COUNT(`card_id`)
        FROM
          `card`
        JOIN
          `player` ON `card`.`card_location_arg` = `player`.`player_id`
        WHERE
          `player`.`player_re` AND `card`.`card_type_arg` = '$NINE'"
    );
    $re['10'] = $this->getUniqueValueFromDB(
      "SELECT
          COUNT(`card_id`)
        FROM
          `card`
        JOIN
          `player` ON `card`.`card_location_arg` = `player`.`player_id`
        WHERE
          `player`.`player_re` AND `card`.`card_type_arg` = '$TEN'"
    );
    $re['jacks'] = $this->getUniqueValueFromDB(
      "SELECT
          COUNT(`card_id`)
        FROM
          `card`
        JOIN
          `player` ON `card`.`card_location_arg` = `player`.`player_id`
        WHERE
          `player`.`player_re` AND `card`.`card_type_arg` = '$JACK'"
    );
    $re['queens'] = $this->getUniqueValueFromDB(
      "SELECT
          COUNT(`card_id`)
        FROM
          `card`
        JOIN
          `player` ON `card`.`card_location_arg` = `player`.`player_id`
        WHERE
          `player`.`player_re` AND `card`.`card_type_arg` = '$QUEEN'"
    );
    $re['kings'] = $this->getUniqueValueFromDB(
      "SELECT
          COUNT(`card_id`)
        FROM
          `card`
        JOIN
          `player` ON `card`.`card_location_arg` = `player`.`player_id`
        WHERE
          `player`.`player_re` AND `card`.`card_type_arg` = '$KING'"
    );
    $re['aces'] = $this->getUniqueValueFromDB(
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
    $kontra['9'] = $this->getUniqueValueFromDB(
      "SELECT
          COUNT(`card_id`)
        FROM
          `card`
        JOIN
          `player` ON `card`.`card_location_arg` = `player`.`player_id`
        WHERE
          `player`.`player_re`=0 AND `card`.`card_type_arg` = '$NINE'"
    );
    $kontra['10'] = $this->getUniqueValueFromDB(
      "SELECT
          COUNT(`card_id`)
        FROM
          `card`
        JOIN
          `player` ON `card`.`card_location_arg` = `player`.`player_id`
        WHERE
          `player`.`player_re`=0 AND `card`.`card_type_arg` = '$TEN'"
    );
    $kontra['jacks'] = $this->getUniqueValueFromDB(
      "SELECT
          COUNT(`card_id`)
        FROM
          `card`
        JOIN
          `player` ON `card`.`card_location_arg` = `player`.`player_id`
        WHERE
          `player`.`player_re`=0 AND `card`.`card_type_arg` = '$JACK'"
    );
    $kontra['queens'] = $this->getUniqueValueFromDB(
      "SELECT
          COUNT(`card_id`)
        FROM
          `card`
        JOIN
          `player` ON `card`.`card_location_arg` = `player`.`player_id`
        WHERE
          `player`.`player_re`=0 AND `card`.`card_type_arg` = '$QUEEN'"
    );
    $kontra['kings'] = $this->getUniqueValueFromDB(
      "SELECT
          COUNT(`card_id`)
        FROM
          `card`
        JOIN
          `player` ON `card`.`card_location_arg` = `player`.`player_id`
        WHERE
          `player`.`player_re`=0 AND `card`.`card_type_arg` = '$KING'"
    );
    $kontra['aces'] = $this->getUniqueValueFromDB(
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
    $re_players = join(", ", $this->getCollectionFromDB(
      "SELECT
        `player`.`player_id`,
        `player`.`player_name`
        FROM
        `player`
        WHERE
        `player`.`player_re`",
      true
    ));

    $kontra_players = join(", ", $this->getCollectionFromDB(
      "SELECT
        `player`.`player_id`,
        `player`.`player_name`
        FROM
        `player`
        WHERE
        NOT `player`.`player_re`",
      true
    ));
    $re_fox_count = $this->getUniqueValueFromDB(
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
    $re_dk_count = $this->getUniqueValueFromDB(
      "SELECT
          COUNT(`doppelkopf_card`)
        FROM
          `doppelkopf`
        JOIN
          `player` ON `doppelkopf`.`doppelkopf_owner`= `player`.`player_id`
        WHERE
          `player`.`player_re`"
    );
    $kontra_fox_count = $this->getUniqueValueFromDB(
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
    $kontra_dk_count = $this->getUniqueValueFromDB(
      "SELECT
          COUNT(`doppelkopf_card`)
        FROM
          `doppelkopf`
        JOIN
          `player` ON `doppelkopf`.`doppelkopf_owner`= `player`.`player_id`
        WHERE
          NOT `player`.`player_re`"
    );
    $charlie_id = $this->getGameStateValue(VAR_CHARLIE);
    $re_charlie = $this->getUniqueValueFromDB(
      "SELECT
          COUNT(`player_id`)
        FROM
          `player`
        WHERE
          `player`.`player_re` AND
          `player`.`player_id` = $charlie_id"
    );
    $kontra_charlie = $this->getUniqueValueFromDB(
      "SELECT
          COUNT(`player_id`)
        FROM
          `player`
        WHERE
          NOT `player`.`player_re` AND
          `player`.`player_id` = $charlie_id"
    );

    $this->notifyAllPlayers('sumCards', clienttranslate('RE (${re_players}) got ${nines} Nines, ${tens} Tens, ${jacks} Jacks, ${queens} Queens, ${kings} Kings and ${aces} Aces'), array(
      're_players' => $re_players,
      'nines' => $re[9],
      'tens' => $re[10],
      'jacks' => $re['jacks'],
      'queens' => $re['queens'],
      'kings' => $re['kings'],
      'aces' => $re['aces'],
    ));

    $this->notifyAllPlayers('sumCards', clienttranslate('KONTRA (${kontra_players}) got ${nines} Nines, ${tens} Tens, ${jacks} Jacks, ${queens} Queens, ${kings} Kings and ${aces} Aces'), array(
      'kontra_players' => $kontra_players,
      'nines' => $kontra[9],
      'tens' => $kontra[10],
      'jacks' => $kontra['jacks'],
      'queens' => $kontra['queens'],
      'kings' => $kontra['kings'],
      'aces' => $kontra['aces'],
    ));

    $re_player_ids = $this->getCollectionFromDB(
      "SELECT
          `player`.`player_id`,
          `player`.`player_no`
          FROM
          `player`
          WHERE
          `player`.`player_re`"
    );

    $kontra_player_ids = $this->getCollectionFromDB(
      "SELECT
          `player`.`player_id`,
          `player`.`player_no`
          FROM
          `player`
          WHERE
          NOT `player`.`player_re`"
    );

    if ($this->getUniqueValueFromDB(
      "SELECT
        COUNT(`player_id`)
      FROM
        `player`
      WHERE
        `player`.`player_re` = 1;",
      true
    ) == 1) {
      $re_add = 0;
      $kontra_add = 0;
      foreach ($re_player_ids as $key => $value) {
        $this->dbIncScore($key, 3 * ($re_score > $kontra_score ? 4 - intdiv($kontra_score, 30) : 0) + $re_add);
        $this->dbIncScore($key, -3 * (($re_score <= $kontra_score ? 4 - intdiv($re_score, 30) : 0) + $kontra_add));
      }
    } else {
      $re_add = $re_fox_count + $re_dk_count + $re_charlie;
      $kontra_add = $kontra_fox_count + $kontra_dk_count + $kontra_charlie + ($re_score <= $kontra_score ? 1 : 0);

      foreach ($re_player_ids as $key => $value) {
        $this->dbIncScore($key, ($re_score > $kontra_score ? 4 - intdiv($kontra_score, 30) : 0) + $re_add);
        $this->dbIncScore($key, - (($re_score <= $kontra_score ? 4 - intdiv($re_score, 30) : 0) + $kontra_add));
      }
    }

    foreach ($kontra_player_ids as $key => $value) {
      $this->dbIncScore($key, ($re_score <= $kontra_score ? 4 - intdiv($re_score, 30) : 0) + $kontra_add);
      $this->dbIncScore($key, + (($re_score > $kontra_score ? 4 - intdiv($kontra_score, 30) : 0) + $re_add));
    }

    if ($re_score > $kontra_score) {
      $this->notifyAllPlayers('winner', clienttranslate('RE won with ${re_score} Points, ${re_charlie} times Charlie Miller, ${re_fox_count} times Fox and ${re_doppelkopf_count} times Doppelkopf vs
        ${kontra_score} Points, ${kontra_charlie} times Charlie Miller, ${kontra_fox_count} times Fox and ${kontra_doppelkopf_count} times Doppelkopf'), array(
        'kontra_score' => $kontra_score,
        're_score' => $re_score,
        'kontra_fox_count' => $kontra_fox_count,
        're_fox_count' => $re_fox_count,
        'kontra_doppelkopf_count' => $kontra_dk_count,
        're_doppelkopf_count' => $re_dk_count,
        'kontra_charlie' => $kontra_charlie,
        're_charlie' => $re_charlie,
        'score' => $this->getCollectionFromDB(
          "SELECT
            `player_id`,
            `player_score`
            FROM
            `player`",
          true
        ),
      ));
    } else {
      $this->notifyAllPlayers('winner', clienttranslate('KONTRA won with ${kontra_score} Points, ${kontra_charlie} times Charlie Miller, ${kontra_fox_count} times Fox and ${kontra_doppelkopf_count} times Doppelkopf, vs
        ${re_score} Points, ${re_charlie} times Charlie Miller, ${re_fox_count} times Fox and ${re_doppelkopf_count} times Doppelkopf'), array(
        'kontra_score' => $kontra_score,
        're_score' => $re_score,
        'kontra_fox_count' => $kontra_fox_count,
        're_fox_count' => $re_fox_count,
        'kontra_doppelkopf_count' => $kontra_dk_count,
        're_doppelkopf_count' => $re_dk_count,
        'kontra_charlie' => $kontra_charlie,
        're_charlie' => $re_charlie,
        'score' => $this->getCollectionFromDB(
          "SELECT
            `player_id`,
            `player_score`
            FROM
            `player`",
          true
        ),
      ));
    }

    // Mark done Solo
    $this->DbQuery(
      "UPDATE player SET `player_solo` = 2 WHERE `player_solo` = 1"
    );

    $this->DBQuery(
      "DELETE FROM `fox`;" // NOI18N
    );

    $this->DBQuery(
      "DELETE FROM `doppelkopf`;" // NOI18N
    );

    if ($this->getGameStateValue(VAR_ROUND) >= $this->getGameStateValue(OPT_ROUNDS)) {
      $this->incGameStateValue(VAR_ROUND, 1);
      $this->gamestate->nextState('endGame');
    } else {
      $this->gamestate->nextState('newRound');
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
    //    if ( $from_version <= 1404301345 )
    // {
    //      // ! important ! Use DBPREFIX_<table_name> for all tables
    //
    //      $sql = 'ALTER TABLE DBPREFIX_xxxxxxx ....';
    //      $this->applyDbUpgradeToAllDB( $sql );
    //    }
    //    if ( $from_version <= 1405061421 )
    // {
    //      // ! important ! Use DBPREFIX_<table_name> for all tables
    //
    //      $sql = 'CREATE TABLE DBPREFIX_xxxxxxx ....';
    //      $this->applyDbUpgradeToAllDB( $sql );
    //    }
    //    // Please add your future database scheme changes here
    //
    //

  }
}
