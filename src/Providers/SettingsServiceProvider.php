<?php

namespace EscolaLms\Courses\Providers;

use EscolaLms\Courses\Enum\PlatformVisibility;
use EscolaLms\Settings\EscolaLmsSettingsServiceProvider;
use EscolaLms\Settings\Facades\AdministrableConfig;
use Illuminate\Support\ServiceProvider;

class SettingsServiceProvider extends ServiceProvider
{
    public function register()
    {
        if (class_exists(\EscolaLms\Settings\EscolaLmsSettingsServiceProvider::class)) {
            if (!$this->app->getProviders(EscolaLmsSettingsServiceProvider::class)) {
                $this->app->register(EscolaLmsSettingsServiceProvider::class);
            }

            AdministrableConfig::registerConfig('escolalms_courses.platform_visibility', ['required', 'string', 'in:' . implode(',', PlatformVisibility::getValues())]);
            AdministrableConfig::registerConfig('escolalms_courses.reminder_of_deadline_count_days', ['integer', 'min: 1']);
        }
    }
}
