<?php

namespace AdairCreative {
	use SilverStripe\ORM\DataObject;
	use SilverStripe\CMS\Model\SiteTree;
	use SilverStripe\Forms\FieldList;
	use SilverStripe\Core\Config\Config;
    use SilverStripe\Forms\HeaderField;
    use SilverStripe\Forms\TextField;
    use SilverStripe\Forms\DropdownField;
    use SilverStripe\Core\Config\Configurable;
    use SilverStripe\Forms\TreeDropdownField;
    use SilverStripe\Forms\ToggleCompositeField;

	/**
	 * @property string $Label
	 * @property int $Type
	 * @property string $URL
	 * @property string $Hash
	 * 
	 * @method SiteTree Page()
	 */
	class BetterLink extends DataObject {
		use Configurable;

		private static $table_name = "ACG_BetterLink";
		private static $db = [
			"Label" => "Varchar(512)",
			"Type" => "Int",
			"URL" => "Varchar(512)",
			"Hash" => "Varchar(512)"
		];

		private static $has_one = [
			"Page" => SiteTree::class
		];

		private static function getConfig_fields() {
			return Config::inst()->exists(BetterLink::class, 'fields') ? Config::inst()->get(BetterLink::class, "fields") : [];
		}

		private static function getFields() {
			return array_merge(["URL", "Page"], BetterLink::getConfig_fields());
		}

		private static function getSelectJS(string $name) {
			return "jQuery(\".better-link-" . $name . "\").addClass(\"hidden\");jQuery(\".better-link-" . $name . ".field-\" + this.value).removeClass(\"hidden\")";
		}

		public static function addFields(string $name, BetterLink $link, FieldList &$fields, string $label = "Link Options") {
			$fields->removeByName($name . "ID");
			$fields->addFieldsToTab("Root.Main", [
				HeaderField::create("", $label),
				TextField::create($name . "-_1_-Label", "Label"),
				DropdownField::create($name . "-_1_-Type", "Type")
					->setSource(BetterLink::getFields())
					->setAttribute("onchange", BetterLink::getSelectJS($name)),

				TextField::create($name . "-_1_-URL", "URL")
					->addExtraClass($link->Type != 0 ? "hidden" : "")
					->addExtraClass("better-link-" . $name . " field-0"),

				TreeDropdownField::create($name . "-_1_-PageID", "Page", SiteTree::class)
					->setEmptyString("Select One")
					->addExtraClass($link->Type != 1 ? "hidden" : "")
					->addExtraClass("better-link-" . $name . " field-1"),
				
				ToggleCompositeField::create("URLExtras", "URL Extras", [
					TextField::create($name . "-_1_-Hash", "Hash")
				])
			]);

			$index = 2;
			foreach (BetterLink::getConfig_fields() as $field) {
				if (!key_exists("name", $field) || !key_exists("class", $field)) continue;

				$fields->addFieldToTab("Root.Main", DropdownField::create($name . "-_1_-" . $field["name"] . "ID", (key_exists("label", $field) ? $field["label"] : $field["name"]))
					->setSource(($field["class"])::get()->map())
					->setEmptyString("Select One")
					->addExtraClass($link->Type != $index ? "hidden" : "")
					->addExtraClass("better-link-" . $field["name"] . " field-" . $index)
				);
				$index++;
			}
		}

		public function getTitle() {
			return $this->Label;
		}

		public function Link() {
			$fields = BetterLink::getFields();
			$link = null;

			if ($this->Type == null) return null;

			if ($this->Type >= count($fields)) {
				throw "Link Type is out of bounds, " . $this->Type . " given, expect up to " . count($fields);
			}
			else {
				$prop =  $this->obj(is_array($fields[$this->Type]) && key_exists("name", $fields[$this->Type]) ? $fields[$this->Type]["name"] : $fields[$this->Type]);

				if (method_exists($prop, "getLink")) $link = $prop->getLink();
				if (method_exists($prop, "Link")) $link = $prop->Link();
				if (property_exists($prop, "Link")) $link = $prop->Link;
				if (property_exists($prop, "URL")) $link = $prop->URL;
				if (method_exists($prop, "forTemplate")) $link = $prop;
				
				return $link == null ? $link : $link . "#" . urlencode($this->Hash);
			}
		}
	}
}