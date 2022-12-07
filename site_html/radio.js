/*!

  This is radio.js
  This was radio56.js
  This was radio-stations13d-radio.js
  
  Services: 
	
	radio.html
  
 *  Howler.js Radio Demo
 *  howlerjs.com
 *
 *  (c) 2013-2020, James Simpson of GoldFire Studios
 *  goldfirestudios.com
 *
 *  MIT License
 */

// Cache references to DOM elements.
var elms = ['station0', 'title0', 'live0', 'playing0', 'station1', 'title1', 'live1', 'playing1', 'station2', 'title2', 'live2', 'playing2', 'station3', 'title3', 'live3', 'playing3', 'station4', 'title4', 'live4', 'playing4', 'station5', 'title5', 'live5', 'playing5', 'station6', 'title6', 'live6', 'playing6', 'station7', 'title7', 'live7', 'playing7', 'station8', 'title8', 'live8', 'playing8', 'station9', 'title9', 'live9', 'playing9', 'station10', 'title10', 'live10', 'playing10', 'station11', 'title11', 'live11', 'playing11', 'station12', 'title12', 'live12', 'playing12', 'station13', 'title13', 'live13', 'playing13', 'station14', 'title14', 'live14', 'playing14', 'station15', 'title15', 'live15', 'playing15', 'station16', 'title16', 'live16', 'playing16', 'station17', 'title17', 'live17', 'playing17', 'station18', 'title18', 'live18', 'playing18', 'station19', 'title19', 'live19', 'playing19', 'station20', 'title20', 'live20', 'playing20', 'station21', 'title21', 'live21', 'playing21', 'station22', 'title22', 'live22', 'playing22', 'station23', 'title23', 'live23', 'playing23', 'station24', 'title24', 'live24', 'playing24', 'station25', 'title25', 'live25', 'playing25', 'station26', 'title26', 'live26', 'playing26', 'station27', 'title27', 'live27', 'playing27', 'station28', 'title28', 'live28', 'playing28', 'station29', 'title29', 'live29', 'playing29'];


elms.forEach(function(elm) {
  window[elm] = document.getElementById(elm);
});

/**
 * Radio class containing the state of our stations.
 * Includes all methods for playing, stopping, etc.
 * @param {Array} stations Array of objects with station details ({title, src, howl, ...}).
 */
var Radio = function(stations) {
  var self = this;

  self.stations = stations;
  self.index = 0;
  
  // Setup the display for each station.
  for (var i=0; i<self.stations.length; i++) {
    window['title' + i].innerHTML = '<b>' + self.stations[i].freq + '</b> ' + self.stations[i].title;
    window['station' + i].addEventListener('click', function(index) {
      var isNotPlaying = (self.stations[index].howl && !self.stations[index].howl.playing());
      
      // Stop other sounds or the current one.
      radio.stop();

      // If the station isn't already playing or it doesn't exist, play it.
      if (isNotPlaying || !self.stations[index].howl) {
        radio.play(index);
      }
    }.bind(self, i));
  }
};
Radio.prototype = {
  /**
   * Play a station with a specific index.
   * @param  {Number} index Index in the array of stations.
   */
  play: function(index) {
    var self = this;
    var sound;

    index = typeof index === 'number' ? index : self.index;
    var data = self.stations[index];

    // If we already loaded this track, use the current one.
    // Otherwise, setup and load a new Howl.
    if (data.howl) {
      sound = data.howl;
    } else {
      sound = data.howl = new Howl({
        src: data.src,
        html5: true, // A live stream can only be played through HTML5 Audio.
        format: ['mp3', 'aac']
      });
    }

    // Begin playing the sound.
    sound.play();

    // Toggle the display.
    self.toggleStationDisplay(index, true);

    // Keep track of the index we are currently playing.
    self.index = index;
  },

  /**
   * Stop a station's live stream.
   */
  stop: function() {
    var self = this;

    // Get the Howl we want to manipulate.
    var sound = self.stations[self.index].howl;

    // Toggle the display.
    self.toggleStationDisplay(self.index, false);

    // Stop the sound.
    if (sound) {
      sound.unload();
    }
  },

  /**
   * Toggle the display of a station to off/on.
   * @param  {Number} index Index of the station to toggle.
   * @param  {Boolean} state true is on and false is off.
   */
  toggleStationDisplay: function(index, state) {
    var self = this;

    // Highlight/un-highlight the row.
    window['station' + index].style.backgroundColor = state ? 'rgba(255, 255, 255, 0.33)' : '';

    // Show/hide the "live" marker.
    window['live' + index].style.opacity = state ? 1 : 0;

    // Show/hide the "playing" animation.
    window['playing' + index].style.display = state ? 'block' : 'none';
  }
};


