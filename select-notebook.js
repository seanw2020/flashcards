//load more cards in background

ezflashcards.countNotes = function (guid) {
	ezflashcards.i += 1;
	var i = ezflashcards.i;
	var g = ezflashcards.guids;
	
	var bgc = $.ajax({
			type: 	"GET",
			url: 	"/bgcount.php",
			data: 	{'guid': guid},
			success:  function(data){
						$('#cb'+data.guid).text(data.count);
						if (typeof g[i] !== 'undefined')
							ezflashcards.countNotes(g[i]);
					},
			dataType: "json",
			async: true,
		});
}

//begin
ezflashcards.selectNotebookSetup = function() {
	if (ezflashcards.vars['selectNotebookSetupDone'])
		return;
	
	ezflashcards.bgci = 0;
	ezflashcards.guids = [];
	ezflashcards.i = 0;
	ezflashcards.a = $('#notebooks').find('a');
	
	var g = ezflashcards.guids;

	for ( var i = 0; i < ezflashcards.a.length; i++) {
		ezflashcards.a.each(function(index) {
			ezflashcards.guids[index] = this.id;
		});
	}
	
	//remove "" (first element); necessary when Search Filter enabled
	ezflashcards.guids.shift();
	
	ezflashcards.vars['selectNotebookSetupDone'] = true;
	ezflashcards.countNotes(g[0]);
	
	//stay selected when clicked
	$('#select-notebook li a').live('click',function() {
		$(this).css({'color':'red', 'font-weight':'bold', 'font-style':'italic'});
	});

}
