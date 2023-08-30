<?php

namespace Controllers;

use \NHM\SystemHelper as SH;

class IndexController extends Controller
{
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
		$f3->set('images', $this->getImages());
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

	private function getImages(): array
	{
		$images = [];
		$dir = $this->f3->get('imageFolder');
		$files = scandir($dir);
		$faker = \Faker\Factory::create();
		foreach ($files as $file) {
			if (preg_match('/\.jpg$/', $file) || preg_match('/\.jpeg$/', $file) || preg_match('/\.png$/', $file)) {
				$images[] = [
					'id' => $file,
					'url' => $this->f3->get('imageServer') . $file,
					'title' => $faker->sentence(3),
					'description' => $faker->paragraph(3),
				];
			}
		}
		return $images;
	}
}