// node.js version = cmd /k node -v
// javascript 1.5 on desktop, 1.7 on iphone. A developer worth his salt does not check javascript version number, he detects features supported.





/* 

BEGIN SCIENCE FRIDAY. Science Friday is a radio program that streams starting at 2pm EasternTime on Friday and is distributed by WNYC-FM, New York City to public radio stations across the nation. This code supplies part of the url of the stream. The url is in the format of: https://s3.amazonaws.com/scifri-episodes/scifri20220211-episode.mp3

// If ( Friday before 2pm EasternTime ){ last Friday's date } else { today's date till Saturday then last Friday's date }

*/

// BEGIN WORKS. IN PRODUCTION. PART OF https://stackoverflow.com/a/47889259/8826818
// if you check for last Friday's date on Friday it returns todays date
function getDateOfLast(refday){
    var days = {
        monday: 1,
        tuesday: 2,
        wednesday: 3,
        thursday: 4,
        friday: 5,
        saturday: 6,
        sunday: 0
    };
    if(!days.hasOwnProperty(refday))throw new Error(refday+" is not listed in "+JSON.stringify(days));
    var currDate = new Date();
    var currTimestamp = currDate.getTime();
    var triggerDay = days[refday];
    var dayMillDiff=0;
    var dayInMill = 1000*60*60*24;
    // add a day to dayMillDiff as long as the desired refday (sunday for instance) is not reached
    while(currDate.getDay()!=triggerDay){
        dayMillDiff -= dayInMill;
        currDate = new Date(currDate.getTime()-dayInMill);
    }
    return new Date(currTimestamp + dayMillDiff);
}
// END. WORKS. IN PRODUCTION. PART OF https://stackoverflow.com/a/47889259/8826818



// JavaScript objects
// https://javascript.info/object


// JavaScript date time
//https://www.toptal.com/software/definitive-guide-to-datetime-manipulation



var date = new Date(); // Used for the rest of the script 


// document.write("<br/>The date is "+date.toLocaleDateString('en-US')); // WORKS


var now_utc =  Date.UTC(date.getUTCFullYear(), date.getUTCMonth(), date.getUTCDate(),
 date.getUTCHours(), date.getUTCMinutes(), date.getUTCSeconds());

// document.write("<br/>The date is "+now_utc); // WORKS

//document.write("<br/>The date is "+now_utc.toLocaleDateString('en-US')); // NOT WORK





var date1 = (date.getFullYear() + ("0" + (date.getMonth() + 1)).slice(-2) + ("0" + date.getDate()).slice(-2)); // WORKS. 
var date2 = (date.getFullYear() + ("0" + (date.getMonth() + 1)).slice(-2) + ("0" + date.getDate()).slice(-2)+" " + ("0" + date.getHours() ).slice(-2) +":"+ ("0" + date.getMinutes()).slice(-2)+":" + ("0" + date.getSeconds()).slice(-2) );
// document.write("<br/>Today's date is "+date1);

