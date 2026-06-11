<?php namespace Quivi\Poi\Controllers;

use Backend\Classes\Controller;
use BackendMenu;

class Itineraries extends Controller
{
    public $implement = [
        \Backend\Behaviors\ListController::class,
        \Backend\Behaviors\FormController::class,
    ];

    public $listConfig = 'config_list.yaml';
    public $formConfig = 'config_form.yaml';

    public function __construct()
    {
        parent::__construct();
        BackendMenu::setContext('Quivi.Poi', 'poi', 'itineraries');
    }

    public function create()
    {
        $this->asExtension('FormController')->create();
    }

    public function create_onSave()
    {
        return $this->asExtension('FormController')->create_onSave();
    }

    public function update($recordId = null)
    {
        $this->asExtension('FormController')->update($recordId);
    }

    public function update_onSave($recordId = null)
    {
        return $this->asExtension('FormController')->update_onSave($recordId);
    }

    public function update_onDelete($recordId = null)
    {
        return $this->asExtension('FormController')->update_onDelete($recordId);
    }
}
