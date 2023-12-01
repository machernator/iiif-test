<?php

namespace Controllers;

use \NHM\SystemHelper as SH;
use \Models\TranslationModel;

abstract class Controller
{
	// f3 instance
	protected $f3;

	// template folders
	protected $views;
	protected $layout;
	protected $content;
	protected $navFolder;

	// load additional css/js files
	protected $CSSTags = [];
	protected $js = [];

	protected $components;
	protected $navigations;
	protected $modals;

	/**
	 * Constructor, sets up Members, gets Instance of DB
	 */
	public function __construct(\Base $f3, array $params)
	{
		$this->f3 = $f3;
		// Pfade
		$this->views = 			$f3->get('tpl_views');
		$this->layout = 		$f3->get('tpl_layout');
		$this->components =		$f3->get('tpl_components');
		$this->content = 		$f3->get('tpl_content');
		$this->navigations = 	$f3->get('tpl_nav');
		$this->modals = 		$f3->get('tpl_modals');

		$f3->set('bodyclass', '');
		$f3->set('username', get_current_user());

		// https or http?
		$server_request_scheme = 'https';
		if (
			(array_key_exists('REQUEST_SCHEME', $_SERVER) && (!empty($_SERVER['REQUEST_SCHEME']) && $_SERVER['REQUEST_SCHEME'] == 'http')) ||
			(array_key_exists('REQUEST_SCHEME', $_SERVER) || empty($_SERVER['HTTPS']) || strtolower($_SERVER['HTTPS']) == 'off')
		) {
			$server_request_scheme = 'http';
		}

		$f3->set('currentUrl', $server_request_scheme . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);

		// Status messages, stored in Session
		SH::initAppStatus();

		// Language
		if (!isset($_SESSION['locale'])) {
			$_SESSION['locale'] = $f3->get('defaultLanguage');
		}
		$f3->set('language', $_SESSION['locale']);
		//Default CSS/JS Files
		/**
		 * Scripts must set the path. Additionally attributes can be passed to the tag (attrs)
		 * see https://developer.mozilla.org/en-US/docs/Web/HTML/Element/script
		 * Attributes with the value true will be written without value (defer, async)
		 *
		 */
		$jsPath = $f3->get('js');
		$cssPath = $f3->get('css');
		$this->appendJS($f3->get('defaultjs'));
		$this->appendCSS($f3->get('defaultcss'));
		// Default F3 Variablen setzen
		$f3->set('main', $this->layout . 'main.html');
		$f3->set('status', $this->components . 'app-status.html');
		$f3->set('contentSidebar', 'sidebar-default');
	}

	/**
	 * Updates script tags and CSS links
	 *
	 * @return  void
	 */
	protected function updateExternalFiles()
	{
		$this->f3->set('jstags', $this->scriptTags());
		$this->f3->set('csstags', $this->CSSTags());
	}

	/**
	 * Renders page Template
	 *
	 * @return string
	 */
	public function renderPage(string $page='index.html'): string
	{
		return \Template::instance()->render($this->views . $page);
	}

	/**
	 * Path to the content template
	 *
	 * @param string $name
	 * @return string
	 */
	protected function content(string $name = 'home'): string
	{
		return $this->content . "{$name}.html";
	}

	/**
	 * Get currently set language
	 *
	 * @return string
	 */
	protected function lang():string
	{
		return $_SESSION['locale'] ?? $this->f3->get('defaultLanguage');
	}

	/**
	 * Override JS Files that should be loaded in current document.
	 *
	 * @param array $js
	 * @return void
	 */
	protected function setJS($js)
	{
		if (gettype($js) === 'array') {
			$this->js = $js;
		} else {
			$this->js= ['src' => $js];
		}
		$this->updateJS();
		$this->updateExternalFiles();
	}

	/**
	 * Add single entry to js
	 *
	 * @param mixed $path
	 * @return void
	 */
	protected function appendJS($js)
	{
		if (gettype($js) === 'array') {
			$this->js = array_merge($this->js, $js);
		} else {
			$this->js[] = ['src' => $js];
		}
		$this->updateJS();
		$this->updateExternalFiles();
	}

	/**
	 * Override CSS Files that should be loaded in current document.
	 *
	 * @param array $js
	 * @return void
	 */
	protected function setCSS($css)
	{
		if (gettype($css) === 'array') {
			$this->CSSTags = $css;
		} else {
			$this->CSSTags= ['href' => $css];
		}
		$this->updateCSS();
		$this->updateExternalFiles();
	}

	/**
	 * Add single entry to CSSTags
	 *
	 * @param string $path
	 * @return void
	 */
	protected function appendCSS($css)
	{
		if (gettype($css) === 'array') {
			$this->CSSTags = array_merge($this->CSSTags, $css);
		} else {
			$this->CSSTags[] = ['href' => $css];
		}
		$this->updateCSS();
		$this->updateExternalFiles();
	}

	/**
	 * Update JS
	 *
	 * @return void
	 */
	protected function updateJS()
	{
		$this->f3->set('js', $this->js);
	}

	/**
	 * Update CSSTags
	 *
	 * @return void
	 */
	protected function updateCSS()
	{
		$this->f3->set('csstags', $this->CSSTags);
	}

	/**
	 * Returns Scripttags for the header of the page
	 *
	 * @return  array 	Array of script tags
	 */
	protected function scriptTags(): array
	{
		$tags = [];
		if ($this->js === null) return $tags;
		foreach ($this->js as $js) {
			if ($js === null) continue;
			$tag = '<script';
			foreach ($js as $attr => $val) {
				if ($val !== true) {
					$tag .= " $attr=\"$val\"";
				} else {
					$tag .= " $attr";
				}
			}
			$tags[] = $tag . '></script>';
		}
		return $tags;
	}

	/**
	 * Returns CSS Links
	 *
	 * @return  array 	Array of link tags
	 */
	protected function CSSTags(): array
	{
		$tags = [];
		foreach ($this->CSSTags as $css) {
			if (!array_key_exists('rel', $css)) {
				$css['rel'] = 'stylesheet';
			}
			$tag = '<link';
			foreach ($css as $attr => $val) {
				if ($val !== true) {
					$tag .= " $attr=\"$val\"";
				} else {
					$tag .= " $attr";
				}
			}
			$tags[] = $tag . '>';
		}
		return $tags;
	}

	/**
	 * Every POST Form has to have a field 'token'. This will be checked against the token
	 * in the current SESSION. If it was not posted, does not exist in SESSION or is different
	 * to the token in SESSION, the POST Request is likely to be a CSRF attack.
	 *
	 * @return  bool
	 */
	protected function isValidToken(): bool
	{
		return \NHM\CSRF::isValidToken($this->f3->get('POST.' .  \NHM\CSRF::getTokenName()));
	}
}