// BEGIN https://stackoverflow.com/a/44118057/8826818 
// Every Friday at 2pm ET a url is created that contains a date string i.e. 20220204. YYYYMMDD. I am in CentralTime.
// If ( Friday before 2pm EasternTime ){ last Friday's date } else { today's date till Saturday then last Friday's date }
var hour = date.getHours(); // Hour w/o leading zero
var day = date.getDay(); // 0 = Sun
var combined = day+'_'+hour;
var run_on = ['5_0','5_1','5_2','5_3','5_4','5_5','5_6','5_7','5_8','5_9','5_10','5_11','5_12']; // This week's stream starts at 2pm EasternTime. I am in CT.
for (i = 0; i < run_on.length; i++){
    if (combined == run_on[i]){
    // begin https://stackoverflow.com/a/30323659/8826818
    const lastFriday = new Date();
    lastFriday.setDate((new Date().getDate() + (6 - new Date().getDay() - 1) - 7));
    // end https://stackoverflow.com/a/30323659/8826818
    
    var date3 = lastFriday.toLocaleDateString('en-GB').split('/').reverse().join(''); // '20220204' yyyymmdd
    var date3 = (date3.toString());
    // var date3=(lastFriday.getFullYear()+("0" + (lastFriday.getMonth() + 1)).slice(-2)+("0" + lastFriday.getDate()).slice(-2)); //yyyymmdd
    
    // document.write("<br/>It is not after 2pm EasternTime on Friday.");
    // document.write("<br/>The day is "+date.getDay()); // THIS CAN NOT BE day. It breaks if day is used.
    // document.write("<br/>The var hour is "+hour);
    // document.write("<br/>The hour is "+("0" + date.getHours() ).slice(-2) );
    // document.write("<br/>Combined is "+combined+" and run_on is "+run_on[i]);
    // document.write("<br/>The date is "+date3);

    document.getElementById("date3").innerHTML= "<a href=https://s3.amazonaws.com/scifri-episodes/scifri" + date3 + "-episode.mp3>Science Friday current episode w/ seek</a>";

    
    break;
    
    } else {
	    
    var DateOfLastFriday = getDateOfLast("friday"); // if checked on Friday returns today's date
    var date3 = DateOfLastFriday.toLocaleDateString('en-GB').split('/').reverse().join(''); // '20220204'
    var date3 = (date3.toString());
    document.getElementById("date3").innerHTML= "<a href=https://s3.amazonaws.com/scifri-episodes/scifri" + date3 + "-episode.mp3>Science Friday current episode w/ seek</a>";

    // var date3=(DateOfLastFriday.getFullYear()+("0" + (DateOfLastFriday.getMonth() + 1)).slice(-2)+("0" + DateOfLastFriday.getDate()).slice(-2)); //yyyymmdd. WORKS

    // document.write("<br/>It is after 2pm EasternTime on Friday.");
    // document.write("<br/>The var date.getDay is "+date.getDay()); // THIS CAN NOT BE day. It breaks if day is used.
    // document.write("<br/>The var hour is "+hour);
    // document.write("<br/>The hour is "+("0" + date.getHours() ).slice(-2) );
    // document.write("<br/>Combined is "+combined+" and run_on is "+run_on[i]);
    // document.write("<br/>Last Friday's date is "+date3);

    }
} 

// END https://stackoverflow.com/a/44118057/8826818 

// END SCIENCE FRIDAY.



