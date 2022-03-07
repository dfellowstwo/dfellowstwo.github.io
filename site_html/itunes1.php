<?php
/* https://github.com/mheap/iTunes-Library-Parser
 * The script will find any songs and output them in a table, providing a list of tracks
 * USES YOUR EXPORTED ITUNES XML PLAYLIST.
 * ITUNESLIBRARY.PHP PARSES THE PLAYLIST AND 1.PHP (ORIGINAL WAS EXAMPLE.PHP) DISPLAYS THE RESULTS.
 */
unlink ('itunes-playlist.zip');
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

          
          
echo "<a href=itunes-playlist.zip>DOWNLOAD RESULTS AND TABLE SORT JAVASCRIPT IN A ZIP FILE</a><br>";
echo "<p><a href=itunes3.php>CLICK TO REPLACE YOUR PLAYLIST WITH A SAMPLE PLAYLIST</a></p>";
echo "<p><a href=itunes0.php>START OVER</a></p>";

ob_start();
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
<!--SORTABLE TABLE	http://www.allmyscripts.com/Table_Sort/ -->
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
            <p class="browserupgrade">You are using an <strong>outdated</strong> browser. Please <a href="http://browsehappy.com/">upgrade your browser</a> to improve your experience.</p>
        <![endif]-->

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

$content = ob_get_contents();
ob_end_clean(); //here, output is cleaned. You may want to flush it with ob_end_flush()
ob_end_flush();
echo "$content";
file_put_contents('itunes4.html', $content, FILE_APPEND);
 $zip = new ZipArchive;
if ($zip->open('itunes-playlist.zip', ZipArchive::CREATE) === TRUE)
{
    // Add files to the zip file
    $zip->addFile('itunes4.html');
    $zip->addFile('itunes_gs_sortable.js');
    $zip->deleteName( 'itunes1.php' ); 
    // All files are added, so close the zip file.
    $zip->close();
}
copy('itunes2.xml', '../itunes.xml');
unlink ('itunes4.html');
exit ();