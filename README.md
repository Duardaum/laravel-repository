# Laravel Repository

A simple Repository and Service layer for Laravel and Lumen Applications.

---

## Features

- Clean separation of data access logic using the Repository Pattern.
- Easily customizable repositories and interfaces.
- Built-in support for soft deletes and query context (active, trashed, or both).
- Simple integration with Laravel/Lumen's Service Container.
- Extendable base repository with common CRUD and other operations.

---

## Summary

- [Compatibility](#compatibility)
- [Features](#features)
- [Installation](#installation)
- [Configuration](#configuration)
- [Usage](#usage)
  - [Models, Repositories and Interfaces](#model-repository--interface)
  - [Basic Usage](#basic-usage)
  - [Create, Update and Delete](#create-update-and-delete)
  - [Create via CSV file (import file)](#create-via-csv-file-import-file)
  - [Read](#read)
  - [Custom Methods](#custom-methods)
  - [Available Methods](#available-methods)
- [Testing](#testing)
- [Contributing](#contributing)
- [License](#license)

---

## Compatibility

|               | Version |      PHP      |      Dependency Version      |
|:-------------:|:-------:|:-------------:|:----------------------------:|
| Laravel/Lumen |   8.*   |      8.0      |        prefer-stable         |
| Laravel/Lumen |   8.*   |      8.1      | prefer-lowest, prefer-stable |
| Laravel/Lumen |   9.*   |   8.0, 8.2    |        prefer-stable         |
| Laravel/Lumen |   9.*   |      8.1      | prefer-lowest, prefer-stable |
| Laravel/Lumen |  10.*   | 8.1, 8.2, 8.3 | prefer-lowest, prefer-stable |
| Laravel/Lumen |  11.*   | 8.2, 8.3, 8.4 |        prefer-stable         |
|    Laravel    |  12.*   | 8.2, 8.3, 8.4 | prefer-lowest, prefer-stable |

## Installation

Use composer to install the package:

```bash
composer require duardaum/laravel-repository
```

## Configuration

In your Laravel/Lumen application, create a new `Service Provider`.  
This Service Provider will be the place where you'll register all your Repositories.  
You can register your repositories in the **AppServiceProvider** if you wish, but to keep things separated and for the possibility the Service Provider be very large, we recommend putting them separated.

```php
namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{

    public function register()
    {
        //
    }
    
    public function boot()
    {
        //
    }

}
```

And then, register the new Service Provider in your application:

On Lumen:
```php
//bootstrap/app.php
$app->register(App\Providers\RepositoryServiceProvider::class);
```
On Laravel:

```php
//config/app.php
...
    'providers' => [
        ...
        App\Providers\RepositoryServiceProvider::class,
    ]
...
```

## Usage

The `Repository Pattern` is a design pattern that became very popular over the years.  
It's a very nice and good way to organize data access and logic in one place, keeping other parts of your application responsible for what they do best,  
especially if you are using other Design Patterns like S.O.L.I.D and Clean Code.  
With this in mind, we created this very simple but yet powerful package, for you to centralize all your data access in a very simple way.

### Model, Repository & Interface

Let's suppose that we have a table called **messages** with this structure, and we'll run some SQL commands on it:

```sql
CREATE TABLE messages (
    id INT PRIMARY KEY,
    content VARCHAR(255) NOT NULL,
    created_at DATETIME NULL,
    updated_at DATETIME NULL,
    deleted_at DATETIME NULL
);
```

First, let's create a Model:

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Message extends Model
{

    use SoftDeletes;

    protected $table = 'messages';

    protected $fillable = [
        'content',
        'created_at',
        'updated_at',
    ];

}
```
Then, let's create an Interface for the Repository. All interfaces **MUST** extend from **BaseRepositoryInterface**:
```php
namespace App\Contracts\Repositories;

use Duardaum\LaravelRepository\Contracts\Repositories\BaseRepositoryInterface;

interface MessageRepositoryInterface extends BaseRepositoryInterface
{

}
```
And let's create the Repository. All repositories **MUST** extend from **BaseRepository** and implement an interface that was extended from **BaseRepositoryInterface**.  
For the repository to know what table it should run the queries on, we need to inform a Model for the Repository:
```php
namespace App\Repositories;

use Duardaum\LaravelRepository\Repositories\BaseRepository;
use App\Contracts\Repositories\MessageRepositoryInterface;
use App\Models\Message;

class MessageRepository extends BaseRepository implements MessageRepositoryInterface
{

    protected string|\Illuminate\Database\Eloquent\Model $_model = Message::class;

}
```
> P.S.: Some people do not like to use Interfaces on repositories. If it's your case, just dont and all be fine too!

Finally, let's register the repository to be used in the application: 
```php
// app/Providers/RepositoryServiceProvider.php
...
    public function register()
    {
        $this->app->bind(\App\Contracts\Repositories\MessageRepositoryInterface::class, \App\Repositories\MessageRepository::class);
    }
...
```
This structure with Repository & Interface is very helpful if you need to make a different implementation of the same repository.  
The contract of the repository stays the same and does not affect your application, needing to go in one place to replace all.

### Basic usage

You can inject the Interface in constructors/methods to instantiate the Repository or use the Service Container to do it:

```php
// 1. On constructor
use App\Contracts\Repositories\MessageRepositoryInterface;

class SomeClass {
    public function __construct
    (
        private MessageRepositoryInterface $_repo_message
    ){}
}

// 2. With Service Container
$repo_message = app(\App\Contracts\Repositories\MessageRepositoryInterface::class);
```

### Create, Update and Delete
To make these operations is very simple:

```php
// create
$message = $repo_message->create(['content' => 'Test 1']);
//update
$repo_message->update(['content' => 'Test 2'], $message->id);
//Delete - soft
$repo_message->delete($message->id);
//Delete - hard
$repo_message->forceDelete($message->id);
```

### Create via CSV file (import file)
One of the common challange we face in our day-in-day work, is importing a large number of register via CSV files to the database. \
Some time, we dont need nothing fancy, just a simple but yet powerfull way to import the file, where we can customize witch column on CSV file we want to import for certain table column. \
Thinking of that, we create an opinionated way to do this kind of work in a simple, fast and using the minimum amount of memory possible.

> This type of task can be done in several ways, but the way your environment is configured and the resources are available will influence performance! 

For this task, we create the `importFile` method on the `BaseRepository`, that has 4 parameters:

- `(string) $path`: Path to the CSV file
- `(array) $columns`: The To (table) / From (file) columns
- `(?callable) $rowGenerate`: A callable to format a row to insert on table
- `(null|stdClass|array) $options`: A setup options to import the file
  - `$options->separator (string)`: [PHP fgetcsv](https://www.php.net/manual/pt_BR/function.fgetcsv.php) separator's. Default `comma`!
  - `$options->chunkSize (int)`: The size of chunk of data to be insert at once per time. Default and max `1000`!
  - `$options->hasHeader (bool)`: If the file has header (first line have column names). Default `false`!

The method return the number of chunk inserted on database!

**Import: Basic (no $rowGenerate)**
```php
$path = '/path/to/csv/file_100_lines.csv';
$columns = [
    //table column => file column position
    'content' => 0,
];

$options = [
    'chunkSize' => 50,
];

$result = $this->_repository->importFile($path, $columns, null, $options); //output: 2
```

**Import: Advanced (with $rowGenerate)**
```php
$path = '/path/to/csv/file_100_lines.csv';

$rowGenerator = function($line){ //csv line
    return [
        //table column => file column position
        'content' => 'IMPORTED: '.$line[0],
        'created_at' => '1800-01-01 00:00:00',
        'updated_at' => $line[2]
    ];
};

$options = [
    'chunkSize' => 50,
];

$result = $this->_repository->importFile($path, [], $rowGenerator, $options); //output: 2
```
For more example, look into our [test suite](./tests/Feature/BaseRepositoryTest.php#L43)!

### Read
One of the greatest features of this package is the possibility to read data from different states (active, deactivated or both) in the same operation with minimal effort.
By default, the repository will get data only for active records (newQuery). To get from trash (deactivated) or both, there is an easy way to do it:

```php
//From trash (deactivated)
$data = $repo_message->onlyTrashed()->findWhere(...);
//The next line will keep on trashed context, so you don't need to put 'onlyTrashed' again:
$data2 = $repo_message->findWhere(...);
//If you want to change query context on next line, just call 'newQuery' or 'withTrashed' and the context will change:
$data3 = $repo_message->newQuery()->findWhere(...);
$data4 = $repo_message->findWhere(...); // now running on 'newQuery' context
```
### Custom methods
To create a custom method on repository is very simple, and you can take advantage of the built-in methods of the base repository to do it:
```php
// app/Repositories/MessageRepository.php
...
    public function getAllDeactived(): \Illuminate\Database\Eloquent\Collection
    {
        return self::onlyTrashed()->all();
    }
..
// app/Contracts/Repositories\MessageRepositoryInterface.php
...
    public function getAllDeactived() : \Illuminate\Database\Eloquent\Collection;
...
```
And you can create a custom method, taking into consideration the query context chosen by user:
```php
// app/Repositories/MessageRepository.php
...
    public function someGreatMethod(): \Illuminate\Database\Eloquent\Collection
    {
        return self::getCurrentQuery()->findWhere(...);    
    }
...
// app/Contracts/Repositories/MessageRepositoryInterface.php
...
    public function someGreatMethod(): \Illuminate\Database\Eloquent\Collection;
...
```

### Available Methods

The `BaseRepository` provides sereval methods out of the box that you can use easily like:

- `getTableName()`: Get the table name
- `deleteWhere(array $where)`: Soft delete records by condition
- `forceDeleteWhere(array $where)`: Force delete records by condition
- `restoreWhere(array $where)`: Restore soft deleted records by condition
- `findWhereLimit(array $where, int $limit = self::DEFAULT_PAGINATOR_LIMIT, array $columns = ['*'])`: Search a limited number of records by condition
- `findWherePaginate(array $where, int $perPage = self::DEFAULT_PAGINATOR_LIMIT, array $columns = ['*'], string $pageName = null, int $page = null)`: Search for records and page the result

You can extend your repository with custom methods as needed. For more available methods, you can find [here](./src/Contracts/Repositories/BaseRepositoryInterface.php) . \
Yet, you can use the `Eloquent` methods strait from repository if you need to via the `builder` method:

```php
$data = $repo_message->builder()->anyEloquentMethodHere(...)
```

You can see how to use all methods looking in our [tests](./tests/Feature/BaseRepositoryTest.php) .

### Testing

To ensure your repositories are working as expected, you can write tests using Laravel's built-in testing tools. Example:

```php
public function test_can_create_message()
{
    $repo = app(\App\Contracts\Repositories\MessageRepositoryInterface::class);
    $message = $repo->create(['content' => 'Hello World']);
    $this->assertDatabaseHas('messages', ['content' => 'Hello World']);
}
```

## Contributing

Pull requests are welcome! For major changes, please open an issue first to discuss what you would like to change.

**How to contribute:**

1. Fork the repository.
2. Create your feature branch (`git checkout -b feature/your-feature`).
3. Commit your changes (`git commit -am 'Add some feature'`).
4. Push to the branch (`git push origin feature/your-feature`).
5. Open a pull request.

Please make sure to update tests as appropriate.

## License

[MIT](./LICENSE.md)