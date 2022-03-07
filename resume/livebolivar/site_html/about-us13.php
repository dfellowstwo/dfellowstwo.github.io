<?php
// https://github.com/PHPMailer/PHPMailer/tree/5.2-stable contactform.php
// HTML5 CONTACT FORM
// REQUIRES: class.phpmailer.php, class.smtp.php, PHPMailerAutoload.php in the same directory
// REQUIRES: FORM STYLE FROM http://technopoints.co.in/php-send-mail
// SPAM FREE CONTACT FORM https://github.com/nfriedly/spam-free-php-contact-form

$msg = '';
// NEXT LINE IS SPAM FREE CONTACT FORM https://github.com/nfriedly/spam-free-php-contact-form
if(isset($_POST['url']) && $_POST['url'] == ''){
//Don't run this unless we're handling a form submission
if (array_key_exists('email', $_POST)) {
    date_default_timezone_set('Etc/UTC');

    require 'PHPMailerAutoload.php';

    //Create a new PHPMailer instance
    $mail = new PHPMailer;
    //Tell PHPMailer to use SMTP - requires a local mail server
    //Faster and safer than using mail()
	$mail->isSMTP();
	$mail->Host       = "smtpout.secureserver.net";
	$mail->Port       = "80";
	$mail->SMTPSecure = "none";
	$mail->SMTPAuth   = true;
	$mail->Username   = "ajellis@livebolivar.com";
	$mail->Password   = "august";

    //Use a fixed address in your own domain as the from address
    //**DO NOT** use the submitter's address here as it will be forgery
    //and will cause your messages to fail SPF checks
    $mail->setFrom('ajellis@livebolivar.com', 'AJ ELLIS');
    //Send the message to yourself, or whoever should receive contact for submissions
    $mail->addAddress('ajellis@livebolivar.com', 'AJ ELLIS');
    // $mail->addAddress('4173999579@vmobl.com', 'DOUG FELLOWS');
    // $mail->addAddress('4173273911@vtext.com', 'AJ ELLIS');
    //Put the submitter's address in a reply-to header
    //This will fail if the address provided is invalid,
    //in which case we should ignore the whole request
    if ($mail->addReplyTo($_POST['email'], $_POST['name'])) {
        $mail->Subject = 'HI THERE. LIVEBOLIVAR contact form';
        //Keep it simple - don't use HTML
        $mail->isHTML(false);
        //Build a simple message body
        $mail->Body = <<<EOT
Name: {$_POST['name']}
Email: {$_POST['email']}
Message: {$_POST['message']}
EOT;
        //Send the message, check for errors
        if (!$mail->send()) {
            //The reason for failing to send will be in $mail->ErrorInfo
            //but you shouldn't display errors to users - process the error, log it on your server.
            $msg = 'Sorry, something went wrong. Please try again later.';
        } else {
            $msg = 'Message sent! Thanks for contacting us. We will be in touch.';
        }
    } else {
        $msg = 'Invalid email address, message ignored.';
    }
}}
?>
<!doctype html>
<!-- saved from url=(0014)about:internet -->
<html lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>ABOUT US</title>
<meta name="description" content="PROPERTY FOR RENT IN AND AROUND BOLIVAR, POLK COUNTY, MISSOURI 65613. CALL AJ ELLIS 1-417-327-3911 M-F 10-5">
<meta name="keywords" content="PROPERTY FOR RENT IN AND AROUND BOLIVAR, POLK COUNTY, MISSOURI 65613. CALL AJ ELLIS 1-417-327-3911 M-F 10-5">
<meta name="robots" content="INDEX,FOLLOW">
<meta name="googlebot" content="INDEX,FOLLOW">
<meta name="author" content="DOUG FELLOWS">
<link rel="stylesheet" type="text/css" href="../Css/screen_styles.css">
<link rel="stylesheet" type="text/css" href="../Css/screen_layout_large.css">
<link rel="stylesheet" type="text/css" media="only screen and (min-width:50px) and (max-width:600px)" href="../Css/screen_layout_small.css">
<link rel="stylesheet" type="text/css" media="only screen and (min-width:601px) and (max-width:800px)" href="../Css/screen_layout_medium.css">
<link rel="stylesheet" type="text/css" media="print" href="../Css/print.css" />
<!--[if lt IE 9]>
<script src="https://html5shiv.googlecode.com/svn/trunk/html5.js"></script>
<![endif]-->
<!-- BEGIN HONEYPOT 
.hidden-with-pos BELOW IS A HONEYPOT
 END HONEYPOT> -->
