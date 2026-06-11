<?php namespace Quivi\Poi\Controllers;

use Backend\Classes\Controller;
use BackendMenu;
use Input;

class ItineraryStops extends Controller
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

    public function formExtendFields($form)
    {
        // Pre-fill itinerary_id from query string when creating a new stop
        if ($form->model && !$form->model->exists) {
            $itineraryId = (int) Input::get('itinerary_id');
            if ($itineraryId) {
                $form->model->itinerary_id = $itineraryId;

                // Auto-set sort_order as next position
                $maxOrder = \Db::table('quivi_poi_itinerary_stops')
                    ->where('itinerary_id', $itineraryId)
                    ->max('sort_order');
                $form->model->sort_order = $maxOrder ? $maxOrder + 1 : 1;
            }
        }
    }
}
