# Courses

Courses and content package

[![swagger](https://img.shields.io/badge/documentation-swagger-green)](https://escolalms.github.io/Courses/)
[![codecov](https://codecov.io/gh/EscolaLMS/Courses/branch/main/graph/badge.svg?token=NRAN4R8AGZ)](https://codecov.io/gh/EscolaLMS/Courses)
[![phpunit](https://github.com/EscolaLMS/Courses/actions/workflows/test.yml/badge.svg)](https://github.com/EscolaLMS/Courses/actions/workflows/test.yml)
[![downloads](https://img.shields.io/packagist/dt/escolalms/courses)](https://packagist.org/packages/escolalms/courses)
[![downloads](https://img.shields.io/packagist/v/escolalms/courses)](https://packagist.org/packages/escolalms/courses)
[![downloads](https://img.shields.io/packagist/l/escolalms/courses)](https://packagist.org/packages/escolalms/courses)

## Model relation

The model user must be extended with the class HasCourses :
```
class User extends EscolaLms\Core\Models\User
{
    use HasCourses;
```

## Database relation

There is simple relation. [see docs for diagram](doc)

1. `Course` general category of the course
2. `Lesson` grouped by Course
3. `Topic` grouped by Lesson

```
Course 1 -> n Lesson
Lesson 1 -> n Topic
Topic 1 -> 1 TopicContent
```

`TopicContent` is an abstract model, this package contains some sample implementatio eg, `RichText`, `Audio`, `Video`, `H5P` and `Image`

You create any of the Content model by post to the same Topic endponit (create and update), [see docs examples](doc)

**Note** that `/api/topics` is using `form-data` - this is due to PHP nature of posting files

List of possible `TopicContent`s is availabe in the endpoint `/api/topics/types`

## Adding new `TopicContent` type

In the ServiceProvider register your class like

```php
use Illuminate\Support\ServiceProvider;
use EscolaLms\Courses\Repositories\TopicRepository;
use CustomPackage\Models\TopicContentCustom;


class CustomServiceProvider extends ServiceProvider
{

    //...

    public function register()
    {
        TopicRepository::registerContentClass(TopicContentCustom::class);
    }
}
```

see [EscolaLmsCourseServiceProvider.php](src/EscolaLmsCourseServiceProvider.php) as reference as well as [Models/TopicContent](package2/src/Models/TopicContent)

## Seeder 

Package comes with seeder that create course with lessons and topics 

```php
php artisan db:seed --class="\EscolaLms\Courses\Database\Seeders\CoursesSeeder"
```
