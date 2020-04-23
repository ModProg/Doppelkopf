<?php

/**
 *------
 * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel dasasdasdasolin <ecolin@boardgamearena.com>
 * dk implementation : © Roland Fredenhagen roland@van-fredenhagen.de
 *
 * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
 * See http://en.boardgamearena.com/#!doc/Studio for more information.
 * -----
 *
 * material.inc.php
 *
 * dk game material description
 *
 * Here, you can describe the material of your game with PHP variables.
 *
 * This file is loaded in your game logic class constructor, ie these variables
 * are available everywhere in your game logic code.
 *
 */


/*

Example:

$this->card_types = array(
1 => array( "card_name" => ...,
...
)
);

 */

if (!defined("ACE")) {

    define("DIAMOND", 1);
    define("HEART", 2);
    define("SPADE", 3);
    define("CLUB", 4);
    define("TRUMP", 5);
    define("NINE", 9);
    define("JACK", 10);
    define("QUEEN", 11);
    define("KING", 12);
    define("TEN", 13);
    define("ACE", 14);
}

$this->suits = array(
    DIAMOND => array(
        'name' => clienttranslate('diamond'),
        'nametr' => self::_('diamond')
    ),
    HEART => array(
        'name' => clienttranslate('heart'),
        'nametr' => self::_('heart')
    ),
    SPADE => array(
        'name' => clienttranslate('spade'),
        'nametr' => self::_('spade')
    ),
    CLUB => array(
        'name' => clienttranslate('club'),
        'nametr' => self::_('club')
    ),
    TRUMP => array(
        'name' => clienttranslate('trump'),
        'nametr' => self::_('trump')
    ),
);

$this->values_label = array(
    NINE => array('name' => '9', 'nametr' => '9'),
    TEN => array('name' => '10', 'nametr' => '10'),
    JACK => array(
        'name' =>  clienttranslate('Jack'),
        'nametr' => self::_('Jack')
    ),
    QUEEN => array(
        'name' =>  clienttranslate('Queen'),
        'nametr' => self::_('Queen')
    ),
    KING => array(
        'name' =>  clienttranslate('King'),
        'nametr' => self::_('King')
    ),
    ACE => array(
        'name' =>  clienttranslate('Ace'),
        'nametr' => self::_('Ace')
    ),
);
