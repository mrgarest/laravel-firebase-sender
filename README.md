# Laravel Firebase Sender

Laravel library for sending notifications with Firebase Cloud Messaging (FCM).
 
❗️ **This library only works with the new FCM HTTP v1 API** ❗️

## Installation

You can install the package via composer:

```
composer require mrgarest/laravel-firebase-sender
```

## Configuration

After installing the package, you will need to publish the configuration file `firebase-sender.php`

```
php artisan vendor:publish --tag=firebase-sender-config
```

After publishing the configuration file, you need to open it and add the Service account data from the Firebase console. 

*If you don't know how to get a Service account, here is a [video from YouTube](#https://www.youtube.com/watch?v=aeBiLIw2KnY).*

## Usage

An example of sending a simple group notification.

```php
$firebaseSender = new FirebaseSender('MY_SERVICE_ACCOUNT_NAME');
$firebaseSender->setTopic('MY_TOPIC');
$firebaseSender->setTitle('Hello world');
$firebaseSender->setBody('This is my first message using Laravel Firebase Sender');
$firebaseSender->send();
```

To send a notification to a specific device, use `setTokenDevices(string $token)` instead of `setTopic(string $topic)`.

### Multilingual notification

To send localized notifications, you need to use the key from your app's localization file.

```php
$firebaseSender = new FirebaseSender('MY_SERVICE_ACCOUNT_NAME');
$firebaseSender->setTopic('MY_TOPIC');
$firebaseSender->setTitleLocKey('hello_world');
$firebaseSender->setBodyLocKey('first_message', ['Laravel Firebase Sender']);
$firebaseSender->send();
```

An example of a localization file in an Android app:

```xml
<string name="hello_world">Hello world</string>
<string name="first_message">This is my first message using %1$s</string>
```

`%1$s` — will be replaced by the first argument from the array.

### Notification log

If you want to use the log of sent notifications, you will also need to publish the migration file and perform the migration.

```
php artisan vendor:publish --tag=firebase-sender-migrations
```
```
php artisan make:migration
```

To add notification information to the log, you'll need to use an additional method before sending notifications:

```php
$firebaseSender->setDatabaseLog();
```

This method can also take an additional value of type string, which can be used, for example, to check whether a notification was sent with a specific argument to avoid duplicate notifications.

```php
$firebaseSender->setDatabaseLog('TEST_VALUE');
```

To check if there is an additional value in the log, you can use this method:

```php
$isValue = FirebaseSenderLog::isValue('TEST_VALUE', 'MY_TOPIC');
```

You can also check the additional value by date range:

```php
$isValue = FirebaseSenderLog::isValueByTimeRange(Carbon::now()->subMinutes(30), 'TEST_VALUE', 'MY_TOPIC');
```

You can also check if notifications were sent within a specific time range:

```php
$isValue = FirebaseSenderLog::isToByTimeRange(Carbon::now()->subMinutes(30), 'MY_TOPIC');
```