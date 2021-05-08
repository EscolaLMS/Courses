<?php

namespace EscolaLms\Courses\Database\Seeders;

use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

use Illuminate\Database\Seeder;

class CoursesPermissionSeeder extends Seeder
{
    public function run()
    {
        // create permissions
        $admin = Role::findOrCreate('admin', 'api');
        $tutor = Role::findOrCreate('tutor', 'api');

        Permission::findOrCreate('update course', 'api');
        Permission::findOrCreate('delete course', 'api');
        Permission::findOrCreate('create course', 'api');
        Permission::findOrCreate('attend course', 'api');

        $admin->givePermissionTo(['update course', 'delete course', 'create course', 'attend course']);
        $tutor->givePermissionTo(['update course', 'delete course', 'create course', 'attend course']);
    }
}
