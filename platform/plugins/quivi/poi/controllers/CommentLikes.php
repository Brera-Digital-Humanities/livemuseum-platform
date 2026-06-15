<?php namespace Quivi\Poi\Controllers;

use Backend\Classes\Controller;
use BackendMenu;

class CommentLikes extends Controller
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
        BackendMenu::setContext('Quivi.Poi', 'poi', 'commentlikes');
    }

    public function update($recordId = null)
    {
        $this->asExtension('FormController')->update($recordId);
    }
}