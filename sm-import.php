<?php
	//require_once('functionsEZ.php');
	//bootstrap();
	

	echo ('RESULTS');
	echo ('<hr/>');
	echo ("If everything works properly (i.e. you don't see lots of errors), simply copy the HTM folder contents to Evernote (see its File Import feature). That folder is located inside the _delme folder.");
	echo ('<hr/>');
	
	$path = getcwd().'/_delme/';
	$imgs = array($path.'*.jpg',$path.'*.gif');
	$bad = array('"','\\','/','?','#','*','..',':','>','<','|','[',']','~','{','}','!');
	$skipped = 0;
	
	//create imgs subfolder
	@mkdir($path."/img",0770);
	
	//create htm subfolder
	@mkdir($path."/htm",0770);
	 
	//copy images to subfolder
	foreach ($imgs as $type) {
		$files = glob($type);
		foreach ($files as $file) {
			copy($file,$path."img/".basename($file));
			unlink($file); //delete
		}
	}
	
	$files = glob($path.'*.htm');
	if (!count($files)) {
		echo('No .htm files found. Be sure to put them in a _delme folder where this php file is located. If you just ran this program, re-run it with new files placed here, since this one deletes images and htm files placed in the root folder (but do not worry, it creates the right files in subfolders, as Supermemo knew them).');
		exit();
	}
		
	foreach ($files as $file) {
		//open, read, close 
		if ($in = fopen($file, "r")) {
			$contents = fread($in, filesize($file));
			fclose($in);
		}else {
			echo('Error opening the file');
			exit();
		}
		
		//delete original
		//unlink($file);
		
		//prep dom
		$in = new DOMDocument();
		
		//read
		@$in->loadHTML($contents); //suppress errors
		
		//get groups
		$g = new DomXpath($in); //find groups
		$group = $g->query('//ul')->item(0)->nodeValue;
		
		//get each li
		$parts = explode("\n",$group);
		
		//only keep groups containing < 20 characters long
		foreach ($parts as $key=>$part) {
			$parts[$key] = str_replace('This page: ','',$part);
			if (strlen($part)>21 || ucfirst($part)!=$part) //not first cap
				unset($parts[$key]);
		}
		array_pop($parts); //todo
		$group = implode(' ' ,$parts);
		
		//$group = substr($group,0,strpos($group,'This page:'));
		$group = preg_replace('/\s\s+/', ' ', $group); //remove line breaks
		$group = html_entity_decode($group);
		$group = strip_tags($group);
		$group = str_replace($bad,'',$group); //sanitize for file sys
		$group = substr($group,0,100); //shorten
		$group = trim($group);
			
		//create group folder
		@mkdir($path.'htm/'.$group,0770);
		
		//parse divs (Q&A)
		$i=0;
		$divs = $in->getElementsByTagName('div');
		foreach ($divs as $div)
		{
			$i++;
			
			//skip first and last
			if ($i==1 || $i==$divs->length)
				continue;

			//check contents (2 chars min to write file)
			$qa = trim($div->nodeValue);			
			if (strlen($qa)<3) {
				$skipped++;
				continue;
			}
			
			$doc = new DOMDocument('1.0');//,'iso-8859-1');
			$doc->encoding = 'UTF-8';
			
			
			$doct = $doc->createTextNode("<!DOCTYPE html>");
			$doct = $doc->appendChild($doct);
			
			$root = $doc->createElement('html');
			$root = $doc->appendChild($root);
			
			$head = $doc->createElement('head');
			$head = $root->appendChild($head);
			
			$meta = $doc->createTextNode('<meta http-equiv="Content-Type" content="text/html; charset=utf-8">');
			$meta = $head->appendChild($meta);
			
			$title = $doc->createElement('title');
			$title = $head->appendChild($title);
			
			$text = $doc->createTextNode("No Title");
			$text = $title->appendChild($text);
			
			$body = $doc->createElement('body');
			$body = $root->appendChild($body);
			
			//add this div
			$itm = $doc->importNode($div,TRUE);
			$itm = $body->appendChild($itm);
			
			//get filename from first td
			$fnX = new DomXpath($doc);
			
			//if xpath can't find it, title to _
			if (!isset($fnX->query('//td')->item(0)->nodeValue)) {
				$fn = "_";
			}else {
				$fn  = trim($fnX->query('//td')->item(0)->nodeValue);
				
				if (strpos($fn,"Subject: Topic #")===0 ||
					strpos($fn,"Item: Item #")===0 ) {
					$fnParts = explode(':',$fn);
					array_shift($fnParts);//remove Subject:
					array_shift($fnParts);//remove Topic #12345:
					$fn = implode(':',$fnParts); //keep rest, putting colons back
					
					//if no next node answer (no content in future td's)
					if (strlen($fnX->query('//td')->item(1)->nodeValue)<10) {
						continue;
					}
				}
			}
			
			//cleanup filename for filesystem
			//$fn = iconv("UTF-8", "ISO-8859-1", $fn);
			$fn = strip_tags($fn);
			$fn = html_entity_decode($fn);
			$fn = str_replace($bad,' ',$fn);
			$fn = substr($fn,0,100);//100 char title limit
			$fn = preg_replace('/\s\s+/', ' ', $fn); //remove line breaks
			$fn = trim($fn);
				
			//save as html
			$html = html_entity_decode($doc->saveHTML());
			$yuck  = array('bordercolor="#0066FF"', 'bgcolor="#99CCFF"', 'bgcolor="#D0E8FF"', 'border="1"',
						   'cellpadding="5" cellspacing="5" width="100%"', '<center>', '</center>', 
							'align="center"', 'BACKGROUND-COLOR: #000000', 'color="#080000"');
							//'size="1"', 'size="2"', 'size="3"','size="4"','size="5"','size="6"');
			
			//remove table adornments
			foreach ($yuck as $y)
				$html = str_replace($y,"",$html);
			
			//put supermemo images in subfolder
			$html = preg_replace('/([0-9]{3,5}).jpg/','../../img/$1.jpg',$html); //repeat from 3-5 numbers, eg. 123.jpg or 12345.jpg
			$html = preg_replace('/([0-9]{3,5}).gif/','../../img/$1.gif',$html); 
			
			//encode in utf-8, suggested by php for 8859-1 docs like SM outputs
			$html = utf8_encode($html);
			
			
			$out = fopen($path . 'htm/'. $group . '/' . $fn.".htm", "w");
			
			//try to write
			if (fwrite($out, $html) === FALSE) {
				echo "Cannot write to file ($out)";
				exit;
			}else {
				echo 'WROTE FILE:'. $path . $group . '/' . $fn."<br/>";
				fclose($out);
			}
		}//each div
	}//each file
	echo "TOTAL SKIPPED DUE TO EMPTY CONTENTS: " .$skipped;
?>