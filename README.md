![Detrack logo](https://www.detrack.com/wp-content/uploads/2016/12/Logo_detrack.png)
# detrack-core-php

Official core library for PHP applications to interact with the [Detrack](https://www.detrack.com) API, built to be as simple to use as possible.

## Installation

Install the package via [composer](https://getcomposer.org):

```
composer require detrack/detrack-core
```
Composer will handle all the dependencies for you.

### Prerequisites

Your PHP installation must have either the GD or the ImageMagick libraries bundled.
Unless you installed Gentoo and compiled PHP from scratch, this should not be a cause of concern.

You must also have created a (free!) account on Detrack, and understand our basic workflow.

# Simple Usage Guide

In this short guide, we'll pretend that we're an e-shop who sells ice-cream, and use the Detrack service to track our deliveries.
We will use this library to implement the bare-minimum functionality for Detrack to function. (An advanced guide is available further below)

## The client object

First, you need to create an instance of `DetrackClient`. This is the main class that handles HTTP requests from your application to the Detrack API, and is needs to be attached to any child objects to provide them with the API Key. The API Key can be retrieved from the web control panel when you log in, and will not change unless you request for a new one.

```
use Detrack\DetrackCore\Client\DetrackClient;

$client = new DetrackClient($your_api_key);
```

You must recreate this client object in every function scope where you wish to interact with the Detrack API. (see below)

## Creating Deliveries

When your e-shop confirms payment from a customer, you need to send a request to us regarding the details of the delivery, so that we can automatically assign it to a free vehicle in your fleet. This is represented via the `Delivery` and `Item` objects.

`Delivery` represents a single order to be delivered to your customer.
`Item` represents an item from your shop within the order. While you do not need this for Detrack to function, it will show up in the Electronic Proof of Deliveries (POD) that you and your customer will get when the Delivery is marked as complete.

There are three attributes that you must give the Delivery object before you submit it, *date*, *do* (Delivery order number) and *address*.

So let's look at the different ways we can create deliveries:

### Via factories

```
use Detrack\DetrackCore\Factory\DeliveryFactory;

$factory = new DeliveryFactory($client); //remember to pass the client!
$delivery = $factory->createNew([
  "date"=>"2018-03-09",
  "do"=>"DO# 12345",
  "address"=>"Null Island",
  "instructions"=>"Tell recipient to come out and retrieve ice cream from van" //not required, but you can specify other fields that are documented on our API reference
  ]);
$delivery->save(); //submits the delivery to Detrack
```

Instead of passing the attributes straightaway in the `createNew` function, you can also first create a blank one, then modify the attributes later:

```
use Detrack\DetrackCore\Factory\DeliveryFactory;

$factory = new DeliveryFactory($client); //remember to pass the client!
$delivery = $factory->createNew();
$delivery->date = 2018-03-09";
$delivery->do = "DO# 12345";
$delivery->address = "Null Island";
$delivery->instructions = "Tell recipient to come out and retrieve ice cream from van"; //not required, but you can specify other fields that are documented on our API reference
$delivery->save(); //submits the delivery to Detrack
```

### Via Class Constructor

If you detest the idea of factories, you can create the Delivery object by yourself, but you **must** attach the client object manually by calling the `setClient()` method before you call save.

```
use Detrack\DetrackCore\Model\Delivery;

$delivery = new Delivery([
  "date"=>"2018-03-09",
  "do"=>"DO# 12345",
  "address"=>"Null Island",
  "instructions"=>"Tell recipient to come out and retrieve ice cream from van" //not required, but you can specify other fields that are documented on our API reference
]);

$delivery->setClient($client)->save(); //you can method chain!
```
Like before, you can also first pass no arguments into the constructor, then modify the attributes later:

```
use Detrack\DetrackCore\Model\Delivery;

$delivery = new Delivery();
$delivery->date = 2018-03-09";
$delivery->do = "DO# 12345";
$delivery->address = "Null Island";
$delivery->instructions = "Tell recipient to come out and retrieve ice cream from van"; //not required, but you can specify other fields that are documented on our API reference
$delivery->setClient($client)->save(); //submits the delivery to Detrack
```

Remember that you **must** attach the client object, and ensure that the required attributes *date*,*do* and *address* are set before you call `save()`.

Note that the `save()` function can be used for both creation and update of deliveries. (See below for example of updates)

And that's it! You've submitted your first delivery to Detrack. If you have your vehicles set up correctly on the Detrack Control Panel, your system will automatically assign deliveries to your drivers and you can start tracking them through their other apps.

## Adding items to deliveries

While what is documented in the previous section is the bare minimum required to get your application integrated with Detrack, you should add more information that is useful to both your staff and your customers. The next point we shall cover is adding items to your deliveries. These items will show up on receipts and Electronic PODs that you and will customers will receive.

The `Item` object has three base required attributes: *sku* (stock keeping unit (number)), *qty* (quantity) and *desc* description.

Creating the Item objects is similar to Deliveries, but you need not use factories since there's no client to attach:

```
use Detrack\DetrackCore\Model\Item;

$item = new Item([
  "sku"=>"IC 456",
  "qty"=>"5",
  "desc"=>"Strawberry flavoured ice-cream"
  ]);
```
Alternatively, like deliveries, you can pass an empty argument into the constructor, then set the attributes later:

```
use Detrack\DetrackCore\Model\Item;

$item = new Item();
$item->sku = "IC 456";
$item->qty = "5";
$item->desc = "Strawberry flavoured ice-cream";
```

Then, you need to add them to a delivery object by first accessing the `items` property and then calling the `add()` method:

```
$delivery->items->add($item);
$delivery->save() //sends the info to Detrack
```
And that's it: The `items` property is an instance of `Detrack\DetrackCore\Model\ItemCollection`, and contains methods like `push()`, `pop()` for you to manipulate the items attached to the delivery. Don't forget to call `save()` afterwards to commit the changes to the Detrack API.

# Advanced Usage Guide

This part contains miscellaneous bits and pieces of info should you require more fine control.

## Find deliveries

Deliveries stored in the Detrack database are identified by both their *do* and their scheduled delivery *date*. To get the identifier used to identify a unique delivery, call the `getIdentifier()` method on the `Delivery` object.

```
$identifier = $delivery->getIdentifier();
/*  returns [
 *    "do" => "DO# 12345",
 *    "date" => "2018-03-09",
 *  ]
 */
```

Afterwards, call the `findDelivery()` method on the `DetrackClient` object and supply the identifier:

```
$client->findDelivery($identifier);
```

This is usually used to retrieve updated information of a delivery.

## Update and delete

The `save()` and `delete()` functions work in the Object-Relation-Mapping style, and you can call them on any `Delivery` object:

```
// Update instructions
$delivery->instructions = "Change of plan, leave ice cream package in the mailbox instead";
$delivery->save();

// Customer no longer wants ice cream
$delivery->delete();
```
Upon deleting, the Delivery job will no longer show up on the Detrack Dashboard, and will no longer be tracked.

## Bulk operations

Bulk operations are available for find, save and delete. However, you should use them with caution when handling large datasets as they may cause your script to timeout if your MAX_EXECUTION_TIME setting in PHP is not long enough.

- `$client->bulkFindDeliveries($array)` to retrieve an array of deliveries. Pass an array of delivery identifiers.
- `$client->bulkSaveDeliveries($array)` to create/update many deliveries at once. Pass an array of delivery objects.
- `$client->findDeliveriesByDate(String $date)` to retrieve an array of deliveries scheduled for a date. Pass a date string in format YYYY-MM-DD.
- `$client->bulkDeleteDeliveries($array)` to delete multiple deliveries at once. Pass an array of delivery identifiers.
- `$client->deleteDeliveriesByDate(String $date)` to delete all deliveries scheduled for a certain date. Pass a date string in format YYYY-MM-DD.

## Vehicles

Currently, Vehicles in the API are read-only, so the only things you can do are retrieve driver information and assign drivers to deliveries.

- `$client->findVehicle(String $name)` to retrieve details about a driver. Will be casted into a `Detrack\DetrackCore\Model\Vehicle`
- `$delivery->assignTo(String $vehicleName | Vehicle $vehicle)` if you want to assign a delivery to a specific driver.


Explain how to run the automated tests for this system


# Contributing

We are open to contributions. If you feel something can be improved, feel free to open a pull request.

## Testing

(to be filled up soon)

## Built With

* [GuzzleHttp](http://www.dropwizard.io/1.0.2/docs/) - The underlying HTTP library

## Authors

* **Chester Koh** - *Initial work* - [chesnutcase](https://github.com/chesnutcase)

## License

This project is licensed under the MIT License - see the [LICENSE.md](LICENSE.md) file for details
