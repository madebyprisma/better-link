<?php

namespace MadeByPrisma\BetterLink;

use SilverStripe\CMS\Model\SiteTree;
use SilverStripe\Forms\CompositeField;
use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\HeaderField;
use SilverStripe\Forms\LabelField;
use SilverStripe\Forms\TextareaField;
use SilverStripe\Forms\TextField;
use SilverStripe\Forms\ToggleCompositeField;
use SilverStripe\Forms\TreeDropdownField;
use UncleCheese\DisplayLogic\Forms\Wrapper;

class BetterLinkField extends CompositeField {
	public function __construct(string $name, ?string $title = null) {
		$children = [
			new TextField("{$name}-_1_-Label", "Label"),
			
			(new DropdownField("{$name}-_1_-Type", "Type", [
				"Page" => "Page",
				"URL" => "URL"
			])),

			$urlField = new TextField("{$name}-_1_-URL", "URL"),

			$pageField = new Wrapper(new TreeDropdownField("{$name}-_1_-PageID", "Page", SiteTree::class)),
			
			new ToggleCompositeField("{$name}-_1_-Advanced", "Advanced", [
				new TextField("{$name}-_1_-Hash", "Hash"),
				new TextareaField("{$name}-_1_-Queries", "Queries"),
				new DropdownField("{$name}-_1_-Target", "Target", [
					"" => "Same window",
					"_blank" => "New window"
				])
			]),
		];

		parent::__construct($children);
		$this->setTitle($title ?? $name);
		$this->setName($name);

		$urlField->hideUnless("{$name}-_1_-Type")->isEqualTo("URL");
		$pageField->hideUnless("{$name}-_1_-Type")->isEqualTo("Page");
	}
}