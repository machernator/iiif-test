<?php

namespace Controllers;

use \NHM\SystemHelper as SH;

class ImageController extends Controller
{
	private $prefix = 'iiif';
	private $mediaModel;

	public function __construct(\Base $f3, array $params)
	{
		parent::__construct($f3, $params);
		$this->mediaModel = new \Models\MediaModel();
	}


	public function manifest(\Base $f3, array $params)
	{
		header("Access-Control-Allow-Origin", "*");
		header('Content-Type: application/json');
		$id = $params['imgId'] ?? null;

		$manifest = $this->mediaModel->manifestFilename(filename: $id);
		if ($manifest === '') {
			die('{"error": "Manifest not found"}');
		}
		echo json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
	}

	/**
	 * Viewer for IIIF Manifests, generate manifest from PID
	 *
	 * @param \Base $f3
	 * @param array $params
	 * @return void
	 */
	public function manifestPID(\Base $f3, array $params)
	{
		header("Access-Control-Allow-Origin", "*");
		header('Content-Type: application/json');
		$pid = $params['pid'] ?? null;
		$startCanvasNr = $_GET['s'] ?? 1;
		$manifest = $this->mediaModel->manifestPID(pid: $pid, startCanvasNr: $startCanvasNr);
		echo json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
	}

	/**
	 * Show mirador viewer for IIIF Manifests
	 *
	 * @param \Base $f3
	 * @param array $params
	 * @return void
	 */
	public function viewerMirador(\Base $f3, array $params)
	{
		$id = $params['imgId'] ?? null;
		$extension = pathinfo($id, PATHINFO_EXTENSION);

		$startCanvasNr = $_GET['s'] ?? 1;

		if ($extension && in_array((strtolower($extension)), ['jpg', 'jpeg', 'png', 'gif', 'jp2', 'webp'])) {
			// $id is an image file name
			$id = pathinfo($id, PATHINFO_FILENAME);
			$manifestPath = "/$id/manifest.json?s=$startCanvasNr";
		} else {
			// $id is not an image file name
			$manifestPath = "/pid/$id/manifest.json?s=$startCanvasNr";
		}

		$f3->set('imgId', $id);
		$f3->set('manifestPath', $manifestPath);
		$f3->set('sitetitle', 'NHM Digitalisate - Viewer');
		$f3->set('contentSidebar', 'sidebar-viewer');
		$f3->set('content', $this->content('viewer-mirador'));

		// CSS/JS Files
		$f3->config($f3->get('controllerConfigPath') . 'viewer-mirador.ini', true);
		$this->setJS($f3->get('controllerjs'));
		$this->appendCSS($f3->get('controllercss'));

		echo $this->renderPage('index-single-col.html');
	}

	public function viewerUniversal(\Base $f3, array $params)
	{
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