// Setup our new radio and pass in the stations.
var radio = new Radio([
   {

    freq: 'SCIENCE FRIDAY',

    title: '',

    // src: 'https://s3.amazonaws.com/scifri-episodes/scifri20220311-episode.mp3',

  src: "https://s3.amazonaws.com/scifri-episodes/scifri" + date3 + "-episode.mp3",
	         

    thowl: null

  },
  
  {

    freq: 'WTOP 103.5FM',

    title: "WASHINGTON, DC",

    src: 'https://24373.live.streamtheworld.com/WTOPFM.mp3',

    howl: null

  },

  {

    freq: 'WAMU 88.5FM ',

    title: "NPR WASHINGTON, DC",

    src: 'http://wamu-1.streamguys.com:80',

    howl: null

  },
  
  {

    freq: 'CNN',

    title: "LIVE TV",
// https://16803.live.streamtheworld.com/CNNTVAAC_SC
// https://tunein.streamguys1.com/cnn-new
// 
    src: 'https://prod-3-88-186-142.wostreaming.net/audacy-cnntvaac-imc',

    howl: null

  },
  
  {

    freq: 'KPCC 89.3FM',

    title: " NPR LOS ANGELES, CA",

    src: 'https://live.wostreaming.net/direct/southerncalipr-kpccfmmp3-imc.mp3',
	// https://playerservices.streamtheworld.com/api/livestream-redirect/KPCC_FM.mp3
    // https://live.scpr.org/kpcclive/
    

    howl: null

  },
  
  {

    freq: 'BBC Radio 4',

    title: "WORLD SERVICE",

    src: 'http://stream.live.vc.bbcmedia.co.uk/bbc_world_service',

    howl: null

  },

{

    freq: 'BBC Radio 1',

    title: "POP HITS",

    src: 'http://stream.live.vc.bbcmedia.co.uk/bbc_radio_one',

    howl: null

  },
  
  {

    freq: 'BBC Radio 6',

    title: "ALTERNATIVE",

    src: 'http://stream.live.vc.bbcmedia.co.uk/bbc_6music',

    howl: null

  },


    {

    freq: 'BBC Radio 3',

    title: "THROUGH THE NIGHT",

    src: 'http://stream.live.vc.bbcmedia.co.uk/bbc_radio_three',

    howl: null

  },
   {

    freq: 'BBC Radio 1Xtra',

    title: "HITS AND NEW",

    src: 
    'http://stream.live.vc.bbcmedia.co.uk/bbc_1xtra',
    //THE FOLLOWING WORKS ON MOBILE BUT NOT DESKTOP 'https://as-hls-ww.live.cf.md.bbci.co.uk/pool_904/live/ww/bbc_1xtra/bbc_1xtra.isml/bbc_1xtra-audio%3d48000.m3u8',

    howl: null

  },

    {

    freq: 'WYMS 88.9FM',

    title: "MILWAUKEE",

    src: 'https://wyms.streamguys1.com/live?platform=88nine',

    howl: null

  },

    {

    freq: 'KGRG 89.9FM',

    title: "SEATTLE",

    src: 'https://www.ophanim.net:8444/s/7090',

    howl: null

  },
  
    {

    freq: 'WXRT 93.1FM',

    title: "CHICAGO ROCK",

    src: 'https://prod-3-88-186-142.wostreaming.net/audacy-wxrtfmmp3-imc',
    // 'https://22123.live.streamtheworld.com/WXRTFM.mp3'
	// 'https://24423.live.streamtheworld.com/WXRTFMAAC_SBM'

    howl: null

  },
  
  {

    freq: 'ALICE 95.5FM',

    title: "POP HITS",

    src: 'https://ample.revma.ihrhls.com/zc1269',

    howl: null

  },
  
  {

    freq: 'KSMU 91.1FM',

    title: " NPR SPRINGFIELD, MO",

    src: 'https://ksmu.streamguys1.com/ksmu3',

    howl: null

  },

{

    // freq: 'XRAY.',
    freq: 'KXRY 107.1FM ',

    title: "PORTLAND, OREGON",

    src: 'https://listen.xray.fm/stream',

    howl: null

  },
  
   {
// BRISTOL, ENGLAND 
    freq: 'PIGPENRADIO.ORG BRISTOL, ENG',

    title: "",

    src: 'http://uk6.internet-radio.com:8213/;stream',

    howl: null

  },
  
    {

    freq: 'KEXP 90.3FM',

    title: "SEATTLE, WA",

    src: 'https://kexp-mp3-128.streamguys1.com/kexp128.mp3',

    howl: null

  },

   {

    freq: 'WHRB 95.3FM',

    title: "HARVARD UNIVERSITY",

    src: 'https://stream.whrb.org/whrb-mp3',

    howl: null

  },
  
   {

    freq: 'KZSU 90.1FM',

    title: "STANFORD UNIVERSITY",

    src: 'http://kzsu-streams.stanford.edu/kzsu-1-128.mp3',

    howl: null

  },
  
   {

    freq: 'WYBC 1340 AM',

    title: "YALE UNIVERSITY",

    src: 'https://wybcx-stream.creek.org/stream',

    howl: null

  },
  
   {

    freq: 'WHCR 90.3FM',

    title: "VOICE OF HARLEM",

    src: 'http://stream.radiojar.com/qzw2b2cuc3vtv',

    howl: null

  },

     {

    freq: 'WUTS 91.3FM',

    title: "U OF THE SOUTH",

    src: 'https://sewanee.streamguys1.com/sewanee',
    // https://sewanee.streamguys1.com/live

    howl: null

  },
  
  {

    freq: 'KHPH 88.1FM',

    title: "NEWS NPR HONOLULU",

    src: 'https://khpr-ice.streamguys1.com/khpr2',

    howl: null

  },

  {

    freq: 'KIPO 89.3',

    title: "CLASSICS NPR HONOLULU",

    src: 'https://khpr-ice.streamguys1.com/kipo2',

    howl: null

  },
  
    {

    freq: 'KTOO 104.3FM',

    title: "NPR NEWS JUNEAU, AK",

    src: 'https://18423.live.streamtheworld.com/KTOOFMAAC.aac',

    howl: null

  },
  
    {

    freq: 'KXLL 100.1FM',

    title: "NPR MUSIC JUNEAU, AK",

    src: 'https://18423.live.streamtheworld.com/KXLLFMAAC.aac',

    howl: null

  },

   {

    freq: 'KSFO 560AM',

    title: "NEWS SAN FRANCISCO",

    src: 'https://14923.live.streamtheworld.com/KSFOAMAAC.aac',

    howl: null

  },

  {

    freq: 'WOR 710AM',

    title: "NEW YORK, NY",

    src: 'https://n08b-e2.revma.ihrhls.com/zc5874',

    howl: null

  },
  
  {

    freq: 'ESPN RADIO',

    title: "",

    src: 'http://edge.espn.cdn.abacast.net/espn-networkmp3-48',

    howl: null

  }
]);
