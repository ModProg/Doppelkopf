/**
 *------
 * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
 * TestProject implementation : © <Your name here> <Your email address here>
 *
 * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
 * See http://en.boardgamearena.com/#!doc/Studio for more information.
 * -----
 *
 * testproject.js
 *
 * TestProject user interface script
 * 
 * In this file, you are describing the logic of your user interface, in Javascript language.
 *
 */

define([
    "dojo","dojo/_base/declare",
    "ebg/core/gamegui_sandbox",
    "ebg/counter"
],
function (dojo, declare) {
    return declare("bgagame.doppelkopf", ebg.core.gamegui_sandbox, {
        constructor: function(){
            console.log('doppelkopf constructor');
              
            // Here, you can init the global variables of your user interface
            // Example:
            // this.myGlobalValue = 0;

        },

        setup: function( gamedatas )
        {
            g_sandboxthemeurl =  g_gamethemeurl;
        
            // Initialize sandbox
            ebg.core.gamegui_sandbox.prototype.setup.call(this,gamedatas);
        }        
   
   });             
});
