<?php
// WIND SPEED AND DIRECTION
	// https://github.com/amwhalen/noaa
namespace noaa\weather;

class Base {

    /**
     * Map a call to a non-existent mutator or accessor directly to its
     * corresponding property
     *
     * @param  string $name
     * @param  array  $arguments
     * @return mixed
     * @throws \Exception If no mutator/accessor can be found
     */
    public function __call($name, $arguments) {

        if (strlen($name) > 3) {

            // set: setName($arg)
            if (strpos($name, 'set') === 0) {
                $property = lcfirst(substr($name, 3));
                $this->$property = array_shift($arguments);
                return $this;
            }

            // get: getName()
            if (0 === strpos($name, 'get')) {
                $property = lcfirst(substr($name, 3));
                return $this->$property;
            }

        }

        throw new \Exception(sprintf('No method named `%s` exists', $name));

    }

    /**
     * Returns the current software version number.
     * @return string The version.
     */
    public function version() {
        return '0.1.0';
    }

}