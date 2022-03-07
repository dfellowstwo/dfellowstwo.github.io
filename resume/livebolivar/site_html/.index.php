<!doctype html>
<!-- https://css-tricks.com/snippets/php/display-styled-directory-contents/ -->
<html>
<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, maximum-scale=1.0, minimum-scale=1.0, initial-scale=1.0">
   <link rel="shortcut icon" href=".favicon.ico">
   <title>PRIVATE/2BACKUP DOUG</title>
   <style>*{margin:0}body{color:#333;font:14px Sans-Serif;padding:50px;background:#eee}h1{text-align:center;padding:20px 0 12px 0;margin:0}h2{font-size:16px;text-align:center;padding:0 0 12px 0}img{width:100%}#container{box-shadow:0 5px 10px -5px rgba(0,0,0,0.5);position:relative;background:white}table{background-color:#f3f3f3;border-collapse:collapse;width:100%;margin:15px 0}th{background-color:#fe4902;color:#FFF;cursor:pointer;padding:7px 10px}th small{font-size:9px}td,th{text-align:left}a{text-decoration:none}td a{color:#630;display:block;padding:9px 0}th a{padding-left:0}td:first-of-type a{background:url(../../../images/file.png) no-repeat 10px 50%;padding-left:55px}th:first-of-type{padding-left:55px}td:not(:first-of-type) a{background-image:none!important}tr:nth-of-type(odd){background-color:#e6e6e6}tr:hover td{background-color:#cacaca}tr:hover td a{color:#000}table tr td:first-of-type a[href$=".reg"]{background-image:url(../../../images/_icon_regedit32x32.png)}table tr td:first-of-type a[href$=".psp"],table tr td:first-of-type a[href$=".PSP"]{background-image:url(../../../images/_icon_psp2.ico)}table tr td:first-of-type a[href$=".pgt"],table tr td:first-of-type a[href$=".PGT"]{background-image:url(../../../images/sothink-favicon32x32.jpg)}table tr td:first-of-type a[href$=".srs"],table tr td:first-of-type a[href$=".SRS"]{background-image:url(../../../images/sr32-favicon-32x32.png)}table tr td:first-of-type a[href$=".pgp"],table tr td:first-of-type a[href$=".PGP"]{background-image:url(../../../images/_icon_pgp.ico)}table tr td:first-of-type a[href$=".lnk"]{background-image:url(../../../images/shortcut-favicon-32x32.png)}table tr td:first-of-type a[href$=".ini"],table tr td:first-of-type a[href$=".txt"],table tr td:first-of-type a[href$=".TXT"]{background-image:url(../../../images/text-favicon-32x32.png)}table tr td:first-of-type a[href$=".bat"],table tr td:first-of-type a[href$=".BAT"]{background-image:url(../../../images/_icon-cmd32x32.jpg)}table tr td:first-of-type a[href$=".bash"],table tr td:first-of-type a[href$=".bsh"],table tr td:first-of-type a[href$=".sh"],table tr td:first-of-type a[href$=".BASH"]{background-image:url(../../../images/bash-favicon-32x32.png)}table tr td:first-of-type a[href$=".jpg"],table tr td:first-of-type a[href$=".png"],table tr td:first-of-type a[href$=".gif"],table tr td:first-of-type a[href$=".svg"],table tr td:first-of-type a[href$=".jpeg"],table tr td:first-of-type a[href$=".jpe"] table tr td:first-of-type a[href$=".ps"],table tr td:first-of-type a[href$=".ai"],table tr td:first-of-type a[href$=".eps"]{background-image:url(../../../images/camera.png)}table tr td:first-of-type a[href$=".zip"],table tr td:first-of-type a[href$=".ZIP"],table tr td:first-of-type a[href$=".tgz"],table tr td:first-of-type a[href$=".tar"],table tr td:first-of-type a[href$=".gz"],table tr td:first-of-type a[href$=".7z"],table tr td:first-of-type a[href$=".Z"],table tr td:first-of-type a[href$=".z"]{background-image:url(../../../images/zip-favicon-32x32.png)}table tr td:first-of-type a[href$=".css"],table tr td:first-of-type a[href$=".scss"]{background-image:url(../../../images/_icon-css.png)}table tr td:first-of-type a[href$=".doc"],table tr td:first-of-type a[href$=".docx"],table tr td:first-of-type a[href$=".dot"],table tr td:first-of-type a[href$=".dtd"],table tr td:first-of-type a[href$=".pdf"],table tr td:first-of-type a[href$=".ppt"],table tr td:first-of-type a[href$=".pptx"],table tr td:first-of-type a[href$=".pps"],table tr td:first-of-type a[href$=".ppsx"],table tr td:first-of-type a[href$=".rtf"],table tr td:first-of-type a[href$=".xls"],table tr td:first-of-type a[href$=".xlsx"] table tr td:first-of-type a[href$=".chm"]{background-image:url(../../../images/word-favicon-32x32)}table tr td:first-of-type a[href$=".avi"],table tr td:first-of-type a[href$=".wmv"],table tr td:first-of-type a[href$=".mp4"],table tr td:first-of-type a[href$=".mov"],table tr td:first-of-type a[href$=".m4a"],table tr td:first-of-type a[href$=".swf"],table tr td:first-of-type a[href$=".mov"],table tr td:first-of-type a[href$=".flv"],table tr td:first-of-type a[href$=".webm"],table tr td:first-of-type a[href$=".ogg"],table tr td:first-of-type a[href$=".m4v"],table tr td:first-of-type a[href$=".mpg"]{background-image:url(../../../images/movie.jpg)}table tr td:first-of-type a[href$=".mp3"],table tr td:first-of-type a[href$=".ogg"],table tr td:first-of-type a[href$=".aac"],table tr td:first-of-type a[href$=".wma"]{background-image:url(../../../images/audio.png)}table tr td:first-of-type a[href$=".html"],table tr td:first-of-type a[href$=".htm"],table tr td:first-of-type a[href$=".xml"],table tr td:first-of-type a[href$=".url"]{background-image:url(../../../images/ie-favicon-32x32)}table tr td:first-of-type a[href$=".php"]{background-image:url(../../../images/php32x32.png)}table tr td:first-of-type a[href$=".js"]{background-image:url(../../../images/script.png)}table tr.dir td:first-of-type a{background-image:url(../../../images/folder-favicon-32x32.png)}
   </style>
   <style>
   @media only screen and (max-width:600px){
		h1, thead {display:none;}
		table {margin:0;}
   }
   </style>

</head>

<body>
<div id="container">
<img src="../../../images/banner_large1360.jpg" alt="BANNER" />
	<h1>Directory Contents</h1>

	<table class="sortable">
	    <thead>
		<tr>
			<th>Filename</th>
			<th>Date Modified</th>
			<th>Size</th>
			<th>Description</th>
			
		</tr>
	    </thead>
	    <tbody><?php

	// Adds pretty filesizes
	function pretty_filesize($file) {
		$size=filesize($file);
		if($size<1024){$size=$size." Bytes";}
		elseif(($size<1048576)&&($size>1023)){$size=round($size/1024, 1)." KB";}
		elseif(($size<1073741824)&&($size>1048575)){$size=round($size/1048576, 1)." MB";}
		else{$size=round($size/1073741824, 1)." GB";}
		return $size;
		
		
	}

 	// Checks to see if veiwing hidden files is enabled
	if($_SERVER['QUERY_STRING']=="hidden")
	{$hide="";
	 $ahref="./";
	 $atext="Hide";}
	else
	{$hide=".";
	 $ahref="./?hidden";
	 $atext="Show";}

	 // Opens directory
	 $myDirectory=opendir(".");

	// Gets each entry
	while($entryName=readdir($myDirectory)) {
	   $dirArray[]=$entryName;
	}

	// Closes directory
	closedir($myDirectory);

	// Counts elements in array
	$indexCount=count($dirArray);

	// Sorts files
	sort($dirArray);

	// Loops through the array of files
	for($index=0; $index < $indexCount; $index++) {

	// Decides if hidden files should be displayed, based on query above.
	    if(substr("$dirArray[$index]", 0, 1)!=$hide) {

	// Resets Variables
		$favicon="";
		$class="file";

	// Gets File Names
		$name=$dirArray[$index];
		$namehref=$dirArray[$index];

	// Gets Date Modified
		$modtime=date("M j Y g:i A", filemtime($dirArray[$index]));
		$timekey=date("YmdHis", filemtime($dirArray[$index]));


	// Separates directories, and performs operations on those directories
		if(is_dir($dirArray[$index]))
		{
				$extn="&lt;Directory&gt;";
				$size="&lt;Directory&gt;";
				$desc="&lt;Directory&gt;";
				$sizekey="0";
				$class="dir";

			// Gets favicon.ico, and displays it, only if it exists.
				if(file_exists("$namehref/favicon.ico"))
					{
						$favicon=" style='background-image:url($namehref/favicon.ico);'";
						$extn="&lt;Website&gt;";
					}

			// Cleans up . and .. directories
				if($name=="."){$name=". (Current Directory)"; $extn="&lt;System Dir&gt;"; $favicon=" style='background-image:url($namehref/.favicon.ico);'";}
				if($name==".."){$name=".. (Parent Directory)"; $extn="&lt;System Dir&gt;";}
		}

	// File-only operations
		else{
			// Gets file extension
			$extn=pathinfo($dirArray[$index], PATHINFO_EXTENSION);

			// Prettifies file type

			
			switch ($extn){
				
				case "png": $desc="PNGIMAGE"; break;
				case "jpg": $desc="JOINT PHOTOGRAPHICS EXPERTS GROUP"; break;
				case "jpeg": $desc="JPEGIMAGE"; break;
				case "svg": $desc="SVGIMAGE"; break;
				case "gif": $desc="GIFIMAGE"; break;
				case "ico": $desc="Windows Icon"; break;

				case "txt": $desc="TEXT FILE"; break;
				case "log": $desc="LOG FILE"; break;
				case "htm": $desc="HTML FILE"; break;
				case "html": $desc="HTML FILE"; break;
				case "xhtml": $desc="HTML FILE"; break;
				case "shtml": $desc="HTML FILE"; break;
				case "php": $desc="HYPERTEXT PREPROCESSOR SCRIPT"; break;
				case "js": $desc="JAVASCRIPT"; break;
				case "css": $desc="CASCADING STYLE SHEET"; break;
				case "chm": $desc="WIN32 COMPRESSED HTML HELP"; break;

				
				case "pdf": $desc="PDF DOCUMENT"; break;
				case "xls": $desc="MICROSOFT EXCEL"; break;
				case "xlsx": $desc="MICROSOFT EXCEL"; break;
				case "doc": $desc="MICROSOFT WORD DOCUMENT"; break;
				case "docx": $desc="MICROSOFT WORD DOCUMENT"; break;
				case "PDF": $desc="PDF DOCUMENT"; break;
				case "XLS": $desc="MICROSOFT EXCEL"; break;
				case "XLSX": $desc="MICROSOFT EXCEL"; break;
				case "DOC": $desc="MICROSOFT WORD DOCUMENT"; break;
				case "DOCX": $desc="MICROSOFT WORD DOCUMENT"; break;
				case "zip": $desc="ZIP ARCHIVE"; break;
				case "ZIP": $desc="ZIP ARCHIVE"; break;
				case "htaccess": $desc="APACHE CONFIG FILE"; break;
				case "exe": $desc="WINDOWS EXECUTABLE"; break;
				case "xml": $desc="EXTENSIBLE MARKUP LANGUAGE"; break;
				case "psp": $desc="PAINT SHOP PRO"; break;
				case "reg": $desc="REGISTRY ENTRY"; break;
				case "EXE": $desc="WINDOWS EXECUTABLE"; break;
				case "XML": $desc="EXTENSIBLE MARKUP LANGUAGE"; break;
				case "PSP": $desc="PAINT SHOP PRO"; break;
				case "REG": $desc="REGISTRY ENTRY"; break;

				case "pgp": $desc="PRETTY GOOD PRIVACY"; break;
				case "pgt": $desc="SOTHINK DHTML MENU"; break;
				case "sh": $desc="SHELL COMMAND LANGUAGE"; break;

				case "pst": $desc="MICROSOFT OUTLOOK"; break;
				case "mdb": $desc="EZ-DEPOSIT"; break;
				case "qbw": $desc="QUICKBOOKS"; break;
				case "QBW": $desc="QUICKBOOKS"; break;

				case "bat": $desc="WINDOWS BATCH FILE"; break;
				case "BAT": $desc="WINDOWS BATCH FILE"; break;
				case "mp3": $desc="MPEG LAYER 3 FORMAT"; break;
				case "MP3": $desc="MPEG LAYER 3 FORMAT"; break;
				case "7z": $desc="ZIP ARCHIVE"; break;
				case "cgi": $desc="COMMON GATEWAY INTERFACE"; break;
				case "ini": $desc="WINDOWS INITIALIZATION FILE FORMAT"; break;
				case "bash": $desc="SHELL COMMAND LANGUAGE"; break;
				case "bsh": $desc="SHELL COMMAND LANGUAGE"; break;				
				case "lnk": $desc="WINDOWS SHORTCUT"; break;
				case "LNK": $desc="WINDOWS SHORTCUT"; break;
				case "SRS": $desc="WINDOWS SEARCH AND REPLACE SCRIPT"; break;
				case "srs": $desc="WINDOWS SEARCH AND REPLACE SCRIPT"; break;


				
				



				
				
				default: if($extn!=""){$desc=strtoupper($desc);} else{$desc="Unknown";} break;
				
			}

			// Gets and cleans up file size
				$size=pretty_filesize($dirArray[$index]);
				$sizekey=filesize($dirArray[$index]);
				
				
		}

	// Output
	 echo("
		<tr class='$class'>
			<td><a href='./$namehref'$favicon class='name'>$name</a></td>
			<td sorttable_customkey='$timekey'><a href='./$namehref'>$modtime</a></td>
			<td sorttable_customkey='$sizekey'><a href='./$namehref'>$size</a></td>
			<td>$desc</td>
		</tr>");
	   }
	}
	?>

	    </tbody>
	</table>

	<h2><?php echo("<a href='$ahref'>$atext hidden files</a>"); ?></h2>
</div>
</body>
 <script>
   var stIsIE=
/*@cc_on!@*/
false;sorttable={init:function(){if(arguments.callee.done){return}arguments.callee.done=true;if(_timer){clearInterval(_timer)}if(!document.createElement||!document.getElementsByTagName){return}sorttable.DATE_RE=/^(\d\d?)[\/\.-](\d\d?)[\/\.-]((\d\d)?\d\d)$/;forEach(document.getElementsByTagName("table"),function(a){if(a.className.search(/\bsortable\b/)!=-1){sorttable.makeSortable(a)}})},makeSortable:function(b){if(b.getElementsByTagName("thead").length==0){the=document.createElement("thead");the.appendChild(b.rows[0]);b.insertBefore(the,b.firstChild)}if(b.tHead==null){b.tHead=b.getElementsByTagName("thead")[0]}if(b.tHead.rows.length!=1){return}sortbottomrows=[];for(var a=0;a<b.rows.length;a++){if(b.rows[a].className.search(/\bsortbottom\b/)!=-1){sortbottomrows[sortbottomrows.length]=b.rows[a]}}if(sortbottomrows){if(b.tFoot==null){tfo=document.createElement("tfoot");b.appendChild(tfo)}for(var a=0;a<sortbottomrows.length;a++){tfo.appendChild(sortbottomrows[a])}delete sortbottomrows}headrow=b.tHead.rows[0].cells;for(var a=0;a<headrow.length;a++){if(!headrow[a].className.match(/\bsorttable_nosort\b/)){mtch=headrow[a].className.match(/\bsorttable_([a-z0-9]+)\b/);if(mtch){override=mtch[1]}if(mtch&&typeof sorttable["sort_"+override]=="function"){headrow[a].sorttable_sortfunction=sorttable["sort_"+override]}else{headrow[a].sorttable_sortfunction=sorttable.guessType(b,a)}headrow[a].sorttable_columnindex=a;headrow[a].sorttable_tbody=b.tBodies[0];dean_addEvent(headrow[a],"click",function(f){if(this.className.search(/\bsorttable_sorted\b/)!=-1){sorttable.reverse(this.sorttable_tbody);this.className=this.className.replace("sorttable_sorted","sorttable_sorted_reverse");this.removeChild(document.getElementById("sorttable_sortfwdind"));sortrevind=document.createElement("span");sortrevind.id="sorttable_sortrevind";sortrevind.innerHTML=stIsIE?'&nbsp<font face="webdings">5</font>':"&nbsp;&#x25B4;";this.appendChild(sortrevind);return}if(this.className.search(/\bsorttable_sorted_reverse\b/)!=-1){sorttable.reverse(this.sorttable_tbody);this.className=this.className.replace("sorttable_sorted_reverse","sorttable_sorted");this.removeChild(document.getElementById("sorttable_sortrevind"));sortfwdind=document.createElement("span");sortfwdind.id="sorttable_sortfwdind";sortfwdind.innerHTML=stIsIE?'&nbsp<font face="webdings">6</font>':"&nbsp;&#x25BE;";this.appendChild(sortfwdind);return}theadrow=this.parentNode;forEach(theadrow.childNodes,function(e){if(e.nodeType==1){e.className=e.className.replace("sorttable_sorted_reverse","");e.className=e.className.replace("sorttable_sorted","")}});sortfwdind=document.getElementById("sorttable_sortfwdind");if(sortfwdind){sortfwdind.parentNode.removeChild(sortfwdind)}sortrevind=document.getElementById("sorttable_sortrevind");if(sortrevind){sortrevind.parentNode.removeChild(sortrevind)}this.className+=" sorttable_sorted";sortfwdind=document.createElement("span");sortfwdind.id="sorttable_sortfwdind";sortfwdind.innerHTML=stIsIE?'&nbsp<font face="webdings">6</font>':"&nbsp;&#x25BE;";this.appendChild(sortfwdind);row_array=[];col=this.sorttable_columnindex;rows=this.sorttable_tbody.rows;for(var c=0;c<rows.length;c++){row_array[row_array.length]=[sorttable.getInnerText(rows[c].cells[col]),rows[c]]}row_array.sort(this.sorttable_sortfunction);tb=this.sorttable_tbody;for(var c=0;c<row_array.length;c++){tb.appendChild(row_array[c][1])}delete row_array})}}},guessType:function(c,b){sortfn=sorttable.sort_alpha;for(var a=0;a<c.tBodies[0].rows.length;a++){text=sorttable.getInnerText(c.tBodies[0].rows[a].cells[b]);if(text!=""){if(text.match(/^-?[£$¤]?[\d,.]+%?$/)){return sorttable.sort_numeric}possdate=text.match(sorttable.DATE_RE);if(possdate){first=parseInt(possdate[1]);second=parseInt(possdate[2]);if(first>12){return sorttable.sort_ddmm}else{if(second>12){return sorttable.sort_mmdd}else{sortfn=sorttable.sort_ddmm}}}}}return sortfn},getInnerText:function(b){hasInputs=(typeof b.getElementsByTagName=="function")&&b.getElementsByTagName("input").length;if(b.getAttribute("sorttable_customkey")!=null){return b.getAttribute("sorttable_customkey")}else{if(typeof b.textContent!="undefined"&&!hasInputs){return b.textContent.replace(/^\s+|\s+$/g,"")}else{if(typeof b.innerText!="undefined"&&!hasInputs){return b.innerText.replace(/^\s+|\s+$/g,"")}else{if(typeof b.text!="undefined"&&!hasInputs){return b.text.replace(/^\s+|\s+$/g,"")}else{switch(b.nodeType){case 3:if(b.nodeName.toLowerCase()=="input"){return b.value.replace(/^\s+|\s+$/g,"")}case 4:return b.nodeValue.replace(/^\s+|\s+$/g,"");break;case 1:case 11:var c="";for(var a=0;a<b.childNodes.length;a++){c+=sorttable.getInnerText(b.childNodes[a])}return c.replace(/^\s+|\s+$/g,"");break;default:return""}}}}}},reverse:function(a){newrows=[];for(var b=0;b<a.rows.length;b++){newrows[newrows.length]=a.rows[b]}for(var b=newrows.length-1;b>=0;b--){a.appendChild(newrows[b])}delete newrows},sort_numeric:function(e,c){aa=parseFloat(e[0].replace(/[^0-9.-]/g,""));if(isNaN(aa)){aa=0}bb=parseFloat(c[0].replace(/[^0-9.-]/g,""));if(isNaN(bb)){bb=0}return aa-bb},sort_alpha:function(e,c){if(e[0].toLowerCase()==c[0].toLowerCase()){return 0}if(e[0].toLowerCase()<c[0].toLowerCase()){return -1}return 1},sort_ddmm:function(e,c){mtch=e[0].match(sorttable.DATE_RE);y=mtch[3];m=mtch[2];d=mtch[1];if(m.length==1){m="0"+m}if(d.length==1){d="0"+d}dt1=y+m+d;mtch=c[0].match(sorttable.DATE_RE);y=mtch[3];m=mtch[2];d=mtch[1];if(m.length==1){m="0"+m}if(d.length==1){d="0"+d}dt2=y+m+d;if(dt1==dt2){return 0}if(dt1<dt2){return -1}return 1},sort_mmdd:function(e,c){mtch=e[0].match(sorttable.DATE_RE);y=mtch[3];d=mtch[2];m=mtch[1];if(m.length==1){m="0"+m}if(d.length==1){d="0"+d}dt1=y+m+d;mtch=c[0].match(sorttable.DATE_RE);y=mtch[3];d=mtch[2];m=mtch[1];if(m.length==1){m="0"+m}if(d.length==1){d="0"+d}dt2=y+m+d;if(dt1==dt2){return 0}if(dt1<dt2){return -1}return 1},shaker_sort:function(h,f){var a=0;var e=h.length-1;var j=true;while(j){j=false;for(var c=a;c<e;++c){if(f(h[c],h[c+1])>0){var g=h[c];h[c]=h[c+1];h[c+1]=g;j=true}}e--;if(!j){break}for(var c=e;c>a;--c){if(f(h[c],h[c-1])<0){var g=h[c];h[c]=h[c-1];h[c-1]=g;j=true}}a++}}};if(document.addEventListener){document.addEventListener("DOMContentLoaded",sorttable.init,false);
/*@cc_on @*/
/*@if (@_win32)
    document.write("<script id=__ie_onload defer src=javascript:void(0)><\/script>");
    var script = document.getElementById("__ie_onload");
    script.onreadystatechange = function() {
        if (this.readyState == "complete") {
            sorttable.init(); // call the onload handler
        }
    };
/*@end @*/
}if(/WebKit/i.test(navigator.userAgent)){var _timer=setInterval(function(){if(/loaded|complete/.test(document.readyState)){sorttable.init()}},10)}window.onload=sorttable.init;function dean_addEvent(b,e,c){if(b.addEventListener){b.addEventListener(e,c,false)}else{if(!c.$$guid){c.$$guid=dean_addEvent.guid++}if(!b.events){b.events={}}var a=b.events[e];if(!a){a=b.events[e]={};if(b["on"+e]){a[0]=b["on"+e]}}a[c.$$guid]=c;b["on"+e]=handleEvent}}dean_addEvent.guid=1;function removeEvent(a,c,b){if(a.removeEventListener){a.removeEventListener(c,b,false)}else{if(a.events&&a.events[c]){delete a.events[c][b.$$guid]}}}function handleEvent(e){var c=true;e=e||fixEvent(((this.ownerDocument||this.document||this).parentWindow||window).event);var a=this.events[e.type];for(var b in a){this.$$handleEvent=a[b];if(this.$$handleEvent(e)===false){c=false}}return c}function fixEvent(a){a.preventDefault=fixEvent.preventDefault;a.stopPropagation=fixEvent.stopPropagation;return a}fixEvent.preventDefault=function(){this.returnValue=false};fixEvent.stopPropagation=function(){this.cancelBubble=true};if(!Array.forEach){Array.forEach=function(e,c,b){for(var a=0;a<e.length;a++){c.call(b,e[a],a,e)}}}Function.prototype.forEach=function(a,e,c){for(var b in a){if(typeof this.prototype[b]=="undefined"){e.call(c,a[b],b,a)}}};String.forEach=function(a,c,b){Array.forEach(a.split(""),function(f,e){c.call(b,f,e,a)})};var forEach=function(a,e,b){if(a){var c=Object;if(a instanceof Function){c=Function}else{if(a.forEach instanceof Function){a.forEach(e,b);return}else{if(typeof a=="string"){c=String}else{if(typeof a.length=="number"){c=Array}}}}c.forEach(a,e,b)}};
   </script>
</html>
