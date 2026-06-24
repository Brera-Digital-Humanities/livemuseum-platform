<?php namespace Quivi\Poi\Models;

use Model;

class PoiSetting extends Model
{
    public $implement = [
        \System\Behaviors\SettingsModel::class,
    ];

    public $settingsCode   = 'quivi_poi_settings';
    public $settingsFields = 'fields.yaml';

    public function initSettingsData()
    {
        $this->google_maps_api_key = '';
    }

    public static function hasGoogleMapsApiKey(): bool
    {
        return trim((string) self::get('google_maps_api_key')) !== '';
    }
}
