# SilverStripe Better Link

## About

Adds a new DataObject to allow for more advanced link behaviour

## Installation

```bash
composer require madebyprisma/better-link
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
		
		$fields->addFieldsToTab("Root.Main", [
			BetterLinkField::create("YourLink", "Your Link")
		]);

		return $fields;
	}
}
```

## Upgrading

This package was previously `adair-creative/better-link` and is no longer supported.

This new package is not compatible with the old one, so you will need to update your code, and rebuild the links.

Versions are denoted by the SilverStripe version they are compatible with. (i.e. 4.12.x is compatible with SilverStripe 4.12)