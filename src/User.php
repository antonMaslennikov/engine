<?php

namespace tomdom\core;

use GeoIp2\Database\Reader;

/**
 * Управление информацией о посетителе
 */
class User extends Model {

    public $authorized = FALSE;

    public function getGeo()
    {
        try
        {
            $reader = new Reader($_SERVER['DOCUMENT_ROOT'] . '/geo/GeoLite2-City.mmdb');

            if ($_SERVER['HTTP_HOST'] == 'tomdom.loc') {
                $_SERVER['REMOTE_ADDR'] = '93.184.162.210';
            }

            $r = $reader->city($_SERVER['REMOTE_ADDR']);

            $this->info['geo']->city    = $r->city->names['ru'];
            $this->info['geo']->country = $r->country->names['ru'];

            return $this->info['geo'];
        }
        catch (\GeoIp2\Exception\AddressNotFoundException $e)
        {
            $geo = new \stdClass;
            $geo->city = '';
            $geo->country = '';
            return $geo;
        }
    }
}
