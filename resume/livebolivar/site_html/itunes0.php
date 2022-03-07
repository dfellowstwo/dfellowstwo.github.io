<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "https://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="https://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>itunes0.php</title> <meta name="description" content="CONVERT YOUR ITUNES PLAYLIST TO A HTML FILE">
<!--https://paragonie.com/blog/2015/10/how-securely-allow-users-upload-files -->
        <meta name="viewport" content="width=device-width, maximum-scale=1.0, minimum-scale=1.0, initial-scale=1.0">

        <link rel="apple-touch-icon" href="../apple-touch-icon.png">
        <!-- Place favicon.ico in the root directory -->

    <!--<link rel="stylesheet" href="../Css/normalize.css"> -->
      <link rel="stylesheet" href="../Css/main.css">
       <script src="../scripts/modernizr-2.8.3.min.js"></script>
       <script src="../scripts/jquery-1.12.0.min.js"></script>
       

       
<style type="text/css">

html, body {
	font-size: 16px;
	width: 100%;
	margin:0 auto;
	background-image:url(../images/background770.jpg);
	background-repeat: repeat;
	font-family: Arial, Helvetica, sans-serif;
}

h1 {
font-size: 2em;
}

.container {
	max-width:100%; 
	margin: 0 auto;
	padding: .5em;
	font-size: 1.25em;
}
input[type="text"]:focus {
    border-color:#333;
}
input[type="file"] {
	width:75%;
}
input[type="submit"] {
    padding:.5em 1em; 
    background:#ccc; 
    border:0 none;
    cursor:pointer;
    -webkit-border-radius: .5em;
    border-radius: .5em;
	width:20%;
}

.container {
    padding: .5em;
}

a {
    line-height: 1.5em;
    display: inline-block;
    text-decoration: none;
    padding: .25em 0;
    margin: .25em 0;
}

li a {
	display:block;
    margin: .25em 0;
}
ol {
    margin:0 1em;
	padding: .25em;

}


/* https://responsivedesign.is/develop/browser-feature-support/media-queries-for-common-device-breakpoints/
/* Smartphones (landscape) ----------- */
@media only screen and (min-width : 321px) {
/* Styles */
}

/* Smartphones (portrait) ----------- */
@media only screen and (max-width : 320px) {
/* Styles */
}

/* iPads (portrait and landscape) ----------- */
@media only screen and (min-device-width : 768px) and (max-device-width : 1024px) {
/* Styles */
}

/* iPads (landscape) ----------- */
@media only screen and (min-device-width : 768px) and (max-device-width : 1024px) and (orientation : landscape) {
/* Styles */
}

/* iPads (portrait) ----------- */
@media only screen and (min-device-width : 768px) and (max-device-width : 1024px) and (orientation : portrait) {
/* Styles */
}
/**********
iPad 3
**********/
@media only screen and (min-device-width : 768px) and (max-device-width : 1024px) and (orientation : landscape) and (-webkit-min-device-pixel-ratio : 2) {
/* Styles */
}

@media only screen and (min-device-width : 768px) and (max-device-width : 1024px) and (orientation : portrait) and (-webkit-min-device-pixel-ratio : 2) {
/* Styles */
}
/* Desktops and laptops ----------- */
@media only screen  and (min-width : 1224px) {
/* Styles */
.container {
	max-width:50%;
}


}

/* Large screens ----------- */
@media only screen  and (min-width : 1824px) {
/* Styles */
}

/* iPhone 4 ----------- */
@media only screen and (min-device-width : 320px) and (max-device-width : 480px) and (orientation : landscape) and (-webkit-min-device-pixel-ratio : 2) {
/* Styles */
}

@media only screen and (min-device-width : 320px) and (max-device-width : 480px) and (orientation : portrait) and (-webkit-min-device-pixel-ratio : 2) {
/* Styles */
}

/* iPhone 5 ----------- */
@media only screen and (min-device-width: 320px) and (max-device-height: 568px) and (orientation : landscape) and (-webkit-device-pixel-ratio: 2){
/* Styles */
}

@media only screen and (min-device-width: 320px) and (max-device-height: 568px) and (orientation : portrait) and (-webkit-device-pixel-ratio: 2){
/* Styles */
}

/* iPhone 6 ----------- */
@media only screen and (min-device-width: 375px) and (max-device-height: 667px) and (orientation : landscape) and (-webkit-device-pixel-ratio: 2){
/* Styles */
}

@media only screen and (min-device-width: 375px) and (max-device-height: 667px) and (orientation : portrait) and (-webkit-device-pixel-ratio: 2){
/* Styles */
}

/* iPhone 6+ ----------- */
@media only screen and (min-device-width: 414px) and (max-device-height: 736px) and (orientation : landscape) and (-webkit-device-pixel-ratio: 2){
/* Styles */
}

@media only screen and (min-device-width: 414px) and (max-device-height: 736px) and (orientation : portrait) and (-webkit-device-pixel-ratio: 2){
/* Styles */
}

