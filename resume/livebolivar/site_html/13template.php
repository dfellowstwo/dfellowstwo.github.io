<!DOCTYPE HTML>
<html lang="en">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<!--<meta name="viewport" content="width=device-width, maximum-scale=1.0, minimum-scale=1.0, initial-scale=1" />-->
		<title>13-TEMPLATE</title>
	<!--
	  https://css-tricks.com/can-we-prevent-css-caching/
        https://stackoverflow.com/questions/728616/disable-cache-for-some-images
     -->   
        <meta Http-Equiv="Cache-Control" Content="no-cache">
        <meta Http-Equiv="Pragma" Content="no-cache">
        <meta Http-Equiv="Expires" Content="0">
        <meta Http-Equiv="Pragma-directive: no-cache">
        <meta Http-Equiv="Cache-directive: no-cache">
	
	<!--<link rel="stylesheet" type="text/css"  href="reset.css" /> -->
               
        <!--https://css-tricks.com/css-variables-with-php/ -->
		<link rel="stylesheet" type="text/css"  href="screen_styles.css" />
		<link rel="stylesheet" type="text/css"  href="screen_layout_large.css" />
		<link rel="stylesheet" type="text/css" media="only screen and (min-width:50px) and (max-width:600px)"    href="screen_layout_small.css">
		<link rel="stylesheet" type="text/css" media="only screen and (min-width:601px) and (max-width:800px)"   href="screen_layout_medium.css">
		
		<link rel="stylesheet" type="text/css" media="print"  href="print.css" />
        
        <link rel="icon" href="../favicon.ico?v=1.1"> 
		<link href="../images/apple-touch-icon.png" rel="apple-touch-icon">
		<link href="../images/apple-touch-icon-76x76.png" rel="apple-touch-icon" sizes="76x76">
		<link href="../images/apple-touch-icon-120x120.png" rel="apple-touch-icon" sizes="120x120">
		<link href="../images/apple-touch-icon-152x152.png" rel="apple-touch-icon" sizes="152x152">
		<link href="../images/apple-touch-icon-180x180.png" rel="apple-touch-icon" sizes="180x180">
		<link href="../images/icon-hires.png" rel="icon" sizes="192x192">
		<link href="../images/icon-normal.png" rel="icon" sizes="128x128">
		
		<!--[if lt IE 9]>
			<script src="../scripts/html5shiv.js">
			</script>
        <![endif]-->
		<style type="text/css">
		
			.container{width:750px;margin:0 auto;}
			body, td, th {
				background-image:url('../../images/background770.jpg');
				margin: 33px;
				color: #000000;
				font-family: Arial, Helvetica, sans-serif;
				text-transform:uppercase;
				 }

			/*DOUG*/ /*BEGIN CORRECT VERTICAL SPACING BETWEEN EVER CHANGING NAV HEIGHT AND ARTICLE*/
			
			@media only screen and (max-width:800px) {
				
			}
						
			@media only screen and (min-width:801px) {
				
			}
			
			@media screen and (orientation: portrait){
				
			}
			@media screen and (orientation: landscape){
				
			}
			/*DOUG*/ /*END CORRECT VERTICAL SPACING BETWEEN EVER CHANGING NAV HEIGHT AND ARTICLE*/
					
		</style>
	</head>
	<body>

		<div class="page">
			<header><a class="logo" href="tel:+14173273911"></a></header>
          <h1 class="noPrint noScreen">PROPERTY FOR RENT IN AND AROUND BOLIVAR, POLK COUNTY, MISSOURI 65613. CALL AJ ELLIS 1-417-327-3911 M-F 9-5</h1>
          <article>
            <h1>CURRENT ROOT .HTACCESS</h1>
          </article>
			<p># STRONG HTACCESS PROTECTION<br>
			  # https://perishablepress.com/improve-site-security-by-protecting-htaccess-files/<br>
			  &lt;Files ~ &quot;^.*\.([Hh][Tt][Aa])&quot;&gt;<br>
			  order allow,deny<br>
			  deny from all<br>
			  satisfy all<br>
	    &lt;/Files&gt;</p>
			<p>#https://www.crucialhosting.com/knowledgebase/htaccess-apache-rewrites-examples#rewrite-engine<br>
			  Options -Indexes +FollowSymLinks</p>
			<p>### BEGIN REDIRECT RENTAL APPLICATION TO HTTPS ###<br>
			  #https://www.namecheap.com/support/knowledgebase/article.aspx/9770/38/how-to-force-https-using-htaccess-file-in-cpanel<br>
			  #WORKS!!<br>
			  RewriteEngine On <br>
			  RewriteCond %{HTTPS} !=on<br>
			  RewriteRule ^site_html/app_exported/app\.php$ https://livebolivar.com/site_html/app_exported/app.php [R,L]<br>
			  ### END REDIRECT RENTAL APPLICATION TO HTTPS ###</p>
			<p>### BEGIN MIME FILE TYPES ###<br>
			  AddType video/ogg .ogm<br>
			  AddType video/ogg .ogv<br>
			  AddType video/ogg .ogg<br>
			  AddType video/webm .webm<br>
			  AddType audio/webm .weba<br>
			  AddType video/mp4 .mp4<br>
			  AddType video/x-m4v .m4v<br>
			  AddType audio/mpeg .mp3<br>
			  AddType audio/ogg .ogg<br>
			  AddType application/vnd.google-earth.kml+xml .kml<br>
			  AddType application/vnd.google-earth.kmz .kmz<br>
			  ### END MIME FILE TYPES ###</p>
			<p>### BEGIN GOOGLE PAGESPEED INSIGHT TWEAKS ###<br>
			  #https://stackoverflow.com/questions/6878427/leverage-browser-caching-how-on-apache-or-htaccess<br>
			  # Enable Compression<br>
			  &lt;IfModule mod_deflate.c&gt;<br>
			  AddOutputFilterByType DEFLATE application/javascript<br>
			  AddOutputFilterByType DEFLATE application/rss+xml<br>
			  AddOutputFilterByType DEFLATE application/vnd.ms-fontobject<br>
			  AddOutputFilterByType DEFLATE application/x-font<br>
			  AddOutputFilterByType DEFLATE application/x-font-opentype<br>
			  AddOutputFilterByType DEFLATE application/x-font-otf<br>
			  AddOutputFilterByType DEFLATE application/x-font-truetype<br>
			  AddOutputFilterByType DEFLATE application/x-font-ttf<br>
			  AddOutputFilterByType DEFLATE application/x-javascript<br>
			  AddOutputFilterByType DEFLATE application/xhtml+xml<br>
			  AddOutputFilterByType DEFLATE application/xml<br>
			  AddOutputFilterByType DEFLATE font/opentype<br>
			  AddOutputFilterByType DEFLATE font/otf<br>
			  AddOutputFilterByType DEFLATE font/ttf<br>
			  AddOutputFilterByType DEFLATE image/svg+xml<br>
			  AddOutputFilterByType DEFLATE image/x-icon<br>
			  AddOutputFilterByType DEFLATE text/css<br>
			  AddOutputFilterByType DEFLATE text/html<br>
			  AddOutputFilterByType DEFLATE text/javascript<br>
			  AddOutputFilterByType DEFLATE text/plain<br>
  &lt;/IfModule&gt;<br>
  &lt;IfModule mod_gzip.c&gt;<br>
			  mod_gzip_on Yes<br>
			  mod_gzip_dechunk Yes<br>
			  mod_gzip_item_include file .(html?|txt|css|js|php|pl)$<br>
			  mod_gzip_item_include handler ^cgi-script$<br>
			  mod_gzip_item_include mime ^text/.*<br>
			  mod_gzip_item_include mime ^application/x-javascript.*<br>
			  mod_gzip_item_exclude mime ^image/.*<br>
			  mod_gzip_item_exclude rspheader ^Content-Encoding:.*gzip.*<br>
