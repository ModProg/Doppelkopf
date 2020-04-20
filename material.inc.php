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


$DIAMOND = 1;
$HEART = 2;
$SPADE = 3;
$CLUB = 4;
$TRUMP = 5;
$NINE= 9;
$JACK= 10;
$QUEEN= 11;
$KING= 12;
$TEN= 13;
$ACE= 14;


$this->suits = array(
  $DIAMOND => array( 'name' => clienttranslate('diamond'),
              'nametr' => self::_('diamond') ),
  $HEART => array( 'name' => clienttranslate('heart'),
              'nametr' => self::_('heart') ),
  $SPADE => array( 'name' => clienttranslate('spade'),
              'nametr' => self::_('spade') ),
  $CLUB => array( 'name' => clienttranslate('club'),
              'nametr' => self::_('club') ),
  $TRUMP => array( 'name' => clienttranslate('trump'),
              'nametr' => self::_('trump') )
);

$this->values_label = array(
  $NINE => '9',
  $TEN => '10',
  $JACK => clienttranslate('Jack'),
  $QUEEN => clienttranslate('Queen'),
  $KING => clienttranslate('King'),
  $ACE => clienttranslate('Ace')
);
