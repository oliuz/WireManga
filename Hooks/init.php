<?php namespace ProcessWire;
/* WireManga */
/**
 * Chapters markup to cache
 *
 * used to cache chapters to keep the output consistent
 *
 * @param Page $page The page containing the chapters to cache
 *
 */
function chapterListForCache(Page $page) {
	$chapters = "No Chapters";
	if($page->children->count) {
		$classes = [
			"wrapperClass"        => "uk-container-expand",
			"volumeClass"         => "uk-card uk-card-default uk-margin-bottom",
			"volumeHeaderClass"   => "uk-card-header",
			"volumeChaptersClass" => "uk-card-body",
			"chapterClass"        => "uk-margin-left",
			"chapterLinkClass"    => "uk-display-block",
		];
		$header = "<h2>Chapters</h2>";
		$chapters = wire("manga")->chaptersList($page, $header, $classes);
	}
	return $chapters;
}

wire()->addHookAfter("Pages::saveReady", null, "cacheChapters");
wire()->addHookAfter("ProcessPageEdit::execute", null, "renameChildrenTab");

function cacheChapters(HookEvent $event) {
	$page = $event->arguments[0];
	$cache = wire("cache");
	if($page->template->name == "wm_manga_single" && wire("session")->get('dontClearCache' . $page->id) !== 1) {
		$cache->delete("chapters:".$page->id);
		$cache->get("chapters:" . $page->id, $cache::expireNever, function() use($page){
			echo chapterListForCache($page);
		});
	}
	elseif($page->template->name == "wm_chapters") {
		$cache->delete("chapters:".$page->parent->id);
		$cache->get("chapters:" . $page->parent->id, $cache::expireNever, function() use($page){
			echo chapterListForCache($page->parent);
		});
		$cache->delete("siblings:" . $page->parent->id);
	}
}

function renameChildrenTab(HookEvent $event) {
	$render = $event->return;
	$template_name = "wm_manga_single"; // Change this to match the exact template name of pages you want to apply tab-renaming to.
	if (false !== strpos($render, "template_{$template_name} ")) {
		$render = str_replace("Children</a>", "Chapters</a>", $render);
		$event->return = $render;
	}
}
/* WireManga end*/