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
 * gameoptions.inc.php
 *
 * dk game options description
 * 
 * In this file, you can define your game options (= game variants).
 *   
 * Note: If your game has no variant, you don't have to modify this file.
 *
 * Note²: All options defined in this file should have a corresponding "game state labels"
 *        with the same ID (see "initGameStateLabels" in dk.game.php)
 *
 * !! It is not a good idea to modify this file when a game is running !!
 *
 */

include "defines.php";

$game_options = array(

    // 100 => array(
    //     'name' => totranslate('Auto Throw'),
    //     'values' => array(
    //         0 => array(
    //             'name' => totranslate('Disable auto throwing'),
    //             'default' => true
    //         ),
    //         1 => array(
    //             'name' => totranslate('Reshuffle automatically when a player could throw'),
    //             'beta' => true,

    //         ),
    //         3 => array(
    //             'name' => totranslate('Reshuffle on wedding as well'),
    //             'beta' => true,
    //         ),
    //     )
    // ),

    OPT_SOLO_ID => array(
        'name' => totranslate("Solos"),
        'values' => array(
            // ON => array(
            //     'name' => totranslate('On'),
            //     'beta' => true,
            // ),
            OFF => array(
                'name' => totranslate('No Solos'),
                'beta' => true,
            ),
            SOLO_ONLY_VOL => array(
                'name' => totranslate('Only Voluntary Solos'),
                'default' => true,'beta' => true,
            ),
        )
    ),


    OPT_H10_ID => array(
        'name' => totranslate('Second ♥ 10 beats first'),
        'values' => array(
            OFF => array(
                'name' => totranslate('On'),
                'default' => true
            ),
            ON => array(
                'name' => totranslate('Off')

            ),
        )
    ),


    /*
    
    // note: game variant ID should start at 100 (ie: 100, 101, 102, ...). The maximum is 199.
    100 => array(
                'name' => totranslate('my game option'),    
                'values' => array(

                            // A simple value for this option:
                            1 => array( 'name' => totranslate('option 1') )

                            // A simple value for this option.
                            // If this value is chosen, the value of "tmdisplay" is displayed in the game lobby
                            2 => array( 'name' => totranslate('option 2'), 'tmdisplay' => totranslate('option 2') ),

                            // Another value, with other options:
                            //  description => this text will be displayed underneath the option when this value is selected to explain what it does
                            //  beta=true => this option is in beta version right now.
                            //  nobeginner=true  =>  this option is not recommended for beginners
                            3 => array( 'name' => totranslate('option 3'), 'description' => totranslate('this option does X'), 'beta' => true, 'nobeginner' => true )
                        )
            )

    */

);
