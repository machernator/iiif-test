<?php
namespace Controllers;
use \NHM\SystemHelper as SH;

class ImageController extends Controller {
	private $prefix = 'iiif';
	private $manifestModel;

	public function __construct(\Base $f3, array $params) {
		parent::__construct($f3, $params);
		$this->manifestModel = new \Models\ManifestModel();
	}

	public function manifest(\Base $f3, array $params) {
		header('Content-Type: application/json');
		$id = $params['imgId'] ?? null;
		$manifest = $this->manifestModel->manifest($id);
		if ($manifest === '') {
			die('{"error": "Manifest not found"}');
		}

		echo $manifest;
	}

	public function manifestPID(\Base $f3, array $params) {
		header("Access-Control-Allow-Origin", "*");
		header('Content-Type: application/json');

		$pid = $params['pid'] ?? null;
		$manifest = $this->manifestModel->manifestPID($pid);
		// echo file_get_contents(APP_ROOT . "/manifests/new-manifest.json", 'r');return;
		echo json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
	}

	public function viewerMirador(\Base $f3, array $params) {

		$manifestModel = new \Models\ManifestModel();
		$id = $params['imgId'] ?? null;
		$f3->set('imgId', $id);
		$f3->set('sitetitle', 'NHM Digitalisate - Viewer');
		$f3->set('contentSidebar', 'sidebar-viewer');
		$f3->set('content', $this->content('viewer-mirador'));

		// CSS/JS Files
		$f3->config($f3->get('controllerConfigPath') . 'viewer-mirador.ini', true);
		$this->setJS($f3->get('controllerjs'));
		$this->appendCSS($f3->get('controllercss'));

		echo $this->renderPage('index-single-col.html');
	}

	public function viewerUniversal(\Base $f3, array $params) {
		$id = $params['imgId'] ?? null;
		$f3->set('imgId', $id);
		$f3->set('sitetitle', 'NHM Digitalisate - Viewer');
		$f3->set('contentSidebar', 'sidebar-viewer');
		$f3->set('content', $this->content('viewer-universal'));

		// CSS/JS Files
		$f3->config($f3->get('controllerConfigPath') . 'viewer-universal.ini', true);
		$this->setJS($f3->get('controllerjs'));
		$this->appendCSS($f3->get('controllercss'));

		echo $this->renderPage('index-single-col.html');
	}
}
