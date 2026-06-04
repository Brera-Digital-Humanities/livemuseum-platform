<?php namespace Quivi\Profile;

use Quivi\Profile\Classes\CorsMiddleware;
use System\Classes\PluginBase;
use Winter\User\Models\User;

class Plugin extends PluginBase
{
    public function boot()
    {
        $this->app->booted(function () {
            $this->app['Illuminate\Contracts\Http\Kernel']
                ->prependMiddleware(CorsMiddleware::class);
        });

        User::extend(function ($model) {
            $model->addFillable('birth_date');
            $model->addDateAttribute('birth_date');
            $model->rules['birth_date'] = 'nullable|date|before:today';
        });
    }

    public function registerComponents()
    {
    }

    public function registerSettings()
    {
    }
}
