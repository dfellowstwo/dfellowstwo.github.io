Hi Cindy,  I like your weather widget at https://jsfiddle.net/sceendy/nea4z7ff/.  It looks and works great.  May I investigate using it at https://www.livebolivar.com/site_html/weather13.php     I like how it asks to use your location.  In addition I would like to use it with a hard coded location.  After trying all day I've given up.  How hard is it to do?  I can fix the location by id=4377835     q=Bolivar,MO,US     lat={37.614479}&lon={-93.410469}    and so forth.        Doug.

I've forked it to https://jsfiddle.net/somebadhat/kgr5c1wf/#&togetherjs=nn6r6OfEh6 
Hi Cindy,  I like your weather widget at https://jsfiddle.net/sceendy/nea4z7ff/.  It looks and works great.  May I investigate using it at https://www.livebolivar.com/site_html/weather13.php     I like how it asks to use your location.  In addition I would like to use it with a hard coded location.  After trying all day I've given up.  How hard is it to do?  I can fix the location by id=4377835     q=Bolivar,MO,US     lat={37.614479}&lon={-93.410469}    and so forth.   I've forked it to https://jsfiddle.net/user/somebadhat/fiddles/      Doug.
Hi Cindy,  I like your weather widget at https://jsfiddle.net/sceendy/nea4z7ff/.  May I investigate using it at https://www.livebolivar.com/site_html/weather13.php?     I would like to start by converting it from "find geolocation" to a fixed location.  After trying all day I've given up.  How hard is it to do?  I can fix the location by id=4377835     q=Bolivar,MO,US     lat={37.614479}&lon={-93.410469}    and so forth.   I've forked it to https://jsfiddle.net/user/somebadhat/fiddles/      Doug.
Hi Cindy,  I like your weather widget at https://jsfiddle.net/sceendy/nea4z7ff/.  May I use it at https://www.livebolivar.com/site_html/weather13.php?  I don't know if it will work for me.  I would like to start by converting it from "find geolocation" to a fixed location.  After trying all day I've given up.  How hard is it to do?  I can fix the location by id=4377835     q=Bolivar,MO,US     lat={37.614479}&lon={-93.410469}    and so forth.   I've forked it to https://jsfiddle.net/user/somebadhat/fiddles/      Doug.

 May I look at using it on my website? 
I would like to get it to stop asking if it can use my location.  

With a little modification I may be able to use it on my website.  After trying all day to convert it from geolocation to a fixed https://jsfiddle.net/user/somebadhat/fiddles/


I like your weather wiMay I ask you a question about your jsfiddle weather widget https://jsfiddle.net/sceendy/nea4z7ff/  How to convert it from geolocation to a fixed location.  I can set it by id 





 window.onload=function(){
  let headers = new Headers();
  let URL = `http://api.openweathermap.org/data/2.5/weather?id=4377835&cnt=7&units=imperial&APPID=889cbe42f435f153dd8768a957fd8a82`;
  fetch(URL, {
    method: 'GET',
    headers: headers,
  }).then(data => data.json());
    }