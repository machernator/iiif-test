<?php

namespace Controllers;

use \Models\MediaModel as MediaModel;
use \NHM\SystemHelper as SH;

class IndexController extends Controller
{
	private $imageModel;
	private $mediaModel;

	public function __construct(\Base $f3, array $params)
	{
		parent::__construct($f3, $params);
		$this->mediaModel = new MediaModel();
	}

	/**
	 * Homepage
	 *
	 * @return void
	 */
	public function index(\Base $f3, array $params)
	{
		$f3->set('sitetitle', 'NHM Objects');
		$f3->set('content', $this->content('home'));
		$f3->set('contentSidebar', null);

		//$f3->set('images', $images);

		echo $this->renderPage('index-single-col.html');
	}

	public function object(\Base $f3, array $params)
	{
		$pid = $params['pid'] ?? null;
		$f3->set('sitetitle', "NHM Objects - $pid");
		$f3->set('content', $this->content('object'));
		$f3->set('contentSidebar', null);

		// get object
		$object = $this->mediaModel->showObjectPID($pid);
		$media = $this->mediaModel->showMediaPID($pid);

		// description
		$description = "";
		if ($object['description'] != null) {
			$paragraphs = explode("\n", $object['description']);
			foreach ($paragraphs as $p) {
				if (trim($p) != "") {
					$description = $description . "<p>$p</p>";
				}
			}
		}
		$object['description'] = $description;

		$f3->set('media', $media);
		$f3->set('object', $object);




		echo $this->renderPage('index-single-col.html');
	}

	/**
	 * Switches languages
	 *
	 * @return void
	 */
	public function language(\Base $f3, array $params)
	{
		$lang = $params['language'] ?? $f3->get('defaultLanguage');
		$_SESSION['locale'] = $lang;
		$f3->reroute($f3->get('SERVER.HTTP_REFERER'));
	}

	/**
	 * Help page
	 *
	 * @return void
	 */
	public function help(\Base $f3, array $params)
	{
		$lang = $this->lang();

		$f3->set('sitetitle', _tr('help'));
		$f3->set('contentSidebar', 'sidebar-help');
		$f3->set('content', $this->content('help'));

		// CSS/JS Files
		$f3->config($f3->get('controllerConfigPath') . 'help.ini', true);
		$this->setJS($f3->get('controllerjs'));
		$this->appendCSS($f3->get('controllercss'));

		echo $this->renderPage();
	}

	private function nl2P(string $text)
	{
	}
}
