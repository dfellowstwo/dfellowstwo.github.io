<?php
// USED BY CONTACT13.HTML AND CONTACT13.PHP  http://technopoints.co.in/php-send-mail/
$name = $_POST['name'];
$email = $_POST['email'];
$message = $_POST['message'];
$formcontent=" From: $name \n Message: $message";
// $recipient = "4173273911@vtext.com";
// $recipient = "4173999579@vmobl.com";
$recipient = "ajellis@livebolivar.com";
$subject = "Livebolivar.com Contact Form";
$mailheader = "From: $email \r\n";
$mail->setFrom('ajellis@livebolivar.com', 'AJ ELLIS');
mail($recipient, $subject, $formcontent, $mailheader) or die("Error!");
?>
<!doctype html>
<!-- saved from url=(0014)about:internet -->
<html lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>CONTACT US</title>
<meta name="description" content="PROPERTY FOR RENT IN AND AROUND BOLIVAR, POLK COUNTY, MISSOURI 65613. CALL AJ ELLIS 1-417-327-3911 M-F 10-5">
<meta name="keywords" content="PROPERTY FOR RENT IN AND AROUND BOLIVAR, POLK COUNTY, MISSOURI 65613. CALL AJ ELLIS 1-417-327-3911 M-F 10-5">
<meta name="robots" content="INDEX,FOLLOW">
<meta name="googlebot" content="INDEX,FOLLOW">
<meta name="author" content="DOUG FELLOWS">

<!--[if lt IE 9]>
<script src="https://html5shiv.googlecode.com/svn/trunk/html5.js"></script>
<![endif]-->

<style type="text/css">