&lt;/IfModule&gt;</p>
			<p># Leverage Browser Caching<br>
			  &lt;IfModule mod_expires.c&gt;<br>
			  ExpiresActive On<br>
			  ExpiresByType image/jpg &quot;access 1 year&quot;<br>
			  ExpiresByType image/jpeg &quot;access 1 year&quot;<br>
			  ExpiresByType image/gif &quot;access 1 year&quot;<br>
			  ExpiresByType image/png &quot;access 1 year&quot;<br>
			  ExpiresByType text/css &quot;access 1 year&quot;<br>
			  ExpiresByType text/html &quot;access 1 year&quot;<br>
			  ExpiresByType application/pdf &quot;access 1 month&quot;<br>
			  ExpiresByType text/x-javascript &quot;access 1 month&quot;<br>
			  ExpiresByType application/x-shockwave-flash &quot;access 1 month&quot;<br>
			  ExpiresByType image/x-icon &quot;access 1 year&quot;<br>
			  ExpiresDefault &quot;access 1 month&quot;<br>
  &lt;/IfModule&gt;<br>
  &lt;IfModule mod_headers.c&gt;<br>
			  #BEGIN https://forums.acquia.com/acquia-products-and-services/acquia-cloud/disable-cache-specific-directory-or-based-file-endings<br>
  &lt;FilesMatch &quot;^/webroot/site_html/weather13.php$&quot;&gt;<br>
			  Header set Cache-Control &quot;max-age=0&quot;<br>
  &lt;/FilesMatch&gt;<br>
			  #  END https://forums.acquia.com/acquia-products-and-services/acquia-cloud/disable-cache-specific-directory-or-based-file-endings<br>
  &lt;filesmatch &quot;\.(pdf|ico|flv|jpg|jpeg|png|gif|css|swf)$&quot;&gt;<br>
			  Header set Cache-Control &quot;max-age=30879000, public&quot;<br>
  &lt;/filesmatch&gt;<br>
  &lt;filesmatch &quot;\.(html|htm|php|js)$&quot;&gt;<br>
			  Header set Cache-Control &quot;max-age=2678400, public&quot;<br>
  &lt;/filesmatch&gt;<br>
  &lt;/IfModule&gt;<br>
			  ### END GOOGLE PAGESPEED INSIGHT TWEAKS ###</p>
		  <div class="clear-fix"></div>
            <nav>
				<a href="../index.html">HOME</a>
				<a href="tel:+14173273911">CALL</a>
				<a href="info13.html">INFO</a>
                <a href="https://www.fox5krbk.com/weather">local fox weather KRBK</a>
				<a href="https://www.ozarksfirst.com/weather">local cbs weather KOLR</a>
				<a href="https://www.kspr.com/weather">local abc weather KSPR</a>
				<a href="https://www.ky3.com/weather/">local nbc weather KY3</a>
				<a href="https://www.google.com/#q=weather+65613">googleweather</a>
				<a href="https://www.weather.com/weather/today/37.653690,-93.399376?par=googleonebox">weather channel</a>
				<a href="https://www.wunderground.com/cgi-bin/findweather/getForecast?query=37.653690,-93.399376&cm_ven=googleonebox">forecast weather underground</a>
				<a href="https://www.wunderground.com/weather-radar/">regional radar weather underground</a>
				<a href="https://www.wunderground.com/radar/radblast.asp?ID=SGF&lat=0&lon=0&label=you&type=N0R&zoommode=pan&map.x=400&map.y=240&centerx=382&centery=567&prevzoom=pan&num=10&delay=15&scale=0.125&showlabels=1&smooth=1&noclutter=1&showstorms=0&rainsnow=1&lightning=0&remembersettings=on&setprefs.0.key=RADNUM&setprefs.0.val=10&setprefs.1.key=RADSPD&setprefs.1.val=15&setprefs.2.key=RADC&setprefs.2.val=1&setprefs.3.key=RADSTM&setprefs.3.val=0&setprefs.4.key=SLABS&setprefs.4.val=1&setprefs.5.key=RADRMS&setprefs.5.val=1&setprefs.6.key=RADLIT&setprefs.6.val=0&setprefs.7.key=RADSMO&setprefs.7.val=1">local radar weather underground</a>
				<a href="https://www.skyandtelescope.com/observing/ataglance?pos=left">sky and telescope</a>
			  <a href="https://www.almanac.com/astronomy/rise/MO/Bolivar/<?php echo date('Y-m-d'); ?>">rise and set times of the planets</a>
				<a href="https://www.weather.gov/climate/index.php?map=2">weather.gov (click nearest city then nowdata tab)</a>
				<a href="https://www.moonconnection.com/moon_module.phtml">moon phase</a>
				

			</nav>
            <p class="navrule1">&nbsp;</p>
            <div class="small noPrint">
				<strong>Call AJ ELLIS at <a href="tel:+14173273911">1-417-327-3911</a> </strong>for all your property rental needs in and around Bolivar, Polk County, Missouri 65613 MONDAY - FRIDAY 10AM TO 5PM.&nbsp; WELCOME TO BOLIVAR.
			</div>
			
			<footer style="page-break-inside:avoid;">
				<div class="print qr">
					<img src="../images/qrcode.38781946.png" width="100" style="float:right;" alt="PROPERTY FOR RENT IN AND AROUND BOLIVAR, POLK COUNTY, MISSOURI 65613. CALL AJ ELLIS 1-417-327-3911 M-F 9-5" />
					<img src="../images/for_rent_sign.jpg" width="105" alt="PROPERTY FOR RENT IN AND AROUND BOLIVAR, POLK COUNTY, MISSOURI 65613. CALL AJ ELLIS 1-417-327-3911 M-F 9-5" />
					<em>	This page printed from:</em><br />
							livebolivar.com<br/>
					 <p>	OFFICE: SPRINGHILL FALLS APARTMENTS #212 1325 S LILLIAN AVE BOLIVAR MONDAY - FRIDAY 10AM TO 5PM</p>
					<p><strong>Call AJ ELLIS at 1-417-327-3911 </strong>for all your property rental needs in and around Bolivar, Polk County, Missouri 65613 MONDAY - FRIDAY 10AM TO 5PM.&nbsp; WELCOME TO BOLIVAR.</p>
				</div>
				&#169; LIVEBOLIVAR.COM
				<br>
				<a class="noScreen" href="https://www.google.com/#hl=en&amp;output=search&amp;sclient=psy-ab&amp;rlz=1C2_____enUS379&amp;q=doug+fellows+site:livebolivar.com&amp;oq=doug&amp;gs_l=hp.3.0.35i39j44i39i27j0j0i20.1460.4682.0.6146.7.6.1.0.0.0.140.706.0j6.6.0.les%3B..0.0...1c.1.5.psy-ab.l6XLIvERCyw&amp;pbx=1&amp;bav=on.2,or.r_gc.r_pw.r_cp.r_qf.&amp;bvm=bv.43148975,d.b2U&amp;fp=443df112168ae7b8&amp;biw=1440&amp;bih=762">&nbsp;  &nbsp;  WEBMASTER Doug Fellows</a>
				

				<span class="noPrint">
				    <a href="https://jigsaw.w3.org/css-validator/check/referer"><img class="cssgif" src="vcss.gif" alt="CALL AJ ELLIS 1-417-327-3911 M-F 9-5.  PROPERTY FOR RENT BOLIVAR, POLK COUNTY, MISSOURI 65613."></a>
				    <a href="sitemap13.html"><img class="cssgif" src="placemark88x31.jpg" alt="CALL AJ ELLIS 1-417-327-3911 M-F 9-5.  PROPERTY FOR RENT BOLIVAR, POLK COUNTY, MISSOURI 65613."></a>
				    <a href="../private"><img class="cssgif" src="placemark88x31.jpg" alt="CALL AJ ELLIS 1-417-327-3911 M-F 9-5.  PROPERTY FOR RENT BOLIVAR, POLK COUNTY, MISSOURI 65613."></a>
				</span>
			</footer>
            <div class="clear-fix"></div>
		</div>

	</body>
</html>
