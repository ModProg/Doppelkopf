/**
 *------
 * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
 * dk implementation : © Roland Fredenhagen roland@van-fredenhagen.de
 *
 * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
 * See http://en.boardgamearena.com/#!doc/Studio for more information.
 * -----
 *
 * dk.js
 *
 * dk user interface script
 * 
 * In this file, you are describing the logic of your user interface, in Javascript language.
 *
 */

define([
    "dojo", "dojo/_base/declare",
    "ebg/core/gamegui",
    "ebg/counter",
    "ebg/stock"
],
    function (dojo, declare) {
        return declare("bgagame.dk", ebg.core.gamegui, {
            constructor: function () {
                console.log('dk constructor');
                // TODO remove on release
                // XXX hide Expressswitch
                // while( dojo.query(".expressswitch").length>0)
                //     dojo.destroy(dojo.query(".expressswitch")[0]);

                this.cardwidth = 72;
                this.cardheight = 105;
                // Here, you can init the global variables of your user interface
                // Example:
                // this.myGlobalValue = 0;

            },

            /*
                setup:
                
                This method must set up the game user interface according to current game situation specified
                in parameters.
                
                The method is called each time the game interface is displayed to a player, ie:
                _ when the game starts
                _ when a player refreshes the game page (F5)
                
                "gamedatas" argument contains all datas retrieved by your "getAllDatas" PHP method.
            */

            setup: function (gamedatas) {
                //dojo.destroy('debug_output');
                console.log("Starting game setup");

                // Setting up player boards
                for (var player_id in gamedatas.players) {
                    var player = gamedatas.players[player_id];

                    dojo.place(this.format_block('jstpl_cardsbelowtable', {
                        player_id: player_id
                    }), 'player_board_'+player_id)

                    // TODO: Setting up players boards if needed
                }

                // TODO: Set up your game interface here, according to "gamedatas"

                // Player hand
                this.playerHand = new ebg.stock(); // new stock object for hand
                this.playerHand.create(this, $('myhand'), this.cardwidth, this.cardheight);
                this.playerHand.image_items_per_row = 6;
                dojo.connect(this.playerHand, 'onChangeSelection', this, 'onPlayerHandSelectionChanged');
                for (let card of this.gamedatas.cardSorting) {
                        let type = this.getCardUniqueId(card.suit, card.value);
                        this.playerHand.addItemType(type, type + card.trump*30, g_gamethemeurl + 'img/cards.png', type);
                    
                }
                console.log("Placing Cards in Hand");

                // Cards in player's hand
                for (let card of this.gamedatas.hand) {
                    this.playerHand.addToStockWithId(this.getCardUniqueId(card.suit, card.value), card.id);
                }
                console.log("Placing Cards on Table");

                // Cards played on table
                for (let i in this.gamedatas.table) {
                    var card = this.gamedatas.table[i];
                    var suit = card.type;
                    var value = card.type_arg;
                    var player_id = card.location_arg;
                    this.playCardOnTable(player_id, suit, value, card.id);
                }

                console.log("Placing Foxes");

                for (let i in this.gamedatas.foxes){
                    var fox = this.gamedatas.foxes[i];
                    this.playCardBelowTable(fox.catcher, fox.suit, fox.value, fox.card);
                }

                console.log("Placing Doppelköpfe");

                for (let i in this.gamedatas.doppelköpfe){
                    var doppelkopf = this.gamedatas.doppelköpfe[i];
                    this.playCardBelowTable(doppelkopf.owner, doppelkopf.suit, doppelkopf.value, doppelkopf.card);
                }

                console.log("Setup Notifications");
                // Setup game notifications to handle (see "setupNotifications" method below)
                this.setupNotifications();

                console.log("Ending game setup");
            },


            ///////////////////////////////////////////////////
            //// Game & client states

            // onEnteringState: this method is called each time we are entering into a new game state.
            //                  You can use this method to perform some user interface changes at this moment.
            //
            onEnteringState: function (stateName, args) {
                //console.log('Entering state: ' + stateName);

                switch (stateName) {

                    /* Example:
                    
                    case 'myGameState':
                    
                        // Show some HTML block at this game state
                        dojo.style( 'my_html_block_id', 'display', 'block' );
                        
                        break;
                   */


                    case 'dummmy':
                        break;
                }
            },

            // onLeavingState: this method is called each time we are leaving a game state.
            //                 You can use this method to perform some user interface changes at this moment.
            //
            onLeavingState: function (stateName) {
                //console.log('Leaving state: ' + stateName);

                switch (stateName) {

                    /* Example:
                    
                    case 'myGameState':
                    
                        // Hide the HTML block we are displaying only during this game state
                        dojo.style( 'my_html_block_id', 'display', 'none' );
                        
                        break;
                   */


                    case 'dummmy':
                        break;
                }
            },

            // onUpdateActionButtons: in this method you can manage "action buttons" that are displayed in the
            //                        action status bar (ie: the HTML links in the status bar).
            //        
            onUpdateActionButtons: function (stateName, args) {
                //console.log('onUpdateActionButtons: ' + stateName);

                if (this.isCurrentPlayerActive()) {
                    switch (stateName) {
                        /*               
                                         Example:
                         
                                         case 'myGameState':
                                            
                                            // Add 3 action buttons in the action status bar:
                                            
                                            this.addActionButton( 'button_1_id', _('Button 1 label'), 'onMyMethodToCall1' ); 
                                            this.addActionButton( 'button_2_id', _('Button 2 label'), 'onMyMethodToCall2' ); 
                                            this.addActionButton( 'button_3_id', _('Button 3 label'), 'onMyMethodToCall3' ); 
                                            break;
                        */
                    }
                }
            },

            ///////////////////////////////////////////////////
            //// Utility methods

            /*
            
                Here, you can defines some utility methods that you can use everywhere in your javascript
                script.
            
            */
            // Get card unique identifier based on its suit and value
            getCardUniqueId: function (suit, value) {
                return (suit - 1) * 6 + (value - 9);
            },


            playCardOnTable: function (player_id, suit, value, card_id) {
                // player_id => direction
                dojo.place(this.format_block('jstpl_cardontable', {
                    x: this.cardwidth * (value - 9),
                    y: this.cardheight * (suit - 1),
                    player_id: player_id
                }), 'playertablecard_' + player_id);

                if (player_id != this.player_id) {
                    // Some opponent played a card
                    // Move card from player panel
                    this.placeOnObject('cardontable_' + player_id, 'overall_player_board_' + player_id);
                } else {
                    // You played a card. If it exists in your hand, move card from there and remove
                    // corresponding item

                    if ($('myhand_item_' + card_id)) {
                        this.placeOnObject('cardontable_' + player_id, 'myhand_item_' + card_id);
                        this.playerHand.removeFromStockById(card_id);
                    }
                }

                // In any case: move it to its final destination
                this.slideToObject('cardontable_' + player_id, 'playertablecard_' + player_id).play();
            },
            playCardBelowTable: function (player_id, suit, value, card_id) {
                // player_id => direction
                dojo.place(this.format_block('jstpl_cardbelowtable', {
                    x: this.cardwidth * (value - 9),
                    y: this.cardheight * (suit - 1),
                    card_id: card_id
                }), 'cardsbelowtable_' + player_id);
            },
            // /////////////////////////////////////////////////
            // // Player's action

            /*
             * 
             * Here, you are defining methods to handle player's action (ex: results of mouse click on game objects).
             * 
             * Most of the time, these methods: _ check the action is possible at this game state. _ make a call to the game server
             * 
             */


            onPlayerHandSelectionChanged: function () {
                var items = this.playerHand.getSelectedItems();

                if (items.length > 0) {
                    var action = 'playCard';
                    if (this.checkAction(action, true)) {
                        // Can play a card
                        var card_id = items[0].id;
                        this.ajaxcall("/" + this.game_name + "/" + this.game_name + "/" + action + ".html", {
                            id: card_id,
                            lock: true
                        }, this, function (result) {
                        }, function (is_error) {
                        });

                        this.playerHand.unselectAll();
                    } else if (this.checkAction('giveCards')) {
                        // Can give cards => let the player select some cards
                    } else {
                        this.playerHand.unselectAll();
                    }
                }
            },




            /*
             * Example:
             * 
             * onMyMethodToCall1: function( evt ) { console.log( 'onMyMethodToCall1' );
             *  // Preventing default browser reaction dojo.stopEvent( evt );
             *  // Check that this action is possible (see "possibleactions" in states.inc.php) if( ! this.checkAction( 'myAction' ) ) { return; }
             * 
             * this.ajaxcall( "/heartsla/heartsla/myAction.html", { lock: true, myArgument1: arg1, myArgument2: arg2, ... }, this, function(
             * result ) {
             *  // What to do after the server call if it succeeded // (most of the time: nothing)
             *  }, function( is_error) {
             *  // What to do after the server call in anyway (success or failure) // (most of the time: nothing)
             *  } ); },
             * 
             */


            ///////////////////////////////////////////////////
            //// Reaction to cometD notifications

            /*
                setupNotifications:
                
                In this method, you associate each of your game notifications with your local method to handle it.
                
                Note: game notification names correspond to "notifyAllPlayers" and "notifyPlayer" calls in
                      your dk.game.php file.
            
            */
            setupNotifications: function () {
                console.log('notifications subscriptions setup');

                dojo.subscribe('newHand', this, "notif_newHand");
                dojo.subscribe('playCard', this, "notif_playCard");
                dojo.subscribe('trickWin', this, "notif_trickWin");
                this.notifqueue.setSynchronous('trickWin', 1000);
                dojo.subscribe('giveAllCardsToPlayer', this, "notif_giveAllCardsToPlayer");
                dojo.subscribe('wrongCard', this, "notif_wrongCard");
                dojo.subscribe('giveSpecToPlayer', this, "notif_giveSpecToPlayer");
                dojo.subscribe('sumCards', this, "notif_sumCards");
                this.notifqueue.setSynchronous('sumCards', 500);
                dojo.subscribe('winner', this, "notif_winner");
            },
            notif_sumCards: function (notif) {
                // We do nothing here (just wait in order players can view the 4 cards played before they're gone.
            },
            notif_winner: function (notif) {
                // We do nothing here (just wait in order players can view the 4 cards played before they're gone.
                for (var player_id in this.gamedatas.players) { 
                    console.log("Score:")
                    console.log(notif.args.score);
                    this.scoreCtrl[ player_id ].setValue( notif.args.score[player_id] );
                    while( dojo.query(".cardbelowtable").length>0)
                        dojo.destroy(dojo.query(".cardbelowtable")[0]);
                }
            },

            notif_newHand: function (notif) {
                // We received a new full hand of 13 cards.
                this.playerHand.removeAll();

                for (var i in notif.args.cards) {
                    var card = notif.args.cards[i];
                    var suit = card.suit;
                    var value = card.value;
                    this.playerHand.addToStockWithId(this.getCardUniqueId(suit, value), card.id);
                }
            },

            notif_playCard: function (notif) {
                // Play a card on the table
                this.playCardOnTable(notif.args.player_id, notif.args.suit, notif.args.value, notif.args.card_id);
            },
            notif_trickWin: function (notif) {
                // We do nothing here (just wait in order players can view the 4 cards played before they're gone.
            },
            notif_wrongCard: function (notif) {
                // We do nothing here (just wait in order players can view the 4 cards played before they're gone.
            },

            notif_giveSpecToPlayer: function (notif){
                this.playCardBelowTable(notif.args.player_id, notif.args.suit, notif.args.value, notif.args.card_id);
            },
            notif_giveAllCardsToPlayer: function (notif) {
                // Move all cards on table to given table, then destroy them
                var winner_id = notif.args.player_id;
                for (var player_id in this.gamedatas.players) {
                    var anim = this.slideToObject('cardontable_' + player_id, 'overall_player_board_' + winner_id);
                    dojo.connect(anim, 'onEnd', function (node) {
                        dojo.destroy(node);
                    });
                    anim.play();
                }
            },
            // TODO: from this point and below, you can write your game notifications handling methods

            /*
            Example:
            
            notif_cardPlayed: function( notif )
            {
                console.log( 'notif_cardPlayed' );
                console.log( notif );
                
                // Note: notif.args contains the arguments specified during you "notifyAllPlayers" / "notifyPlayer" PHP call
                
                // TODO: play the card in the user interface.
            },    
            
            */
        });
    });