p	{ padding:0 1em 0 1em;	}
@media screen and (max-width: 534px) {
		body{font-size:24px;}
		#z042718 a {line-height:3em;}
		}
 </style>
 <style>html,body,div,span,applet,object,iframe,h1,h2,h3,h4,h5,h6,p,blockquote,pre,a,abbr,acronym,address,big,cite,code,del,dfn,em,img,ins,kbd,q,s,samp,small,strike,strong,sub,sup,tt,var,b,u,i,center,dl,dt,dd,ol,ul,li,fieldset,form,label,legend,table,caption,tbody,tfoot,thead,tr,th,td,article,aside,canvas,details,embed,figure,figcaption,footer,header,hgroup,menu,nav,output,ruby,section,summary,time,mark,audio,video{margin:0;padding:0;border:0;font:inherit;font-size:100%;vertical-align:baseline}@media screen{.print{display:none}}html{line-height:1em}.hide{display:none}a{text-decoration:none}ol,ul{list-style:none}table{border-collapse:collapse;border-spacing:0}caption,th,td{text-align:left;font-weight:normal;vertical-align:middle}q,blockquote{quotes:none}q:before,q:after,blockquote:before,blockquote:after{content:"";content:none}a img{border:0}article,aside,details,figcaption,figure,footer,header,hgroup,main,menu,nav,section,summary{display:block}*,*:before,*:after{-moz-box-sizing:border-box;-webkit-box-sizing:border-box;box-sizing:border-box}.red-text{color:#f00}.green-text{color:#008000}.noScreen{display:none}.noScreen2{display:none}body{color:#000;font-family:Arial;font-size:16px;background:#515673 url(../images/background_gradient.jpg) repeat-x 0 0}.page{max-width:1360px;margin:0 auto;position:relative;background-color:#fff;padding:.5em}h1{font-size:2em;margin:0 0 .5em 0;font-weight:normal;color:#a6430a;line-height:1em}h2{font-size:1.7em;margin:0 0 1em 0}h3{font-size:1.5em;margin:0 0 1em 0}a{color:#de9000;text-decoration:none;line-height:1em}a:hover{color:#009eff}a.cta:hover{background-position:right -44px}footer{font-size:.85em;color:#000;background-color:#fff;padding:10px 10px 10px 0}.promo h3{font-size:1.1em;margin:0}.promo{background-repeat:no-repeat;background-size:60px 60px;margin-bottom:2em}.promo.one{background-image:url(../images/promo_1.jpg)}.promo.two{background-image:url(../images/promo_2.jpg)}.promo.three{background-image:url(../images/promo_3.jpg)}.promo.four{background-image:url(prop_html/116_w_maupin_st_a_b60X60.jpg)}.promo.five{background-image:url(prop_html/709-w-fairplay-st60x60.jpg)}.promo.six{background-image:url(prop_html/211_w_division_st60x60.jpg)}.promo.seven{background-image:url(prop_html/835_e_buffalo_st60x60.jpg)}.promo.eight{background-image:url(prop_html/1870-1890_pike_ave60x60.jpg)}.promo.nine{background-image:url(prop_html/1870-1890_pike_ave60x60.jpg)}.promo.ten{background-image:url(prop_html/760_762_morgan_st60x60.jpg)}.promo.eleven{background-image:url(prop_html/909-n-albany-ave60X60.jpg)}.promo.twelve{background-image:url(prop_html/427_bradford_st60x60.jpg)}.promo.eleven{background-image:url(prop_html/511_n_benton_ave60x60.jpg)}.promo.fourteen{background-image:url(prop_html/422_bradford_st60x60.jpg)}.promo.fifteen{background-image:url(prop_html/422_bradford_st60x60.jpg)}.promo.sixteen{background-image:url(prop_html/740_742_morgan_st60x60.jpg)}.promo.seventeen{background-image:url(prop_html/839_841_morgan_st60x60.jpg)}.promo.eighteen{background-image:url(prop_html/317_n_pike_ave60X60.jpg)}.promo.nineteen{background-image:url(prop_html/210_s_dunnegan_ave60X60.jpg)}.promo.twenty{background-image:url(prop_html/611_w_locust_st60X60.jpg)}.promo.twenty-one{background-image:url(prop_html/920-w-morgan-st13-60X60.jpg)}.promo.twenty-two{background-image:url(prop_html/413_n_park_ave60X60.jpg)}.promo.twenty-three{background-image:url(prop_html/515_e_summit_st60x60.jpg)}.promo.twenty-four{background-image:url(prop_html/2877_s_orchard_ave60x60.jpg)}.promo.twenty-five{background-image:url(prop_html/222_n_albany_ave60x60.jpg)}.promo.twenty-six{background-image:url(prop_html/824_s_lillian_ave60x60.jpg)}.promo.twenty-seven{background-image:url(prop_html/206_w_walnut_st60X60.jpg)}.promo.twenty-eight{background-image:url(prop_html/314_n_gary_ave60x60.jpg)}.promo.twenty-nine{background-image:url(prop_html/811_w_locust_st60x60.jpg)}.promo.thirty{background-image:url(prop_html/216_s_dunnegan_ave60x60.jpg)}.promo.thirty-one{background-image:url(prop_html/639_morgan_st60x60.jpg)}.promo.thirty-two{background-image:url(prop_html/760_762_morgan_st60x60.jpg)}.promo.thirty-three{background-image:url(prop_html/637-n-main-ave60x60.jpg)}.promo.thirty-four{background-image:url(prop_html/513_s_missouri_ave60x60.jpg)}nav a{color:#f5a06e;text-transform:uppercase;text-decoration:none;display:inline-block;font-weight:bold;font-size:.9em}nav a:hover{color:#fff}.clear-fix{clear:both;line-height:1px}.print{display:none}#springhill{margin:0 0 .5em 0}cssgif{max-width:88px;float:right;margin:0 0 .25em 0}.spacelink{padding-left:1em}.videolinks{padding-left:1em}.vspace{margin-bottom:1em}.vspace2{margin-bottom:.25em}.springhill{padding-left:.25em}body{margin:0;padding:0}header a.logo{display:block;position:absolute;background-position:0 0;background-repeat:no-repeat}header a.logo{width:80px;height:80px;top:9px;right:9px;background-image:url(../images/icon-phone.png);background-size:80px 80px}header{height:382px;background:url(../images/banner_large1360.jpg) no-repeat right 0;background-size:1360px 382px}nav{top:390px;width:100%;display:block;position:absolute;background-color:#a6430a}nav a{color:#f5a06e;text-transform:uppercase;display:inline-block;font-weight:bold;font-size:.9em;padding:1em}nav a:hover{color:#fff}article{padding:70px 20px 10px 0}.promo_container{padding:0 0 15px 20px}.promo_container .promo{width:33%;float:left;background-position:0 0}.promo_container .promo .content{padding:0 30px 0 70px}.promo_container2{margin:2em 0 0 0;padding:0 0 15px 20px}.promo_container2 .promo{width:33%;float:left;background-position:0 0}.promo_container2 .promo .content{padding:0 30px 0 70px}.promo_container3{margin:2em 0 0 0;padding:0 0 15px 20px}.promo_container3 .promo{width:33%;float:left;background-position:0 0}.promo_container3 .promo .content{padding:0 30px 0 70px}p{margin:1em 2em .75em 0}.promo h1{margin:0}.promo a.cta{font-size:1.25em;line-height:1em;font-weight:bold;margin:0;padding:0 1em 0 0;background:url(../images/cta_arrow.png) no-repeat right 0 top 6px}.hidelg{display:none}footer{padding-left:20px}@media screen and (min-width:50px) and (max-width:320px){header{height:75px;background:url(../images/banner_small.jpg);background-size:500px 85px;background-repeat:no-repeat;background-position:center top}header a.logo{width:40px;height:40px;top:6px;right:5px;background-image:url(../images/icon-phone.png);background-size:40px 40px}html{line-height:1.5em}#noRent{display:none}.spacelink{padding-left:2em}.videolinks{padding-left:2em}.vspace{margin-bottom:2em}.vspace2{margin-bottom:.25em}p+p{margin-top:2em}@media only screen and (min-width:50px) and (max-width:320px){a.cta span{display:none}}article{padding:20px 20px 10px 0}li{text-align:left;padding:1em 0}nav{display:block;position:static;padding:10px 0 10px 0;background-color:#515673}nav a{color:#fff;display:block;margin:15px 15px 30px 15px;padding:9px;border:1px solid #a6abc5;background:url(../images/mobile_link_arrow.png) no-repeat right center;-webkit-border-radius:12px;-moz-border-radius:12px;border-radius:12px;font-size:1.25em;}nav a:hover{color:#fff;background-color:rgba(255,255,255,.15)}.promo_container{padding:0}.promo_container .promo{width:auto;float:none;padding:10px 0 0 0;background-position:20px 13px;border-top:1px solid #ccc}.promo_container .promo.one{width:auto;background-position:20px 13px}.promo_container .promo.two{width:auto;background-position:20px 13px}.promo_container .promo.three{width:auto;background-position:20px 13px}.promo_container .promo .content{padding:0 20px 5px 90px}.promo_container p{font-size:1.5em;margin:0 2em .75em 0}.promo_container2{margin:0;padding:0}.promo_container2 .promo{width:auto;float:none;padding:10px 0 0 0;background-position:20px 13px;border-top:1px solid #ccc}.promo_container2 .promo.twenty-seven{width:auto;background-position:20px 13px}.promo_container2 .promo.twenty-eight{width:auto;background-position:20px 13px}.promo_container2 .promo.thirty-three{width:auto;background-position:20px 13px}.promo_container2 .promo .content{padding:0 20px 5px 90px}.promo_container2 p{font-size:1.5em;margin:.5em 2em .75em 0}.promo_container3{margin:0;padding:0}.promo_container3 .promo{width:auto;float:none;padding:10px 0 0 0;background-position:20px 13px;border-top:1px solid #ccc}.promo_container3 .promo.thirty-four{width:auto;background-position:20px 13px}.promo_container3 .promo .content{padding:0 20px 5px 90px}.promo_container3 p{font-size:1.5em;margin:.5em 2em .75em 0}.promo a.cta{font-size:1.25em;line-height:1em;font-weight:bold;margin:0;padding:0 1em 0 0;background:url(../images/cta_arrow.png) no-repeat right 0 top 12px}a{color:#00f;text-decoration:none;line-height:1em}article p{font-size:1.25em;font-weight:bold;margin:0 0 1.25em 0;padding:0;background:0}article p a.cta{line-height:3em}.hidelg{display:inline-block}footer{border-top:1px solid #a6abc5;padding-left:0}footer a{padding:0 1em 0 0}body{background-image:none}}@media screen and (min-width:321px) and (max-width:600px){#noRent{width:45%}header{height:75px;background:url(../images/banner_small.jpg);background-size:500px 85px;background-repeat:no-repeat;background-position:center top}header a.logo{width:40px;height:40px;top:6px;right:5px;background-image:url(../images/icon-phone.png);background-size:40px 40px}html{line-height:1.5em}.spacelink{padding-left:2em}.videolinks{padding-left:2em}.vspace{margin-bottom:2em}.vspace2{margin-bottom:.25em}p+p{margin-top:2em}@media only screen and (min-width:50px) and (max-width:320px){a.cta span{display:none}}article{padding:20px 20px 10px 0}li{text-align:left;padding:1em 0}nav{display:block;position:static;padding:10px 0 10px 0;background-color:#515673}nav a{color:#fff;display:block;margin:15px 15px 45px 15px;padding:9px;border:1px solid #a6abc5;background:url(../images/mobile_link_arrow.png) no-repeat right center;-webkit-border-radius:12px;-moz-border-radius:12px;border-radius:12px;font-size:1.25em;}nav a:hover{color:#fff;background-color:rgba(255,255,255,.15)}.promo_container{padding:0}.promo_container .promo{width:auto;float:none;padding:10px 0 0 0;background-position:20px 13px;border-top:1px solid #ccc}.promo_container .promo.one{width:auto;background-position:20px 13px}.promo_container .promo.two{width:auto;background-position:20px 13px}.promo_container .promo.three{width:auto;background-position:20px 13px}.promo_container .promo .content{padding:0 20px 5px 90px}.promo_container p{font-size:1.5em;margin:1em 2em .75em 0}.promo_container2{margin:0;padding:0}.promo_container2 .promo{width:auto;float:none;padding:10px 0 0 0;background-position:20px 13px;border-top:1px solid #ccc}.promo_container2 .promo.twenty-seven{width:auto;background-position:20px 13px}.promo_container2 .promo.twenty-eight{width:auto;background-position:20px 13px}.promo_container2 .promo.thirty-three{width:auto;background-position:20px 13px}.promo_container2 .promo .content{padding:0 20px 5px 90px}.promo_container2 p{font-size:1.5em;margin:.5em 2em .75em 0}.promo_container3{margin:0;padding:0}.promo_container3 .promo{width:auto;float:none;padding:10px 0 0 0;background-position:20px 13px;border-top:1px solid #ccc}.promo_container3 .promo.thirty-four{width:auto;background-position:20px 13px}.promo_container3 .promo .content{padding:0 20px 5px 90px}.promo_container3 p{font-size:1.5em;margin:.5em 2em .75em 0}.promo a.cta{font-size:1.25em;line-height:1em;font-weight:bold;margin:0;padding:0 1em 0 0;background:url(../images/cta_arrow.png) no-repeat right 0 top 12px}a{color:#00f;text-decoration:none;line-height:1em}article p{font-size:1.25em;font-weight:bold;margin:0 0 1.25em 0;padding:0;background:0}article p a.cta{line-height:3em}.hidelg{display:inline-block}footer{border-top:1px solid #a6abc5;padding-left:0}footer a{padding:0 6em 0 0}body{background-image:none}}@media screen and (min-width:601px) and (max-width:800px){.promo_container .promo{width:50%}.clear-fix2{clear:both;line-height:1px}.spacelink{padding-left:1.5em}.videolinks{padding-left:1.5em}.vspace{margin-bottom:1.5em}p.vspace2{margin-bottom:.25em}p+p{margin-top:2em}header{height:200px;background:url(../images/banner_medium.jpg) no-repeat 90% 0;background-size:800px 200px}header a.logo{width:60px;height:60px;top:9px;right:9px;background-image:url(../images/icon-phone.png);background-size:60px 60px}nav{top:208px}nav a{margin-right:20px}.promo_container{padding:0 20px 15px 20px}.promo_container .promo{background-position:0 0}.promo_container .promo .content{padding:70px 30px 0 0}.promo_container .promo.one{background-position:0 0}.promo_container .promo.two{background-position:0 0}.promo_container .promo.three{background-position:0 0}body{background-image:none}a.cta span{display:none}}@media print{.promo img {display:none}.print{display:inherit}.noPrint{display:none}.spacelink{display:none}.vspace{display:none}.vspace2{display:none}.springhill{padding-left:0}.noScreen{display:inline}body{font:12pt Georgia,"Times New Roman",Times,serif;line-height:1.3;background:#fff;color:#000}.promo h1,.promo h2,.promo h3,p,li{color:inherit;font-size:.75em}h1{margin:0;color:inherit;font-size:.75em}a,a:visited{color:inherit;text-decoration:none}.page{width:100%;margin:0;padding:0}nav{display:none}article{padding:0}a.cta:after{content:"Learn more online at: " attr(href);display:none;font-style:italic;font-weight:normal;margin-top:15px}.promo_container{padding:0;font-size:11pt;margin-top:1em;margin-bottom:0}.promo_container .promo{width:100%;margin-top:.5em;margin-bottom:.25em}.promo_container .promo .content{padding:0 30px 0 0}a.cta{text-transform:none;background:0;color:inherit;display:none}footer{color:#000;background:0;padding:0;font-size:.7em}footer .qr{border-top:1px solid #ddd;margin-top:20px;padding-top:20px}footer .qr img{float:left;margin-right:20px}header a.logo{top:15px;background:0}header{display:none}.promo.one,.promo.two,.promo.three,.promo.four,.promo.five,.promo.six,.promo.seven,.promo.eight,.promo.nine,.promo.ten,.promo.eleven,.promo.twelve,.promo.thirteen,.promo.fourteen,.promo.fifteen,.promo.sixteen,.promo.seventeen,.promo.eighteen,.promo.nineteen,.promo.twenty,.promo.twenty-one,.promo.twenty-two,.promo.twenty-three,.promo.twenty-four,.promo.twenty-five,.promo.twenty-six,.promo.twenty-seven,.promo.twenty-eight,.promo.twenty-nine,.promo.thirty,.promo.thirty-one,.promo.thirty-two,.promo.thirty-three,.promo.thirty-four{background:0}.promo{position:relative}.promo img{position:absolute;top:0;left:0;display:none}}.cd-top{display:inline-block;height:20px;width:20px;position:fixed;bottom:40px;right:10px;box-shadow:0 0 10px rgba(0,0,0,0.05);overflow:hidden;text-indent:100%;white-space:nowrap;background:rgba(232,98,86,0.8) url(../images/cd-top-arrow.svg) no-repeat center 50%;visibility:hidden;opacity:0;-webkit-transition:opacity .3s 0s,visibility 0s .3s;-moz-transition:opacity .3s 0s,visibility 0s .3s;transition:opacity .3s 0s,visibility 0s .3s}.cd-top.cd-is-visible,.cd-top.cd-fade-out,.no-touch .cd-top:hover{-webkit-transition:opacity .3s 0s,visibility 0s 0s;-moz-transition:opacity .3s 0s,visibility 0s 0s;transition:opacity .3s 0s,visibility 0s 0s}.cd-top.cd-is-visible{visibility:visible;opacity:1}.cd-top.cd-fade-out{opacity:.5}.no-touch .cd-top:hover{background-color:#e86256;opacity:1}@media only screen and (min-width:768px){.cd-top{right:20px;bottom:20px}}@media only screen and (min-width:1024px){.cd-top{height:60px;width:60px;right:30px;bottom:30px}}@media only screen and (max-width:600px){.cd-top{height:30px;width:30px}}@media screen and (min-width:601px) and (max-width:890px){article{padding:115px 20px 10px 0}}@media screen and (min-width:1359px){nav{width:98.80%}}@media screen and (min-width:1342px) and (max-width:1358px){nav{width:98.80%}}@media screen and (min-width:1325px) and (max-width:1341px){nav{width:98.80%}}@media screen and (min-width:1270px) and (max-width:1324px){nav{width:98.75%}}@media screen and (min-width:1198px) and (max-width:1269px){nav{width:98.70%}}@media screen and (min-width:1104px) and (max-width:1197px){nav{width:98.60%}}@media screen and (min-width:0945px) and (max-width:1103px){nav{width:98.50%}}@media screen and (min-width:0851px) and (max-width:0944px){nav{width:98.10%}}@media screen and (min-width:0718px) and (max-width:0850px){nav{width:97.90%}}@media screen and (min-width:0642px) and (max-width:0717px){nav{width:97.70%}}@media screen and (min-width:0601px) and (max-width:0652px){nav{width:97.40%}}
 /* ----------- My Form ----------- */
.myform{
max-width:600px;
	width:100%;
	padding:14px;
}
	/* ----------- basic ----------- */
	#basic{
		border:solid 2px #DEDEDE;	
	}
	#basic h1 {
		font-size:14px;
		font-weight:bold;
		margin-bottom:8px;
	}
	#basic p{
		font-size:11px;
		color:#666666;
		margin-bottom:20px;
		border-bottom:solid 1px #dedede;
		padding-bottom:10px;
	}
	#basic label{
		display:block;
		font-weight:bold;
		text-align:right;
		width:140px;
		float:left;
	}
	#basic .small{
		color:#666666;
		display:block;
		font-size:11px;
		font-weight:normal;
		text-align:right;
		width:140px;
	}
	#basic input{
		float:left;
		width:200px;
		margin:2px 0 30px 10px;
	}
	#basic button{ 
		clear:both;
		margin-left:150px;
		background:#888888;
		color:#FFFFFF;
		border:solid 1px #666666;
		font-size:11px;
		font-weight:bold;
		padding:4px 6px;
	}


	/* ----------- stylized ----------- */
	#stylized{
		border:solid 2px #b7ddf2;
		background:#de9000;

	}
	#stylized h1 {
		font-size:14px;
		font-weight:bold;
		margin-bottom:8px;
	}
	#stylized p{
		font-size:11px;
		color:#666666;
		margin-bottom:20px;
		border-bottom:solid 1px #b7ddf2;
		padding-bottom:10px;
	}
	#stylized label{
		display:block;
		font-weight:bold;
		
		
		
	}

	#stylized .small{
		color:#666666;
		display:block;
		font-size:11px;
		font-weight:normal;
		text-align:right;
		width:140px;
	}
	#stylized input{
		
		font-size:12px;
		padding:4px 2px;
		border:solid 1px #aacfe4;
		width:400px;
		margin:2px 0 20px 10px;
	}
	#stylized button{ 
		clear:both;
		margin-left:160px;
		width:125px;
		height:31px;
		background:#444;
		text-align:center;
		line-height:31px;
		color:#FFFFFF;
		font-size:11px;
		font-weight:bold;
	}
	textarea{ width:410px;height:200px}
