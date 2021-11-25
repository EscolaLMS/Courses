<?php

namespace EscolaLms\Courses\Tests;

use EscolaLms\Auth\EscolaLmsAuthServiceProvider;
use EscolaLms\Auth\Tests\Models\Client;
use EscolaLms\Categories\EscolaLmsCategoriesServiceProvider;
use EscolaLms\Courses\AuthServiceProvider;
use EscolaLms\Courses\EscolaLmsCourseServiceProvider;
use EscolaLms\Courses\Tests\Models\User as UserTest;
use EscolaLms\Notifications\EscolaLmsNotificationsServiceProvider;
use EscolaLms\Scorm\EscolaLmsScormServiceProvider;
use EscolaLms\Settings\EscolaLmsSettingsServiceProvider;
use EscolaLms\Tags\EscolaLmsTagsServiceProvider;
use EscolaLms\Templates\EscolaLmsTemplatesServiceProvider;
use EscolaLms\TopicTypes\EscolaLmsTopicTypesServiceProvider;
use Illuminate\Testing\TestResponse;
use Laravel\Passport\Passport;
use Laravel\Passport\PassportServiceProvider;
use Spatie\Permission\PermissionServiceProvider;

class TestCase extends \EscolaLms\Core\Tests\TestCase
{
    protected ?TestResponse $response;

    protected function setUp(): void
    {
        parent::setUp();
        Passport::useClientModel(Client::class);
    }

    protected function getPackageProviders($app)
    {
        return [
            ...parent::getPackageProviders($app),
            EscolaLmsAuthServiceProvider::class,
            PermissionServiceProvider::class,
            PassportServiceProvider::class,
            EscolaLmsCategoriesServiceProvider::class,
            EscolaLmsCourseServiceProvider::class,
            AuthServiceProvider::class,
            EscolaLmsScormServiceProvider::class,
            EscolaLmsTagsServiceProvider::class,
            EscolaLmsTopicTypesServiceProvider::class,
            EscolaLmsSettingsServiceProvider::class,
            EscolaLmsTemplatesServiceProvider::class,
            EscolaLmsNotificationsServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('auth.providers.users.model', UserTest::class);
        $app['config']->set('passport.client_uuids', true);
        $app['config']->set('database.connections.mysql.strict', false);
        $app['config']->set('app.debug', (bool) env('APP_DEBUG', true));
        $app['config']->set('escolalms.tags.ignore_migrations', false);

        $app['config']->set('scorm', [
            'table_names' => [
                'user_table' => 'users',
                'scorm_table' => 'scorm',
                'scorm_sco_table' => 'scorm_sco',
                'scorm_sco_tracking_table' => 'scorm_sco_tracking',
            ],
            // Scorm directory. You may create a custom path in file system
            'disk' => 'local',
        ]);
    }

    public function assertApiResponse(array $actualData)
    {
        $this->assertApiSuccess();

        $response = json_decode($this->response->getContent(), true);
        $responseData = $response['data'];

        $this->assertNotEmpty($responseData['id']);
        $this->assertModelData($actualData, $responseData);
    }

    public function assertApiSuccess()
    {
        $this->response->assertJson(['success' => true]);
    }

    public function assertModelData(array $actualData, array $expectedData)
    {
        foreach ($actualData as $key => $value) {
            if (in_array($key, ['created_at', 'updated_at'])) {
                continue;
            }
            $this->assertEquals($actualData[$key], $expectedData[$key]);
        }
    }
}
