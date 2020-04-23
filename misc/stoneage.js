// Coloretto main javascript

define([
    "dojo","dojo/_base/declare",
    "ebg/core/gamegui",
    "dijit/Dialog",
    "ebg/scrollmap",
    "ebg/counter",
    "ebg/dices"
],
function (dojo, declare) {
    return declare("bgagame.stoneage", ebg.core.gamegui, {
        constructor: function(){
            console.log('stoneage constructor');
              
            this.nbr_workers_resource_source = {0:0,1:0,2:0,3:0,4:0};   // Resource type => nbr of workers displayed
            
            this.current_zone_accumulation = null;      // Zone where current player is placing workers right now
            this.current_zone_accumulation_nbr = 0;
            
            this.building_location_to_id = {};
            this.building_id_to_type = {};
            
            this.resourceChoiceDlg = null;
            this.resourceChoiceArgs = {};
            
            this.resource_names = {0:'food',1:'wood',2:'clay',3:'stone',4:'gold'};
            
            this.dices = null;
        },
        setup: function( gamedatas )
        {
            console.log( "start creating player boards" );
            for( var player_id in gamedatas.players )
            {
                var player = gamedatas.players[player_id];              
                var player_board_div = $('player_board_'+player_id);
                dojo.place( this.format_block('jstpl_player_board', player ), player_board_div );
                
                $('agri_'+player_id).innerHTML = player.agriculture;
                $('workernbr_'+player_id).innerHTML = player.workers;   // Note: this will decrease with "placeWorkerOnZone" during setup
                $('workernbrtotal_'+player_id).innerHTML = player.workers;   // Note: this will NOT decrease
                
                // Number of building buyed
                if( gamedatas.buildingbuyed[player_id] )
                {   $('buildingcount_'+player_id).innerHTML =  gamedatas.buildingbuyed[player_id];  } 
                
                if( player_id == this.player_id  )          
                {
                    dojo.connect( $('take_resources_'+this.player_id), 'onclick', this, 'onTakeResources' );
                    
                    if( toint( player.pick_resources ) == 1 )
                    {   dojo.style( $('take_resources_'+this.player_id+'_wrap'), 'display', 'inline' );  }
                }
 
            }
            
            // Click on zones
            dojo.query( '.zone' ).connect( 'onclick', this, 'onClickOnZone' );
            
            // Place workers on board
            for( var zone_category_id in gamedatas.workers )
            {
                for( var zone_id in gamedatas.workers[ zone_category_id ] )
                {
                    for( player_id in gamedatas.workers[ zone_category_id ][ zone_id ] )
                    {
                        var worker_nbr = gamedatas.workers[ zone_category_id ][ zone_id ][ player_id ];
                        this.placeWorkersOnZone( zone_category_id, zone_id, player_id, worker_nbr );
                    }
                }
            }
            
            // Place buildings on board
            for( var i in gamedatas.buildings )
            {
                this.placeBuildingOnBoard( gamedatas.buildings[i] );
            }
            
            // Place my buildings
            for( i in gamedatas.mybuildings )
            {
                this.placeBuildingOnMyBuilding( gamedatas.mybuildings[i] );
            }
            
            // Place civilization cards on board
            for( i in gamedatas.cards )
            {
                this.placeCardOnBoard( gamedatas.cards[i] );
            }
            
            // Place my civilization cards
            for( i in gamedatas.mycards )
            {
                this.placeCardOnMyCards( gamedatas.mycards[i] );
            }
            
            // Resources
            for( player_id in gamedatas.resource )
            {
                for( var resource_id in gamedatas.resource[ player_id ] )
                {
                    $('resource_'+resource_id+'_'+player_id).innerHTML = gamedatas.resource[ player_id ][ resource_id ];
                } 
            }
            
            // Tools
            for( var tool_index in gamedatas.tools )
            {
                var tool = gamedatas.tools[tool_index];
                this.insertOrUpdateTool( tool );
            }
            
            // Dices
            this.dices = {};
            for( i=0;i<7;i++ )
            {
                this.dices[i] = new ebg.dices();
                this.dices[i].create( 'dice_'+i, 1, 6 );
            }
            
            // Game progression
            $('remaining_cards').innerHTML = this.gamedatas.remaining_cards;
            for( var building_stack_id in this.gamedatas.building_stack )
            {
                $('building_stack_'+building_stack_id).innerHTML = this.gamedatas.building_stack[building_stack_id];
            }
            
            this.setFirstPlayer( gamedatas.firstplayer );
                       
            // Tooltips
            
            this.addTooltipHtml( 'zone_resource_0', this.formatZoneTooltip( 'hunting grounds', _('food'), 2 ) );
            this.addTooltipHtml( 'zone_resource_1', this.formatZoneTooltip( 'forest', _('wood'), 3 ) );
            this.addTooltipHtml( 'zone_resource_2', this.formatZoneTooltip( 'clay pit', _('brick'), 4 ) );
            this.addTooltipHtml( 'zone_resource_3', this.formatZoneTooltip( 'quarry', _('stone'), 5 ) );
            this.addTooltipHtml( 'zone_resource_4', this.formatZoneTooltip( 'river', _('gold'), 6 ) );


            this.addTooltip( 'zone_tool_0', _('Tool maker (new tool)'), '' );
            this.addTooltip( 'zone_hut_0', _('Hut (1 new people)'), '' );
            this.addTooltip( 'zone_field_0', _('Field (increase agriculture level)'), '' );
            
            this.addTooltipToClass( 'ttworker', _('Number of people available'), '' );
            this.addTooltipToClass( 'ttworkertotal', _('Total number of people'), '' );
            this.addTooltipToClass( 'ttfood', _('Food stock'), '' );
            this.addTooltipToClass( 'ttagri', _('Agriculture level'), '' );
            this.addTooltip( 'firstplayer', _('First player'), '' );
            this.addTooltipToClass( 'ttwood', _('Wood stock'), '' );
            this.addTooltipToClass( 'ttclay', _('Brick stock'), '' );
            this.addTooltipToClass( 'ttstone', _('Stone stock'), '' );
            this.addTooltipToClass( 'ttgold', _('Gold stock'), '' );
            this.addTooltipToClass( 'ttbuilding', _('Number of building built'), '' );
            
            this.setupNotifications();
            
            dojo.connect(window, "onresize", this, dojo.hitch( this, 'adaptInterface' ));
            this.adaptInterface();
            
        },
        
        adaptInterface: function()
        {
            // Adapt interface depending on available screen size
            var page = dojo.position( 'page-content' );

            if( page.w > 950 )
            {
                // Very large screen
                // => use all available space
                
                dojo.addClass( 'board_plus_cards', 'large_screen' );
            }
            else
            {
                dojo.removeClass( 'board_plus_cards', 'large_screen' );
            }
        },
        
        formatZoneTooltip: function( title, resource, qt )
        {
            var html = '';
            
            html += '<h3>'+ucFirst(title)+'</h3>';
            html += '<hr/>';
            
            html += dojo.string.substitute( _("You can place people here to produce ${ress}."), { ress: '<b>'+ resource+'</b>' } );
            html += '<br/>';
            html += _("Each people gives you 1 die.");
            html += '<br/>';
            html += dojo.string.substitute( _("Sum of the dice / ${divider} = the ${ress} you get"), { divider: '<b>'+ qt+'</b>', ress:  resource } );
            
            return html;
        },

      

        ///////////////////////////////////////////////////
        //// Game & client states
        
        onEnteringState: function( stateName, args )
        {
           console.log( 'Entering state: '+stateName );
            
            switch( stateName )
            {
              
            case 'newTurn':
                this.nbr_workers_resource_source = {0:0,1:0,2:0,3:0,4:0};   // Reset (no workers on the board)
                break;
                
            case 'placeWorkers':
                this.current_zone_accumulation = null;
                this.current_zone_accumulation_nbr = 0;
                break;
                
            case 'resourceProduction':
                dojo.style( $('diceboard'), 'display', 'none' );
                dojo.fx.wipeIn( { node:'diceboard', duration: 750 } ).play();
                dojo.style( $('dice_result'), 'display', 'inline-block' );
                dojo.style( $('available_tools'), 'display', 'block' );
                dojo.style( $('diceChoice'), 'display', 'none' );  
                this.setupDices( args.args.dices );
                this.updateToolsForUse( args.args.tools );
                if( this.isCurrentPlayerActive() )
                {   this.addActionButton( 'action_accept', _("I'm fine"), 'onAcceptProduction' );   }
                break;
                
            case 'useWorkers':
                if( this.isCurrentPlayerActive() )
                {   this.addActionButton( 'action_cancelall', _("Don't use remaining workers"), 'onCancelAllWorkers' );   }
                break;
                
            case 'feedWorkers':
                if( this.isCurrentPlayerActive() )
                {
                }            
                break;
            case 'diceChoice':
                if( dojo.style( $('diceboard'), 'display' ) == 'none' )
                {
                    dojo.style( $('diceboard'), 'display', 'none' );
                    dojo.fx.wipeIn( { node:'diceboard', duration: 750 } ).play();
                }
                dojo.style( $('dice_result'), 'display', 'none' );
                dojo.style( $('available_tools'), 'display', 'none' );
                dojo.style( $('diceChoice'), 'display', 'block' );
                
                this.setupDices( args.args );

                break;
                
            }
        },
        onLeavingState: function( stateName )
        {
            console.log( 'Leaving state: '+stateName );
             
            switch( stateName )
            {
            case 'resourceProduction':
                dojo.fx.wipeOut( { node:'diceboard', duration: 750 } ).play();
                break;
            case 'diceChoice':
                // Remove the panel if there was only 1 choice remaining
                console.log( 'hiding diceChoice ?' );
                console.log( this.gamedatas.gamestate );
                if( this.getObjectLength( this.gamedatas.gamestate.args.dices ) == 1 )
                {
                    dojo.fx.wipeOut( { node:'diceboard', duration: 750 } ).play();
                }
                break;
            case 'dummy':
                break;
            }                
        }, 

        onUpdateActionButtons: function( stateName, args )
        {
           console.log( 'onUpdateActionButtons: '+stateName );
            
            switch( stateName )
            {
            case 'feedWorkers':
                if( this.isCurrentPlayerActive() )
                {   
                    // Show only if not visible at now
                    console.log( this.resourceChoiceDlg );
                    
                    if( $('resourceChoiceDlg' ) )
                    {   // Already instanciated
                    }
                    else
                    {
                        var ress_nbr = args[ this.player_id ];
                        this.showResourceChoiceDialog({
                                            choice_type: 'feed',
                                            ress_nbr_max: ress_nbr,
                                            ress_nbr_min: ress_nbr,
                                            ress_diff: null
                                        });                    
                    }
                    
                    this.addActionButton( 'dontwanttofeed', _("I don't want to (10pts penalty)"),  'onDontFeedWorkers' );
                }
                break;
            case 'dummy':
                break;
               
            }
        },
        

        ///////////////////////////////////////////////////
        //// Utility functions
        
        // Place workers of the given player in specified zone
        //  nbr = number of workers
        // workers are slided from player panel and player workers number is updated
        // exact place of workers is determined thanks to this.nbr_workers_resource_source
        placeWorkersOnZone: function( zone_category_id, zone_id, player_id, nbr )
        {
            console.log( "placeWorkersOnZone "+zone_category_id+" "+zone_id+" "+player_id+" x"+nbr );
            
            var baseno = 0;
            if( zone_category_id == 'resource' )
            {
                baseno = toint( this.nbr_workers_resource_source[ zone_id ] );
            }
            
            for( var no=baseno; no<(baseno + toint(nbr) ); no++ )
            {
                var target = $( 'workerplace_'+zone_category_id+"_"+zone_id+"_"+no );
                console.log( 'workerplace_'+zone_category_id+"_"+zone_id+"_"+no );
            
                dojo.place( this.format_block('jstpl_worker', {
                    zone_category:zone_category_id,
                    zone_id:zone_id,
                    player:player_id,
                    no:no,
                    color:this.gamedatas.players[player_id].color
                } ), target );
                
                $('workernbr_'+player_id).innerHTML = toint( $('workernbr_'+player_id).innerHTML )-1;
                this.placeOnObject( $('worker_'+zone_category_id+"_"+zone_id+"_"+no), $('player_board_'+player_id ) );
                this.slideToObject( $('worker_'+zone_category_id+"_"+zone_id+"_"+no), target ).play();
            }         

            if( zone_category_id == 'resource' )
            {
                this.nbr_workers_resource_source[ zone_id ]+=toint( nbr );
            }            
        },
        
        // Remove workers from specified zone
        removeWorkersFromZone: function( zone_category_id, zone_id, player_id )
        {
            console.log( "removeWorkersFromZone "+zone_category_id+" "+zone_id+" "+player_id );
            
            var zone_string = 'worker_'+zone_category_id+'_'+zone_id;
            
            // Get the workers div and slide them to player's panel
            dojo.query( '.worker_'+player_id ).forEach( dojo.hitch( this, function( node ) {
                console.log( node.id );
                
                if( node.id.indexOf( zone_string ) != -1 )
                {
                    console.log( 'found a worker to remove !' );

                    // Move worker to player panel and destroy it                    
                    var anim = this.slideToObject( node, $('player_board_'+player_id ) );
                    var destroyOnEnd = function( node ) { console.log( "destroying" ); console.log( node ); dojo.destroy( node );   };
                    dojo.connect( anim, 'onEnd', destroyOnEnd );
                    anim.play();

                    $('workernbr_'+player_id).innerHTML = toint( $('workernbr_'+player_id).innerHTML )+1;
                    
                    if( zone_category_id == 'resource' )
                    {   this.nbr_workers_resource_source[ zone_id ]--; }
                }
                
            } ) );

        },
        
        // Place building on some available location on board
        placeBuildingOnBoard: function( building )
        {
            console.log( 'placeBuildingOnBoard' );
            console.log( building );
            
            // Find a free location
            var target_place_id = building.location_arg;
            
            var type = building.type;
            var back_x = -( toint( type )-1 )*70;
            dojo.place( this.format_block('jstpl_building', {
                    id:building.id,
                    back_x:back_x
                } ), $('buildingzone_'+target_place_id) );
                
            this.building_id_to_type[ building.id ] = building.type;
            this.building_location_to_id[ building.location_arg ] = building.id;

            this.addTooltip( 'zone_building_'+building.location_arg, this.gamedatas.building_types[type].description, '' );
        },
        
        // Place building on "my building" place
        placeBuildingOnMyBuilding: function( building )
        {
            console.log( 'placeBuildingOnMyBuilding' );
            console.log( building );

            dojo.place( '<div class="building_wrap" id="building_wrap_'+building.id+'"></div>', $('mybuilding' ) );
            
            var type = building.type;
            var back_x = -( toint( type )-1 )*70;
            dojo.place( this.format_block('jstpl_building', {
                    id:building.id,
                    back_x:back_x
                } ), $('building_wrap_'+building.id) );

            this.building_id_to_type[ building.id ] = building.type;
            this.building_location_to_id[ building.location_arg ] = building.id;
        },
       
        // Place civilization card on corresponding location on board
        placeCardOnBoard: function( card )
        {
            console.log( 'placeCardOnBoard' );
            console.log( card );
            
            var type = card.type;
            var back_x = -( toint( type )-1 )*85;
            dojo.place( this.format_block('jstpl_card', {
                    id:card.id,
                    back_x:back_x
                } ), $('cardzone_'+card.location_arg) );

            this.addTooltip( 'zone_card_'+card.location_arg, '<b>'+_('Immediately:') +'</b> ' + this.gamedatas.card_types[type].bonus.description+'<br/><b>'+_('At the end of the game:')+'</b> '+this.gamedatas.card_types[type].score.description, '' );
        },

        // Place civilization card on "my cards" location
        placeCardOnMyCards: function( card, from )
        {
            console.log( 'placeCardOnMyCards' );
            console.log( card );
        
            var type = card.type;
            var back_x = -( toint( type )-1 )*85;
            
            var bonus_type = this.gamedatas.card_types[ type ].score.type;

            dojo.place( this.format_block('jstpl_mycardwrap', {
                    id:card.id
                } ), $('mycardsplace_'+bonus_type) );

            dojo.place( this.format_block('jstpl_card', {
                    id:card.id,
                    back_x:back_x
                } ), $('mycardwrap_'+card.id) );
            
            if( typeof from != 'undefined' )
            {
                this.placeOnObject( $('card_'+card.id), from );
                this.slideToObject( $('card_'+card.id), $('mycardwrap_'+card.id) ).play();
            }

            this.addTooltip( 'card_'+card.id, '<b>'+_('At the end of the game:')+'</b> '+this.gamedatas.card_types[type].score.description, '' );            
        },
        
        setFirstPlayer: function( player_id )
        {
            console.log( 'setFirstPlayer' );
            
            var firstplayer = $('firstplayer');
            if( ! firstplayer )
            {
                dojo.place( this.format_block('jstpl_firstplayer', {} ), $('firstplayer_'+player_id) );
            }
            else
            {
                firstplayer = this.attachToNewParent( firstplayer, $('firstplayer_'+player_id) );
                this.slideToObject( firstplayer, $('firstplayer_'+player_id) ).play();
                this.addTooltip( 'firstplayer', _('First player'), '' );
            }
        },
        
        setupDices: function( dices )
        {
            console.log( 'setupDices' );
            console.log( dices );
            console.log( this.dices );

            dojo.empty( 'diceChoice' );
            
            for( var i=0;i<7; i++ )
            {
                if( dices.dices[i] )
                {
                    dojo.style( 'dice_'+i, 'display', 'inline-block' );
                    this.dices[i].setValue( [dices.dices[i]] );                
                }
                else
                {
                    dojo.style( 'dice_'+i, 'display', 'none' );                
                }
            }
                        
            $('dicesum').innerHTML = dices.sum;   
            
            if( dices.diviser )
            {          
                // This is a "resource production" type dice set
                  
                if( toint( dices.toolBonus ) === 0 )
                {   dojo.style( $('tool_bonus_wrap'), 'display', 'none' );    }
                else
                {   
                    dojo.style( $('tool_bonus_wrap'), 'display', 'inline' );    
                    $('tool_bonus').innerHTML = dices.toolBonus;
                }

                // Update resource type & divisor
                var ress_type = dices.resource_id;
                var back_pos = "-"+(ress_type*30)+"px 0px";
                dojo.style( $('resourceicon'), 'backgroundPosition', back_pos );
                
                $('resourcedivisor').innerHTML = dices.diviser;
                $('resourceresult').innerHTML = Math.floor( dices.sum/dices.diviser );
            }
            else
            {
                // This is a "dice choice" type dice set
                
                // Add corresponding dices choice

                for( i=0;i<7; i++ )
                {
                    if( dices.dices[i] )
                    {
                        var diceValue = dices.dices[i];
                        var ress_name = '';
                        switch( toint( diceValue ) )
                        {
                        case 1: ress_name='wood';
                                break;                        
                        case 2: ress_name='clay';
                                break;                        
                        case 3: ress_name='stone';
                                break;                        
                        case 4: ress_name='gold';
                                break;                        
                        case 5: ress_name='tool';
                                break;                        
                        case 6: ress_name='agri';
                                break;                        
                        }
                        
                        dojo.place( this.format_block('jstpl_diceChoice_item', {
                                diceid: i,
                                ress_name: ress_name
                            } ), $('diceChoice') );
                        
                        dojo.connect( $('diceChoice'+i), 'onclick', this, 'onChooseItem' );
                    }
                }
            }
            
        },
        
        // Insert or update this tool in player panel
        insertOrUpdateTool: function( tool )
        {
            console.log( 'insertOrUpdateTool' );
            console.log( tool );
            
            if( $('tool_'+tool.id ) )
            {
                // This tool already exist => make an update by a change of class
                dojo.removeClass( 'tool_'+tool.id ); 
                dojo.addClass( 'tool_'+tool.id, ['sa_icon','panelicon_tool'+tool.uniq+tool.level+tool.used] );
            }
            else
            {
                // This tool does not exist => create it
                var player_id = tool.player;
                if( toint( tool.uniq ) === 0 )
                {   dojo.place( this.format_block('jstpl_toolpanel', tool ), $('toolzone_'+player_id) );    }
                else
                {   dojo.place( this.format_block('jstpl_toolpanel', tool ), $('uniqtoolzone_'+player_id) );    }
            }
        },
        
        // Update "usetoolzone" and "useuniqtoolzone" layers
        // with tools given in parameter
        updateToolsForUse: function( tools )
        {
            console.log( 'updateToolsForUse' );
            
            dojo.empty( 'usetoolzone' );
            dojo.empty( 'useuniqtoolzone' );
            
            // We do the update only in "resourceProduction" state
            if( this.gamedatas.gamestate.name == 'resourceProduction' )
            {
                var active_player = this.gamedatas.gamestate.active_player;
                
                for( var i in tools )
                {
                    var tool = tools[i];
                    if( tool.player == active_player )
                    {
                        // This tool should be displayed
                        if( toint( tool.uniq ) === 0 )
                        {   dojo.place( this.format_block('jstpl_toolforuse', tool ), $('usetoolzone') );    }
                        else
                        {   dojo.place( this.format_block('jstpl_toolforuse', tool ), $('useuniqtoolzone') );    }
                        dojo.connect( $('usetool_'+tool.id), 'onclick', this, 'onUseTool' );
                        this.addTooltip( 'usetool_'+tool.id, '', _('Use this tool to increase the production') );
                    }
                }   
            }
            
        },

        ///////////////////////////////////////////////////
        //// Resource choice dialog
                
        showResourceChoiceDialog: function( args )
        {
            console.log( 'showResourceChoiceDialog' );
            console.log( args );
            this.resourceChoiceArgs = args;
            
            dojo.destroy( 'resourceChoiceDlg' );
            
            var ress_nbr = args.ress_nbr_min+'-'+args.ress_nbr_max;
            if( args.ress_nbr_min == args.ress_nbr_max )
            {   ress_nbr = ''+args.ress_nbr_min;   }

            var title = dojo.string.substitute( _('You must select ${ress_nbr} resources'), {ress_nbr:ress_nbr} );
            
            if( args.choice_type == 'feed' )
            {   title += ' '+ _('to feed your people');  }
            if( args.choice_type == 'pick' )
            {   title = dojo.string.substitute( _('You must choose ${ress_nbr} resources'), {ress_nbr:ress_nbr} );  }

            if( args.ress_diff !== null  )
            {
                if( toint( args.ress_diff ) == 1 )
                {   title += _(' (of the same type)');   }
                else
                {   title += dojo.string.substitute( _(' (of ${diff} different types)'), {diff:args.ress_diff} );   }
            }

            this.resourceChoiceDlg = new dijit.Dialog({ title: title });

            var cancellabel = _('Cancel');
            if( args.choice_type == 'feed' )
            {   cancellabel = _("I don't want to (10pts penalty)");  }
            var html = dojo.string.substitute( jstpl_resourceChoiceDlg, {confirm_label:_('Confirm'),cancel_label:cancellabel} );
             
            this.resourceChoiceDlg.attr("content", html );
            this.resourceChoiceDlg.show();            

            for( var resource_id=1;resource_id<=4; resource_id++ )
            {
                if( ( toint( $('resource_'+resource_id+'_'+this.player_id).innerHTML ) > 0 ) || (args.choice_type == 'pick') )
                {
                    dojo.place( this.format_block( 'jstpl_resourceChoiceDlg_items', {id:resource_id,ress_name:this.resource_names[resource_id]}), 'choiceitems' );
                }
            }

            dojo.query(".count_increment").connect( "onclick", this, "onIncrementRessChoiceCounter" );
            dojo.query(".count_decrement").connect( "onclick", this, "onDecrementRessChoiceCounter" ); 
            dojo.connect( $('cancel_choice'), 'onclick', this, function(evt){
                evt.preventDefault();
                this.resourceChoiceDlg.hide();
                
                if( args.choice_type == 'feed' )
                {
                    this.ajaxcall( '/stoneage/stoneage/dontfeedworkers.html', { lock:true }, this, function( result ) {} );
                }
                
                dojo.destroy( 'resourceChoiceDlg' );
            } );
            dojo.connect( $('confirm_choice'), 'onclick', this, 'onRessChoiceConfirm' );
            this.checkRessChoiceValidity();
        },
        onIncrementRessChoiceCounter: function( evt )
        {
            console.log( 'onIncrementRessChoiceCounter' );
            evt.preventDefault();
            var resource_id = evt.currentTarget.id.substr( 11 );
            
            var newCount = toint( $('count_'+resource_id).innerHTML )+1;
            if( newCount <= toint( $('resource_'+resource_id+'_'+this.player_id ).innerHTML ) || (this.resourceChoiceArgs.choice_type == 'pick') ) // If enough resources
            {
                $('count_'+resource_id).innerHTML = newCount;
            }
            else
            {
                this.showMessage( _('Not enough resource'), 'error' );
            }
            this.checkRessChoiceValidity();
        },
        onDecrementRessChoiceCounter: function( evt )
        {
            console.log( 'onDecrementRessChoiceCounter' );
            evt.preventDefault();
            var resource_id = evt.currentTarget.id.substr( 11 );
            
            var newCount = toint( $('count_'+resource_id).innerHTML )-1;
            if( newCount >= 0 )
            {
                $('count_'+resource_id).innerHTML = newCount;
            }
            this.checkRessChoiceValidity();            
        },
        // Check if current resource choice is valid and enable/disable confirm choice button
        checkRessChoiceValidity: function()
        {
            console.log( 'checkRessChoiceValidity' );
            var nbr_diff = 0;
            var nbr_total = 0;
            for( var resource_id=1;resource_id<=4;resource_id++ )
            {
                if( $('count_'+resource_id) )
                {
                    var count = toint( $('count_'+resource_id).innerHTML );
                    if( count > 0 )
                    {   nbr_diff ++;    }
                    nbr_total += count;
                }
            }
            
            console.log( 'nbr_total='+nbr_total+', nbr_diff='+nbr_diff );
            console.log( this.resourceChoiceArgs );
            
            dojo.style( 'confirm_choice', 'display', 'none' );
            if( nbr_total >= this.resourceChoiceArgs.ress_nbr_min && nbr_total <= this.resourceChoiceArgs.ress_nbr_max )
            {
                console.log( 'total match' );
                if( this.resourceChoiceArgs.ress_diff === null || this.resourceChoiceArgs.ress_diff === nbr_diff )
                {   
                    console.log( 'diff match' );
                    dojo.style( 'confirm_choice', 'display', 'inline' ); 
                }
            }
        },
        onRessChoiceConfirm: function( evt )
        {
            console.log( 'onRessChoiceConfirm' );
            evt.preventDefault();

            var resources = '';
            for( var resource_id=1;resource_id<=4;resource_id++ )
            {
                if( $('count_'+resource_id) )
                {
                    var count = toint( $('count_'+resource_id).innerHTML );
                    resources += resource_id+','+count+';';
                }
            }
            
            if( this.resourceChoiceArgs.choice_type == 'building' || this.resourceChoiceArgs.choice_type == 'card' )
            {
                // Use workers that are here with these resources
                this.ajaxcall( '/stoneage/stoneage/useWorkers.html', { 
                    zone_category: this.resourceChoiceArgs.zone_category,
                    zone_id: this.resourceChoiceArgs.zone_id,
                    resources: resources,
                    lock:true 
                }, this, function( result ) {} );
            }
            else if( this.resourceChoiceArgs.choice_type == 'feed' )   
            {
                // Feed player's people with resources
                this.ajaxcall( '/stoneage/stoneage/feedWorkers.html', { 
                    resources: resources,
                    lock:true 
                }, this, function( result ) {} );
            }
            else if( this.resourceChoiceArgs.choice_type == 'pick' )  
            {
                // Pick 2 free resources
                this.ajaxcall( '/stoneage/stoneage/pickResource.html', { 
                    resources: resources,
                    lock:true 
                }, this, function( result ) {} );
            }
            this.resourceChoiceDlg.hide(); 
            dojo.destroy( 'resourceChoiceDlg' );             
        },
        
        ///////////////////////////////////////////////////
        //// UI Events

        
        onClickOnZone: function( evt )
        {
            console.log( 'onClickOnZone' );
            evt.preventDefault();
            
            var zone_ids = evt.currentTarget.id.split( '_' );
            if( zone_ids.length == 3 )
            {
                var zone_category = zone_ids[1];
                var zone_id = zone_ids[2];
                
                if( this.checkAction( 'placeWorkers', true ) )
                {
                    // Place some workers here
                    var available = this.gamedatas.gamestate.args[ zone_category ][ zone_id ];
                    
                    if( available <= 0 )
                    {
                        this.showMessage( _("This place is no more available"), 'error' );
                        return ;
                    }
                    
                    if( zone_category == 'resource' )
                    {
                        // Specific case: several workers can be placed here
                        // Check first that we have enough workers available
                        if( toint( $('workernbr_'+this.player_id).innerHTML ) !== 0 )
                        {
                            if( this.current_zone_accumulation === null )
                            {
                               // New zone to accumulate workers
                               this.current_zone_accumulation = zone_id;
                               this.current_zone_accumulation_nbr = 0;
                               this.addActionButton( 'action_confirm', _('Confirm workers placement'), 'onConfirmWorkers' );
                               this.addActionButton( 'action_cancel', _('Cancel'), 'onCancelWorkers', null, null, 'gray' );               
                               
                            }
                            else if( zone_id != this.current_zone_accumulation )
                            {
                                this.showMessage( _("You must continue to put workers where you started"), 'error' );
                                return ;
                            }
                            
                            this.current_zone_accumulation_nbr++;

                            // Put some worker here, directly
                            this.placeWorkersOnZone( 'resource', zone_id, this.player_id, 1 );
                        }
                    }
                    else
                    {
                        if( this.current_zone_accumulation !== null )
                        {
                                this.showMessage( _("You must continue to put workers where you started"), 'error' );
                                return ;
                        }
                        
                        // In other cases: immediately request the placement of a worker here
                        this.ajaxcall( '/stoneage/stoneage/placeWorkers.html', { 
                            zone_category: zone_category,
                            zone_id: zone_id,
                            nbr: (zone_category=='hut') ? 2 : 1,     // 2 for hut, 1 in any other cases
                            lock:true 
                        }, this, function( result ) {} );                        
                    }
                }
                else if( this.checkAction( 'useWorkers' ) )
                {        
                    var workerplace_id;     
                    var workers;       
                    if( zone_category == 'building' )
                    {
                        console.log( 'useWorkers on building of type' );

                        workerplace_id = 'workerplace_building_'+zone_id+"_0";
                        workers = dojo.query( '#'+workerplace_id+' .worker_'+this.player_id );
                        if( workers.length > 0 )
                        {                        
                            var building_id = this.building_location_to_id[ zone_id ];
                            var building_type_id = this.building_id_to_type[ building_id ];
                            var building_type = this.gamedatas.building_types[ building_type_id ];
                            console.log( building_type );
                            
                            if( toint( building_type.points ) === 0 )
                            {
                                // For this building, we must choose resources !
                                if( building_type.cost.any )
                                {
                                    this.showResourceChoiceDialog({
                                        choice_type: 'building',
                                        zone_category: zone_category,
                                        zone_id: zone_id,
                                        ress_nbr_max: 7,
                                        ress_nbr_min: 1,
                                        ress_diff: null
                                    });
                                    return;                            
                                }
                                else
                                {
                                    this.showResourceChoiceDialog({
                                        choice_type: 'building',
                                        zone_category: zone_category,
                                        zone_id: zone_id,
                                        ress_nbr_max: building_type.cost.nbr,
                                        ress_nbr_min: building_type.cost.nbr,
                                        ress_diff: building_type.cost.nbr_diff
                                    });
                                    return;
                                }
                                                       
                            }
                        }
                         
                    }
                    if( zone_category == 'card' )
                    {
                        console.log( 'useWorkers on civilization card '+zone_id );
                        
                        // Check there is a worker of this player there
                        workerplace_id = 'workerplace_card_'+zone_id+"_0";
                        workers = dojo.query( '#'+workerplace_id+' .worker_'+this.player_id );
                        if( workers.length > 0 )
                        {                        
                            var card_cost = toint( zone_id )+1;
                            this.showResourceChoiceDialog({
                                choice_type: 'card',
                                zone_category: zone_category,
                                zone_id: zone_id,
                                ress_nbr_max: card_cost,
                                ress_nbr_min: card_cost,
                                ress_diff: null
                            });
                        }
                        return;                    
                    }
                
                
                    // Use workers that are here
                    this.ajaxcall( '/stoneage/stoneage/useWorkers.html', { 
                        zone_category: zone_category,
                        zone_id: zone_id,
                        lock:true 
                    }, this, function( result ) {} );                        
                }
            }
            else
            {   console.error( 'bad zone id: '+evt.currentTarget.id );  }
        },
        
        onConfirmWorkers: function()
        {
            console.log( 'onConfirmWorkers' );

            this.ajaxcall( '/stoneage/stoneage/placeWorkers.html', { 
                zone_category: 'resource',
                zone_id: this.current_zone_accumulation,
                nbr: this.current_zone_accumulation_nbr,     // 2 for hut, 1 in any other cases
                lock:true 
            }, this, function( result ) {} );

            this.removeActionButtons();        
        },
        
        onCancelWorkers: function()
        {
            console.log( 'onCancelWorkers' );
            
            this.removeWorkersFromZone( 'resource', this.current_zone_accumulation, this.player_id );
            this.current_zone_accumulation = null;
            this.current_zone_accumulation_nbr = 0;
            this.removeActionButtons();
        },
        
        onAcceptProduction: function()
        {
            console.log( 'onAcceptProduction' );
            if( this.checkAction( 'acceptProduction' ) )
            {
                this.ajaxcall( '/stoneage/stoneage/acceptProduction.html', { lock:true }, this, function( result ) {} );
            }
        },
        
        onCancelAllWorkers: function()
        {
            console.log( 'onCancelAllWorkers' );
            if( this.checkAction( 'cancelAllWorkers' ) )
            {
                dojo.destroy( 'CancelAllWorkers_dialog' );
                var cancelDlg = new dijit.Dialog({ title: _('Are you sure ?') });

                var html = "<div id='CancelAllWorkers_dialog'>";
                html += _("Are you sure you don't want to use remaining people ?");
                html += "<br/><br/>";
                html += "<a class='bgabutton bgabutton_blue' id='confirm_btn' href='#'><span>"+_("I confirm")+"</span></a> ";
                html += "<a class='bgabutton bgabutton_blue' id='infirm_btn' href='#'><span>"+_("Please, no")+"</span></a>";
                html += "</div>";

                cancelDlg.attr("content", html );
                cancelDlg.show();

                dojo.connect( $('confirm_btn'), 'onclick', this, function( evt )
                {
                    evt.preventDefault();
                    cancelDlg.hide();
                    this.ajaxcall( '/stoneage/stoneage/cancelAllWorkers.html', { lock:true }, this, function( result ) {} );
                } );
                dojo.connect( $('infirm_btn'), 'onclick', this, function( evt )
                {
                    evt.preventDefault();
                    cancelDlg.hide();
                } );
            }
        },
        
        onDontFeedWorkers: function()
        {
            console.log( 'onDontFeedWorkers' );
            if( this.checkAction( 'feedWorkers' ) )
            {
                this.ajaxcall( '/stoneage/stoneage/dontfeedworkers.html', { lock:true }, this, function( result ) {} );
            }        
        },
        
        onUseTool: function( evt )
        {
            if( this.checkAction( 'useTools' ) )
            {
                console.log( 'onUseTool' );
                evt.preventDefault();
                var tool_id = evt.currentTarget.id.substr(8);
                console.log( 'use tool '+tool_id );
                this.ajaxcall( '/stoneage/stoneage/useTool.html', { lock:true,tool:tool_id }, this, function( result ) {} );
            }
        },

        onChooseItem: function( evt )
        {
            console.log( 'onChooseItem' );
            evt.preventDefault();
            if( this.checkAction( 'chooseItem' ) )
            {
                var item_id = evt.currentTarget.id.substr( 10 );
                console.log( item_id );
                this.ajaxcall( '/stoneage/stoneage/diceChoice.html', { lock:true,dice:item_id }, this, function( result ) {} );
            }
        },
        
        onTakeResources: function( evt )
        {
            console.log( 'onTakeResources' );
            evt.preventDefault();
            
            this.showResourceChoiceDialog({
                choice_type: 'pick',
                ress_nbr_max: 2,
                ress_nbr_min: 2,
                ress_diff: null
            });                     
        },
        
        ///////////////////////////////////////////////////
        //// Reaction to cometD notifications

        setupNotifications: function()
        {
            console.log( 'notifications subscriptions setup' );
           
            dojo.subscribe( 'placeWorkers', this, "notif_placeWorkers" );
            dojo.subscribe( 'useWorkers', this, "notif_useWorkers" );
            dojo.subscribe( 'firstPlayer', this, "notif_firstPlayer" );
            dojo.subscribe( 'additionalWorker', this, "notif_additionalWorker" );
            dojo.subscribe( 'increaseAgriculture', this, "notif_increaseAgriculture" );
            dojo.subscribe( 'resourceStockUpdate', this, "notif_resourceStockUpdate" );
            dojo.subscribe( 'buyBuilding', this, "notif_buyBuilding" );
            dojo.subscribe( 'newBuildings', this, "notif_newBuildings" );
            dojo.subscribe( 'toolUpdate', this, "notif_toolUpdate" );
            dojo.subscribe( 'useTool', this, "notif_useTool" );
            dojo.subscribe( 'foodPenalty', this, "notif_foodPenalty" );
            dojo.subscribe( 'nofoodPenalty', this, "notif_nofoodPenalty" );
            dojo.subscribe( 'cardsUpdate', this, "notif_cardsUpdate" );
            dojo.subscribe( 'scorePoint', this, "notif_scorePoint" );
            dojo.subscribe( 'buyCard', this, "notif_buyCard" );
            dojo.subscribe( 'drawCard', this, "notif_drawCard" );
            dojo.subscribe( 'playerdrawCard', this, "notif_playerdrawCard" );
            dojo.subscribe( 'canPickResource', this, "notif_canPickResource" );
            dojo.subscribe( 'usePickResources', this, "notif_usePickResources" );
            dojo.subscribe( 'finalScoring', this, "notif_finalScoring" );
            dojo.subscribe( 'sendPlayerItem', this, "notif_sendPlayerItem" );
            
        },  
 
        notif_placeWorkers: function( notif )
        {
            console.log( 'notif_placeWorkers' );
            console.log( notif );
            
            if( this.current_zone_accumulation === null )
            {
                this.placeWorkersOnZone( notif.args.zone_category_id, notif.args.zone_id, notif.args.player_id, notif.args.nbr );
            }
            else
            {
                // In this case, we already placed workers during order creation
                this.current_zone_accumulation = null;
                this.current_zone_accumulation_nbr = 0;            
            }
        },
        notif_useWorkers: function( notif )
        {
            console.log( 'notif_useWorkers' );
            console.log( notif );
            
            this.removeWorkersFromZone( notif.args.zone_category_id, notif.args.zone_id, notif.args.player_id );
        },
        notif_firstPlayer: function( notif )
        {
            console.log( 'notif_firstPlayer' );
            console.log( notif );
            this.setFirstPlayer( notif.args.player_id );
        },
        notif_additionalWorker: function( notif )
        {
            console.log( 'notif_additionalWorker' );
            console.log( notif );
            
            $('workernbr_'+notif.args.player_id).innerHTML = toint( $('workernbr_'+notif.args.player_id).innerHTML )+1;
            $('workernbrtotal_'+notif.args.player_id).innerHTML = toint( $('workernbrtotal_'+notif.args.player_id).innerHTML )+1;
        },
        notif_increaseAgriculture: function( notif )
        {
            console.log( 'notif_increaseAgriculture' );
            console.log( notif );
            
            $('agri_'+notif.args.player_id).innerHTML = Math.min( 10, toint( $('agri_'+notif.args.player_id).innerHTML )+1 );
        },
        notif_resourceStockUpdate: function( notif )
        {
            console.log( 'notif_resourceStockUpdate' );
            console.log( notif );
            
            for( var resource_id in notif.args.delta )
            {
                var delta = notif.args.delta[ resource_id ];
                var stock_item = $('resource_'+resource_id+'_'+notif.args.player_id);
                stock_item.innerHTML = toint( stock_item.innerHTML ) + toint( delta );
            }
        },
        notif_buyBuilding: function( notif )
        {
            console.log( 'notif_buyBuilding' );
            console.log( notif );
            
            this.scoreCtrl[ notif.args.player_id ].incValue( notif.args.points );  
            
            $('buildingcount_'+notif.args.player_id).innerHTML = toint( $('buildingcount_'+notif.args.player_id).innerHTML ) + 1;
            
            $('building_stack_'+notif.args.building_stack).innerHTML = toint( $('building_stack_'+notif.args.building_stack).innerHTML )-1;
            
            if( notif.args.player_id == this.player_id )
            {
                // Move card to player hand
                dojo.place( '<div class="building_wrap" id="building_wrap_'+notif.args.building_id+'"></div>', $('mybuilding' ) );
                this.attachToNewParent( $('building_'+notif.args.building_id ), $('building_wrap_'+notif.args.building_id ) );
                this.slideToObject( $('building_'+notif.args.building_id ), $('building_wrap_'+notif.args.building_id ) ).play();
            }
            else
            {
                dojo.fadeOut({ node:"building_"+notif.args.building_id,
                               onEnd: function() {
                                dojo.destroy( 'building_'+notif.args.building_id );
                               }
                             }).play();                
            }
       },
       notif_newBuildings: function( notif )
       {
            console.log( 'notif_newBuildings' );
            console.log( notif );
        
            for( var i in notif.args )
            {
                this.placeBuildingOnBoard( notif.args[i] );
            }    
       },
       notif_toolUpdate: function( notif )
       {
            console.log( 'notif_toolUpdate' );
            console.log( notif );
            
            for( var i in notif.args.tools )
            {
                var tool = notif.args.tools[i];
                this.insertOrUpdateTool( tool );
            }
            
            this.updateToolsForUse( notif.args.tools );
       },
       notif_useTool: function( notif )
       {
            console.log( 'notif_useTool' );
            console.log( notif );
            
            this.setupDices( notif.args.argResourceProduction.dices );
            this.updateToolsForUse( notif.args.argResourceProduction.tools );

            // Report the update in player panel
            dojo.empty( 'uniqtoolzone_'+notif.args.player_id );
            for( var i in notif.args.argResourceProduction.tools )
            {
                var tool = notif.args.argResourceProduction.tools[i];
                this.insertOrUpdateTool( tool );
            }
       },
       notif_foodPenalty: function( notif )
       {
            console.log( 'notif_foodPenalty' );
            console.log( notif );
            
            var player_id = notif.args.player_id;
            
            // Reduce food stock to 0
            $('resource_0_'+player_id).innerHTML = '0';
            
            // 10pts penalty
            this.scoreCtrl[ player_id ].incValue( -10 );  
       },
       notif_nofoodPenalty: function( notif )
       {
            console.log( 'notif_foodPenalty' );
            console.log( notif );
            
            var player_id = notif.args.player_id;
            
            // Reduce food stock to 0
            $('resource_0_'+player_id).innerHTML = '0';
       },
       notif_cardsUpdate: function( notif )
       {
            console.log( 'notif_cardsUpdate' );
            console.log( notif );
            
            for( var i in notif.args )
            {
                var card = notif.args[i];
               
                if( $('card_'+card.id ) )
                {
                    console.log( 'moving card '+card.id+' to location '+card.location_arg );
                    // Card exists, just move it to a new location
                    var newcard = this.attachToNewParent( $('card_'+card.id ), $('cardzone_'+card.location_arg ) );
                    this.slideToObject( newcard, $('cardzone_'+card.location_arg ), 1000 ).play();
                    this.addTooltip( 'zone_card_'+card.location_arg, '<b>'+_('Immediately:') +'</b> ' + this.gamedatas.card_types[card.type].bonus.description+'<br/><b>'+_('At the end of the game:')+'</b> '+this.gamedatas.card_types[card.type].score.description, '' );
                }
                else
                {
                    // Card does not exists at now => simple creation
                    this.placeCardOnBoard( card );                    
                    $('remaining_cards').innerHTML = toint( $('remaining_cards').innerHTML )-1;
                }
            }
       },
       notif_scorePoint: function( notif )
       {
            console.log( 'notif_scorePoint' );
            console.log( notif );
            this.scoreCtrl[ notif.args.player_id ].incValue( notif.args.points );  
       },
       
       notif_buyCard: function( notif )
       {
            console.log( 'notif_buyCard' );
            console.log( notif );
            
            // Fade out this card and remove it from its place
            dojo.fadeOut({ node:"card_"+notif.args.card.id,
                               onEnd: function() {
                                dojo.destroy( 'card_'+notif.args.card.id );
                               }
                             }).play();
           
           if( notif.args.player_id == this.player_id )
           {
               this.placeCardOnMyCards( notif.args.card, $('cardzone_'+notif.args.zone_id ) );
           }
       },
       notif_drawCard: function( notif )
       {
            console.log( 'notif_drawCard' );
            console.log( notif );
                                        
            this.placeCardOnMyCards( notif.args );
       },
       notif_playerdrawCard: function( notif )
       {
            console.log( 'notif_playerdrawCard' );
            console.log( notif );
            
            $('remaining_cards').innerHTML = toint( $('remaining_cards').innerHTML )-1;
       },
       notif_canPickResource : function( notif )
       {
            console.log( 'canPickResource' );
            dojo.style( $('take_resources_'+this.player_id+'_wrap'), 'display', 'inline' );
       },
       notif_usePickResources : function( notif )
       {
            console.log( 'usePickResources' );
            if( notif.args.player_id == this.player_id )
            {   dojo.style( $('take_resources_'+this.player_id+'_wrap'), 'display', 'none' );   }
       },
       notif_finalScoring: function( notif )
       {
            console.log( 'notif_finalScoring' );
            console.log( notif );
            
            // Display final scoring dialog box
            dojo.destroy( 'finalScoringDlg' );
            var finalScoringDlg = new dijit.Dialog({ title: _('Final scoring') });
            var finalScoringContent = '';

            for( var player_id in notif.args )
            {
                var player = notif.args[ player_id ];
                finalScoringContent += dojo.string.substitute( jstpl_finalscoringLine, {
                    'name': this.gamedatas.players[ player_id ].name,
                    'playercolor': this.gamedatas.players[ player_id ].color,
                    'culture': dojo.string.substitute( jstpl_finalscoringCulture, player.culture ),
                    'building': dojo.string.substitute( jstpl_finalscoringItem, player.building ),
                    'tool': dojo.string.substitute( jstpl_finalscoringItem, player.tool ),
                    'people': dojo.string.substitute( jstpl_finalscoringItem, player.people ),
                    'agriculture': dojo.string.substitute( jstpl_finalscoringItem, player.agriculture ),
                    'resources': player.resources.points,
                    'total': player.total
                } );
            }

            var html = dojo.string.substitute( jstpl_finalscoring, { 
                finalScoringContent:finalScoringContent,
                close_label: _('Close')
            } );
             
            finalScoringDlg.attr("content", html );
            finalScoringDlg.show();            

            dojo.connect( $('closeScoring'), 'onclick', this, function(evt){
                evt.preventDefault();
                finalScoringDlg.hide();
            } );            
       },
       notif_sendPlayerItem: function( notif )
       {
            console.log( 'notif_sendPlayerItem' );
            console.log( notif );

            var player_panel = $('overall_player_board_'+notif.args.player_id );
            if( typeof notif.args.nbr == 'undefined' )
            {
                this.slideTemporaryObject( '<div class="sa_icon panelicon_'+notif.args.type+'"></div>', player_panel, $(notif.args.source), player_panel, 800, 800 );           
            }
            else
            {
                var delay = 200;
                for( var i=0;i<notif.args.nbr; i++ )
                {
                    this.slideTemporaryObject( '<div class="sa_icon panelicon_'+notif.args.type+'"></div>', player_panel, $(notif.args.source), player_panel, 800, delay );           
                    delay += 200;
                }
            }
       }
    });   
});


