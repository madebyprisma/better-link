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
	 * @property string $Extension
	 * @property string $Queries
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
			"Hash" => "Varchar(512)",
			"Extension" => "Varchar(512)",
			"Queries" => "Varchar(512)"
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

		private function getFormattedQueries() {
			$output = "";

			$arr = explode(",", $this->Queries);
			$index = 0;
			foreach ($arr as $query) {
				$str = trim($query);
				if (strlen($str) == 0) continue;
				$output .= "?" . $str . ($index < count($arr) - 1 ? "&" : "");
				$index++;
			}

			return $output;
		}

		public static function addFields(string $name, BetterLink $link, FieldList &$fields, string $label = "Link Options", string $tabName = "Root.Main") {
			$fields->removeByName($name . "ID");
			$fields->addFieldsToTab($tabName, [
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
					TextField::create($name . "-_1_-Extension", "Segment Extension"),
					TextField::create($name . "-_1_-Queries", "Queries ({Name}={Value},{etc.})"),
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

		public function getAlt() {
			$fields = BetterLink::getFields();
			$alt = "";

			if (!key_exists($this->Type, $fields)) {
				return "";
			}

			$prop =  $this->obj(is_array($fields[$this->Type]) && key_exists("name", $fields[$this->Type]) ? $fields[$this->Type]["name"] : $fields[$this->Type]);

			if (method_exists($prop, "getTitle")) $alt = $prop->getTitle();
			else if (method_exists($prop, "Title")) $alt = $prop->Title();
			else if (property_exists($prop, "Title")) $alt = $prop->Title;
			else if (property_exists($prop, "Label")) $alt = $prop->Label;
			else if (method_exists($prop, "getLabel")) $alt = $prop->getLabel();
			else if (method_exists($prop, "Label")) $alt = $prop->Lable();

			return $alt;
		}

		public function getTitle() {
			return $this->Label;
		}

		public function Link() {
			$fields = BetterLink::getFields();
			$link = null;

			if ($this->Type === null) return null;

			if ($this->Type >= count($fields)) {
				throw "Link Type is out of bounds, " . $this->Type . " given, expect up to " . count($fields);
			}
			else {
				$prop =  $this->obj(is_array($fields[$this->Type]) && key_exists("name", $fields[$this->Type]) ? $fields[$this->Type]["name"] : $fields[$this->Type]);

				if (method_exists($prop, "getLink")) $link = $prop->getLink();
				else if (method_exists($prop, "Link")) $link = $prop->Link();
				else if (property_exists($prop, "Link")) $link = $prop->Link;
				else if (property_exists($prop, "URL")) $link = $prop->URL;
				else if (method_exists($prop, "forTemplate")) $link = $prop;
				
				return $link == null ? $link : $link . (substr($this->Extension, 0, 1) != "/" ? $this->Extension : str_replace("/", $this->Extension, 1)) . $this->getFormattedQueries() . ($this->Hash ? "#" . urlencode($this->Hash) : "");
			}
		}
	}
}