/* Samsung Galaxy S3 ----------- */
@media only screen and (min-device-width: 320px) and (max-device-height: 640px) and (orientation : landscape) and (-webkit-device-pixel-ratio: 2){
/* Styles */
}

@media only screen and (min-device-width: 320px) and (max-device-height: 640px) and (orientation : portrait) and (-webkit-device-pixel-ratio: 2){
/* Styles */
}

/* Samsung Galaxy S4 ----------- */
@media only screen and (min-device-width: 320px) and (max-device-height: 640px) and (orientation : landscape) and (-webkit-device-pixel-ratio: 3){
/* Styles */
}

@media only screen and (min-device-width: 320px) and (max-device-height: 640px) and (orientation : portrait) and (-webkit-device-pixel-ratio: 3){
/* Styles */
}

/* Samsung Galaxy S5 ----------- */
@media only screen and (min-device-width: 360px) and (max-device-height: 640px) and (orientation : landscape) and (-webkit-device-pixel-ratio: 3){
/* Styles */
}

@media only screen and (min-device-width: 360px) and (max-device-height: 640px) and (orientation : portrait) and (-webkit-device-pixel-ratio: 3){
/* Styles */
}



</style>

<!--https://stackoverflow.com/questions/34664989/hide-form-and-display-div-on-form-submit/34665041
works but the form does not submit.
<script type="text/javascript">
 $(document).ready(function() {
        $("#myform").submit(function(e) {
            e.preventDefault();
            $("#first").hide();
            $("#second").hide();
        });
    });
</script> -->





</head>

<body class="container">
        <!--[if lt IE 8]>
            <p class="browserupgrade">You are using an <strong>outdated</strong> browser. Please <a href="https://browsehappy.com/">upgrade your browser</a> to improve your experience.</p>
        <![endif]-->
<img src="../images/_banner4.jpg" alt="CALL 1-417-327-3911(c) | AJ ELLIS | PROPERTY FOR RENT IN AND AROUND BOLIVAR, POLK COUNTY MISSOURI 65613.  HI, MY NAME IS AJ ELLIS.  I OWN OR REPRESENT OVER 300,000 SQ.FT. OF LIVING AND WORK RENTAL SPACE IN AND AROUND BOLIVAR, MISSOURI, INCLUDING THE SINGLE LARGEST APARTMENT COMPLEX IN BOLIVAR, A FOUR BUILDING, THREE STORY COMPLEX OF 100, ONE, TWO, AND THREE BEDROOM APARTMENTS.  CALL ME AT 1-417-327-3911, EMAIL ME AT AJELLIS@LIVEBOLIVAR.COM, OR SKYPE ME AT AJELLIS3 FOR YOUR PROPERTY MANAGEMENT OR PROPERTY RENTAL NEEDS.  I HAVE AVAILABLE FOR RENT SINGLE FAMILY HOMES, TRIPLEXES, DUPLEXES, APARTMENTS, TRAILERS, AND OFFICE AND BUSINESS SPACE.  SEE WHAT RENTAL PROPERTIES ARE AVAILABLE UNDER THE PROPERTIES TAB.  THERE IS VIDEO, SLIDESHOWS, FLOORPLANS AND PRICES.  IF YOU FIND SOMETHING YOU LIKE FILL OUT THE ONLINE APPLICATION AND I WILL CONTACT YOU." width="100%">

<h1>STYLE YOUR ITUNES PLAYLIST INTO A SORTABLE HTML TABLE</h1>
<h4><a href="itunes.html">SAMPLE RESULTS</a></h4>

<!--
https://www.tutorialspoint.com/php/php_file_uploading.htm
https://stackoverflow.com/questions/19032182/php-how-to-hide-the-form-after-its-submitted

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
	// https://stackoverflow.com/questions/11094776/php-how-to-go-one-level-up-on-dirname-file
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

<ol>
  <li>EXPORT YOUR PLAYLIST (SONG LIST) AND NAME IT itunes.xml (CASE and NAME IS IMPORTANT).
  <li>UPLOAD YOUR PLAYLIST:</li>
    <ol>
      <li id="first">4 MB max file size</li>
      <li id="second">4 MB file will take ~30 seconds to process. You will see no progress.</li>
    </ol>
  </li>
</ol>
    
    
   <!--  	https://www.diffen.com/difference/GET-vs-POST-HTTP-Requests
   			https://www.tutorialspoint.com/php/php_file_uploading.htm
    -->
<form action = "#here" method = "POST" enctype = "multipart/form-data"> 
<input type = "file" name = "uploaded_file" />
        <input type = "submit" value="Upload"/>
           



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

      <p><a href="GitHub - mheap-iTunes-Library-Parser Class to parse iTunes Library XML Files.htm">SOURCE OF PARSE</a><br>
          <a href="Javascript table sorting script.htm">SOURCE OF TABLE SORT</a><br>
        <a href="itunes_gs_sortable.js">DOWNLOAD TABLE SORT JAVASCRIPT</a><br>
          <a href="itunes2.xml">SAMPLE PLAYLIST</a><br>
         
          </p>
          
          
          
          
</body>
</html>
