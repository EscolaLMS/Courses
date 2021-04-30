# Courses

Courses and content package

[![codecov](https://codecov.io/gh/EscolaLMS/Courses/branch/main/graph/badge.svg?token=NRAN4R8AGZ)](https://codecov.io/gh/EscolaLMS/Courses)
[![phpunit](https://github.com/EscolaLMS/Courses/actions/workflows/test.yml/badge.svg)](https://github.com/EscolaLMS/Courses/actions/workflows/test.yml)
[![downloads](https://img.shields.io/packagist/dt/escolalms/courses)](https://packagist.org/packages/escolalms/courses)
[![downloads](https://img.shields.io/packagist/v/escolalms/courses)](https://packagist.org/packages/escolalms/courses)
[![downloads](https://img.shields.io/packagist/l/escolalms/courses)](https://packagist.org/packages/escolalms/courses)

[Swagger](https://escolalms.github.io/Courses/)

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