@media screen and (max-width:470px) {
	#stylized input{width:375px;}
	textarea{ width:375px;height:200px}
	}
 @media screen and (max-width:450px) {
	#stylized input{width:325px;}
	textarea{ width:325px;height:200px}
	}
  @media screen and (max-width:375px) {
	#stylized input{width:250px;}
	textarea{ width:250px;height:200px;margin:0 0 0 .75em}
	}
	
 </style>

 </head>
<body>

<div class="page">


<header>
<a class="logo" href="tel:+14173273911" title="CALL AJ ELLIS AT 1-417-2=327-3911 M-F 10-5"></a>
</header>


<article>
 <h1>THANK YOU FOR CONTACTING US.</h1>
</article>
 <p>CALL AJ ELLIS AT <a href="tel:1-417-327-3911">1-417-327-3911</a> FOR ALL YOUR PROPERTY RENTAL NEEDS IN AND AROUND BOLIVAR, POLK COUNTY, SOUTHWEST MISSOURI 65613 M-F 10-5 OR COME TO THE OFFICE - <A HREF="map_office13.html">SPRINGHILL FALLS APARTMENTS<br>
   1325 SOUTH LILLIAN AVE&nbsp; #212 BOLIVAR.</a></p>
<p id=z042718>
 <a href="sms:1-417-327-3911">TEXT AJ</a><br>
 <a href="SKYPE:AJELLIS3?CALL">SKYPE (AJELLIS3)</a><br>
   
    <a href="MAILTO:AJELLIS@LIVEBOLIVAR.COM">EMAIL AJELLIS@LIVEBOLIVAR.COM</a><br>
   
    <a href="MAILTO:4173273911@vtext.com">EMAIL TO MY PHONE</a><br>
