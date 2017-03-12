<?php namespace ProcessWire;
/*
 * WireManga - a module for creating a manga reader
 *
 */

class WireMangaSetup extends Wire {
	/**
	 * Install Module
	 */
	public function install() {
		$this->setArrays();
		$this->createTemplates();
		$this->createPages();
		$this->createFields();

		$initFile = $this->config->paths->siteModules . "WireManga/Hooks/init.php";
		if(!file_exists($initFile)) {
			copy($initFile, $this->config->paths->site);
		}
		// Change title field in the wm_chapters template context
		$t = $this->wire("templates")->get("name=wm_chapters");
		$f = $t->fieldgroup->getField('title', true);
		$f->label = "Chapter Number";
		$this->wire("fields")->saveFieldgroupContext($f, $t->fieldgroup);

		// Change field settings for the wm_manga_single template context
		$t = $this->wire("templates")->get("name=wm_manga_single");
		$f = $t->fieldgroup->getField('wm_description', true);
		$f->columnWidth = "70%";
		$this->wire("fields")->saveFieldgroupContext($f, $t->fieldgroup);
		$f = $t->fieldgroup->getField('wm_cover', true);
		$f->columnWidth = "30%";
		$this->wire("fields")->saveFieldgroupContext($f, $t->fieldgroup);
	}


	/**
	 * Uninstall Module
	 */
	public function uninstall() {
		$this->setArrays();
		$this->deletePages();
		$this->deleteFields();
		$this->deleteTemplates();
	}

	protected $templates_;
	protected $pages_;
	protected $fields_;

	public function setArrays() {

		$this->pages_ = [
			["title" => "Manga",   "name" => "manga"  , "template" => "wm_manga"   , "path" => "/"],
			["title" => "Authors", "name" => "authors", "template" => "wm_taxonomy", "path" => "/"],
			["title" => "Artists", "name" => "artists", "template" => "wm_taxonomy", "path" => "/"],
			["title" => "Genres",  "name" => "genres" , "template" => "wm_taxonomy", "path" => "/"],
			["title" => "Types",   "name" => "type"   , "template" => "wm_taxonomy", "path" => "/"],
			["title" => "Manga Status"     , "name" => "manga-status", "template" => "wm_taxonomy", "path" => "/"],
			["title" => "Scanlation Status", "name" => "scan-status" , "template" => "wm_taxonomy", "path" => "/"],
			["title" => "Ajax"             , "name" => "ajax"        , "template" => "wm_ajax"    , "path" => "/"],
		];
		
		$this->fields_ = [
			["name" => "wm_alt_titles"    , "type" => "Text"    , "add_to" => "wm_manga_single", "label" => "Alternative Titles"],
			["name" => "wm_description"   , "type" => "Textarea", "add_to" => "wm_manga_single", "label" => "Description"],
			["name" => "wm_cover"         , "type" => "Image"   , "add_to" => "wm_manga_single", "label" => "Cover Image", "required" => true, "ext" => "gif jpg jpeg png", "maxFiles" => 1, "descRows" => 0],
			["name" => "wm_authors"       , "type" => "Page"    , "add_to" => "wm_manga_single", "label" => "Author", "required" => true, "parent" => "authors", "template" => "wm_terms", "addable" => 1, "inputfield" => "InputfieldAsmSelect"],
			["name" => "wm_artists"       , "type" => "Page"    , "add_to" => "wm_manga_single", "label" => "Artist", "parent" => "artists", "template" => "wm_terms", "addable" => 1, "inputfield" => "InputfieldAsmSelect"],
			["name" => "wm_genres"        , "type" => "Page"    , "add_to" => "wm_manga_single", "label" => "Genre", "parent" => "genres" , "template" => "wm_terms", "addable" => 1, "inputfield" => "InputfieldAsmSelect"],
			["name" => "wm_type"          , "type" => "Page"    , "add_to" => "wm_manga_single", "label" => "Type", "required" => true, "parent" => "type"   , "template" => "wm_terms", "addable" => 1, "inputfield" => "InputfieldAsmSelect"],
			["name" => "wm_manga_status"  , "type" => "Page"    , "add_to" => "wm_manga_single", "label" => "Manga Status", "required" => true, "parent" => "manga-status", "template" => "wm_terms" , "addable" => 1, "inputfield" => "InputfieldAsmSelect"],
			["name" => "wm_scan_status"   , "type" => "Page"    , "add_to" => "wm_manga_single", "label" => "Scanlation Status", "required" => true, "parent" => "scan-status", "template" => "wm_terms", "addable" => 1, "inputfield" => "InputfieldAsmSelect"],
			["name" => "wm_sort_chapters" , "type" => "Options" , "add_to" => "wm_manga_single", "label" => "Chapter Order", "setOptionsString" => "Volumes and chapters ascending\nVolumes descending and chapters ascending\nVolumes and chapters descending"],
			["name" => "wm_comments"      , "type" => "Comments", "add_to" => "wm_manga_single", "label" => "Comments"],
			["name" => "wm_chapter_name"  , "type" => "Text"    , "add_to" => "wm_chapters"    , "label" => "Chapter Name"],
			["name" => "wm_chapter_volume", "type" => "Text"    , "add_to" => "wm_chapters"    , "label" => "Manga Volume"],
			["name" => "wm_chapter_images", "type" => "Image"   , "add_to" => "wm_chapters"    , "label" => "Chapter Images", "required" => true, "ext" => "gif jpg jpeg png", "maxFiles" => 0, "descRows" => 0],
		];

		$this->templates_ = [
			["name" => "wm_manga"],
			["name" => "wm_manga_single"],
			["name" => "wm_chapters", "urlSegments" => 1],
			["name" => "wm_taxonomy"],
			["name" => "wm_terms"],
			["name" => "wm_ajax"],
			["name" => "wm_list"],
		];
	}

