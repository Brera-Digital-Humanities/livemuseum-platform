<?php namespace Quivi\Poi;

use Backend;
use System\Classes\PluginBase;

class Plugin extends PluginBase
{
    public function registerComponents()
    {
    }

    public function registerSettings()
    {
    }

    public function register()
    {
        $this->registerConsoleCommand('Quivi.Poi:Sync', 'Quivi\Poi\Console\Sync');
    }

    public function boot()
    {
        \Winter\User\Models\User::extend(function ($model) {
            $model->hasMany['comments'] = [
                \Quivi\Poi\Models\Comment::class,
                'key' => 'user_id',
            ];
        });

        \Winter\User\Controllers\Users::extendFormFields(function ($form, $model) {
            if (!$model instanceof \Winter\User\Models\User) {
                return;
            }

            $form->addTabFields([
                'quivi_comments' => [
                    'label'   => 'Commenti',
                    'type'    => 'partial',
                    'path'    => '$/quivi/poi/partials/_user_comments.htm',
                    'tab'     => 'Commenti',
                ],
            ]);
        });
    }

    public function registerNavigation()
    {
        return [
            'poi' => [
                'label'       => 'LiveMuseum',
                'url'         => Backend::url('quivi/poi/pois'),
                'icon'        => 'icon-map-marker',
                'permissions' => ['quivi.poi.*'],
                'order'       => 200,
                'sideMenu'    => [
                    'pois' => [
                        'label'       => 'POI',
                        'url'         => Backend::url('quivi/poi/pois'),
                        'icon'        => 'icon-map-marker',
                        'permissions' => ['quivi.poi.pois'],
                    ],
                    'pictures' => [
                        'label'       => 'Foto',
                        'url'         => Backend::url('quivi/poi/pictures'),
                        'icon'        => 'icon-photo',
                        'permissions' => ['quivi.poi.pictures'],
                    ],
                    'comments' => [
                        'label'       => 'Commenti e Reviews',
                        'url'         => Backend::url('quivi/poi/comments'),
                        'icon'        => 'icon-comment',
                        'permissions' => ['quivi.poi.comments'],
                    ],
                    'bookmarks' => [
                        'label'       => 'Bookmark',
                        'url'         => Backend::url('quivi/poi/bookmarks'),
                        'icon'        => 'icon-bookmark',
                        'permissions' => ['quivi.poi.bookmarks'],
                    ],
                ],
            ],
        ];
    }

    public function registerPermissions()
    {
        return [
            'quivi.poi.pois' => [
                'tab'   => 'LiveMuseum',
                'label' => 'Gestione POI',
            ],
            'quivi.poi.pictures' => [
                'tab'   => 'LiveMuseum',
                'label' => 'Gestione foto',
            ],
            'quivi.poi.comments' => [
                'tab'   => 'LiveMuseum',
                'label' => 'Gestione commenti',
            ],
            'quivi.poi.bookmarks' => [
                'tab'   => 'LiveMuseum',
                'label' => 'Visualizzazione bookmark',
            ],
        ];
    }
}
