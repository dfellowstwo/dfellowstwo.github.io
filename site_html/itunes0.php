<!DOCTYPE html>
<html>
<head>
	<meta http-equiv=content-type content="text/html; charset=utf-8">
	<meta name=viewport content="width=device-width, initial-scale=1.0">
	<title>TURN YOUR EXPORTED ITUNES PLAYLIST INTO A SORTABLE HTML TABLE</title>
	<meta content="TURN YOUR EXPORTED ITUNES PLAYLIST INTO A SORTABLE HTML TABLE" name="description">
	<link href="../apple-touch-icon.png" rel="apple-touch-icon">
	<link href="../Css/main.css" rel="stylesheet">
	<script src="../scripts/modernizr-2.8.3.min.js"></script>
	<script src="../scripts/jquery-1.12.0.min.js"></script>
	<style type="text/css">
	html,body{font-size:16px;width:100%;margin:0 auto;background-image:url(../images/background770.jpg);background-repeat:repeat;font-family:Arial,Helvetica,sans-serif}a{text-decoration:none}h1{font-size:2em}.container{max-width:100%;margin:0 auto;padding:.5em;font-size:1.25em}input[type="text"]:focus{border-color:#333}input[type="file"]{width:75%}input[type="submit"]{padding:.5em 1em;background:#ccc;border:0 none;cursor:pointer;-webkit-border-radius:.5em;border-radius:.5em;width:20%}.container{padding:.5em}li a{display:block;margin:.25em 0}ol{margin:0 1em;padding:.25em}@media only screen and (min-width :1224px){.container{max-width:50%}}
	</style>
</head>
<body class="container">
	<img alt="me@dfellows.rf.gd" src="../images/dfellows.rf.gd980x275.jpg" width="100%">
<!--<h1>CREATE A SORTABLE HTML TABLE USING YOUR ITUNES PLAYLIST</h1> -->
<h1>TURN YOUR EXPORTED ITUNES PLAYLIST INTO A SORTABLE HTML TABLE</h1>
	<p><a href="itunes.html">SAMPLE RESULTS</a></p>
	
<!--
https://www.tutorialspoint.com/php/php_file_uploading.htm
http://stackoverflow.com/questions/19032182/php-how-to-hide-the-form-after-its-submitted
-->
	<?php
	ini_set('session.cache_limiter','public');
	session_cache_limiter(false); //google search php webpage has expired back button

	if(isset($_FILES['uploaded_file'])){
	$errors= array();
	$file_name = $_FILES['uploaded_file']['name'];
	$file_size = $_FILES['uploaded_file']['size'];
	$file_tmp = $_FILES['uploaded_file']['tmp_name'];
	$file_type = $_FILES['uploaded_file']['type'];
	$file_ext=strtolower(end(explode('.',$_FILES['uploaded_file']['name'])));


	$expensions= array("itunes.xml");
	if(in_array($file_name,$expensions)=== false){
	$errors[]='only itunes.xml allowed. Check file name and case.';
	}
	if($file_size > 4194304) {
	$errors[]='File size must be less than 4 MB';
	}
	// THE FOLLOWING SAVES THE UPLOADED FILE ONE FOLDER UP THE DIRECTORY STRUCTURE
	// https://paragonie.com/blog/2015/10/how-securely-allow-users-upload-files
	// http://stackoverflow.com/questions/11094776/php-how-to-go-one-level-up-on-dirname-file
	$upOne = realpath(__DIR__ . '/..');
	$newname = $upOne.'/'.$file_name;
	//Attempt to move the uploaded file to it's new place
	if ((move_uploaded_file($_FILES['uploaded_file']['tmp_name'],$newname))) {
	echo "";
	} else {
	echo "Error: A problem occurred during file upload!";
	}

	}
	?>
	<p style=text-transform:none;>These PHP v5.3 scripts are built around an <a href=https://github.com/mheap/iTunes-Library-Parser>Itunes library parser</a> at GitHub.  Online (CloudlinuxOS / Centos6, Apache 2.4, and php 5.4) they work without notice on an Itunes v.9 playlist.  Offline (Windows 10, XAMMP v5.5.19, and php v5.5.19) they work with some "Notice: Undefined property" messages.  If you want to fiddle with them you can download the files below. </p>

	<ol>
		<li>EXPORT YOUR PLAYLIST (SONG LIST) AND NAME IT itunes.xml (CASE and NAME ARE IMPORTANT).</li>
		<li>UPLOAD YOUR PLAYLIST:
			<ol>
				<li id="first">4 MB max file size</li>
				<li id="second">4 MB file will take ~30 seconds to process. You will see no progress.</li>
			</ol>
		</li>
	</ol>
<!--    	
http://www.diffen.com/difference/GET-vs-POST-HTTP-Requests
https://www.tutorialspoint.com/php/php_file_uploading.htm
-->
	<form action="#here" enctype="multipart/form-data" method="post">
		<input name="uploaded_file" type="file"> <input type="submit" value="Upload">
		<ul>
			<li id="here">Results:&nbsp;<?php
			if(isset($_FILES['uploaded_file'])){
			if(empty($errors)==true) {
			move_uploaded_file($file_tmp,"images/".$file_name);
			echo "Upload successful!";

			}else{
			print_r($errors);
			}
			}?></li>
			<li>File sent: <?php echo $_FILES['uploaded_file']['name'];  ?></li>
			<li>File size: <?php echo $_FILES['uploaded_file']['size'];  ?></li>
			<li>File type: <?php echo $_FILES['uploaded_file']['type'];  ?></li>
<!--        <li>File temp name: <?php echo $_FILES["uploaded_file"]["tmp_name"];  ?></li>
<li>Error Message: <?php echo $_FILES["uploaded_file"]["error"];  ?></li>
-->
		</ul>
	</form>
	<ol start="3">
		<li>CREATE A SORTABLE TABLE WITH:
			<ol>
				<li><a href="itunes1.php">NAME, ALBUM, ARTIST, PLAY COUNT</a></li>
				<li><a href="itunes2.php">EVERYTHING</a></li>
			</ol>
		</li>
		<li><a href="itunes3.php">WHEN YOU ARE FINISHED DELETE YOUR ITUNES.XML IF YOU WANT</a></li>
	</ol>
	<p>
		<a href="https://github.com/mheap/iTunes-Library-Parser">SOURCE OF PARSE at GITHUB</a><br>
		<a href="GitHub - mheap-iTunes-Library-Parser Class to parse iTunes Library XML Files.htm">SOURCE OF PARSE local</a><br>
		<a href="Javascript%20table%20sorting%20script.htm">SOURCE OF TABLE SORT</a><br>
		<a href="itunes_gs_sortable.js">DOWNLOAD TABLE SORT JAVASCRIPT</a><br>
		<a href="itunes2.xml">SAMPLE PLAYLIST</a><br>
		<a href="itunes-source.zip">USE OFFLINE (236K windows zip file)</a>.  YOU MIGHT GET SOME "Notice: Undefined property" MESSAGES.
	</p>
</body>
</html>