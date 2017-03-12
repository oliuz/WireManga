<?php namespace ProcessWire;

class WireMangaConfig extends ModuleConfig {
    public function __construct() {
        $this->add([
			[// Checkbox field: useMyDomain
				"name"  => "wm_clean_up",
				"type"  => "checkbox",
				"label" => $this->_("Clean Up"),
				"description" => $this->_("Remove all templates, fields and pages created by this module when it is uninstalled."),
				"value" => $this->_(0),
			],
		]);
    }
}