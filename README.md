![Detrack logo](https://www.detrack.com/wp-content/uploads/2016/12/Logo_detrack.png)
# detrack-core-php

Official core library for PHP applications to interact with the [Detrack](https://www.detrack.com) API.

**Important v2 release** v2 is **incompatible** with code using the v1 library due to major overhauls in the backend API. Class names and method names have been changed. Please review the documentation carefully and refactor your code accordingly should you decide to upgrade.

## Installation

Install the package via [composer](https://getcomposer.org):

```bash
composer require detrack/detrack-core:^2.0
```
Composer will handle all the dependencies for you.

### Prerequisites

**Requires PHP >= 7.1**

PHP 5.6 and 7.0 has already been officially deprecated; our new library and API takes advantage of the new features PHP 7.1 has to offer for better stability.

You must also have created a (free!) account on Detrack, and understand our basic workflow.

And remember to include the autoloader, if you haven't already:

```php
require_once "vendor/autoload.php";
```
## Simple Usage Guide

In this short guide, we'll pretend that we're an e-shop who sells ice-cream, and use the Detrack service to track our deliveries.
We will use this library to implement the bare-minimum functionality for Detrack to function. (An advanced guide is available further below)

### The client facade

First, you need to configure the static facade of `Detrack\DetrackCore\Client\DetrackClientStatic`.

```php
Detrack\DetrackCore\Client\DetrackClientStatic::setApiKey($apiKey);
```

All objects in this library will then use this API key for the rest of your request lifecycle.

### Creating Deliveries

When your e-shop confirms payment from a customer, you need to send a request to us regarding the details of the delivery so that a free vehicle in your fleet can be assigned to complete this delivery. This is represented via the `Job` and `Item` objects.

`Detrack\DetrackCore\Resource\Job` represents a single delivery job you assign to your drivers. In the context of an e-shop, this also represents a single order on the store.
`Detrack\DetrackCore\Resource\Model\Item` represents an item from your shop within the order. While you do not need this for Detrack to function, it will show up in the Electronic Proof of Deliveries (POD) that you and your customer will get when the Delivery is marked as complete.

There are three attributes that you must give the Job object before you submit it, *date*, *do_number* (Delivery order number) and *address*.

```php
use Detrack\DetrackCore\Resource\Job;

$delivery = new Job([
  "date"=>"2018-12-19",
  "do_number"=>"DO# 12345",
  "address"=>"Null Island",
  "instructions"=>"Tell recipient to come out and retrieve ice cream from van" //not required, but you can specify other fields that are documented on our API reference
]);

$delivery->save();
```
You can also first pass no arguments into the constructor, then modify the attributes later:

```php
use Detrack\DetrackCore\Resource\Job;

$delivery = new Job();
$delivery->date = "2018-12-19";
$delivery->do_number = "DO# 12345";
$delivery->address = "Null Island";
$delivery->instructions = "Tell recipient to come out and retrieve ice cream from van"; //not required, but you can specify other fields that are documented on our API reference
$delivery->save(); //submits the delivery to Detrack
```

Remember that you must ensure that the required attributes *date*, *do_number* and *address* are set before you call `save()`.

Note that the `save()` function behaves as an "upsert" function, which automatically creates jobs that do not yet exist or updates jobs that already exist. If you require a "strict insert" and "strict update", use `create()` and `save()` respectively, but be prepared to catch an `Exception` if `create()` is called on a job with a conflicting *do_number* on the same day and `update()` is called on a job that does not yet exist.

And that's it! You've submitted your first delivery to Detrack. If you have your vehicles set up correctly on the Detrack Control Panel, your system will automatically assign deliveries to your drivers and you can start tracking them through their other apps.

### Adding items to deliveries

While what is documented in the previous section is the bare minimum required to get your application integrated with Detrack, you should add more information that is useful to both your staff and your customers. The next point we shall cover is adding items to your deliveries. These items will show up on receipts and Electronic PODs that you and will customers will receive.

The `Item` object has three base required attributes: *sku* (stock keeping unit (number)), *qty* (quantity) and *desc* description.

Creating the Item objects is similar to Deliveries, but you need not use factories since there's no client to attach:

```php
use Detrack\DetrackCore\Resource\Model\Item;

$item = new Item([
  "sku"=>"IC 456",
  "qty"=>"5",
  "desc"=>"Strawberry flavoured ice-cream"
]);
```
Alternatively, like jobs, you can pass an empty argument into the constructor, then set the attributes later:

```php
use Detrack\DetrackCore\Model\Item;

$item = new Item();
$item->sku = "IC 456";
$item->qty = "5";
$item->desc = "Strawberry flavoured ice-cream";
```

Then, you need to add them to a delivery object by first accessing the `items` property and then calling the `add()` method:

```php
$delivery->items->add($item);
$delivery->save() //sends the info to Detrack
```
And that's it: The `items` property is an instance of `Detrack\DetrackCore\Model\ItemCollection`, and contains methods like `push()`, `pop()` for you to manipulate the items attached to the delivery. Don't forget to call `save()` afterwards to commit the changes to the Detrack API.

## Advanced Usage Guide

This part contains miscellaneous bits and pieces of info should you require more fine control.

### Find deliveries

Deliveries stored in the Detrack database are identified by both their *do_number* and their scheduled delivery *date*.

The `hydrate()` method is available to fill up the rest of the attributes given the *do_number* and *date*.

This is usually used to retrieve updated information of a delivery.

### Upsert and delete

The `save()` and `delete()` functions work in the Object-Relation-Mapping style, and you can call them on any `Job` object:

```php
// Update instructions
$delivery->instructions = "Change of plan, leave ice cream package in the mailbox instead";
$delivery->save();

// Customer no longer wants ice cream
$delivery->delete();
```
Upon deleting, the Delivery job will no longer show up on the Detrack Dashboard, and will no longer be tracked.

### Create and updates

As mentioned above, if you require strict inserts and updates, use `create()` and `update()`:
```php
$delivery = new Job();
$delivery->do_number = "DO# 12345";
$delivery->address = "PHP Island";
$delivery->date = date("Y-m-d");
$delivery->items->add(new Item(["sku"=>"1","qty"=>5,"desc"=>"Chocolate Ice Cream"]));

//will throw exception
$delivery->update();
//ok
$delivery->create();

$delivery->items->pop();
$delivery->items->add(new Item(["sku"=>"2","qty"=>5,"desc"=>"Strawberry Ice Cream"]));
//will throw exception
$delivery->create();
//ok
$delivery->update();
```

### Retrieving Documents

The `downloadDoc(String $document, String $format, String $target)` method is available to download documents relevant to each `Job`.

- `$document` - `"pod"` (default) or `"shipping-label"`
- `$format` - `"pdf"` (default) or `"tiff"`
- `$target` - `NULL` (default) or `/path/to/folder` or `/path/to/nonexistent/file`.
    - If `NULL` is passed, raw file data will be returned to you as a string.
    - If the path of an existing folder is given, the file is downloaded into that folder with the default file name from the server.
    - If a path to a nonexistent file is given, the file will be downloaded to that exact path with that name.

### Bulk operations

Bulk operations are available for list, create (strict) and delete. However, you should use them with caution when handling large datasets as they may cause your script to timeout if your `MAX_EXECUTION_TIME` setting in PHP is not long enough.

- `Job::listJobs(array $args, String $query)`
    To use if you need to display many jobs like an overview in a dashboard. `$args` is an associative array with the following keys:
    - *page* `int`, default `1`
    - *limit* `int`, default `50`
    - *sort* `String`, provide the attribute name to sort by (e.g. `"date"`). Add a minus sign in front to flip the order. **Does not work if you use together with `$query`**
    - *date* `String` date in `Y-m-d` format, only list deliveries on that day
    - *type* `"delivery"` (default) or `"collection"`
    - *assign_to* `String` only show jobs driven by this driver
    - *status* `String` only show jobs of a certain status. Available are `"complete"`,`"completed_partial"`,`"failed"`
    - *do_number* `String` only show jobs of a certain DO number. Used to show reattempts (because reattempts will have the same DO number but different *date*)
    `$query` is a search term that lets you search across all other fields such as address.
- `Job::createJobs(array $jobs)`
    Bulk create an array of jobs. You can either pass an array of `Job` objects or an array of associative arrays representing attributes of `Job` objects.
- `Job::deleteJobs(array $jobs)`
    Bulk delete an array of jobs. You can either pass an array of `Job` objects or an array of associative arrays representing attributes of `Job` objects, although you only need the *id* field. The method will automatically strip the other attributes for you.

## Vehicles

Vehicles can also be created, retrieved, updated and deleted in a fashion similar to Jobs. The fully qualified class name is `Detrack\DetrackCore\Resource\Vehicle`.

### Creating vehicles

There are three keys to `Vehicle`s in the Detrack backend – `name`, which is the name of the driver the *organisation* sets, `detrack_id`, the id unique to the *driver*, and `id`, the id unique to the pairing of the *driver and the organisation*.

The *detrack_id* is the hash string unique to each Driver that can be seen when they open the Detrack Proof of Delivery App on their phone. To create a delivery, construct a `Vehicle` object and fill in the *detrack_id* and *driver* attributes:

```php
use Detrack\DetrackCore\Resource\Vehicle;

$vehicle = new Vehicle();
$vehicle->detrack_id = "SGVsbG8gV29ybGQ";
$vehicle->name = "Tom's Van";
$vehicle->save();
//or
$vehicle->create();
```

Similarly to `Job`s the `hydrate()`,`update()`,`delete()` functions are also available for `Vehicle` objects.

### Programmatically assigning jobs to vehicles

Use the `assignTo(Vehicle $vehicle)` method on `Job` objects to assign a job to your drivers. Continuing from the above examples:

```php
$tomsVehicle = new Vehicle();
$tomsVehicle->name = "Tom's Van";
$tomsVehicle->hydrate(); //optional, assignTo will do it for you anyway

$delivery->assignTo($tomsVehicle);
```

Thereafter somewhere else in your code, you can then call `getVehicle()` on the `Job` objects to retrieve the `Vehicle` that has been assigned to it.

```php
echo $delivery->getVehicle()->name;
//prints "Tom's Van"
```

# Contributing

We are open to contributions. If you feel something can be improved, feel free to open a pull request.

## Bug Reports & Feature Requests

Open an issue on GitHub, or send an email to [info@detrack.com](mailto:info@detrack.com) addressed to the Engineering team.

## Setting up the development environment

Clone this repository and install composer dev dependencies.
```sh
git clone https://github.com/detrack/detrack-core-php.git .
composer install
```

## Testing

Refer to the `.env.example` file and create your own `.env` to enter testing API Keys, DO numbers, Driver detrack_id etc.
The test will create sample jobs on your Detrack Dashboard, and on successful completion, delete them thereafter. (Consequently, an internet connection is required to run the test suites)
Run `phpunit` on the `tests` folder, either through the `vendor` folder or through a globally installed executable.

## Built With

* [GuzzleHttp](http://docs.guzzlephp.org/en/stable/) - The underlying HTTP library

## Authors

* **Chester Koh** - *Initial work* - [chesnutcase](https://github.com/chesnutcase)

## License

This project is licensed under the MIT License - see the [LICENSE.md](LICENSE.md) file for details
