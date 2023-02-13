<?php

namespace MadeByPrisma\BetterLink;

use SilverStripe\Assets\File;
use SilverStripe\ORM\DataObject;
use SilverStripe\CMS\Model\SiteTree;
use SilverStripe\Forms\FieldList;
use SilverStripe\Core\Config\Config;
use SilverStripe\Forms\HeaderField;
use SilverStripe\Forms\TextField;
use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\TreeDropdownField;
use SilverStripe\Forms\ToggleCompositeField;
use SilverStripe\Versioned\Versioned;

/**
 * @property string $Label
 * @property string $Type
 * @property string $URL
 * @property string $Hash
 * @property string $Queries
 * @property int $SiteTreeID
 * 
 * @method SiteTree Page()
 */
class BetterLink extends DataObject {
	private static $extensions = [
		Versioned::class
	];

	private static $table_name = "MadeByPrisma_BetterLink";
	private static $db = [
		"Label" => "Varchar(256)",
		"Type" => "Enum('Page, URL', 'Page')",
		"URL" => "Varchar(256)",
		"Hash" => "Varchar(256)",
		"Queries" => "Varchar(512)"
	];

	private static $has_one = [
		"Page" => SiteTree::class
	];

	private function getFormattedQueries() {
		$output = [];
		$queries = explode(",", $this->Queries ?: "");

		foreach ($queries as $query) {
			$exploded = explode("=", trim($query));
			
			if (count($exploded) != 2) continue;

			$output[] = $exploded[0] . "=" . urlencode($exploded[1]);
		}

		return count($output) > 0 ? "?" . implode("&", $output) : "";
	}

	private function getFormattedHash() {
		return $this->Hash ? "#" . $this->Hash : "";
	}

	public function getTitle() {
		return $this->Label;
	}

	public function Link() {
		$path = "";

		switch ($this->Type) {
			case "Page":
				$path = $this->Page()->Link();
				break;
			case "URL":
				$path = $this->URL;
				break;
			default:
				return "";
		}

		return $path . $this->getFormattedQueries() . $this->getFormattedHash();
	}

	public function forTemplate() {
		return $this->renderWith("MadeByPrisma\\BetterLink\\DefaultLink");
	}
}