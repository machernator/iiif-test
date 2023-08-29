<?php
namespace Controllers;
use \NHM\SystemHelper as SH;

class ImageController extends Controller {
	private $prefix = 'iiif';
	private $imageModel;

	public function __construct(\Base $f3, array $params) {
		parent::__construct($f3, $params);
		$this->imageModel = new \Models\ImageModel();
	}

	public function manifest(\Base $f3, array $params) {
		header('Content-Type: application/json');
		$id = $params['imgId'] ?? null;
		$manifest = $this->imageModel->manifest($id);
		if ($manifest === '') {
			die('{"error": "Manifest not found"}');
		}

		echo $manifest;
	}

	public function viewer(\Base $f3, array $params) {
		$id = $params['imgId'] ?? null;
		$f3->set('imgId', $id);
		$f3->set('sitetitle', 'NHM Digitalisate - Viewer');
		$f3->set('contentSidebar', 'sidebar-viewer');
		$f3->set('content', $this->content('viewer'));

		// CSS/JS Files
		$f3->config($f3->get('controllerConfigPath') . 'viewer.ini', true);
		$this->setJS($f3->get('controllerjs'));
		$this->appendCSS($f3->get('controllercss'));

		echo $this->renderPage('index-viewer.html');
	}
}