<style type="text/css">

p	{ padding:0 1em 0 1em;	}
@media screen and (max-width: 534px) {
		body{font-size:24px;}
		.hidep {display:inline-block}
		}
@media screen and (max-width: 320px) {
		body{font-size:24px;}
		.hidep {display:none}
		}
	
	
	p{padding:0 1em 0 1em}@media screen and (max-width:534px){body{font-size:24px}#z042718 a{line-height:3em}}html{line-height:1em}.hide{display:none}a{text-decoration:none}.red-text{color:#f00}.green-text{color:#008000}body{color:#000;font-family:Arial;font-size:16px;background:#515673 url(../images/background_gradient.jpg) repeat-x 0 0}.page{max-width:1360px;margin:0 auto;position:relative;background-color:#fff;padding:.5em}h1{font-size:2em;margin:0 0 .5em 0;font-weight:normal;color:#a6430a;line-height:1em}h2{font-size:1.7em;margin:0 0 1em 0}h3{font-size:1.5em;margin:0 0 1em 0}a{color:#de9000;text-decoration:none;line-height:1em}a:hover{color:#009eff}a.cta:hover{background-position:right -44px}footer{font-size:.85em;color:#000;background-color:#fff;padding:10px 10px 10px 0}.promo h3{font-size:1.1em;margin:0}nav a{color:#f5a06e;text-transform:uppercase;text-decoration:none;display:inline-block;font-weight:bold;font-size:.9em}nav a:hover{color:#fff}.clear-fix{clear:both;line-height:1px}.print{display:none}#springhill{margin:0 0 .5em 0}cssgif{max-width:88px;float:right;margin:0 0 .25em 0}.spacelink{padding-left:1em}.videolinks{padding-left:1em}.vspace{margin-bottom:1em}.vspace2{margin-bottom:.25em}.springhill{padding-left:.25em}body{margin:0;padding:0}header a.logo{display:block;position:absolute;background-position:0 0;background-repeat:no-repeat}header a.logo{width:80px;height:80px;top:9px;right:9px;background-image:url(../images/icon-phone.png);background-size:80px 80px}header{height:382px;background:url(../images/banner_large1360.jpg) no-repeat right 0;background-size:1360px 382px}nav{top:390px;width:100%;display:block;position:absolute;background-color:#a6430a}nav a{color:#f5a06e;text-transform:uppercase;display:inline-block;font-weight:bold;font-size:.9em;padding:1em}nav a:hover{color:#fff}article{padding:70px 20px 10px 0}.promo_container{padding:0 0 15px 20px}.promo_container .promo{width:33%;float:left;background-position:0 0}.promo_container .promo .content{padding:0 30px 0 70px}.promo_container2{margin:2em 0 0 0;padding:0 0 15px 20px}.promo_container2 .promo{width:33%;float:left;background-position:0 0}.promo_container2 .promo .content{padding:0 30px 0 70px}.promo_container3{margin:2em 0 0 0;padding:0 0 15px 20px}.promo_container3 .promo{width:33%;float:left;background-position:0 0}.promo_container3 .promo .content{padding:0 30px 0 70px}p{margin:1em 2em .75em 0}.promo h1{margin:0}.promo a.cta{font-size:1.25em;line-height:1em;font-weight:bold;margin:0;padding:0 1em 0 0;background:url(../images/cta_arrow.png) no-repeat right 0 top 6px}.hidelg{display:none}footer{padding-left:20px}@media screen and (min-width:50px) and (max-width:320px){header{height:75px;background:url(../images/banner_small.jpg);background-size:500px 85px;background-repeat:no-repeat;background-position:center top}header a.logo{width:40px;height:40px;top:6px;right:5px;background-image:url(../images/icon-phone.png);background-size:40px 40px}html{line-height:1.5em}#noRent{display:none}.spacelink{padding-left:2em}.videolinks{padding-left:2em}.vspace{margin-bottom:2em}.vspace2{margin-bottom:.25em}p+p{margin-top:2em}@media only screen and (min-width:50px) and (max-width:320px){a.cta span{display:none}}article{padding:20px 20px 10px 0}li{text-align:left;padding:1em 0}nav{display:block;position:static;padding:10px 0 10px 0;background-color:#515673}nav a{color:#fff;display:block;margin:15px 15px 30px 15px;padding:9px;border:1px solid #a6abc5;background:url(../images/mobile_link_arrow.png) no-repeat right center;-webkit-border-radius:12px;-moz-border-radius:12px;border-radius:12px;font-size:1.25em}nav a:hover{color:#fff;background-color:rgba(255,255,255,.15)}.promo_container{padding:0}.promo_container .promo{width:auto;float:none;padding:10px 0 0 0;background-position:20px 13px;border-top:1px solid #ccc}.promo_container .promo.one{width:auto;background-position:20px 13px}.promo_container .promo.two{width:auto;background-position:20px 13px}.promo_container .promo.three{width:auto;background-position:20px 13px}.promo_container .promo .content{padding:0 20px 5px 90px}.promo_container p{font-size:1.5em;margin:0 2em .75em 0}.promo_container2{margin:0;padding:0}.promo_container2 .promo{width:auto;float:none;padding:10px 0 0 0;background-position:20px 13px;border-top:1px solid #ccc}.promo_container2 .promo.twenty-seven{width:auto;background-position:20px 13px}.promo_container2 .promo.twenty-eight{width:auto;background-position:20px 13px}.promo_container2 .promo.thirty-three{width:auto;background-position:20px 13px}.promo_container2 .promo .content{padding:0 20px 5px 90px}.promo_container2 p{font-size:1.5em;margin:.5em 2em .75em 0}.promo_container3{margin:0;padding:0}.promo_container3 .promo{width:auto;float:none;padding:10px 0 0 0;background-position:20px 13px;border-top:1px solid #ccc}.promo_container3 .promo.thirty-four{width:auto;background-position:20px 13px}.promo_container3 .promo .content{padding:0 20px 5px 90px}.promo_container3 p{font-size:1.5em;margin:.5em 2em .75em 0}.promo a.cta{font-size:1.25em;line-height:1em;font-weight:bold;margin:0;padding:0 1em 0 0;background:url(../images/cta_arrow.png) no-repeat right 0 top 12px}a{color:#00f;text-decoration:none;line-height:1em}article p{font-size:1.25em;font-weight:bold;margin:0 0 1.25em 0;padding:0;background:0}article p a.cta{line-height:3em}.hidelg{display:inline-block}footer{border-top:1px solid #a6abc5;padding-left:0}footer a{padding:0 1em 0 0}body{background-image:none}}@media screen and (min-width:321px) and (max-width:600px){#noRent{width:45%}header{height:75px;background:url(../images/banner_small.jpg);background-size:500px 85px;background-repeat:no-repeat;background-position:center top}header a.logo{width:40px;height:40px;top:6px;right:5px;background-image:url(../images/icon-phone.png);background-size:40px 40px}html{line-height:1.5em}.spacelink{padding-left:2em}.videolinks{padding-left:2em}.vspace{margin-bottom:2em}.vspace2{margin-bottom:.25em}p+p{margin-top:2em}@media only screen and (min-width:50px) and (max-width:320px){a.cta span{display:none}}article{padding:20px 20px 10px 0}li{text-align:left;padding:1em 0}nav{display:block;position:static;padding:10px 0 10px 0;background-color:#515673}nav a{color:#fff;display:block;margin:15px 15px 45px 15px;padding:9px;border:1px solid #a6abc5;background:url(../images/mobile_link_arrow.png) no-repeat right center;-webkit-border-radius:12px;-moz-border-radius:12px;border-radius:12px;font-size:1.25em}nav a:hover{color:#fff;background-color:rgba(255,255,255,.15)}.promo_container{padding:0}.promo_container .promo{width:auto;float:none;padding:10px 0 0 0;background-position:20px 13px;border-top:1px solid #ccc}.promo_container .promo.one{width:auto;background-position:20px 13px}.promo_container .promo.two{width:auto;background-position:20px 13px}.promo_container .promo.three{width:auto;background-position:20px 13px}.promo_container .promo .content{padding:0 20px 5px 90px}.promo_container p{font-size:1.5em;margin:1em 2em .75em 0}.promo_container2{margin:0;padding:0}.promo_container2 .promo{width:auto;float:none;padding:10px 0 0 0;background-position:20px 13px;border-top:1px solid #ccc}.promo_container2 .promo.twenty-seven{width:auto;background-position:20px 13px}.promo_container2 .promo.twenty-eight{width:auto;background-position:20px 13px}.promo_container2 .promo.thirty-three{width:auto;background-position:20px 13px}.promo_container2 .promo .content{padding:0 20px 5px 90px}.promo_container2 p{font-size:1.5em;margin:.5em 2em .75em 0}.promo_container3{margin:0;padding:0}.promo_container3 .promo{width:auto;float:none;padding:10px 0 0 0;background-position:20px 13px;border-top:1px solid #ccc}.promo_container3 .promo.thirty-four{width:auto;background-position:20px 13px}.promo_container3 .promo .content{padding:0 20px 5px 90px}.promo_container3 p{font-size:1.5em;margin:.5em 2em .75em 0}.promo a.cta{font-size:1.25em;line-height:1em;font-weight:bold;margin:0;padding:0 1em 0 0;background:url(../images/cta_arrow.png) no-repeat right 0 top 12px}a{color:#00f;text-decoration:none;line-height:1em}article p{font-size:1.25em;font-weight:bold;margin:0 0 1.25em 0;padding:0;background:0}article p a.cta{line-height:3em}.hidelg{display:inline-block}footer{border-top:1px solid #a6abc5;padding-left:0}footer a{padding:0 6em 0 0}body{background-image:none}}@media screen and (min-width:601px) and (max-width:800px){.promo_container .promo{width:50%}.clear-fix2{clear:both;line-height:1px}.spacelink{padding-left:1.5em}.videolinks{padding-left:1.5em}.vspace{margin-bottom:1.5em}p.vspace2{margin-bottom:.25em}p+p{margin-top:2em}header{height:200px;background:url(../images/banner_medium.jpg) no-repeat 90% 0;background-size:800px 200px}header a.logo{width:60px;height:60px;top:9px;right:9px;background-image:url(../images/icon-phone.png);background-size:60px 60px}nav{top:208px}nav a{margin-right:20px}.promo_container{padding:0 20px 15px 20px}.promo_container .promo{background-position:0 0}.promo_container .promo .content{padding:70px 30px 0 0}.promo_container .promo.one{background-position:0 0}.promo_container .promo.two{background-position:0 0}.promo_container .promo.three{background-position:0 0}body{background-image:none}a.cta span{display:none}}@media print{.promo img{display:none}.print{display:inherit}.noPrint{display:none}.spacelink{display:none}.vspace{display:none}.vspace2{display:none}.springhill{padding-left:0}.noScreen{display:inline}body{font:12pt Georgia,"Times New Roman",Times,serif;line-height:1.3;background:#fff;color:#000}.promo h1,.promo h2,.promo h3,p,li{color:inherit;font-size:.75em}h1{margin:0;color:inherit;font-size:.75em}a,a:visited{color:inherit;text-decoration:none}.page{width:100%;margin:0;padding:0}nav{display:none}article{padding:0}a.cta:after{content:"Learn more online at: " attr(href);display:none;font-style:italic;font-weight:normal;margin-top:15px}.promo_container{padding:0;font-size:11pt;margin-top:1em;margin-bottom:0}.promo_container .promo{width:100%;margin-top:.5em;margin-bottom:.25em}.promo_container .promo .content{padding:0 30px 0 0}a.cta{text-transform:none;background:0;color:inherit;display:none}footer{color:#000;background:0;padding:0;font-size:.7em}footer .qr{border-top:1px solid #ddd;margin-top:20px;padding-top:20px}footer .qr img{float:left;margin-right:20px}header a.logo{top:15px;background:0}header{display:none}}@media screen and (min-width:601px) and (max-width:890px){article{padding:115px 20px 10px 0}}@media screen and (min-width:1359px){nav{width:98.80%}}@media screen and (min-width:1342px) and (max-width:1358px){nav{width:98.80%}}@media screen and (min-width:1325px) and (max-width:1341px){nav{width:98.80%}}@media screen and (min-width:1270px) and (max-width:1324px){nav{width:98.75%}}@media screen and (min-width:1198px) and (max-width:1269px){nav{width:98.70%}}@media screen and (min-width:1104px) and (max-width:1197px){nav{width:98.60%}}@media screen and (min-width:0945px) and (max-width:1103px){nav{width:98.50%}}@media screen and (min-width:0851px) and (max-width:0944px){nav{width:98.10%}}@media screen and (min-width:0718px) and (max-width:0850px){nav{width:97.90%}}@media screen and (min-width:0642px) and (max-width:0717px){nav{width:97.70%}}@media screen and (min-width:0601px) and (max-width:0652px){nav{width:97.40%}}.myform{max-width:460px;width:90%;padding:14px;margin-top:1.5em}#basic{border:solid 2px #dedede}#basic h1{font-size:14px;font-weight:bold;margin-bottom:8px}#basic p{font-size:11px;color:#666;margin-bottom:20px;border-bottom:solid 1px #dedede;padding-bottom:10px}#basic label{display:block;font-weight:bold;text-align:right;width:140px;float:left}#basic .small{color:#666;display:block;font-size:11px;font-weight:normal;text-align:right;width:140px}#basic input{float:left;width:200px;margin:2px 0 30px 10px}#basic button{clear:both;margin-left:150px;background:#888;color:#fff;border:solid 1px #666;font-size:11px;font-weight:bold;padding:4px 6px}#stylized{border:solid 2px #b7ddf2;background:#de9000}#stylized h1{font-size:14px;font-weight:bold;margin-bottom:8px}#stylized p{font-size:11px;color:#666;margin-bottom:20px;border-bottom:solid 1px #b7ddf2;padding-bottom:10px}#stylized label{display:block;font-weight:bold}#stylized .small{color:#666;display:block;font-size:11px;font-weight:normal;text-align:right;width:140px}#stylized input{font-size:12px;padding:4px 2px;border:solid 1px #aacfe4;width:400px;margin:2px 0 20px 10px}#stylized button{clear:both;margin-left:160px;width:125px;height:31px;background:#444;text-align:center;line-height:31px;color:#fff;font-size:11px;font-weight:bold}textarea{width:410px;height:200px;overflow: auto;}@media screen and (max-width:470px){#stylized input{width:350px}textarea{width:360px;height:200px}}@media screen and (max-width:460px){#stylized input { width: 350px;}textarea{width:360px;height:200px}}@media screen and (max-width:450px){#stylized input{width:325px}textarea{width:335px;height:200px}}@media screen and (max-width:400px){#stylized input { width: 300px;}textarea{width:310px;height:200px}}@media screen and (max-width:375px){#stylized input{width:275px}textarea{width:285px;height:200px;}}@media screen and (max-width:350px){#stylized input{width:250px}textarea{width:260px;height:200px;}}@media screen and (max-width:320px){#stylized input{width:250px}textarea{width:250px;height:200px;margin:0 0 0 .75em}}

.hidden-with-pos { position: fixed; top: -500px; left: -500px; }

#center {text-align: center; color:white;}

	
	
 </style>
 </head>
<body>
 <?php if (!empty($msg)) {
    echo "<p>&nbsp;</p><h2 id=center>&nbsp;  $msg.  <br>Home in 5 seconds.</h2>
    <script>
        var timer = setTimeout(function() {
            window.location='http://livebolivar.com'
        }, 5000);
    </script>";
} ?>
<div class="page">
			<header>
				<img src="../images/banner_large.jpg" class="print" width="100%" />
				<a class="logo" href="../index.html">
					<img src="../images/banner_large.jpg" class="print" width="115" />
				</a>
			</header>
<img src="aj8.jpg" alt="PROPERTY FOR RENT IN AND AROUND BOLIVAR, POLK COUNTY, MISSOURI 65613. CALL AJ ELLIS 1-417-327-3911 M-F 10-5" width="100%">
<p>HI,  MY NAME IS AJ ELLIS. I'VE LIVED AND BUILT PROPERTY IN POLK COUNTY MISSOURI FOR OVER 25 YEARS. </p>
<p>QUIET AND SOLITUDE. TWENTY-THREE MILES NORTH OF THE THIRD LARGEST CITY IN MISSOURI AND TWENTY-THREE MILES EAST OF THE FIFTH LARGEST LAKE IN MISSOURI, ALL THE CULTURAL AND RECREATIONAL OPPORTUNITES WITHOUT THE HUSTLE AND BUSTLE. AWARD WINNING PUBLIC SCHOOLS, HOSPITAL AND PRIVATE UNIVERSITY. </p>
<p>COME TO WHERE THE FOUNDERS OF SPRINGFIELD CHOOSE TO MAKE THEIR HOME. </p>
<p>COME HOME TO BOLIVAR.</p> 


<p id=z042718>
 <a href=map_office13.html><span class=hidelg>MAP TO</span> OFFICE</a><br>
 <a href="tel:1-417-327-3911">CALL AJ M-F 10-5</a><br>
 <a href="sms:1-417-327-3911">TEXT</a><br>
 <a href="MAILTO:AJELLIS@LIVEBOLIVAR.COM">AJELLIS@LIVEBOLIVAR.COM</a><br>
  <a href="MAILTO:4173273911@vtext.com">TEXT USING EMAIL</a><br>
OR BY USING THE FORM BELOW.</p>
 <p>WELCOME TO BOLIVAR.</p>
 <img src="../images/_sig13.gif" alt="CALL AJ ELLIS AT 1-417-327-3911 FOR ALL YOUR PROPERTY RENTAL NEEDS IN AND AROUND BOLIVAR, MISSOURI 65613" width="150" height="72" border="0" align="left">
</p>
<P>&nbsp;</P><P>&nbsp;</P>

<div id="stylized" class="myform">
<form method="post">

	<label for="name">Name: <input type="text" name="name" id="name"></label><br>
	<label for="email">Email address: <input type="email" name="email" id="email"></label><br>
	<label for="message">Message: <textarea name="message" id="message"></textarea></label><br>		
	<!-- // <BEGIN HONEYPOT> -->
	<!-- Important: if you add any fields to this page, you will also need to update the php script -->
	<p class="hidden-with-pos">Leave this empty:<br /><input name="url" /></p>
	<!-- // <END HONEYPOT> -->
	<input type="submit" value="Send" />
</form>
</div>

<div class="clear-fix">&nbsp;</div>

			<nav>
				<a href="../index.html">HOME</a>
				<a href=tel:1-417-327-3911>CALL <span class=hidelg><span class=hidep>1-</span>417-327-3911 M<span class=hidep>ON</span>-F<span class=hidep>RI</span><span class=hidep>&nbsp;</span>10<span class=hidep>AM</span>-5<span class=hidep>PM</span></span></a> <a href=map_office13.html> <span class=hidelg>MAP TO</span> OFFICE</a> <a href=app_exported/app.php><span class=hidelg>ONLINE</span> APPLICATION</a>
				<a href=weather13.php>WEATHER <span class=hidelg>RADAR</span></a> <a href=info13.html>INFO<span class=hidelg>RMATION</span></a> <a href="https://www.google.com/maps/dir/?api=1&destination=37.6019214,-93.4168756&travelmode=driving&zoom=13">DRIVING DIRECTIONS AT GOOGLE MAPS</a>
				<a class="hidelg" href="../index.html">HOME</a>
			</nav>
			
<footer class="noPrint">
				&copy; LIVEBOLIVAR.COM
				<div class="print qr">
					<img src="../prop_html/qrcode.38781946.png" width="50%" alt="PROPERTY FOR RENT IN AND AROUND BOLIVAR, POLK COUNTY, MISSOURI 65613. CALL AJ ELLIS 1-417-327-3911 M-F 10-5"/>
					<p><em>This page printed from:</em></p>
					<pre>livebolivar.com</pre>
					<p><strong>livebolivar.com</strong> is Bolivar's leading property rental company.</p>
					<div class="clear-fix"></div>
				</div>
			</footer> 
</body>
</html>





