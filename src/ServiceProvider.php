<?php

namespace Armincms\Iranmobleh;
 
use Illuminate\Foundation\Support\Providers\AuthServiceProvider;   
use Laravel\Nova\Nova as LaravelNova;
use Zareismail\Gutenberg\Gutenberg;

class ServiceProvider extends AuthServiceProvider 
{ 
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [    
        \Armincms\Koomeh\Models\KoomehProperty::class => \Armincms\Koomeh\Policies\Property::class, 
    ];

    /**
     * Register any application services.
     *
     * @return void
     */
    public function boot()
    { 
        $this->registerPolicies();   
        $this->fragments();
        $this->widgets();
        $this->templates();
        $this->menus();
        $this->routes(); 
        $this->app->booted(function() { 
            app('router')->pushMiddlewareToGroup(
                'web', \Spatie\ResponseCache\Middlewares\CacheResponse::class
            ); 
        });
    }   

    /**
     * Register the application's Gutenberg fragments.
     *
     * @return void
     */
    protected function fragments()
    {   
        Gutenberg::fragments([ 
            Cypress\Fragments\PropertyForm::class,
        ]);
    }

    /**
     * Register the application's Gutenberg widgets.
     *
     * @return void
     */
    protected function widgets()
    {   
        Gutenberg::widgets([  
            Cypress\Widgets\EditMyProperty::class,
            Cypress\Widgets\MyProperty::class,
        ]);
    }

    /**
     * Register the application's Gutenberg templates.
     *
     * @return void
     */
    protected function templates()
    {   
        Gutenberg::templates([  
            \Armincms\Iranmobleh\Gutenberg\Templates\EditMyPropertyWidget::class,
            \Armincms\Iranmobleh\Gutenberg\Templates\MyPropertyTableRow::class,
            \Armincms\Iranmobleh\Gutenberg\Templates\MyPropertyWidget::class,
        ]); 
    }

    /**
     * Register the application's menus.
     *
     * @return void
     */
    protected function menus()
    {    
        $this->app->booted(function() {   
        }); 
    }

    /**
     * Register the tool's routes.
     *
     * @return void
     */
    protected function routes()
    {
        if ($this->app->routesAreCached()) {
            return;
        }

        \Route::middleware(['web', 'auth'])
                ->prefix('iranmoble')
                ->group(__DIR__.'/../routes/api.php'); 
    } 
}
