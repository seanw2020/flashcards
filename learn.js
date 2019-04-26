//DEFINE STUFF
ezflashcards.learnSetup = function() {
	//killswitch
	//var leave = typeof ezflashcards.vars['learnSetupDone'] !== 'undefined' ? true : false;
	//if (leave)
	//	return;

	//load more cards in background
	ezflashcards.vars['prev'] = 0;
	ezflashcards.vars['next'] = 2;
	ezflashcards.vars['flip'] = 0;
	ezflashcards.vars['side'] = 'Q'; //Q or A
	
	ezflashcards.vars['stop'] = false; //stop loading cards in bg or no
	ezflashcards.vars['int']  = 0;
	ezflashcards.vars['play'] = false;
	ezflashcards.vars['cid']  = 0; //current id -- todo remove
	ezflashcards.vars['coll'] = true; //collapsed card or not
	ezflashcards.vars['plto'] = 0; //timeout int
	ezflashcards.vars['flto'] = 0;
	//ezflashcards.vars['thme'] = 0; //theming is done in settings.js 

	var ng = ezflashcards.vars['nbgd']; //notebook guid 

	ezflashcards.vars['curr'][ng] = 1;
	ezflashcards.vars['down'][ng] = parseInt($('#downloaded-'+ng).text()); //total downloaded cards
	ezflashcards.vars['totl'][ng] = parseInt($('#total-notes-'+ng).text());
	
	//shortcuts
	var c = {};
	
	ezflashcards.learnUpdateShortcuts(c);
	
	//initial settings
	$(c.c).removeClass('hidden');
	setTimeout(function(){$('#edit-guid').attr('href',$(c.e).attr('href'));},8000); //wait a few seconds for settings/options page to load
	$('.ui-collapsible-content').children().removeClass('ui-li-desc'); //collapsible adds this(?)
	
	// prev button
	$('#prev-' +ng).live('click', function() {
		ezflashcards.learnStopPlay();
		
		//hide others - useful after searches
		$('#learn-'+ng + ' #cardset-'+ng).children().addClass("hidden"); 
		
		//collapse previous
		$(c.c).trigger("collapse").addClass('hidden');

		//go back 1
		if (ezflashcards.vars['curr'][ng] - 1 < 1)
		 ezflashcards.vars['curr'][ng] = ezflashcards.vars['totl'][ng];
		else
		 ezflashcards.vars['curr'][ng] -= 1;
			
		ezflashcards.learnUpdateShortcuts(c);
		
		//show card
		$(c.c).removeClass('hidden');
		$(c.c).trigger("expand");
		ezflashcards.vars['coll'] = false;
		
		//update page
		$('#header-learn-'+ng).text(ezflashcards.vars['curr'][ng] + '/' + ezflashcards.vars['down'][ng]);
		$('#edit-guid').attr('href',$(c.e).attr('href'));
		ezflashcards.learnUpdateSkip();
		$(this).removeClass('ui-btn-active ui-state-persist');
		
		return false;
	});

	// flip button
	$('#flip-'+ng ).live('click', function(event, keepPlaying) {
		keepPlaying = typeof keepPlaying !== 'undefined' ? true : false;

		if (ezflashcards.vars['play']==true && keepPlaying==false)
			ezflashcards.learnStopPlay();

		ezflashcards.vars['coll'] = !ezflashcards.vars['coll']; //toggle
		ezflashcards.learnUpdateShortcuts(c);
		$(c.c).trigger((c.l) ? "collapse" : "expand"); //collapse or expand
		
		$(this).removeClass('ui-btn-active ui-state-persist');
		return false;
	});

	// next button
	$('#next-'+ ng).live('click', function(event, playing) {
		//http://stackoverflow.com/questions/894860/set-a-default-parameter-value-for-a-javascript-function
		playing = typeof playing !== 'undefined' ? true : false;
		
		//if next manually pressed, stop Auto
		if (!playing)
			ezflashcards.learnStopPlay();
		
		//hide others - useful after searches
		$('#learn-'+ng + ' #cardset-'+ng).children().addClass("hidden"); 
		
		//collapse previous
		$(c.c).trigger("collapse").addClass('hidden');
		ezflashcards.vars['coll'] = true;
		
		//advance 1
		ezflashcards.learnAdvance(ng);
		ezflashcards.learnUpdateShortcuts(c);
		
		//autoplay
		if (playing){
			ezflashcards.learnPlaySeek(c,ng);
		}

		ezflashcards.learnUpdatePage(c,ng);
		
		$(this).removeClass('ui-btn-active ui-state-persist');
		
		return false;
	});

	// play button
	$('#play-'+ng).live('click', function() {
		ezflashcards.vars['play'] = !ezflashcards.vars['play'];
		
		//hide all, advance to next wrong card, update page
		$('#learn-'+ng + ' #cardset-'+ng).children().addClass("hidden");
		ezflashcards.learnPlaySeek(c,ng);
		ezflashcards.learnUpdatePage(c,ng);
		
		if (ezflashcards.vars['play']==true) {
			//do first manually
			$(c.c).trigger('collapse'); //current card
			ezflashcards.vars['flto']=setTimeout(function(){$(c.c).trigger('expand');},4000);
			//ezflashcards.vars['flto']=setTimeout(function(){$(c.c).trigger('expand');},ezflashcards.vars['timr']/5);
			//ezflashcards.vars['flto']=setTimeout(function(){$('#next-'+ ezflashcards.vars['nbgd']).trigger("click",[true]);},ezflashcards.vars['timr']);
					
			ezflashcards.learnPlay();
		}else {
			ezflashcards.learnStopPlay();
		}
		return false;
	});
	
	// skip -- aka Right/Wrong buttons
	$('#skip-'+ng).live('click', function() {
		$(c.c).toggleClass('skip-card');
		
		ezflashcards.learnUpdateSkip();
		
		if (ezflashcards.vars['play']==true)
		  $('#play-'+ng).addClass('ui-btn-active ui-state-persist');
		
		$(this).removeClass('ui-btn-active ui-state-persist');
		return false;
	});


	ezflashcards.learnBindKeycuts(c,ng);

	//ezflashcards.learnLoadMore();
}


