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


if (!defined('ACE')) { // ensure this block is only invoked once, since it is included multiple times
  define("TRUMP", 5);
  define("DIAMOND", 1);
  define("HEART", 2);
  define("SPADE", 3);
  define("CLUB", 4);
  define("JACK", 11);
  define("QUEEN", 12);
  define("KING", 13);
  define("ACE", 14);
}

$this->suits = array(
  DIAMOND => array( 'name' => clienttranslate('diamond'),
              'nametr' => self::_('diamond') ),
  HEART => array( 'name' => clienttranslate('heart'),
              'nametr' => self::_('heart') ),
  SPADE => array( 'name' => clienttranslate('spade'),
              'nametr' => self::_('spade') ),
  CLUB => array( 'name' => clienttranslate('club'),
              'nametr' => self::_('club') ),
  TRUMP => array( 'name' => clienttranslate('trump'),
              'nametr' => self::_('trump') )
);

$this->values_label = array(
  9 => '9',
  10 => '10',
  JACK => clienttranslate('Jack'),
  QUEEN => clienttranslate('Queen'),
  KING => clienttranslate('King'),
  ACE => clienttranslate('Ace')
);


$this->jacks = array(
  array(1,11),
  array(2,11),
  array(3,11),
  array(4,11)
);

$this->queens = array(
  array(1,12),
  array(2,12),
  array(3,12));

$this->diamonds=array(
array(1,9),
array(1,10),
array(1,13),
array(1,14),
);

$this->trumps = array_merge($this->diamonds, $this->jacks, $this->queens, array( array(2,10)));