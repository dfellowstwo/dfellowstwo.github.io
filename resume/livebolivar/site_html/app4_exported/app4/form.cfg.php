<?php exit(0); ?> { 
"settings":
{
	"data_settings" : 
	{
		"save_database" : 
		{
			"database" : "",
			"is_present" : false,
			"password" : "",
			"port" : 3306,
			"server" : "",
			"tablename" : "",
			"username" : ""
		},
		"save_file" : 
		{
			"filename" : "form-results.csv",
			"is_present" : true
		},
		"save_sqlite" : 
		{
			"database" : "app4.dat",
			"is_present" : false,
			"tablename" : "app4"
		}
	},
	"email_settings" : 
	{
		"auto_response_message" : 
		{
			"custom" : 
			{
				"body" : "\n<!DOCTYPE html>\n<html dir=\"ltr\" lang=\"en\">\n<head><title>You got mail!</title></head>\n<body style=\"background-color: #f9f9f9; padding-left: 1%; padding-top:1%; padding-right: 20px; max-width: 1000px; font-family: Helvetica,Arial;\">\n<style type=\"text/css\">\nbody {background-color: #f9f9f9;padding-left: 1%; padding-top: 1%;padding-right: 2%;max-width:1000px;font-family: Helvetica, Arial;}p{font-size: 12px; color: #666666;}\nh1{font-size: 60px !important;color: #cccccc !important;margin:0px;}\nh2{font-size: 28px !important;color: #666666 ! important;margin: 0px;border-bottom: 1px dotted #00A2FF; padding-bottom:3px;}\ntable{width:100%;}\ntd:first-child {width:40%; font-size: 12px !important; line-height:30px;color: #666666 !important; margin: 0px;border-bottom: 1px solid#e9e9e9;}\ntd:last-child {width:60%; font-size: 12px !important;font-weight:bold; color: #333 !important; vertical-align:text-top;padding-right:5px;}\na:link {color:#666666; text-decoration:underline;} a:visited{color:#666666; text-decoration:none;} a:hover {color:#00A2FF;}\nb{font-weight: bold;}\n</style>\n<img src=\"https://www.livebolivar.com/images/_banner4.jpg\" alt=\"\" width=\"750\" height=\"135\">\n<h2 style=\"font-size: 28px !important;color: #666666 ! important;margin: 0px; border-bottom: 1px dotted #00A2FF; padding-bottom:3px;\">Thanks for taking the time to fill out LIVEBOLIVAR.COM's<br/> rental application form. <br/>Here's a copy of what you submitted:</h2>\n<div>\n[_form_results_]\n</div>\n</body>\n</html>",
				"is_from_red" : false,
				"is_present" : true,
				"key" : "custom-code(1)",
				"subject" : "Thank you for your submission"
			},
			"from" : "AJELLIS@LIVEBOLIVAR.COM",
			"is_present" : true,
			"to" : "[EMAIL]"
		},
		"notification_message" : 
		{
			"bcc" : "",
			"cc" : "",
			"custom" : 
			{
				"body" : "<!DOCTYPE html>\n<html dir=\"ltr\" lang=\"en\">\n<head><title>You got mail!</title></head>\n<body style=\"background-color: #f9f9f9; padding-left: 5%; padding-top: 3%; padding-right: 10px; max-width: 850px; font-family: Helvetica, Arial;\">\n<style type=\"text/css\">\nbody {background-color: #f9f9f9;padding-left: 5%; padding-top: 3%; padding-right: 1%;max-width:850px;font-family: Helvetica, Arial;}\np{font-size: 10px; color: #666666;}\nh1{font-size: 60px !important;color: #cccccc !important;margin:0px;}\nh2{font-size: 28px !important;color: #666666 ! important;margin: 0px; border-bottom: 1px dotted #00A2FF; padding-bottom:3px;}\ntable{width:100%;}\ntd {font-size: 12px !important; line-height: 13px;color: #666666 !important; margin: 0px;border-bottom: 1px solid #e9e9e9;}\ntd:first-child {font-size: 13px !important; font-weight:bold; color: #333 !important; vertical-align:text-top; min-width:10%; padding-right:5px;}\na:link {color:#666666; text-decoration:underline;} a:visited {color:#666666; text-decoration:none;} a:hover {color:#00A2FF;}\nb{font-weight: bold;}\n</style>\n<h3 style=\"font-size: 20px !important; color: #000000 !important; margin: 0px;\">By filling out and submitting this application I authorize and give permission to Ellis Rentals to verify all information provided on this form as to my credit, employment, past and current rental information, criminal background check and personal references.</h3>\n<p style=\"font-size: 12px; color: #666666;\">\nThis notification can be received w/o payment being received.<br>\nConfirm Paypal payment email was received.<br></p>\n\n<div>\n[_form_results_]\n</div>\n</body>\n</html>\n",
				"is_from_red" : false,
				"is_present" : true,
				"key" : "custom-code(4)",
				"subject" : "Somebody filled out your form!"
			},
			"from" : "[EMAIL]",
			"is_present" : true,
			"replyto" : "",
			"to" : "AJELLIS@LIVEBOLIVAR.COM"
		}
	},
	"general_settings" : 
	{
		"colorboxautoenabled" : false,
		"colorboxautotime" : 3,
		"colorboxenabled" : false,
		"colorboxname" : "Default",
		"formname" : "app4",
		"is_appstore" : "0",
		"timezone" : "America/Chicago"
	},
	"mailchimp" : 
	{
		"apiKey" : "",
		"lists" : []
	},
	"payment_settings" : 
	{
		"confirmpayment" : "<center>\n<p>&nbsp; </p>\n<style type=\"text/css\">\n#docContainer table {width:80%; margin-top: 5px; margin-bottom: 5px;}\n#docContainer td {text-align:right; min-width:25%; font-size: 12px !important; line-height: 20px;margin: 0px;border-bottom: 1px solid #e9e9e9; padding-right:5px;}\n#docContainer td:first-child {text-align:left; font-size: 13px !important; font-weight:bold; vertical-align:text-top; min-width:50%;}\n#docContainer th {font-size: 13px !important; font-weight:bold; vertical-align:text-top; text-align:right; padding-right:5px;}\n#docContainer th:first-child {text-align:left;}\n#docContainer tr:first-child {border-bottom-width:2px; border-bottom-style:solid;}\n#docContainer center {margin-bottom:15px;}\n#docContainer form input { margin:5px; }\n#docContainer #fb_confirm_inline { margin:5px; text-align:center;}\n#docContainer #fb_confirm_inline>center h2 { }\n#docContainer #fb_confirm_inline>center p { margin:5px; }\n#docContainer #fb_confirm_inline>center a { }\n#docContainer #fb_confirm_inline input { border:none; color:transparent; font-size:0px; background-color: transparent; background-repat: no-repeat; }\n#docContainer #fb_paypalwps { background: url('https://coffeecupimages.s3.amazonaws.com/paypal.gif');background-repeat:no-repeat; width:145px; height:42px; }\n#docContainer #fb_authnet { background: url('https://coffeecupimages.s3.amazonaws.com/authnet.gif'); background-repeat:no-repeat; width:135px; height:38px; }\n#docContainer #fb_2checkout { background: url('https://coffeecupimages.s3.amazonaws.com/2co.png'); background-repeat:no-repeat; width:210px; height:44px; }\n#docContainer #fb_invoice { background: url('https://coffeecupimages.s3.amazonaws.com/btn_email.png'); background-repeat:no-repeat; width:102px; height:31px; }\n#docContainer #fb_invoice:hover { background: url('https://coffeecupimages.s3.amazonaws.com/btn_email_hov.png'); }\n#docContainer #fb_goback { color: inherit; }\n</style>\n\n[_cart_summary_]\n<h2>Almost done! </h2>\n<p>Your application will not be processed until you click the payment button below.</p>\n<p>You can review your order before the payment is made.</p>\n<p>If you do not have a PayPal account you can \"Create a PayPal account\" for free.</p>\n<a id=\"fb_goback\"href=\"?action=back\">Back to form</a></center>",
		"currencysymbol" : "$",
		"decimals" : 2,
		"fixedprice" : "2500",
		"invoicelabel" : "LIVEBOLIVAR.COM's RENTAL APPLICATION FEE | CALL LORI ELLIS AT 1-417-777-5049 M-F 10-5",
		"is_present" : true,
		"paymenttype" : "redirect",
		"paypalwps" : 
		{
			"business" : "ajellis@livebolivar.com",
			"enabled" : true,
			"pdt_token" : "NJ#JDDxa>{CJBQ<\\&b|.D@-{*e)~$0DI$3MB9\",`L,O+]b%$z!Z.*@3#}'-"
		},
		"shopcurrency" : "USD",
		"usecustomsymbol" : false
	},
	"redirect_settings" : 
	{
		"confirmpage" : "<!DOCTYPE html>\n<html dir=\"ltr\" lang=\"en\">\n<head>\n<title>Success!</title>\n<meta charset=\"utf-8\">\n<style type=\"text/css\">\nbody {font-family: Helvetica, Arial;}\ntable{width:100%;}\np{font-size: 16px;font-weight: bold;color: #666;}\nh1{font-size: 60px !important;color: #ccc !important;margin:0px;}\nh2{font-size: 28px !important;color: #666 !important;margin: 0px; border-bottom: 1px dotted #00A2FF; padding-bottom:3px;}\nh3{font-size: 16px; color: #a1a1a1; border-top: 1px dotted #00A2FF; padding-top:1.7%; font-weight: bold;}\nh3 span{color: #ccc;}\ntd {font-size: 12px !important; line-height: 30px;  color: #666 !important; margin: 0px;border-bottom: 1px solid #e9e9e9;}\ntd:first-child {font-size: 12px !important; font-weight:bold; color: #333 !important; vertical-align:text-top; width:40%; padding-right:5px;}\ntd:last-child {font-size: 13px !important; font-weight:bold; color: #333 !important; vertical-align:text-top; width:60%; padding-right:5px;}\na:link {color:#666; text-decoration:none;} a:visited {color:#666; text-decoration:none;} a:hover {color:#00A2FF;}\nhtml{\n\tbackground-color:#FFFFCD;\n\tbackground-repeat: repeat;\n\t}\n.container{width:750px;margin:0 auto;}\nIMG.doug {\n    display: block;\n    margin-left: auto;\n    margin-right: auto }\n</style>\n<script type=\"text/javascript\" src=\"../../../files/_jquery.min.js\"></script>\n<script type=\"text/javascript\" src=\"../../../files/_stickytooltip.js\"></script>\n<link rel=\"stylesheet\" type=\"text/css\" href=\"../../../files/_stickytooltip.css\">\n</head>\n<body class=\"container\">\n<img class=\"doug\" src=\"../../../images/_banner4.jpg\" alt=\"\" width=\"750\" height=\"135\" usemap=\"#Map\">\n  \n  <map name=\"Map\"><area shape=\"rect\" coords=\"313,122,652,141\" href=\"MAILTO:AJELLIS@LIVEBOLIVAR.COM\" title=\"CLICK HERE TO SEND ME AN EMAIL\" alt=\"CLICK HERE TO SEND ME AN EMAIL\" data-tooltip=\"email\"><area shape=\"rect\" coords=\"-10,122,305,136\" href=\"TEL:+14177775049\" title=\"CLICK TO CALL LORI AT 417-777-5049\" alt=\"CLICK TO CALL ME\" data-tooltip=\"skype2\"><area shape=\"rect\" coords=\"173,1,650,105\" href=\"#\" onClick=\"history.go(-1);return false;\" title=\"PROPERTY AVAILABLE NOW OR IN 30 DAYS\" alt=\"GO BACK\" data-tooltip=\"banner\"><area shape=\"rect\" coords=\"654,4,748,132\" href=\"../../site_html/map_office13.html\" title=\"CLICK HERE FOR A MAP TO THE OFFICE\" alt=\"CLICK HERE FOR A MAP TO THE OFFICE\" data-tooltip=\"office\"><area shape=\"rect\" coords=\"1,1,170,105\" href=\"../about-us13.php\" title=\"CLICK HERE TO LEARN MORE ABOUT US\" alt=\"CLICK HERE TO LEARN MORE ABOUT US\" data-tooltip=\"aj\">\n<area shape=\"rect\" coords=\"0,107,653,120\" href=\"../../site_html/map_office13.html\" alt=\"CLICK HERE FOR A MAP TO THE OFFICE\" title=\"CLICK HERE FOR A MAP TO THE OFFICE\" data-tooltip=\"map_address\">\n</map>\n<h1>Thanks! </h1>\n<h2>If you had a problem send us a <a href=../../contact13b.php style=\"color:blue;\">message</a>.</h2>\n<h2>We received your payment and the form is on its way. <br>You may log into your account at <a href=\"http://www.paypal.com\" style=\"color:blue;\">www.paypal.com</a> to view details of this transaction. </h2>\n<p>A copy of your answers will be emailed to the address you entered in the form.</p>\n<p>Here&rsquo;s what you sent:</p>\n<div>[_form_results_]</div>\n<!-- link back to your Home Page -->\n<h3>Let&rsquo;s go <span> <a target=\"_blank\" href=\"https://www.livebolivar.com/\">Back Home</a></span></h3></center>\n<div id=\"mystickytooltip\" class=\"stickytooltip\">\n<div style=\"padding:5px\">\n   <div id=\"email\" class=\"atip\"> <img src=\"../../email.jpg\" height=\"322\" width=\"353\" alt=\"\"> </div>\n   <div id=\"skype\" class=\"atip\"> <img src=\"../../skype.jpg\" height=\"322\" width=\"353\" alt=\"\"> </div>\n   <div id=\"skype2\" class=\"atip\"> <img src=\"../../skype2.jpg\" height=\"558\" width=\"410\" alt=\"\"> </div>\n   <div id=\"aj\" class=\"atip\"> <img src=\"../../aj.jpg\" height=\"322\" width=\"353\" alt=\"\"> </div>\n   <div id=\"banner\" class=\"atip\"> <img src=\"../../banner.jpg\" height=\"108\" width=\"351\" alt=\"\"> </div>\n   <div id=\"office\" class=\"atip\"> <img src=\"../../../images/map_office.jpg\" height=\"438\" width=\"353\" alt=\"\"> </div>\n   <div id=\"map_screenshot\" class=\"atip\"> <img src=\"../../map_screenshot6.jpg\" height=\"322\" width=\"525\" alt=\"\"> </div>\n   <div id=\"map_address\" class=\"atip\"> <img src=\"../../map_screenshot6.jpg\" height=\"322\" width=\"525\" alt=\"\"> </div>\n   </div>\n<div class=\"stickystatus\"></div></div>\n</body>\n</html>",
		"gotopage" : "",
		"inline" : "<center>\n<style type=\"text/css\">\n#docContainer table {margin-top: 30px; margin-bottom: 30px; width:80% !important;}\n#docContainer td {font-size: 12px !important; line-height: 30px;color: #666666 !important; margin: 0px;border-bottom: 1px solid #e9e9e9;}\n#docContainer td:first-child {font-size: 13px !important; font-weight:bold; color: #333 !important; vertical-align:text-top; min-width:50%; padding-right:5px;}\n</style>\n[_form_results_]\n<h2>Thank you!</h2><br/>\n<p>Your form was successfully submitted. We received the information shown above.</p>\n</center>",
		"type" : "confirmpage"
	},
	"uid" : "b9e30e5b105d132c12a17790ce2513a5",
	"validation_report" : "in_line"
},
"rules":{"propertyiwouldliketolease":{"label":"I would like to lease","fieldtype":"dropdown","required":true,"values":["","","Springhill Falls 1BR","Springhill Falls 2BR","Springhill Falls 3BR"]},"moveinmonth":{"label":"Move in month","fieldtype":"dropdown","required":true,"values":["","ASAP","June","July","August","September","October","November","December","January","February","March","April","May"]},"moveinday":{"label":"Move in Day","fieldtype":"dropdown","required":true,"values":["","ASAP","1","2","3","4","5","6","7","8","9","10","11","12","13","14","15","16","17","18","19","20","21","22","23","24","25","26","27","28","29","30","31"]},"comment":{"label":"Comment","fieldtype":"text"},"name":{"label":"Name","fieldtype":"text","required":true,"messages":"THIS FIELD IS REQUIRED"},"email":{"email":true,"label":"Email","fieldtype":"email","required":true,"messages":"THIS FIELD IS REQUIRED"},"ssn":{"label":"Social Security Number","fieldtype":"text","required":true,"messages":"THIS FIELD IS REQUIRED"},"dob":{"label":"Date of Birth","fieldtype":"text","required":true,"messages":"THIS FIELD IS REQUIRED"},"phone":{"phone":"phoneUS","label":"Phone","fieldtype":"tel","required":true,"messages":"THIS FIELD IS REQUIRED"},"upload":{"label":"Drivers license","accept":"jpg|jpeg|png|gif|tif|tiff","files":false,"attach":true,"database":false,"maxbytes":1024000,"fieldtype":"fileupload","required":true,"messages":"THIS FIELD IS REQUIRED"}},
"payment_rules":{},
"conditional_rules":{},
"application_version":"Web Form Builder (Windows), build 2.5.5437"
}