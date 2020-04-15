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

$this->suits = array(
1 => array( 'name' => clienttranslate('diamond'),
            'nametr' => self::_('diamond') ),
  3 => array( 'name' => clienttranslate('spade'),
              'nametr' => self::_('spade') ),
  2 => array( 'name' => clienttranslate('heart'),
              'nametr' => self::_('heart') ),
  4 => array( 'name' => clienttranslate('club'),
              'nametr' => self::_('club') )
);

$this->values_label = array(
  9 => '9',
  10 => '10',
  11 => clienttranslate('J'),
  12 => clienttranslate('Q'),
  13 => clienttranslate('K'),
  14 => clienttranslate('A')
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