//FUNCTIONS
ezflashcards.learnStopPlay = function() {
	window.clearInterval(ezflashcards.vars['plto']); //stop playing
	window.clearInterval(ezflashcards.vars['flto']); //stop flipping
	ezflashcards.vars['play'] = false;
	$('#play-'+ezflashcards.vars['nbgd']).removeClass('ui-btn-active ui-state-persist');
}

ezflashcards.learnPlay = function() {	
	var ng = ezflashcards.vars['nbgd'];
	
	$('#play-'+ezflashcards.vars['nbgd']).addClass('ui-btn-active ui-state-persist');
	
	ezflashcards.vars['plto'] = setInterval(function() { 
		ezflashcards.vars['flto']=setTimeout(function(){$('#next-'+ ezflashcards.vars['nbgd']).trigger("click",[true]);},0); //show next card immediately
		ezflashcards.vars['flto']=setTimeout(function(){$("#flip-"+ ezflashcards.vars['nbgd']).trigger("click",[true]);},4000); //wait a fraction before flipping
		//ezflashcards.vars['flto']=setTimeout(function(){$("#flip-"+ ezflashcards.vars['nbgd']).trigger("click",[true]);},ezflashcards.vars['timr']/5); //wait a fraction before flipping
       },ezflashcards.vars['timr']); //time before moving to next
}
	  
ezflashcards.learnLoadMore = function (ng) {
	//var ng = ezflashcards.vars['nbgd'];
	
	//if downloaded already, leave
	if (ezflashcards.vars['dwnd'][ng]==true)
		return; 
	
	// leave if downloaded all of them in the initial page load
	if (ezflashcards.vars['totl'][ng] < ezflashcards.vars['down'][ng]) {
		ezflashcards.vars['down'][ng] = ezflashcards.vars['totl'][ng];
		$('#header-learn-'+ezflashcards.vars['nbgd']).text(ezflashcards.vars['curr'][ng] + '/' + ezflashcards.vars['down'][ng]);
		return;
	}
	
	$.get("/bgload.php",
			{down: ezflashcards.vars['down'][ng],
			 nbgd: ng,
			 totl: ezflashcards.vars['totl'][ng],
			},
			    
			function(data){
  			   $('#cardset-'+ezflashcards.vars['nbgd']).append(data.out);
			   $('#cardset-'+ezflashcards.vars['nbgd']).children('.collapse-me').removeClass('collapse-me').collapsible();
			   
			   ezflashcards.vars['down'][ng]=data.down;
			   $('#header-learn-'+ng).text(ezflashcards.vars['curr'][ng] + '/' + ezflashcards.vars['down'][ng]);
			   if (data.stop==false) { 
				   ezflashcards.learnLoadMore(ng);
				   ezflashcards.learnUpdateSize();
			   }
			   else
				   ezflashcards.vars['dwnd'][ng]=true; //downloaded
			},
			  
			"json");
}

