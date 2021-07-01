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
 * states.inc.php
 *
 * dk game states description
 *
 */

/*
Game state machine is a tool used to facilitate game development by doing common stuff that can be set up
in a very easy way from this configuration file.

Please check the BGA Studio presentation about game state to understand this, and associated documentation.

Summary:

States types:
_ activeplayer: in this type of state, we expect some action from the active player.
_ multipleactiveplayer: in this type of state, we expect some action from multiple players ( the active players )
_ game: this is an intermediary state where we don't expect any actions from players. Your game logic must decide what is the next game state.
   _ manager: special type for initial and final state

   Arguments of game states:
   _ name: the name of the GameState, in order you can recognize it on your own code.
   _ description: the description of the current game state is always displayed in the action status bar on
                  the top of the game. Most of the time this is useless for game state with "game" type.
   _ descriptionmyturn: the description of the current game state when it's your turn.
_ type: defines the type of game states ( activeplayer / multipleactiveplayer / game / manager )
_ action: name of the method to call when this game state become the current game state. Usually, the
action method is prefixed by 'st' ( ex: 'stMyGameStateName' ).
_ possibleactions: array that specify possible player actions on this step. It allows you to use 'checkAction'
method on both client side ( Javascript: this.checkAction ) and server side ( PHP: self::checkAction ).
_ transitions: the transitions are the possible paths to go from a game state to another. You must name
transitions in order to use transition names in 'nextState' PHP method, and use IDs to
specify the next game state for each transition.
_ args: name of the method to call to retrieve arguments for this gamestate. Arguments are sent to the
client side to be used on 'onEnteringState' or to set arguments in the gamestate description.
_ updateGameProgression: when specified, the game progression is updated ( => call to your getGameProgression
method ).
*/

//    !! It is not a good idea to modify this file when a game is running !!

$machinestates = array(

    // The initial state. Please do not modify.
    1 => array(
        'name' => 'gameSetup',
        'description' => '',
        'type' => 'manager',
        'action' => 'stGameSetup',
        'transitions' => array('' => 10)
    ),

    /// New hand
    10 => array(
        'name' => 'newHand',
        'description' => '',
        'type' => 'game',
        'action' => 'stNewHand',
        'updateGameProgression' => true,
        'transitions' => array('' => 20)
    ),

    // TODO Reset Player States
    20 => array(
        'name' => 'preRound',
        'description' => '',
        'type' => 'game',
        'action' => 'stPreRound',
        'transitions' => array('playerChoose' => 22, 'startGame' => 40, 'reshuffle' => 10)
    ),


    // Reservation
    21 => array(
        'name' => 'playerReservation',
        'description' => clienttranslate('${actplayer} must choose how to play'),
        'descriptionmyturn' => clienttranslate('${you} must choose:'),
        'type' => 'activeplayer',
        'possibleactions' => array('gesund', 'reservation'),
        'transitions' => array('' => 22)
    ),
    22 => array(
        'name' => 'nextReservation',
        'description' => '',
        'type' => 'game',
        'action' => 'stNextReservation',
        'transitions' => array('nextPlayer' => 21, 'mandatorySolo' => 24)
    ),

    // Mandatory Solo
    23 => array(
        'name' => 'playerMandatory',
        'description' => clienttranslate('${actplayer} must choose if they want to play a mandatory solo'),
        'descriptionmyturn' => clienttranslate('Do ${you} want to play a mandatory solo?'),
        'type' => 'activeplayer',
        'possibleactions' => array('mandatorySolo', 'no'),
        'transitions' => array('' => 24)
    ),
    24 => array(
        'name' => 'nextMandatory',
        'description' => '',
        'type' => 'game',
        'action' => 'stNextMandatory',
        'transitions' => array('nextPlayer' => 23, 'normalSolo' => 26)
    ),

    // Normal Solo
    // TODO Only ask already mandatory solo
    25 => array(
        'name' => 'playerSolo',
        'description' => clienttranslate('${actplayer} must choose if they want to play a solo'),
        'descriptionmyturn' => clienttranslate('Do ${you} want to play a solo?'),
        'type' => 'activeplayer',
        'possibleactions' => array('playerSolo', 'no'),
        'transitions' => array('nextPlayer' => 26)
    ),
    26 => array(
        'name' => 'nextSolo',
        'description' => '',
        'type' => 'game',
        'action' => 'stNextSolo',
        'transitions' => array('nextPlayer' => 25, 'throwing' => 28)
    ),

    // Throwing
    27 => array(
        'name' => 'playerThrowing',
        'description' => clienttranslate('${actplayer} must choose to play'),
        'descriptionmyturn' => clienttranslate('Do ${you} want to throw?'),
        'type' => 'activeplayer',
        'possibleactions' => array('throw', 'no'),
        'transitions' => array('nextPlayer' => 28, 'reshuffle' => 10)
    ),
    28 => array(
        'name' => 'nextThrowing',
        'description' => '',
        'type' => 'game',
        'action' => 'stNextThrowing',
        'transitions' => array('nextPlayer' => 27, 'poverty' => 40, 'reshuffle' => 10)
    ),

    // Poverty
    29 => array(
        'name' => 'playerPoverty',
        'description' => clienttranslate('${actplayer} must choose to poverty'),
        'descriptionmyturn' => clienttranslate('Do ${you} want to play poverty?'),
        'type' => 'activeplayer',
        'possibleactions' => array('poverty', 'no'),
        'transitions' => array('' => 30)
    ),
    30 => array(
        'name' => 'nextPoverty',
        'description' => '',
        'type' => 'game',
        'action' => 'stNextPoverty',
        'transitions' => array('nextPlayer' => 29, 'startGame' => 40, 'reshuffle' => 10, 'takeOver' => 31)
    ),
    31 => array(
        'name' => 'takeOver',
        'description' => clienttranslate('${actplayer} needs to decide if they want to take over ${otherplayer}?'),
        'args' => 'argTakeOver',
        'descriptionmyturn' => clienttranslate('Do ${you} want to take over ${otherplayer}?'),
        'type' => 'multipleactiveplayer',
        'action' => 'stTakeOver',
        'possibleactions' => array('takeOver', 'no'),
        'transitions' => array('startGame' => 40, 'reshuffle' => 10)
    ),

    // Trick

    40 => array(
        'name' => 'newTrick',
        'description' => '',
        'type' => 'game',
        'action' => 'stNewTrick',
        'transitions' => array('' => 41)
    ),
    41 => array(
        'name' => 'playerTurn',
        'description' => clienttranslate('${actplayer} must play a card'),
        'descriptionmyturn' => clienttranslate('${you} must play a card'),
        'type' => 'activeplayer',
        'possibleactions' => array('playCard'),
        'transitions' => array('' => 42)
    ),
    42 => array(
        'name' => 'nextPlayer',
        'description' => '',
        'type' => 'game',
        'action' => 'stNextPlayer',
        'updateGameProgression' => true,
        'transitions' => array('nextPlayer' => 41, 'nextTrick' => 40, 'endHand' => 50)
    ),

    // End of the hand ( scoring, etc... )
    50 => array(
        'name' => 'endHand',
        'description' => '',
        'type' => 'game',
        'action' => 'stEndHand',
        'updateGameProgression' => true,
        'transitions' => array('newRound' => 10, 'endGame' => 99)
    ),

    // Final state.
    // Please do not modify ( and do not overload action/args methods ).
    99 => array(
        'name' => 'gameEnd',
        'description' => clienttranslate('End of game'),
        'type' => 'manager',
        'action' => 'stGameEnd',
        'args' => 'argGameEnd'
    )

);
