<!DOCTYPE html> 
<html> 
<head> 
	<title>My Page</title> 
	<meta name="viewport" content="width=device-width, initial-scale=1"> 
	<link rel="stylesheet" href="http://code.jquery.com/mobile/1.2.0/jquery.mobile-1.2.0.min.css" />
	<script src="http://code.jquery.com/jquery-1.8.2.min.js"></script>
	<script src="http://code.jquery.com/mobile/1.2.0/jquery.mobile-1.2.0.min.js"></script>
</head> 
<body> 

<div data-role="page">

	<div data-role="header">
		<h1>My Title</h1>
	</div><!-- /header -->

	<div data-role="content">	
		<p>Hello world</p>
		
		<div data-role="collapsible">
   <h3>I'm a header</h3>
   <p>I'm the collapsible content. By default I'm closed, but you can click the header to open me.</p>
</div>

		
<div data-role="collapsible-set">

	<div data-role="collapsible" data-collapsed="false">
	<h3>Section 1</h3>
	<p>I'm the collapsible set content for section 1.</p>
	</div>
	
	<div data-role="collapsible">
	<h3>Section 2</h3>
	<p>I'm the collapsible set content for section 2.</p>
	</div>
	
</div>
	
		
	</div><!-- /content -->

</div><!-- /page -->

</body>
</html>