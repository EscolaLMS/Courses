<?php

namespace EscolaLms\Courses\Database\Seeders;

use EscolaLms\Core\Enums\UserRole;
use EscolaLms\Courses\Enums\CoursesPermissionsEnum;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

use Illuminate\Database\Seeder;

class CoursesPermissionSeeder extends Seeder
{
    public function run()
    {
        // create permissions
        $admin = Role::findOrCreate(UserRole::ADMIN, 'api');
        $tutor = Role::findOrCreate(UserRole::TUTOR, 'api');

        Permission::findOrCreate(CoursesPermissionsEnum::COURSE_UPDATE, 'api');
        Permission::findOrCreate(CoursesPermissionsEnum::COURSE_DELETE, 'api');
        Permission::findOrCreate(CoursesPermissionsEnum::COURSE_CREATE, 'api');
        Permission::findOrCreate(CoursesPermissionsEnum::COURSE_ATTEND, 'api');
        Permission::findOrCreate(CoursesPermissionsEnum::COURSE_UPDATE_OWNED, 'api');
        Permission::findOrCreate(CoursesPermissionsEnum::COURSE_DELETE_OWNED, 'api');
        Permission::findOrCreate(CoursesPermissionsEnum::COURSE_ATTEND_OWNED, 'api');

        $admin->givePermissionTo([
            CoursesPermissionsEnum::COURSE_UPDATE,
            CoursesPermissionsEnum::COURSE_DELETE,
            CoursesPermissionsEnum::COURSE_CREATE,
            CoursesPermissionsEnum::COURSE_ATTEND,
            CoursesPermissionsEnum::COURSE_UPDATE_OWNED,
            CoursesPermissionsEnum::COURSE_DELETE_OWNED,
            CoursesPermissionsEnum::COURSE_ATTEND_OWNED,
        ]);
        $tutor->givePermissionTo([
            CoursesPermissionsEnum::COURSE_CREATE,
            CoursesPermissionsEnum::COURSE_UPDATE_OWNED,
            CoursesPermissionsEnum::COURSE_DELETE_OWNED,
            CoursesPermissionsEnum::COURSE_ATTEND_OWNED,
        ]);
    }
}
