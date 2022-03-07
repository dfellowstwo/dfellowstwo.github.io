/*!
 *  Howler.js Radio Demo
 *  howlerjs.com
 *
 *  (c) 2013-2017, James Simpson of GoldFire Studios
 *  goldfirestudios.com
 *
 *  MIT License
 */

// Cache references to DOM elements.
var elms = ['station0', 'title0', 'live0', 'playing0', 'station1', 'title1', 'live1', 'playing1', 'station2', 'title2', 'live2', 'playing2', 'station3', 'title3', 'live3', 'playing3', 'station4', 'title4', 'live4', 'playing4', 'station5', 'title5', 'live5', 'playing5', 'station6', 'title6', 'live6', 'playing6', 'station7', 'title7', 'live7', 'playing7', 'station8', 'title8', 'live8', 'playing8', 'station9', 'title9', 'live9', 'playing9', 'station10', 'title10', 'live10', 'playing10', 'station11', 'title11', 'live11', 'playing11'];
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
      sound.stop();
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

// Setup our new radio and pass in the stations.
var radio = new Radio([
  {
    freq: 'WTOP 103.5FM NEWS WASHINGTON, DC',
    title: "",
    src: ['https://playerservices.streamtheworld.com/api/livestream-redirect/WTOPFM.mp3'],
    howl: null
  },
  {
    freq: 'WAMU 88.5FM NPR IN WASHINGTON, DC',
    title: "",
    src: ['http://wamu-1.streamguys.com:80'],
    howl: null
  },
  {
    freq: 'KSMU 91.1FM NPR IN SPRINGFIELD, MO',
    title: "",
    src: ['http://ksmu.streamguys1.com:80/ksmu3'],
    howl: null
  },
  {
    freq: 'KTOO 104.3FM NPR IN JUNEAU, ALASKA',
    title: "",
    src: ['http://playerservices.streamtheworld.com/api/livestream-redirect/KTOOFM.mp3'],
    howl: null
  },
  {
    freq: 'KHPH 88.1FM NEWS NPR IN HONOLULU',
    title: "",
    src: ['http://khpr-ice.streamguys1.com:80/khpr2'],
    howl: null
  },
  {
    freq: 'KIPO 89.3FM CLASSIC NPR HONOLULU',
    title: "",
    src: ['http://khpr-ice.streamguys1.com:80/kipo2'],
    howl: null
  },
  {
    freq: 'KXRY 107.1FM PORTLAND, OREGON',
    title: "",
    src: ['http://listen.xray.fm:8000/stream'],
    howl: null
  },
  {
    freq: 'PIGPENRADIO.ORG  BRISTOL, ENGLAND',
    title: "",
    src: ['http://uk6.internet-radio.com:8213/;stream'],
    howl: null
  },
    {
    freq: 'ESPN RADIO',
    title: "",
    src: ['http://edge.espn.cdn.abacast.net/espn-networkmp3-48'],
     // http://c8icyelb.prod.playlists.ihrhls.com/189_icy
     // http://c8.prod.playlists.ihrhls.com/189/playlist.m3u8
     // 
     
     
    howl: null
  },
  {
    freq: 'WHCR THE VOICE OF HARLEM 90.3FM',
    title: "",
    src: ['http://134.74.174.212:7072/mp3audio1.mp3'],

     
    howl: null
  },
  {
    freq: 'KSFO 560AM NEWS SAN FRANCISCO',
    title: "",
    src: ['http://14643.live.streamtheworld.com/KSFOAM.mp3'],

     
    howl: null
  },
  {
    freq: 'not working yet WOR 710AM NEWS NYC',
    title: "",
    src: [''],
    
    // google search - m3u8 howler.js
    
    // https://www.iheart.com/live/710-wor-5874/?autoplay=true
    // http://c5.prod.playlists.ihrhls.com/5874/playlist.m3u8/?1.mp3
    // http://c5.prod.playlists.ihrhls.com/5874/1.mp3
    // http://c5.prod.playlists.ihrhls.com:80/5874/playlist.m3u8
    // http://c5.prod.playlists.ihrhls.com:80/5874/playlist.m3u8?listeningSessionID=5b86ea144010d43b_4505790_IcKf7v0H__0000005yZXK&downloadSessionID=0
    // http://c5icyelb.prod.playlists.ihrhls.com/5874_icy/1.mp3
    // https://c5.prod.playlists.ihrhls.com/5874/playlist.m3u8?autoplay=true
    // http://c5.prod.playlists.ihrhls.com/5874/playlist.m3u8

// https://www.radiodiscussions.com/showthread.php?704857-Are-IHeart-quot-iPhone-quot-MP3-feeds-ending

// ffmpeg -i "http://c5.prod.playlists.ihrhls.com:80/5874/playlist.m3u8" -acodec mp3 -ab 257k radio.mp3
     
    howl: null
  }
    
]);
