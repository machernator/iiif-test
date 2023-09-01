<?php

namespace Controllers;
use \Models\ImageModel as ImageModel;
use \NHM\SystemHelper as SH;

class IndexController extends Controller
{
	private $imageModel;

	public function __construct(\Base $f3, array $params)
	{
		parent::__construct($f3, $params);
		$this->imageModel = new ImageModel();
	}

	/**
	 * Homepage
	 *
	 * @return void
	 */
	public function index(\Base $f3, array $params)
	{
		$f3->set('sitetitle', 'NHM Digitalisate');
		$f3->set('content', $this->content('home'));
		$f3->set('contentSidebar', null);
		$images = $this->imageModel->listMedia();
		$f3->set('images', $images);

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
}