ezflashcards.learnUpdateSize = function() {
    $('#learn-'+ezflashcards.vars['nbgd']).find('span').css({'font-size': '', 'line-height':''});
    $('#learn-'+ezflashcards.vars['nbgd']).find('div').css({'line-height': '', 'overflow-y':'', 'height':''});
    $('.ui-fullsize .ui-btn-inner').css({'font-size': ezflashcards.vars['qsiz'] }); // Q font size
    $('.answer, .card, .card div, .card .ui-li-desc').css({'font-size': ezflashcards.vars['asiz'],'white-space': "normal", 'margin-top':0}); // A font size
    //$('.card .ui-li-desc').css('marginTop',0);
}

ezflashcards.learnHideCard = function() {
	var ng = ezflashcards.vars['nbgd'];
	ezflashcards.vars['cid'] = '#card-' +ezflashcards.vars['nbgd'] + '-' + ezflashcards.vars['curr'][ng] + ezflashcards.vars['side'];
	$(ezflashcards.vars['cid']).hide();
}

ezflashcards.learnUpdateShortcuts =function(c) {
	var ng = ezflashcards.vars['nbgd'];
	c.c = "#card-" + ezflashcards.vars['nbgd'] + "-"+ ezflashcards.vars['curr'][ng]; //current card
	c.q = "#card-" + ezflashcards.vars['nbgd'] + "-"+ ezflashcards.vars['curr'][ng] + "Q"; //current question
	c.a = "#card-" + ezflashcards.vars['nbgd'] + "-"+ ezflashcards.vars['curr'][ng] + "A"; //current answer
	c.e = "#card-" + ezflashcards.vars['nbgd'] + "-"+ ezflashcards.vars['curr'][ng] + "E"; //current edit
	c.l = ezflashcards.vars['coll']; //collapsed boolean
}

ezflashcards.learnUpdateSkip = function() {
	var ng  = ezflashcards.vars['nbgd'];
	var cc = "#card-" + ezflashcards.vars['nbgd'] + "-"+ ezflashcards.vars['curr'][ng]; //current card
	
	//set proper icon color
	var color = "white";
	var theme = ezflashcards.vars['thme'];
	(theme=="a" || theme=="b") ? color= "white" : color="gray";

	if ($(cc).hasClass('skip-card')) {
		$('#skip-'+ ng +' .ui-btn-text').text('Right');
		$('.skip .ui-icon').css('background','url(/themes/GlyphishPro/xtras/xtras-'+color+'/17-check.png) 50% 50% no-repeat');
	}
	else {
		$('#skip-'+ ng +' .ui-btn-text').text('Wrong');
		$('.skip .ui-icon').css('background','url(/themes/GlyphishPro/xtras/xtras-'+color+'/60-x.png) 50% 50% no-repeat');
	}
}

ezflashcards.learnAdvance = function(ng) {
	if (ezflashcards.vars['curr'][ng] + 1 > ezflashcards.vars['totl'][ng])
		ezflashcards.vars['curr'][ng] = 1;
	else
		ezflashcards.vars['curr'][ng] += 1;
}

ezflashcards.learnPlaySeek = function(c,ng) {
	var i = 1;
	while ($(c.c).hasClass('skip-card')) {
		ezflashcards.learnAdvance(ng);
		ezflashcards.learnUpdateShortcuts(c);
		if (i==ezflashcards.vars['totl'][ng]) {
			ezflashcards.learnStopPlay();
			$('#all-memorized .ui-link').trigger('click');
			break;
			return;
		}
		i+=1;
	}
}

ezflashcards.learnUpdatePage = function(c,ng) {
	//show card
	$(c.c).removeClass('hidden');
	
	//update page
	$('#header-learn-'+ng).text(ezflashcards.vars['curr'][ng] + '/' + ezflashcards.vars['down'][ng]);
	$('#edit-guid').attr('href',$(c.e).attr('href'));
	ezflashcards.learnUpdateSkip();
}