	protected function copyFiles($src, $dest) {
		$files = scandir($src);
		@mkdir($dest);
		foreach($files as $file) {
			if($file !== "." && $file !== ".."){
				if(is_dir("$src/$file")){
					$this->copyFiles("$src/$file", "$dest/$file");
				} else {
					copy("$src/$file", "$dest/$file");
				}
			}
		}
	}

	/** 
	 * Create templates when module is installed
	 */
	protected function createTemplates() {
		// loop the templates array and create the templates
		foreach($this->templates_ as $tpl) {
			if(!$this->wire("templates")->get("name={$tpl["name"]}")->id) {
				$fg = new Fieldgroup();
				$fg->name = $tpl["name"];
				$fg->add("title");
				$fg->save();
				$t = new Template();
				$t->name = $tpl["name"];
				$t->fieldgroup = $fg;
				$t->save();
				if(isset($tpl["urlSegments"])) {
					$t->urlSegments = $tpl["urlSegments"];
				}
				$t->save();
				$tpl_id = $tpl["name"] . "_id";
				$$tpl_id = $t->id;
			}
		}
		
		// Create templates relationships
		$t = $this->wire("templates")->get("name=wm_manga");
		$t->childTemplates = array($wm_manga_single_id);
		$t->save();

		$t = $this->wire("templates")->get("name=wm_manga_single");
		$t->parentTemplates = array($wm_manga_id);
		$t->childTemplates = array($wm_chapters_id);
		$t->save();

		$t = $this->wire("templates")->get("name=wm_chapters");
		$t->parentTemplates = array($wm_manga_single_id);
		$t->save();

		$t = $this->wire("templates")->get("name=wm_taxonomy");
		$t->childTemplates = array($wm_terms_id);
		$t->save();

		$t = $this->wire("templates")->get("name=wm_terms");
		$t->parentTemplates = array($wm_taxonomy_id);
		$t->save();
	}

	/** 
	 * Create fields when module is installed
	 */
	protected function createFields() {
		foreach($this->fields_ as $field) {
			$template_id = $this->wire("templates")->get("name={$field["add_to"]}")->id;

			if(!$this->wire("fields")->get("name={$field["name"]}")->id) {
				if($field["type"] === "Repeater") {
					$this->createRepeater($field["name"], $field["fields"], $field["label"], $template_id, null);
					continue;
				}

				$f = new Field();
				$f->type = $this->modules->get("Fieldtype{$field["type"]}");
				$f->name = $field["name"];
				$f->label = $field["label"];
				if(isset($field["required"]) && $field["required"]) {
					$f->required = 1;
				}
				$f->save();
				if(isset($field["notes"])) {
					$f->notes = $field["notes"];
				}
				if($field["type"] == "Image") {
					$f->extensions = $field["ext"];
					$f->maxFiles = $field["maxFiles"];
					$f->descriptionRows = $field["descRows"];
					$f->outputFormat = 1;
				}
				if(isset($field["parent"])) {
				}
				if($field["type"] == "Page") {
					$p = $this->wire("pages")->get("template=wm_taxonomy, name={$field["parent"]}");
					$f->parent_id = $p->id;
					$t = $this->wire("templates")->get("name={$field["template"]}");
					$f->template_id = $t->id;
					$f->usePageEdit = 1;
					$f->labelFieldName = "title";
					$f->inputfield = $field["inputfield"];
					$f->addable = $field["addable"];
				}
				if($field["name"] == "wm_description") {
					$f->inputfieldClass = "InputfieldCKEditor";
				}
				if($field["name"] == "wm_views") {
					$f->collapsed = 4;
				}
				if(isset($field["default"])) {
					$f->required = 1;
					$f->defaultValue = $field["default"];
				}
				if(isset($field["setOptionsString"])) {
					$manager = new SelectableOptionManager();
					$manager->setOptionsString($f, $field["setOptionsString"], false);
					$f->save();
				}
				$f->save();
				if($template_id) {
					$t = $this->wire("templates")->get($template_id);
					$t->fieldgroup->add($f);
					$t->fieldgroup->save();
				}
			}
		}
	}

