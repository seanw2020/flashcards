//common (to mobile and pc) navigation script
var ezflashcards = {}; //global Object container
ezflashcards.vars = new Array();
ezflashcards.vars['thme'] = "a"; //default theme - adjusted in settings
ezflashcards.vars['totl'] = {}; //per card setting
ezflashcards.vars['curr'] = {}; //per card setting
ezflashcards.vars['down'] = {}; //per card setting
ezflashcards.vars['dwnd'] = {}; //downloaded
ezflashcards.vars['mobl'] = $('#mobl').text();
ezflashcards.vars['qsiz'] = $.cookie('qsiz') ? $.cookie('qsiz') : "1em"; //question font size
ezflashcards.vars['asiz'] = $.cookie('asiz') ? $.cookie('asiz') : "1em"; //answer font size
ezflashcards.vars['timr'] = $.cookie('timr') ? $.cookie('timr') : 5000;//timer for auto repetitions
ezflashcards.vars['skip'] = {}; //per card setting