ezflashcards.learnBindKeycuts = function(c,ng) {
	//enter
	$("#learn-"+ng).keydown(function(event) {
	   if(event.target != this) return;	

	    //enter (numpad too)
	    if (event.which == 13) { 
	       $('#skip-'+ng).trigger('click');
	       return false;
	    }
	    
	    //left arrow
	    if (event.which == 37) {
	    	$('#prev-'+ng).trigger('click');
	       return false;
	    }
	    
	    //numpad 4
	    if (event.which == 100) { 
	    	$('#prev-'+ng).trigger('click');
	       return false;
	    }
	    
	    //numpad 5
	    if (event.which == 101) { 
	    	$('#flip-'+ng).trigger('click');
	       return false;
	    }
	    
	    //numpad 6
	    if (event.which == 102) {
	    	$('#next-'+ng).trigger('click');
	       return false;
	    }

	    //numpad 2 
	    if (event.which == 98) {
	    	$('html, body').animate({scrollTop: $(document).height()}, 4000);	
	       return false;
	    }

	    //numpad 8 
	    if (event.which == 104) {
	    	$('html, body').animate({scrollTop: 0}, 4000);	
	       return false;
	    }

	    //numpad 0 
	    if (event.which == 96) {
	    	$('#flip-'+ng).trigger('click');
	       return false;
	    }
	    
	    //numpad +
	    if (event.which == 107) {
	    	console.log('numpad +');
	    	$('#play-'+ng).trigger('click');
	       return false;
	    }
	    
	    //right arrow
	    if (event.which == 39) { 
	    	$('#next-'+ng).trigger('click');
	       return false;
	    }
	    
	    //spacebar
	    if (event.which == 32) {
	    	$('#skip-'+ng).trigger('click');
	       return false;
	    }
	    
	    //b
	    if (event.which == 66) {
	    	$('#prev-'+ng).trigger('click');
	       return false;
	    }
	    
	    //a
	    if (event.which == 65) {
	    	$('#play-'+ng).trigger('click');
	       return false;
	    }
	    
	    //s
	    if (event.which == 83) {
	    	$('#flip-'+ng).trigger('click');
	       return false;
	    }
	
	    //n
	    if (event.which == 78) {
	    	$('#next-'+ng).trigger('click');
	       return false;
	    }
	
	    //x
	    if (event.which == 88) {
			 $('#next-'+ng).trigger('click');
			 return false;
		}

	
		//w
	    if (event.which == 87) {
	    	$('#skip-'+ng).trigger('click');
	       return false;
	    }

	    //e
	    if (event.which == 69) {
	    	var ce = "#card-" + ezflashcards.vars['nbgd'] + "-"+ ezflashcards.vars['curr'][ng] + "E"; //current edit
			var url = $(ce).attr('href');
	    	$('#edit-guid').trigger('click');
			window.open(url, 'edit');
	        return false;
	    }
	
	   //u
	   if (event.which == 85) {
		   ezflashcards.learnUpdateFlashcard();
	   }
	});
}	
	
ezflashcards.learnUpdateFlashcard = function() {
		var c = {};
		var ng = ezflashcards.vars['nbgd'];
		
		ezflashcards.learnUpdateShortcuts(c);
		$(c.c).unbind();
	
	   console.log('here');
		var ccnm = ezflashcards.vars['curr'][ng];
		var href = $(c.e).attr('href');
		var skip = $(c.c).hasClass('skip-card');
		var guid = (href.substr(href.lastIndexOf('/') + 1)); //http://stackoverflow.com/questions/4758103/last-segment-of-url
		
		$.mobile.showPageLoadingMsg('a', "Updating flashcard...", false);
		
		$.get("/bgload-single.php",
				{
					guid: guid,
					ccnm: ccnm,
					nbgd: ng
				},
				    
				function(data){
	  			   $(c.c).replaceWith(data.out);
	  			   $.mobile.hidePageLoadingMsg();
	  			   
	  			   if (skip)
	  				 $(c.c).addClass('skip-card');
	  				   
	  			   $(c.c).collapsible();
				   $(c.c).trigger('collapse');
	  			   ezflashcards.learnUpdateSize();
	  			   
	  			   $('#settings').dialog('close');
				},
				  
				"json");
}
