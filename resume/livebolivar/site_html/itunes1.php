<?php
/* https://github.com/mheap/iTunes-Library-Parser
 * The script will find any songs and output them in a table, providing a list of tracks
 * USES YOUR EXPORTED ITUNES XML PLAYLIST.
 * ITUNESLIBRARY.PHP PARSES THE PLAYLIST AND 1.PHP (ORIGINAL WAS EXAMPLE.PHP) DISPLAYS THE RESULTS.
 */

require 'iTunesLibrary.php';
/* PUT YOUR EXPORTED ITUNES XML PLAYLIST FILENAME IN THE NEXT LINE */
$library = new iTunesLibrary("../itunes.xml");
$count = count($library->getTracks());

function get_star_rating($rating) {
	switch ($rating) {
		case 100:
			return "*****";
		case 80:
		 	return "****";
		case 60:
		 	return "***";
		case 40:
		 	return "**";
		case 20:
		 	return "*";
		default:
			return "";
	}
}

echo <<<HEADER
<!DOCTYPE html>
<html>
<head>
	<meta charset="UTF-8">
	<title>itunes1.php</title>
	<meta name="viewport" content="width=device-width, maximum-scale=1.0, minimum-scale=1.0, initial-scale=1.0">
	<style>
	body,td,th {
	font-family: Arial, Helvetica, sans-serif;
	text-transform: uppercase;
}
	table { role: data; border-collapse: collapse; }
		table, tr, th, td {  border: 1px solid grey; }
		th { font-weight: bold; }
		td { padding: .25em 1em; white-space: nowrap; }
		table.sortable thead {
    background-color:#eee;
    color:#666666;
    font-weight: bold;
    cursor: default;}
	</style>
<!--SORTABLE TABLE	https://www.allmyscripts.com/Table_Sort/ -->
<script type="text/javascript" src="itunes_gs_sortable.js"></script>
<script type="text/javascript">
 <!--
var TSort_Data = new Array ('my_table', 's', 's', 's');
var TSort_Initial = new Array (1, '0A');
 tsRegister();
 // -->
 </script> 
</head>
<body>
       <p>
  <!--[if lt IE 8]>
            <p class="browserupgrade">You are using an <strong>outdated</strong> browser. Please <a href="https://browsehappy.com/">upgrade your browser</a> to improve your experience.</p>
        <![endif]-->
          
          
  <!--LINK TO SOURCE FILES -->
  <a href="GitHub - mheap-iTunes-Library-Parser Class to parse iTunes Library XML Files.htm">SOURCE OF PARSE</a><br>
  <a href="Javascript table sorting script.htm">SOURCE OF TABLE SORT</a><br>
 <a href="itunes_gs_sortable.js">DOWNLOAD TABLE SORT JAVASCRIPT</a><br>
          SAVE THIS PAGE.&nbsp; the table sort javascript and this page must be in the same folder to work.&nbsp; if you choose file - save they will not be.&nbsp; right click on a blank part of the page and  view source.&nbsp; copy all and paste it into a text editor, save it to your downloads folder then download the javascript.<br>
        </p>
<table id="my_table">
		<thead>
			<tr><th>Name</th><th>Album</th><th>Artist</th><th>Play Count</th></tr>
		</thead>
		<tbody>

HEADER;

foreach( $library->getTracks() as $track ) {
	if ( strstr( $track->Kind, 'audio file' ) && $track->Kind != 'Ringtone' && $track->Genre != 'Audiobook' && $track->Genre != 'Podcast' ) { 
		echo "\t\t\t<tr><td>"
		. $track->Name .  "</td><td>"
		. $track->Album . "</td><td>"
		. $track->Artist . "</td><td>"
		. $track->Play_Count . "</td></tr>\n";
	}
}

echo <<<FOOTER
		</tbody>
	</table>
</body>
</html>
FOOTER;

