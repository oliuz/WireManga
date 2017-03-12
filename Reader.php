<?php namespace ProcessWire;
/*
 * WireManga - a module for creating a manga reader
 *
 */

class WireMangaReader extends Process
{

	protected $page;
	protected $urlSegment1;
	protected $arrKey;
	protected $chapters;

	public function __construct($urlSegment1) {
		$this->page = wire("page");
		$this->urlSegment1 = $urlSegment1;
		
		$chaptersCache = $this->cache->get("siblings:" . $this->page->parent->id, function() {
			return array_values($this->page->siblings("template=wm_chapters, wm_chapter_images>0")->sort('name')->explode('name'));
		});
		$this->chapters = $chaptersCache;
		$this->arrKey = array_search($this->page->name, $this->chapters);
	}


	/*
	|--------------------------------------------------------------------------
	| Number of images in this chapter
	|--------------------------------------------------------------------------
	*/
	public function imagesCount() {
		return count($this->chapterImages());
	}

	/*
	|--------------------------------------------------------------------------
	| Number of images in this chapter
	|--------------------------------------------------------------------------
	*/
	public function nextChapter() {
		// link to the next chapter
		$nextChapterLink = $this->page->parent->url;
		if(($this->arrKey+1) != count($this->chapters))
		{
			// next chapter object
			$nextChapter = $this->wire("pages")->get("template=wm_chapters, parent=/manga/{$this->page->parent->name}/, name=" . $this->chapters[$this->arrKey+1]);

			// next chapter first image link
			$nextChapterLink = $nextChapter->url . "1/";
		}
		return $nextChapterLink;
	}

	public function nextPage() {
		// link to next page/image
		$next = $this->page->url . ($this->urlSegment1 + 1)."/";
		if (($this->urlSegment1+1) == ($this->imagesCount() + 1))
		{
			$next = $this->nextChapter();
		}
		return $next;
	}

	public function prevChapter() {
		// link to the previous chapter
		$prevChapterLink = $this->page->parent->url;
		if(($this->arrKey-1) != -1)
		{
			// previous chapter object
			$prevChapter = $this->wire("pages")->get("template=wm_chapters, parent=/manga/{$this->page->parent->name}/, name=" . $this->chapters[$this->arrKey-1]);
			// previous chapter last image link
			$prevChapterImageCount = $prevChapter->wm_chapter_images->count;
			$prevChapterLink = $prevChapter->url . ($prevChapterImageCount);
		}
		return $prevChapterLink;
	}

	public function prevPage() {
		// link to previous page/image
		$prev = $this->page->url . ($this->urlSegment1-1)."/";
		if (($this->urlSegment1-1) == 0)
		{
			$prev = $this->prevChapter();
		}
		return $prev;
	}

	public function imageSrc() {
		$currentImage = $this->urlSegment1 - 1;
		return $this->chapterImages()->eq($currentImage)->url;
	}

	/*
	|--------------------------------------------------------------------------
	| Sort the images in this chapter
	|--------------------------------------------------------------------------
	*/
	public function chapterImages() {
		$page = $this->wire("page");
		$array = $page->wm_chapter_images->getArray();
		natsort($array);
		$imagesArray = new WireArray();
		$imagesArray->import($array);
		return $imagesArray;
	}

	public function pagesList() {
		$page = $this->page;
		$pageList = "";
		$x = 1;
		$pageList .= "<select class='reader--pages-list uk-select'>";
		foreach($this->chapterImages() as $image) {
			$selected = ($this->urlSegment1 == $x) ? "selected='selected'" : "";
			$pageList .= "<option value='{$x}' {$selected}>Page {$x}</option>";
			$x++;
		}
		$pageList .= "</select>";
		return $pageList;
	}

	public function chaptersList() {
		$page = $this->page;
		$chapterList = "";
		if(count($this->chapters) > 1) {
			$chapterList .= "<select class='reader--chapters-list uk-select'>";
			foreach($this->chapters as $chapter) {
				$selected = ($this->page->name == $chapter) ? "selected='selected'" : "";
				$chapterList .= "<option value='{$chapter}' {$selected}>Chapter {$chapter}</option>";
			}
			$chapterList .= "</select>";
		}
		return $chapterList;
	}
}
