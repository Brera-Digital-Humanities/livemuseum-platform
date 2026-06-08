<?php namespace Quivi\Poi\Controllers;

use Backend\Classes\Controller;
use BackendMenu;

class Bookmarks extends Controller
{
    public $implement = [
        \Backend\Behaviors\ListController::class,
    ];

    public $listConfig = 'config_list.yaml';

    public function __construct()
    {
        parent::__construct();
        BackendMenu::setContext('Quivi.Poi', 'poi', 'bookmarks');
    }
}
