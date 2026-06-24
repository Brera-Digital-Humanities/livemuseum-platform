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
        return [
            'settings' => [
                'label'       => 'LiveMuseum POI',
                'description' => 'Impostazioni generali del plugin LiveMuseum (Google Maps API Key, ecc.)',
                'icon'        => 'icon-map-marker',
                'class'       => 'Quivi\Poi\Models\PoiSetting',
                'order'       => 200,
                'keywords'    => 'livemuseum poi google maps api key coordinate',
                'permissions' => ['quivi.poi.pois'],
            ],
        ];
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
                    'itineraries' => [
                        'label'       => 'Itinerari',
                        'url'         => Backend::url('quivi/poi/itineraries'),
                        'icon'        => 'icon-road',
                        'permissions' => ['quivi.poi.itineraries'],
                    ],
                    'bookmarkfolders' => [
                        'label'       => 'Collections',
                        'url'         => Backend::url('quivi/poi/bookmarkfolders'),
                        'icon'        => 'icon-folder',
                        'permissions' => ['quivi.poi.bookmarkfolders'],
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
                    'commentlikes' => [
                        'label'       => 'Like commenti',
                        'url'         => Backend::url('quivi/poi/commentlikes'),
                        'icon'        => 'icon-thumbs-up',
                        'permissions' => ['quivi.poi.commentlikes'],
                    ],
                    'reports' => [
                        'label'       => 'Segnalazioni',
                        'url'         => Backend::url('quivi/poi/reports'),
                        'icon'        => 'icon-flag',
                        'permissions' => ['quivi.poi.reports'],
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
            'quivi.poi.bookmarkfolders' => [
                'tab'   => 'LiveMuseum',
                'label' => 'Gestione collections',
            ],
            'quivi.poi.commentlikes' => [
                'tab'   => 'LiveMuseum',
                'label' => 'Visualizzazione like commenti',
            ],
            'quivi.poi.reports' => [
                'tab'   => 'LiveMuseum',
                'label' => 'Gestione segnalazioni',
            ],
            'quivi.poi.itineraries' => [
                'tab'   => 'LiveMuseum',
                'label' => 'Gestione itinerari',
            ],
        ];
    }
}
