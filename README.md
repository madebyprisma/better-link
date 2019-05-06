# SilverStripe Better Link

## About

Adds a new DataObject to allow for more advanced link behaviour

## Installation

```bash
composer require adair-creative/better-link
```

## Guide

### Basic

```php
<?php

use AdairCreative\BetterLink;

class YourPage extends Page {
	private static $has_one = [
		"YourLink" => BetterLink::class
	];

	public function getCMSFields() {
		$fields = parent::getCMSFields();
		
		BetterLink::addFields($fields);

		return $fields;
	}
}
```

### Extending

config.yml
```yml
AdairCreative\BetterLink:
    fields:
        - name: "ProductToUse"
          label: "Product" # Optional
          class: "SiteProduct"
```

BetterLinkExtension.php
```php
<?php

use SilverStripe\ORM\DataExtension;

class BetterLinkExtension extends DataExtension {
	private static $has_one = [
		"ProductToUse" => SiteProduct::class
	];
}
```