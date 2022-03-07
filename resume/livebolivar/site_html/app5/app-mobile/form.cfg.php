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
			"database" : "app-mobile.dat",
			"is_present" : false,
			"tablename" : "app-mobile"
		}
	},
	"email_settings" : 
	{
		"auto_response_message" : 
		{
			"custom" : 
			{
				"body" : "\n<!DOCTYPE html>\n<html dir=\"ltr\" lang=\"en\">\n<head><title>You got mail!</title></head>\n<body style=\"background-color: #f9f9f9; padding-left: 1%; padding-top:1%; padding-right: 20px; max-width: 1000px; font-family: Helvetica,Arial;\">\n<style type=\"text/css\">\nbody {background-color: #f9f9f9;padding-left: 1%; padding-top: 1%;padding-right: 2%;max-width:1000px;font-family: Helvetica, Arial;}p{font-size: 12px; color: #666666;}\nh1{font-size: 60px !important;color: #cccccc !important;margin:0px;}\nh2{font-size: 28px !important;color: #666666 ! important;margin: 0px;border-bottom: 1px dotted #00A2FF; padding-bottom:3px;}\ntable{width:100%;}\ntd:first-child {width:40%; font-size: 12px !important; line-height:30px;color: #666666 !important; margin: 0px;border-bottom: 1px solid#e9e9e9;}\ntd:last-child {width:60%; font-size: 12px !important;font-weight:bold; color: #333 !important; vertical-align:text-top;padding-right:5px;}\na:link {color:#666666; text-decoration:underline;} a:visited{color:#666666; text-decoration:none;} a:hover {color:#00A2FF;}\nb{font-weight: bold;}\n</style>\n<img src=\"http://livebolivar.com/images/_banner4.jpg\" alt=\"\" width=\"750\" height=\"135\">\n<h2 style=\"font-size: 28px !important;color: #666666 ! important;margin: 0px; border-bottom: 1px dotted #00A2FF; padding-bottom:3px;\">Thanks for taking the time to fill out LIVEBOLIVAR.COM's rental application form. <br/>Here's a copy of what you submitted:</h2>\n<div>\n[_form_results_]\n</div>\n</body>\n</html>",
				"is_from_red" : false,
				"is_present" : true,
				"key" : "custom-code",
				"subject" : "Thank you for your submission"
			},
			"from" : "AJELLIS@LIVEBOLIVAR.COM",
			"is_present" : true,
			"to" : "[MY_EMAIL_ADDRESS_IS]"
		},
		"notification_message" : 
		{
			"bcc" : "",
			"cc" : "",
			"custom" : 
			{
				"body" : "<!DOCTYPE html>\n<html dir=\"ltr\" lang=\"en\">\n<head><title>You got mail!</title></head>\n<body style=\"background-color: #f9f9f9; padding-left: 11%; padding-top: 7%; padding-right: 20px; max-width: 700px; font-family: Helvetica, Arial;\">\n<style type=\"text/css\">\nbody {background-color: #f9f9f9;padding-left: 11%; padding-top: 7%; padding-right: 2%;max-width:700px;font-family: Helvetica, Arial;}\np{font-size: 12px; color: #666666;}\nh1{font-size: 60px !important;color: #cccccc !important;margin:0px;}\nh2{font-size: 28px !important;color: #666666 ! important;margin: 0px; border-bottom: 1px dotted #00A2FF; padding-bottom:3px;}\ntable{width:80%;}\ntd {font-size: 12px !important; line-height: 30px;color: #666666 !important; margin: 0px;border-bottom: 1px solid #e9e9e9;}\ntd:first-child {font-size: 13px !important; font-weight:bold; color: #333 !important; vertical-align:text-top; min-width:10%; padding-right:5px;}\na:link {color:#666666; text-decoration:underline;} a:visited {color:#666666; text-decoration:none;} a:hover {color:#00A2FF;}\nb{font-weight: bold;}\n</style>\n<h1 style=\"font-size: 60px !important; color: #cccccc !important; margin: 0px;\">Hi Lori,</h1>\n<p style=\"font-size: 12px; color: #666666;\">\n This is the new application for mobile devices. <br> From April 1, 2017 to April 1, 2018 One hundred and twenty-seven people used the desktop application. <br>The fee is $30. <br> They have a choice of coming into the office, filling out the desktop app for $25, or using the mobile app for $30. <br> The mobile app requires: <br> First Middle Last Name <br> Email <br> SSN <br> DOB <br> Landlord Phone <br> Their Phone <br> Their Address if different than ID <br> Pic of government issued photo ID <br> $30 payment. <BR> As always you must check Paypal to verify payment.</p><p><h1 style=\"font-size: 60px !important; color: #cccccc !important; margin: 0px;\">Hey there,</h1>\n<p style=\"font-size: 12px; color: #666666;\">\nSomeone filled out your form, and here's what they said:\n</p>\n<div>\n[_form_results_]\n</div>\n</body>\n</html>\n",
				"is_from_red" : false,
				"is_present" : true,
				"key" : "custom-code",
				"subject" : "Somebody filled out your form!"
			},
			"from" : "[MY_EMAIL_ADDRESS_IS]",
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
		"formname" : "APPLICATION | LIVEBOLIVAR.COM | CALL AJ ELLIS AT 1-417-327-3911",
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
		"confirmpayment" : "<center>\n<style type=\"text/css\">\n#docContainer table {width:80%; margin-top: 5px; margin-bottom: 5px;}\n#docContainer td {text-align:right; min-width:25%; font-size: 12px !important; line-height: 20px;margin: 0px;border-bottom: 1px solid #e9e9e9; padding-right:5px;}\n#docContainer td:first-child {text-align:left; font-size: 13px !important; font-weight:bold; vertical-align:text-top; min-width:50%;}\n#docContainer th {font-size: 13px !important; font-weight:bold; vertical-align:text-top; text-align:right; padding-right:5px;}\n#docContainer th:first-child {text-align:left;}\n#docContainer tr:first-child {border-bottom-width:2px; border-bottom-style:solid;}\n#docContainer center {margin-bottom:15px;}\n#docContainer form input { margin:5px; }\n#docContainer #fb_confirm_inline { margin:5px; text-align:center;}\n#docContainer #fb_confirm_inline>center h2 { }\n#docContainer #fb_confirm_inline>center p { margin:5px; }\n#docContainer #fb_confirm_inline>center a { }\n#docContainer #fb_confirm_inline input { border:none; color:transparent; font-size:0px; background-color: transparent; background-repat: no-repeat; }\n#docContainer #fb_paypalwps { background: url('https://coffeecupimages.s3.amazonaws.com/paypal.gif');background-repeat:no-repeat; width:145px; height:42px; }\n#docContainer #fb_authnet { background: url('https://coffeecupimages.s3.amazonaws.com/authnet.gif'); background-repeat:no-repeat; width:135px; height:38px; }\n#docContainer #fb_2checkout { background: url('https://coffeecupimages.s3.amazonaws.com/2co.png'); background-repeat:no-repeat; width:210px; height:44px; }\n#docContainer #fb_invoice { background: url('https://coffeecupimages.s3.amazonaws.com/btn_email.png'); background-repeat:no-repeat; width:102px; height:31px; }\n#docContainer #fb_invoice:hover { background: url('https://coffeecupimages.s3.amazonaws.com/btn_email_hov.png'); }\n#docContainer #fb_goback { color: inherit; }\n</style>\n[_cart_summary_]\n<h2>Almost done! </h2>\n<p>Your application will not be processed until you click the payment button below.</p>\n<a id=\"fb_goback\"href=\"?action=back\">Back to form</a></center>",
		"currencysymbol" : "$",
		"decimals" : 2,
		"fixedprice" : "2500",
		"invoicelabel" : "LIVEBOLIVAR.COM's RENTAL APPLICATION FEE | CALL AJ ELLIS AT 1-417-327-3911",
		"is_present" : false,
		"paymenttype" : "redirect",
		"shopcurrency" : "USD",
		"usecustomsymbol" : false
	},
	"redirect_settings" : 
	{
		"confirmpage" : "<!DOCTYPE html>\n<html dir=\"ltr\" lang=\"en\">\n<head>\n<title>Success!</title>\n<meta charset=\"utf-8\">\n<style type=\"text/css\">\nbody {font-family: Helvetica, Arial;}\ntable{width:100%;}\np{font-size: 16px;font-weight: bold;color: #666;}\nh1{font-size: 60px !important;color: #ccc !important;margin:0px;}\nh2{font-size: 28px !important;color: #666 !important;margin: 0px; border-bottom: 1px dotted #00A2FF; padding-bottom:3px;}\nh3{font-size: 16px; color: #a1a1a1; border-top: 1px dotted #00A2FF; padding-top:1.7%; font-weight: bold;}\nh3 span{color: #ccc;}\ntd {font-size: 12px !important; line-height: 30px;  color: #666 !important; margin: 0px;border-bottom: 1px solid #e9e9e9;}\ntd:first-child {font-size: 12px !important; font-weight:bold; color: #333 !important; vertical-align:text-top; width:40%; padding-right:5px;}\ntd:last-child {font-size: 13px !important; font-weight:bold; color: #333 !important; vertical-align:text-top; width:60%; padding-right:5px;}\na:link {color:#666; text-decoration:none;} a:visited {color:#666; text-decoration:none;} a:hover {color:#00A2FF;}\nhtml{\n\tbackground-image:url(../../../images/background770.jpg);\n\tbackground-repeat: repeat;\n\t}\n.container{width:750px;margin:0 auto;}\nIMG.doug {\n    display: block;\n    margin-left: auto;\n    margin-right: auto }\n</style>\n<script type=\"text/javascript\" src=\"../../../files/_jquery.min.js\"></script>\n<script type=\"text/javascript\" src=\"../../../files/_stickytooltip.js\"></script>\n<link rel=\"stylesheet\" type=\"text/css\" href=\"../../../files/_stickytooltip.css\">\n</head>\n<body class=\"container\">\n<img class=\"doug\" src=\"../../../images/_banner4.jpg\" alt=\"\" width=\"750\" height=\"135\" usemap=\"#Map\">\n  \n  <map name=\"Map\"><area shape=\"rect\" coords=\"170,123,453,141\" href=\"MAILTO:AJELLIS@LIVEBOLIVAR.COM\" title=\"CLICK HERE TO SEND ME AN EMAIL\" alt=\"CLICK HERE TO SEND ME AN EMAIL\" data-tooltip=\"email\"><area shape=\"rect\" coords=\"466,121,652,141\" href=\"SKYPE:AJELLIS3?CALL\" title=\"CLICK HERE TO CALL ME WITH SKYPE\" alt=\"CLICK HERE TO CALL ME WITH SKYPE\" data-tooltip=\"skype\"><area shape=\"rect\" coords=\"-10,123,155,136\" href=\"TEL:+14173273911\" title=\"CLICK TO CALL ME\" alt=\"CLICK TO CALL ME\" data-tooltip=\"skype2\"><area shape=\"rect\" coords=\"173,1,650,105\" href=\"#\" onClick=\"history.go(-1);return false;\" title=\"PROPERTY AVAILABLE NOW OR IN 30 DAYS\" alt=\"PROPERTY AVAILABLE NOW OR IN 30 DAYS\" data-tooltip=\"banner\"><area shape=\"rect\" coords=\"654,4,748,132\" href=\"../../map_office.html\" title=\"CLICK HERE FOR A MAP TO THE OFFICE\" alt=\"CLICK HERE FOR A MAP TO THE OFFICE\" data-tooltip=\"office\"><area shape=\"rect\" coords=\"1,1,170,105\" href=\"../../about_us.html\" title=\"CLICK HERE TO LEARN MORE ABOUT US\" alt=\"CLICK HERE TO LEARN MORE ABOUT US\" data-tooltip=\"aj\">\n  <area shape=\"rect\" coords=\"165,107,643,121\" href=\"../../map_office.html\" alt=\"CLICK HERE FOR A MAP TO THE OFFICE\" title=\"CLICK HERE FOR A MAP TO THE OFFICE\" data-tooltip=\"map_address\">\n</map>\n<h1>Thanks! </h1>\n<h2>We received your payment and the form is on its way.</h2>\n<p>Here&rsquo;s what you sent:</p>\n<div>[_form_results_]</div>\n<!-- link back to your Home Page -->\n<h3>Let&rsquo;s go <span> <a target=\"_blank\" href=\"http://www.livebolivar.com/index.html\">Back Home</a></span></h3></center>\n<div id=\"mystickytooltip\" class=\"stickytooltip\">\n<div style=\"padding:5px\">\n   <div id=\"email\" class=\"atip\"> <img src=\"../../email.jpg\" height=\"322\" width=\"353\" alt=\"\"> </div>\n   <div id=\"skype\" class=\"atip\"> <img src=\"../../skype.jpg\" height=\"322\" width=\"353\" alt=\"\"> </div>\n   <div id=\"skype2\" class=\"atip\"> <img src=\"../../skype2.jpg\" height=\"558\" width=\"410\" alt=\"\"> </div>\n   <div id=\"aj\" class=\"atip\"> <img src=\"../../aj.jpg\" height=\"322\" width=\"353\" alt=\"\"> </div>\n   <div id=\"banner\" class=\"atip\"> <img src=\"../../banner.jpg\" height=\"108\" width=\"351\" alt=\"\"> </div>\n   <div id=\"office\" class=\"atip\"> <img src=\"../../../images/map_office.jpg\" height=\"438\" width=\"353\" alt=\"\"> </div>\n   <div id=\"map_screenshot\" class=\"atip\"> <img src=\"../../map_screenshot6.jpg\" height=\"322\" width=\"525\" alt=\"\"> </div>\n   <div id=\"map_address\" class=\"atip\"> <img src=\"../../map_screenshot6.jpg\" height=\"322\" width=\"525\" alt=\"\"> </div>\n   </div>\n<div class=\"stickystatus\"></div></div>\n</body>\n</html>",
		"gotopage" : "https://www.livebolivar.com/site_html/app-mobile_exported/app-mobile/confirm.html",
		"inline" : "<center>\n<style type=\"text/css\">\n#docContainer table {margin-top: 30px; margin-bottom: 30px; width:80%;}\n#docContainer td {font-size: 12px !important; line-height: 30px;color: #666666 !important; margin: 0px;border-bottom: 1px solid #e9e9e9;}\n#docContainer td:first-child {font-size: 13px !important; font-weight:bold; color: #333 !important; vertical-align:text-top; min-width:50%; padding-right:5px;}\n</style>\n[_form_results_]\n<h2>Thank you!</h2><br/>\n<p>Your form was successfully submitted. We received the information shown above.</p>\n</center>",
		"type" : "confirmpage"
	},
	"uid" : "26757d026735764142db7599e6556022",
	"validation_report" : "in_line"
},
"rules":{"my_name_is":{"label":"NAME","fieldtype":"text","required":true},"my_email_address_is":{"email":true,"label":"EMAIL","fieldtype":"email","required":true},"socialsecuritynumber":{"label":"SSN","fieldtype":"text","maxlength":"9","required":true},"my_date_of_birth_is":{"label":"DOB","fieldtype":"text","required":true},"text79":{"label":"LANDLORD","fieldtype":"text","maxlength":"14","required":true},"text80":{"label":"MY PHONE","fieldtype":"text","required":true,"maxlength":"14"},"text77":{"label":"ADDRESS","fieldtype":"text","required":false},"upload1":{"label":"ID","accept":"txt|rtf|jpg|jpeg|png|gif|pdf|bmp|doc|docx|pgp|zip|html|xlsx","files":true,"attach":true,"database":false,"maxbytes":1024000,"fieldtype":"fileupload","required":true}},
"payment_rules":{"my_name_is":{},"my_email_address_is":{},"socialsecuritynumber":{},"my_date_of_birth_is":{},"text80":{}},
"conditional_rules":{},
"application_version":"Web Form Builder (Windows), build 2.5.5437"
}