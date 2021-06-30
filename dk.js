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
// Suits
const DIAMOND = 0;
const HEART = 1;
const SPADE = 2;
const CLUB = 3;
const TRUMP = 4;

// Values
const NINE = 0;
const JACK = 1;
const QUEEN = 2;
const KING = 3;
const TEN = 4;
const ACE = 5;

// Modes
const NORMAL = 0;
const SOLODIAMOND = 1;
const SOLOHEART = 2;
const SOLOSPADE = 3;
const SOLOCLUB = 4;
const SOLOQUEEN = 5;
const SOLOJACK = 6;
const SOLOACE = 7;

const CARDWIDTH = 72;
const CARDHEIGTH = 105;

define([
  "dojo",
  "dojo/_base/declare",
  "ebg/core/gamegui",
  "ebg/counter",
  "ebg/stock",
], function (dojo, declare) {
  return declare("bgagame.dk", ebg.core.gamegui, {
    constructor: function () {
      console.log("dk constructor");
      // TODO remove on release
      // XXX hide Expressswitch

      //while (dojo.query(".expressswitch").length > 0)
      //    dojo.destroy(dojo.query(".expressswitch")[0]);
    },
    setup: function (gamedatas) {
      this.gamedatas = gamedatas
      //dojo.destroy('debug_output');
      console.log("Starting game setup");
      console.log("DK:", this);
      // Setting up player boards
      console.log("Gamedatas:", gamedatas);
      for (var player_id in gamedatas.players) {
        if (gamedatas.wedding && player_id == gamedatas.wedding) {
          this.showMessage(_("Wedding!"), "info");
          dojo.place(
            this.format_block("jstpl_weddingrings", {
              player_id: player_id,
            }),
            "player_board_" + player_id
          );
        }

        dojo.place(
          this.format_block("jstpl_cardsbelowtable", {
            player_id: player_id,
          }),
          "player_board_" + player_id
        );
      }

      this.setupHandCards();

      console.log("Placing Cards on Table");

      // Cards played on table
      for (let i in this.gamedatas.table) {
        var card = this.gamedatas.table[i];
        var suit = card.type;
        var value = card.type_arg;
        player_id = card.location_arg;
        this.playCardOnTable(player_id, suit, value, card.id);
      }

      console.log("Placing Foxes");

      for (let i in this.gamedatas.foxes) {
        var fox = this.gamedatas.foxes[i];
        this.playCardBelowTable(fox.catcher, fox.suit, fox.value, fox.card);
      }

      console.log("Placing Doppelköpfe");

      for (let i in this.gamedatas.doppelköpfe) {
        var doppelkopf = this.gamedatas.doppelköpfe[i];
        this.playCardBelowTable(
          doppelkopf.owner,
          doppelkopf.suit,
          doppelkopf.value,
          doppelkopf.card
        );
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

        case "dummmy":
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

        case "dummmy":
          break;
      }
    },

    // onUpdateActionButtons: in this method you can manage "action buttons" that are displayed in the
    //                        action status bar (ie: the HTML links in the status bar).
    //
    onUpdateActionButtons: function (stateName, args) {
      if (this.isCurrentPlayerActive()) {
        switch (stateName) {
          case "playerReservation":
            this.addActionButton(
              "button_gesund",
              this.gamedatas.res2
                ? _("Gesund (Silent Solo)")
                : _("Gesund (Normal Game)"),
              "onGesund"
            );
            var lable = [];
            if (this.gamedatas.canThrow) lable.push(_("Throwing"));
            if (this.gamedatas.res2) lable.push(_("Wedding"));
            if (this.gamedatas.solo_allowed) lable.push(_("Solo"));

            if (lable.length > 0) {
              this.addActionButton(
                "button_reservation",
                dojo.string.substitute(_("Reservation: ${r}"), {
                  r: lable.join(_("/")),
                }),
                "onReservation"
              );
            }
            break;
          case "playerMandatory":
            this.addActionButton("button_yes", _("Yes"), "onSolo");
            if (this.gamedatas.canThrow || this.gamedatas.res2) {
              lable = [];
              if (this.gamedatas.canThrow) lable.push(_("Throwing"));
              if (this.gamedatas.res2) lable.push(_("Wedding"));

              this.addActionButton(
                "button_no",
                dojo.string.substitute(_("No: ${r}"), {
                  r: lable.join(_("/")),
                }),
                "onNo"
              );
              this.mandatory = true;
            } else this.onSolo();
          case "playerSolo":
            this.mandatory = false;
            this.addActionButton("button_yes", _("Yes"), "onSolo");
            if (this.gamedatas.canThrow || this.gamedatas.res2) {
              lable = [];
              if (this.gamedatas.canThrow) lable.push(_("Throwing"));
              if (this.gamedatas.res2) lable.push(_("Wedding"));

              this.addActionButton(
                "button_no",
                dojo.string.substitute(_("No: ${r}"), {
                  r: lable.join(_("/")),
                }),
                "onNo"
              );
            } else this.onSolo();
            break;
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

    setupHandCards: function () {
      // Player hand
      if (!this.playerHand) {
        this.playerHand = new ebg.stock(); // new stock object for hand
        this.playerHand.create(
          this,
          $("myhand"),
          CARDWIDTH,
          CARDHEIGTH
        );
        this.playerHand.image_items_per_row = 6;
        dojo.connect(
          this.playerHand,
          "onChangeSelection",
          this,
          "onPlayerHandSelectionChanged"
        );
        for (let card of this.gamedatas.cardSorting) {
          let type = this.getCardUniqueId(card.suit, card.value);
          this.playerHand.addItemType(
            type,
            type + card.trump * 30,
            g_gamethemeurl + "img/cards.png",
            type
          );
        }
        console.log("Placing Cards in Hand");

        // Cards in player's hand
        for (let card of this.gamedatas.hand) {
          this.playerHand.addToStockWithId(
            this.getCardUniqueId(card.suit, card.value),
            card.id
          );
        }
      } else {
        console.log("Resorting Cards in Hand");
        let weights = {};
        for (let card of this.gamedatas.cardSorting) {
          let type = this.getCardUniqueId(card.suit, card.value);
          weights[type] = type + card.trump * 30;
        }
        this.playerHand.changeItemsWeight(weights)
      }
    },

    // Get card unique identifier based on its suit and value
    getCardUniqueId: function (suit, value) {
      suit = Number.parseInt(suit)
      value = Number.parseInt(value)
      return (suit) * 6 + (value);
    },

    playCardOnTable: function (player_id, suit, value, card_id) {
      // player_id => direction
      dojo.place(
        this.format_block("jstpl_cardontable", {
          x: CARDWIDTH * Number.parseInt(value),
          y: CARDHEIGTH * Number.parseInt(suit),
          player_id: player_id,
        }),
        "playertablecard_" + player_id
      );

      if (player_id != this.player_id) {
        // Some opponent played a card
        // Move card from player panel
        this.placeOnObject(
          "cardontable_" + player_id,
          "overall_player_board_" + player_id
        );
      } else {
        // You played a card. If it exists in your hand, move card from there and remove
        // corresponding item

        if ($("myhand_item_" + card_id)) {
          this.placeOnObject(
            "cardontable_" + player_id,
            "myhand_item_" + card_id
          );
          this.playerHand.removeFromStockById(card_id);
        }
      }

      // In any case: move it to its final destination
      this.slideToObject(
        "cardontable_" + player_id,
        "playertablecard_" + player_id
      ).play();
    },
    playCardBelowTable: function (player_id, suit, value, card_id) {
      // player_id => direction
      dojo.place(
        this.format_block("jstpl_cardbelowtable", {
          x: CARDWIDTH * (value),
          y: CARDHEIGTH * (suit),
          card_id: card_id,
        }),
        "cardsbelowtable_" + player_id
      );
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
    action: function (action, options = {}) {
      if (this.checkAction(action)) {
        this.ajaxcall(
          "/" + this.game_name + "/" + this.game_name + "/" + action + ".html",
          {
            lock: true,
            ...options,
          },
          this,
          function (result) { },
          function (is_error) { }
        );
      }
    },
    onPlayerHandSelectionChanged: function () {
      var items = this.playerHand.getSelectedItems();

      if (items.length > 0) {
        this.action("playCard", { id: items[0].id });
        this.playerHand.unselectAll();
      }
    },
    onGesund: function () {
      this.action("gesund");
    },
    onReservation: function () {
      this.action("reservation");
    },
    onSolo: function () {
      var keys = [];
      keys[SOLODIAMOND] = _("Diamand Solo");
      keys[SOLOHEART] = _("Heart Solo");
      keys[SOLOSPADE] = _("Spade Solo");
      keys[SOLOCLUB] = _("Club Solo");
      keys[SOLOQUEEN] = _("Queen Solo");
      keys[SOLOJACK] = _("Jack Solo");
      keys[SOLOACE] = _("Ace Solo");

      console.log(this.multipleChoiceDialog(
        _("Which solo do you want to play?"),
        keys,
        dojo.hitch(this, function (choice) {
          this.action(this.mandatory ? "mandatorySolo" : "playerSolo", { solo: choice });
        })
      ));
      dojo.style(dojo.byId("popin_multipleChoice_dialog_close"), "display", "none");
    },
    onNo: function () {
      this.action("no");
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
      console.log("notifications subscriptions setup");

      dojo.subscribe("newHand", this, "notif_newHand");
      dojo.subscribe("playCard", this, "notif_playCard");
      dojo.subscribe("trickWin", this, "notif_trickWin");
      this.notifqueue.setSynchronous("trickWin", 1500);
      dojo.subscribe(
        "giveAllCardsToPlayer",
        this,
        "notif_giveAllCardsToPlayer"
      );
      dojo.subscribe("giveSpecToPlayer", this, "notif_giveSpecToPlayer");
      dojo.subscribe("sumCards", this, "notif_sumCards");
      this.notifqueue.setSynchronous("sumCards", 500);
      dojo.subscribe("winner", this, "notif_winner");
      dojo.subscribe("wedding", this, "notif_wedding");
      dojo.subscribe("gesund", this, "notif_gesund");
      dojo.subscribe("reservation", this, "notif_reservation");
      dojo.subscribe("weddingComplete", this, "notif_weddingComplete");
      dojo.subscribe("solo", this, "notif_solo");
    },

    notif_gesund: function (notif) {
      this.showBubble("playertablecard_" + notif.args.player_id, _("Gesund"));
    },

    notif_reservation: function (notif) {
      this.showBubble(
        "playertablecard_" + notif.args.player_id,
        _("Reservation")
      );
    },

    notif_wedding: function (notif) {
      this.showMessage(_("Wedding!"), "info");
      this.showBubble("playertablecard_" + notif.args.player_id, _("Wedding!"));

      dojo.place(
        this.format_block("jstpl_weddingrings", {
          player_id: notif.args.player_id,
        }),
        "player_board_" + notif.args.player_id
      );
    },

    notif_weddingComplete: function (notif) {
      dojo.destroy(dojo.query(".weddingrings")[0]);
    },

    notif_solo: function (notif) {
      console.log("Rearanging Cards");
      this.gamedatas.cardSorting = notif.args.cardSorting;
      this.setupHandCards();
    },

    notif_sumCards: function (notif) {
      // We do nothing here (just wait in order players can view the 4 cards played before they're gone.
    },

    notif_winner: function (notif) {
      for (var player_id in this.gamedatas.players) {
        console.log("Score:");
        console.log(notif.args.score);
        this.scoreCtrl[player_id].setValue(notif.args.score[player_id]);
        while (dojo.query(".cardbelowtable").length > 0)
          dojo.destroy(dojo.query(".cardbelowtable")[0]);
      }
    },

    notif_newHand: function (notif) {
      this.gamedatas.cardSorting = notif.args.cardSorting;

      this.gamedatas.hand = notif.args.hand;

      this.setupHandCards();
    },

    notif_playCard: function (notif) {
      // Play a card on the table
      this.playCardOnTable(
        notif.args.player_id,
        notif.args.suit,
        notif.args.value,
        notif.args.card_id
      );
    },
    notif_trickWin: function (notif) {
      // We do nothing here (just wait in order players can view the 4 cards played before they're gone.
    },

    notif_giveSpecToPlayer: function (notif) {
      this.playCardBelowTable(
        notif.args.player_id,
        notif.args.suit,
        notif.args.value,
        notif.args.card_id
      );
    },
    notif_giveAllCardsToPlayer: function (notif) {
      // Move all cards on table to given table, then destroy them
      var winner_id = notif.args.player_id;
      for (var player_id in this.gamedatas.players) {
        var anim = this.slideToObject(
          "cardontable_" + player_id,
          "overall_player_board_" + winner_id
        );
        dojo.connect(anim, "onEnd", function (node) {
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
