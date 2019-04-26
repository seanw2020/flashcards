ezflashcards.settingsSetup = function() {
	$('#swatch-a').live('click', function() {
		ezflashcards.settingsChangeTheme("a");
		ezflashcards.vars['thme'] = "a";
		//$.cookie("swatch", "a", { expires: 7, path: '/' });
	});
	
	$('#swatch-b').live('click', function() {
		ezflashcards.settingsChangeTheme("b");
		ezflashcards.vars['thme'] = "b";
		//$.cookie("swatch", "b", { expires: 7, path: '/' });
	});
	
	$('#swatch-c').live('click', function() {
		ezflashcards.settingsChangeTheme("c");
		ezflashcards.vars['thme'] = "c";
		//$.cookie("swatch", "c", { expires: 7, path: '/' });
	});
	
	$('#swatch-d').live('click', function() {
		ezflashcards.settingsChangeTheme("d");
		ezflashcards.vars['thme'] = "d";
		//$.cookie("swatch", "d", { expires: 7, path: '/' });
	});
	
	$('#swatch-e').live('click', function() {
		ezflashcards.settingsChangeTheme("e");
		ezflashcards.vars['thme'] = "e";
	});
	
	// huge font
	$('#font-size-huge').live('click', function() {
		ezflashcards.vars['qsiz'] = "42px";
		ezflashcards.vars['asiz'] = "46px";
	});

	// medium font
	$('#font-size-large').live('click', function() {
		ezflashcards.vars['qsiz'] = "32px";
		ezflashcards.vars['asiz'] = "36px";
	});
	
	// normal font
	$('#font-size-normal').live('click', function() {
		ezflashcards.vars['qsiz'] = "18px";
		ezflashcards.vars['asiz'] = "22px";
	});
	
	// small font
	$('#font-size-small').live('click', function() {
		ezflashcards.vars['qsiz'] = "1em";
		ezflashcards.vars['asiz'] = "1em";
	});

	// auto delay speed 1 
	$('#timer-1').live('click', function() {
		ezflashcards.vars['timr'] = 6000;
		$('.ui-dialog').dialog('close');
	});
	
	// auto delay speed 2 
	$('#timer-2').live('click', function() {
		ezflashcards.vars['timr'] = 10000;
		$('.ui-dialog').dialog('close');
	});

	// auto delay speed 3 
	$('#timer-3').live('click', function() {
		ezflashcards.vars['timr'] = 20000;
		$('.ui-dialog').dialog('close');
	});

	// auto delay speed 4 
	$('#timer-4').live('click', function() {
		ezflashcards.vars['timr'] = 30000;
		$('.ui-dialog').dialog('close');
	});

	// update flashcard
	// SEE learn.js for bind -- $('#update-guid').live('click', function() {
	$('#update-guid').live('click', function() {
		ezflashcards.learnUpdateFlashcard();
	});
}


//http://stackoverflow.com/questions/8656801/how-to-change-theme-dynamically-in-jquery-mobile
ezflashcards.settingsChangeTheme = function(theme) {
	//set your new theme letter
	//var theme = 'a';

	//reset all the buttons widgets
	$.mobile.activePage.find('.ui-btn')
	                   .removeClass('ui-btn-up-a ui-btn-up-b ui-btn-up-c ui-btn-up-d ui-btn-up-e ui-btn-hover-a ui-btn-hover-b ui-btn-hover-c ui-btn-hover-d ui-btn-hover-e')
	                   .addClass('ui-btn-up-' + theme)
	                   .attr('data-theme', theme);

	//reset the header/footer widgets
	$.mobile.activePage.find('.ui-header, .ui-footer')
	                   .removeClass('ui-bar-a ui-bar-b ui-bar-c ui-bar-d ui-bar-e')
	                   .addClass('ui-bar-' + theme)
	                   .attr('data-theme', theme);

	//reset the page widget
	$.mobile.activePage.removeClass('ui-body-a ui-body-b ui-body-c ui-body-d ui-body-e')
	                   .addClass('ui-body-' + theme)
	                   .attr('data-theme', theme);

	//swap icon colors depending on dark or light theme
	var color = "white";
	(theme=="a" || theme=="b") ? color= "white" : color="gray"; 
	$('.prev .ui-icon').css('background','url(/themes/GlyphishPro/xtras/xtras-'+color+'/36-circle-west.png) 50% 50% no-repeat');
	$('.flip .ui-icon').css('background','url(/themes/GlyphishPro/icons/icons-'+color+'/165-glasses-3.png) 50% 50% no-repeat');
	$('.next .ui-icon').css('background','url(/themes/GlyphishPro/xtras/xtras-'+color+'/20-circle-east.png) 50% 50% no-repeat');
	$('.next .ui-icon').css('background','url(/themes/GlyphishPro/xtras/xtras-'+color+'/20-circle-east.png) 50% 50% no-repeat');
	$('.play .ui-icon').css('background','url(/themes/GlyphishPro/xtras/xtras-'+color+'/30-circle-play.png) 50% 50% no-repeat');
	ezflashcards.learnUpdateSkip();
	
	//write cookie -- TODO inefficient -- here instead of links because $.cookie interferes with iphone page swapping
	$.cookie("swatch", ezflashcards.vars['thme'], { expires: 999, path: '/' });
	$.cookie("qsiz"  , ezflashcards.vars['qsiz'], { expires: 999, path: '/' });
	$.cookie("asiz"  , ezflashcards.vars['asiz'], { expires: 999, path: '/' });
	$.cookie("timr"  , ezflashcards.vars['timr'], { expires: 999, path: '/' });

	ezflashcards.learnUpdateSize();
	
}
