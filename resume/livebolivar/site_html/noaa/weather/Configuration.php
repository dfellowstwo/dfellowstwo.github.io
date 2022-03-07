<?php
// WIND SPEED AND DIRECTION
	// https://github.com/amwhalen/noaa
namespace noaa\weather;

use noaa\weather\Base,
    noaa\weather\cache\Cache,
    noaa\weather\cache\NoCache;

class Configuration extends Base {

    protected $cache;
    protected $temperatureScale;
    protected $distanceUnit;

    public function __construct() {

        // defaults
        $this->setCache(new NoCache());
        $this->setTemperatureScale('F');
        $this->setDistanceUnit('miles');

    }

}