	/**
	 * Creates a repeater field with associated fieldgroup, template, and page
	 *
	 * @param string $repeaterName The name of your repeater field
	 * @param string $repeaterFields List of field names to add to the repeater, separated by spaces
	 * @param string $repeaterLabel The label for your repeater
	 * @param string $template_id ID of template used for pages created/selected by this field
	 * @param string $repeaterTags Tags for the repeater field
	 * @return Returns the new Repeater field
	 *
	 */
	public function createRepeater($repeaterName, $repeaterFields, $repeaterLabel, $template_id, $repeaterTags) {
		$fieldsArray = explode(' ', $repeaterFields);
		
		$f = new Field();
		$f->type = $this->modules->get("FieldtypeRepeater");
		$f->name = $repeaterName;
		$f->label = $repeaterLabel;
		$f->tags = $repeaterTags;
		$f->repeaterReadyItems = 3;
		
		//Create fieldgroup
		$repeaterFg = new Fieldgroup();
		$repeaterFg->name = "repeater_$repeaterName";
		
		//Add fields to fieldgroup
		foreach($fieldsArray as $field) {
			$repeaterFg->append($this->fields->get($field));
		}
		
		$repeaterFg->save();
		
		//Create template
		$repeaterT = new Template();
		$repeaterT->name = "repeater_$repeaterName";
		$repeaterT->flags = 8;
		$repeaterT->noChildren = 1;
		$repeaterT->noParents = 1;
		$repeaterT->noGlobal = 1;
		$repeaterT->slashUrls = 1;
		$repeaterT->fieldgroup = $repeaterFg;
		
		$repeaterT->save();
		
		//Setup page for the repeater - Very important
		$repeaterPage = "for-field-{$f->id}";
		$f->parent_id = $this->pages->get("name=$repeaterPage")->id;
		$f->template_id = $repeaterT->id;
		$f->repeaterReadyItems = 3;
		
		//Now, add the fields directly to the repeater field
		foreach($fieldsArray as $field) {
			$f->repeaterFields = $this->fields->get($field);
		}
		
		$f->save();
		if($template_id) {
			$t = $this->wire("templates")->get($template_id);
			$t->fieldgroup->add($f);
			$t->fieldgroup->save();
		}
	}

	/**
	 * Create pages when module is installed
	 */
	protected function createPages() {
		foreach($this->pages_ as $page_) {
			$parent_path = $page_["path"];
			$p = $this->wire("pages")->get("name={$page_["name"]}");
			if(!$p->id) {
				$p = new Page();
				$p->template = $page_["template"];
				$p->parent = $this->wire("pages")->get("path={$parent_path}");
				$p->title = $page_["title"];
				$p->name = $page_["name"];
				$p->save();
			}
		}
	}

	/**
	 * Delete pages when module is uninstalled
	 */
	protected function deletePages() {
		foreach($this->pages_ as $page_) {
			$page_path = $page_["path"] . $page_["name"] . "/";
			$p = $this->wire("pages")->get("path={$page_path}");
			if($p->id && $p->name != "admin") {
				$this->wire("pages")->delete($p, true);
			}
		}
	}

	/**
	 * Delete fields when module is uninstalled
	 */
	protected function deleteFields() {
		foreach($this->fields_ as $field) {
			$f = $this->wire("fields")->get($field["name"]);
			if($f->id){
				$fieldgroups = $f->getFieldgroups();
				foreach($fieldgroups as $fg) {
					$fg->remove($f);
					$fg->save();
				}
				if($f->type->name === "FieldtypeRepeater") {
					$repeater_fg = $this->wire("fieldgroups")->get("name=repeater_{$field["name"]}");
					if($repeater_fg->id){
						$repeater_fg->remove($field["fields"]);
						$repeater_fg->save();
					}

					$repeater_tpl = $this->wire("templates")->get("name=repeater_{$field["name"]}");
					if($repeater_tpl->id){
						$repeater_tpl->flags = Template::flagSystemOverride;
						$repeater_tpl->flags = 0;
						$repeater_tpl->save();
						$this->wire("templates")->delete($repeater_tpl);
						$this->wire("fieldgroups")->delete($repeater_fg);
					}
				}
				$this->wire("fields")->delete($f);
			}
		}
	}

	/**
	 * Delete templates when module is uninstalled
	 */
	protected function deleteTemplates() {
		foreach($this->templates_ as $tpl) {
			$t = $this->wire("templates")->get("name={$tpl["name"]}");
			if($t->id && $tpl["name"] !== "user") {
				$this->wire("templates")->delete($t);
				$this->wire("fieldgroups")->delete($t->fieldgroup);
			}
		}
	}
}
