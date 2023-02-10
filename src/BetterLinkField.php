<?php

namespace MadeByPrisma\BetterLink;

use SilverStripe\CMS\Model\SiteTree;
use SilverStripe\Forms\CompositeField;
use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\TextareaField;
use SilverStripe\Forms\TextField;
use SilverStripe\Forms\ToggleCompositeField;
use SilverStripe\Forms\TreeDropdownField;

class BetterLinkField extends CompositeField {
	private static function getSelectJS(string $name) {
		return "jQuery(\".better-link-" . $name . "\").addClass(\"hidden\");jQuery(\".better-link-" . $name . ".field-\" + this.value).removeClass(\"hidden\")";
	}

	public function __construct(string $name, ?string $title = null) {
		$children = [
			new TextField("{$name}:Label", "Label"),
			
			(new DropdownField("{$name}:Type", "Type", [
				"Page" => "Page",
				"URL" => "URL"
			]))
			->setAttribute("onchange", self::getSelectJS($name)),

			(new TextField("{$name}:URL", "URL"))
			->addExtraClass("better-link-" . $name . " field-URL"),

			(new TreeDropdownField("{$name}:PageID", "Page", SiteTree::class))
			->addExtraClass("better-link-" . $name . " field-Page"),
			
			new ToggleCompositeField("{$name}:Advanced", "Advanced", [
				new TextField("{$name}:Hash", "Hash"),
				new TextareaField("{$name}:Queries", "Queries")
			])
		];

		parent::__construct($children);
		$this->setName($name);
		$this->setTitle($title);
	}
}