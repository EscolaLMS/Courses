<?php

namespace Tests\APIs;

use EscolaLms\Core\Tests\CreatesUsers;
use EscolaLms\Courses\Database\Seeders\CoursesPermissionSeeder;
use EscolaLms\Courses\Tests\TestCase;
use EscolaLms\Settings\Database\Seeders\PermissionTableSeeder as SettingsPermissionSeeder;
use EscolaLms\Settings\Enums\SettingsPermissionsEnum;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class CourseAdministrableConfigTest extends TestCase
{
    use CreatesUsers;
    use DatabaseTransactions;

    /**
     * @test
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(CoursesPermissionSeeder::class);
        $this->seed(SettingsPermissionSeeder::class);

        $this->user = config('auth.providers.users.model')::factory()->create();
        $this->user->guard_name = 'api';
        $this->user->assignRole('admin');
    }

    public function test_course_administrable_config()
    {
        $this->response = $this->actingAs($this->user, 'api')->json(
            'GET',
            '/api/config',
        );

        $this->response->assertOk();
        $this->response->assertJsonFragment([
            'escolalms_courses' => ['platform_visibility' => 'public']
        ]);

        $this->response = $this->actingAs($this->user, 'api')->json(
            'GET',
            '/api/admin/config',
        );

        $this->response->assertOk();
        $this->response->assertJsonFragment([
            'escolalms_courses' => [
                'platform_visibility' => [
                    'rules' => ['required', 'string', 'in:public,registered'],
                    'public' => true,
                    'readonly' => false,
                    'value' => 'public',
                ]
            ]
        ]);
    }
}