OR BY USING THE FORM BELOW.</p>
 <p>WELCOME TO BOLIVAR.</p>
 <img src="../images/_sig13.gif" alt="CALL AJ ELLIS AT 1-417-327-3911 FOR ALL YOUR PROPERTY RENTAL NEEDS IN AND AROUND BOLIVAR, MISSOURI 65613" width="150" height="72" border="0" align="left">
LIVEBOLIVAR.COM<br />
   OFFICE - SPRINGHILL FALLS APARTMENTS<br>
   1325 SOUTH LILLIAN AVE&nbsp; #212
   
   BOLIVAR, MISSOURI 65613<br>  
    <a href="tel:+14173273911">1-417-327-3911</a>    </p>

<div id="stylized" class="myform">

<form id="form1" id="form1" action="mail.php" method="POST">
    <label>CONTACT US</label>
    <br>
    <label>Name
        <span class="small">Add your name</span>
    </label>
<input type="text" name="name">
    <label>Email
        <span class="small">Enter a Valid Email</span>
    </label>
<input type="text" name="email">

    <label>Message
        <span class="small">Type Your Message</span>
    </label>
<textarea name="message"></textarea><br />

    <button type="submit" value="Send" style="margin-top:15px;">Submit</button>
<div class="spacer"></div>

</form>

