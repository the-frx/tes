<?php

namespace App\Helpers;

use Webpatser\Countries\Countries;

class CountryHelper
{
    public static function getCountryOptions()
    {
        $countries = Countries::all();
        $options = [];

        foreach ($countries as $country) {
            $options[$country->iso_3166_2] = $country->name;
        }

        asort($options);

        return $options;
    }
}
