# Estasi Validator

It is a set of necessary immutable validators. Provides a simple data validation chain 
mechanism that allows you to apply multiple validators in a given order.

## Installation
To install with a composer:
```
composer require estasi/validator
```

## Requirements
- PHP 7.4 or newer
- ext-mbstring
- ext-intl
- [Data Structures](https://github.com/php-ds/polyfill): 
    `composer require php-ds/php-ds`
    <br><small><i>Polyfill is installed with the estasi/validator package.</i></small>

## Usage

### Basic usage
```php
<?php

declare(strict_types=1);

use Estasi\Validator\Email;

$email = 'john@doe.com';
$validator = new Email(Email::ALLOW_UNICODE);
if ($validator->isValid($email)) {
    // your code is here
} else {
    // print "Email "$email" is not correct!"
    echo $validator->getLastErrorMessage();
}
```

### Custom message
```php
<?php

declare(strict_types=1);

use Estasi\Validator\Email;

$email = 'john@doe.com';
$validator = new Email(Email::ALLOW_UNICODE, [Email::E_INVALID_EMAIL => 'Custom error message.']);
if ($validator->isValid($email)) {
    // your code is here
} else {
    // print "Custom error message."
    echo $validator->getLastErrorMessage();
}
```
or
```php
<?php

declare(strict_types=1);

use Estasi\Validator\Email;

$email = 'john@doe.com';
$validator = new Email(Email::ALLOW_UNICODE);
if ($validator->isValid($email)) {
    // your code is here
} else {
    if ($validator->isLastError(Email::E_INVALID_EMAIL)) {
        echo "Custom error message.";
        // or your other code depending on the error
    }
    //...
}
```

### Identical
Only the Identical validator can accept the second parameter in the isValid(), notValid() and __invoke() methods
#### Simple comparison
```php
<?php

declare(strict_types=1);

use Estasi\Validator\Identical;

$token = 'string';
$value = 'string';

$validator = new Identical($token, Identical::STRICT_IDENTITY_VERIFICATION);
if ($validator->isValid($value)) {
    // your code is here
}
```
or 
```php
<?php

declare(strict_types=1);

use Estasi\Validator\Identical;

$context = 'string';
$value = 'string';

$validator = new Identical(null, Identical::STRICT_IDENTITY_VERIFICATION);
if ($validator->isValid($value, $context)) {
    // your code is here
}
```
#### Comparison with the value in the array
```php
<?php

declare(strict_types=1);

use Estasi\Validator\Identical;

$token = 'email';
$value = 'john@doe.com';
$context = ['names' => ['firstname' => 'John', 'lastname' => 'Doe'], 'email' => 'john@doe.com'];

$validator = new Identical($token, Identical::STRICT_IDENTITY_VERIFICATION);
if ($validator->isValid($value, $context)) {
    // your code is here
}
```
You can also check the value of an unrestricted nested context if the context is an array. 
The nesting separator will thus be the symbol ".".
```php
<?php

declare(strict_types=1);

use Estasi\Validator\Identical;

$token = 'names.lastname';
$value = 'Doe';
$context = ['names' => ['firstname' => 'John', 'lastname' => 'Doe'], 'email' => 'john@doe.com'];

$validator = new Identical($token, Identical::STRICT_IDENTITY_VERIFICATION);
if ($validator->isValid($value, $context)) {
    // your code is here
}
```

### Chain
Here are two validator tasks in the chain: explicitly (by class declaration) and via the factory (array)
```php
<?php

declare(strict_types=1);

use Estasi\Validator\{Chain,Identical,Regex};

$datum = [
    'password' => [
        'original' => 'password_25',
        'confirm'  => 'password_25'
    ]
];

$chain = new Chain();
$chain = $chain->attach(
                   new Regex('[A-Za-z0-9_]{8,12}', Regex::OFFSET_ZERO, [Regex::OPT_ERROR_VALUE_OBSCURED => true]),
                   Chain::WITH_BREAK_ON_FAILURE
               )
               ->attach(
                   [
                       Chain::VALIDATOR_NAME => 'identical',
                       Chain::VALIDATOR_OPTIONS => [
                           Identical::OPT_TOKEN => 'password.original',
                           Identical::OPT_ERROR_VALUE_OBSCURED => true
                       ]
                   ],
                   Chain::WITH_BREAK_ON_FAILURE
               );
if($chain->isValid($datum['password']['original'], $datum)) {
    // your code is here
}
```

## License
All contents of this package are licensed under the [BSD-3-Clause license](https://github.com/estasi/validator/blob/master/LICENSE.md).