</div> 
            <!--HOW TO USE EXPRESS INSTALL FEATURE OF SWFOBJECT.JS  https://blog.deconcept.com/swfobject/ -->

<!-- <script type="text/javascript" src="../scripts/swfobject.js"></script><div id="CC7212088">A FORM TO SEND EMAIL SHOULD BE HERE</div> -->
<!-- <p> -->
  <!-- <script type="text/javascript">var so = new SWFObject("contact.swf", "contact2.xml", "750", "259", "7,0,0,0", "#ffffff");so.addParam("classid", "clsid:d27cdb6e-ae6d-11cf-96b8-444553540000");so.addParam("quality", "high");so.addParam("scale", "noscale");so.addParam("salign", "lt");so.addParam("FlashVars", "xmlfile=contact2.xml&amp;w=750&amp;h=259");so.useExpressInstall("../scripts/expressInstall.swf");so.write("CC7212088");</script>  -->
  
           


  			<nav>
				<a href="../index.html">HOME</a>
<a href=tel:1-417-327-3911>CALL <span class=hidelg>327-3911 M-F10-5</span></a> <a href=map_office13.html><span class=hidelg>MAP TO</span> OFFICE</a> <a href=app_exported/app.php><span class=hidelg>ONLINE</span> APPLICATION</a>
<a href=weather13.php>WEATHER <span class=hidelg>RADAR</span></a> <a href=info13.html>INFO<span class=hidelg>RMATION</span></a> <a href="https://www.google.com/maps/dir/?api=1&destination=37.6019214,-93.4168756&travelmode=driving&zoom=13">DRIVING DIRECTIONS AT GOOGLE MAPS</a>

		
			</nav>

<p>&nbsp; </p>
&#169; <a href="https://www.google.com/search?source=hp&q=DOUG+FELLOWS+site%3Alivebolivar.com">LIVEBOLIVAR.COM</a>
<div class="clear-fix"></div>

</div>
</body>
</html>




