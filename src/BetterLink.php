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

	/**
	 * @property string $Label
	 * @property int $Type
	 * @property string $URL
	 * 
	 * @method SiteTree Page()
	 */
	class BetterLink extends DataObject {
		use Configurable;

		private static $table_name = "ACG_BetterLink";
		private static $db = [
			"Label" => "Varchar(512)",
			"Type" => "Int",
			"URL" => "Varchar(512)"
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

				DropdownField::create($name . "-_1_-PageID", "Page")
					->setSource(SiteTree::get()->map())
					->setEmptyString("Select One")
					->addExtraClass($link->Type != 1 ? "hidden" : "")
					->addExtraClass("better-link-" . $name . " field-1")
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

		public function Link() {
			$fields = BetterLink::getFields();

			if ($this->Type >= count($fields)) {
				throw "Link Type is out of bounds, " . $this->Type . " given, expect up to " . count($fields);
			}
			else {
				if (!key_exists("name", $fields[$this->Type])) return null;

				$prop = $this->obj($fields[$this->Type]["name"]);

				if (method_exists($prop, "getLink")) return $prop->getLink();
				if (method_exists($prop, "Link")) return $prop->Link();
				if (property_exists($prop, "Link")) return $prop->Link;
				if (property_exists($prop, "URL")) return $prop->URL;
				if (method_exists($prop, "forTemplate")) return $prop;
				return null;
			}
		}
	}
}