<?php
namespace Controllers;
use \NHM\SystemHelper as SH;

class ImageController extends Controller {
	private $prefix = 'iiif';

	/**
	 * Homepage
	 *
	 * @return void
	 */
	public function info(\Base $f3, array $params) {
		header('Content-Type: application/json');
		$id = $params['imgId'] ?? null;

		$file = @file_get_contents("{$this->prefix}/$id/info.json", 'r');
		if ($file === false) {
			die('{"error": "Image not found"}');
		}

		echo $file;
	}

	public function manifest(\Base $f3, array $params) {
		header('Content-Type: application/json');
		$id = $params['imgId'] ?? null;

		$file = @file_get_contents("{$this->prefix}/$id/manifest.json", 'r');
		if ($file === false) {
			die('{"error": "Manifest not found"}');
		}

		echo $file;
	}

	public function viewer(\Base $f3, array $params) {
		$id = $params['imgId'] ?? null;

		$f3->set('sitetitle', 'NHM Digitalisate - Viewer');
		$f3->set('contentSidebar', 'sidebar-viewer');
		$f3->set('content', $this->content('viewer'));

		// CSS/JS Files
		$f3->config($f3->get('controllerConfigPath') . 'viewer.ini', true);
		$this->setJS($f3->get('controllerjs'));
		$this->appendCSS($f3->get('controllercss'));

		echo $this->renderPage();
	}
}
