<?php
namespace Controllers;

use \Models\FileModel;

class FileController extends Controller {
	private $fm;

	public function __construct($f3, $params) {
		$this->fm = new FileModel();
	}

	public function render($f3, $params) {
		$fileid = $params['fileid'] ?? 0;
		if ($fileid !== 0) {
			$file = $this->fm->getFile($fileid);

			header("Expires: 0");
			header("Cache-Control: no-store, no-cache, must-revalidate");
			header("Cache-Control: post-check=0, pre-check=0", false);
			header("Pragma: no-cache");
			header('Content-Description: File Transfer');
			header("Content-Type: " . $file['Typ']);
			header('Content-Disposition: inline; filename="' . $file['Dateiname'] . '"');
			$this->fm->getFileData($fileid);
		}
		else {
			echo 'Fehler';
		}
	}
}