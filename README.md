# Laravel Repository

A simple Repository and Service layer to Laravel and Lumen Application

---

## Compatibility

|               | Version |      PHP      |      Dependency Version      |
|:-------------:|:-------:|:-------------:|:----------------------------:|
| Laravel/Lumen |   8.*   |      8.0      |        prefer-stable         |
| Laravel/Lumen |   8.*   |      8.1      | prefer-lowest, prefer-stable |
| Laravel/Lumen |   9.*   |   8.0, 8.2    |        prefer-stable         |
| Laravel/Lumen |   9.*   |      8.1      | prefer-lowest, prefer-stable |
| Laravel/Lumen |  10.*   | 8.1, 8.2, 8.3 | prefer-lowest, prefer-stable |

## Installation

Use composer to install the package

```bash
composer require duardaum/laravel-repository
```

## Configuration


In your Laravel/Lumen application, create a new Service Provider. \
This Service Provider will be the place where you'll register all your Repositories. \
You can register your repositories in the **AppServiceProvider** if you wish, but for keep things separated and for the possibility the Service Provider be very large, we recommend putting then separated.

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

The Repository Pattern is a designer pattern that became very popular with the pass of years. 
It's a very nice and good way to organize access data and logic in one place, keeping another parts of your application responsible for what they do best, 
specially if you are using another Designer Patterns like S.O.L.I.D and Clean Code. \
With this in mind, we create this very simple but yet powerfully package, for you to centralize all your data access in a very simple way.

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
Then, let's create an Interface for the Repository. All interfaces **MUST** to extends from **BaseRepositoryInterface**:
```php
namespace App\Contracts\Repositories;

use Duardaum\LaravelRepository\Contracts\Repositories\BaseRepositoryInterface;

interface MessageRepositoryInterface extends BaseRepositoryInterface
{

}
```
And let's create the Repository. All repositories **MUST** to extends from **BaseRepository** and implements an interface the was extended from **BaseRepositoryInterface**. \
For the repository know what table they should run the queries, we need to inform a Model for the Repository:
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
Finally, let's register the repository to be used on the application: 
```php
// app/Providers/RepositoryServiceProvider.php
...
    public function register()
    {
        $this->app->bind(\App\Contracts\Repositories\MessageRepositoryInterface::class, \App\Repositories\MessageRepository::class);
    }
...
```
This structure with Repository & Interface is very helpful, if you need to make a different implementation of the same repository. 
The contract of the repository keeps the same and no affect you application, needing to go in one place to replace all.

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

### Read
One of the greatest features that have on this package, is a possibility to read data from different states (activate, deactivate of both) in the same operation with minimal effort.
By default, the repository will get data only for active records (newQuery). To get from trash (deactivate) or both, there is an easy way to do it:

```php
//From trash (deactivate)
$data = $repo_message->onlyTrashed()->findWhere(...);
//The next line will keep on trashed context, so you don't need to put 'onlyTrashed' again:
$data2 = $repo_message->findWhere(...);
//If you want to change query context on next line, just call 'newQuery' or 'withTrashed' and the context will change:
$data3 = $repo_message->newQuery()->findWhere(...);
$data4 = $repo_message->findWhere(...); // are running on 'newQuery' context
```
### Custom methods
To create a custom method on repository is very simple, and you can take advantage of the build-in methods of the base repository to do it:
```php
// app/Repositories/MessageRepository.php
...
    public function getAllDeactived(): \Illuminate\Database\Eloquent\Collection
    {
        self::onlyTrashed()->all();
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

## Contributing

Pull requests are welcome. For major changes, please open an issue first
to discuss what you would like to change.

Please make sure to update tests as appropriate.

## License

[MIT](./LICENSE.md)