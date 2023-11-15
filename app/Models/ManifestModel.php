<?php

namespace Models;

use \NHM\SystemHelper as SH;
use \FormLib\Form;

class ManifestModel extends Model
{
	// partials of manifest
	private $manifest = [];
	private $canvas = [];
	private $sequence = [];
	private $image = [];
	private $metadata = [];
	private $metaDataMap = [
		"collection_name" => "Collection",
		"datetime_original" => "Created",
		"media_creator" => "Creator",
		"description" => "Description",
		"copyright" => "License"
	];

	private $manifestPath = "/../../manifests/";
	// Extension used on the image server
	private $imageServerExtension = ".jpg";
	private $thumbNailWidth = 120;

	/**
	 * Load all manifest partials and convert them to arrays
	 */
	public function __construct()
	{
		parent::__construct();
		$manifestFile = @file_get_contents(__DIR__ . "{$this->manifestPath}manifest.json", 'r');
		$canvasFile = @file_get_contents(__DIR__ . "{$this->manifestPath}canvas.json", 'r');
		$sequenceFile = @file_get_contents(__DIR__ . "{$this->manifestPath}sequence.json", 'r');
		$imageFile = @file_get_contents(__DIR__ . "{$this->manifestPath}image.json", 'r');

		$this->manifest = json_decode($manifestFile, true);
		$this->sequence = json_decode($sequenceFile, true);
		$this->canvas = json_decode($canvasFile, true);
		$this->image = json_decode($imageFile, true);
	}
	/**
	 * Create single image manifest
	 *
	 * @return  array
	 */
	public function manifestFilename(string $filename): array
	{
		// Get all Data for filename
		$allMedia = $this->showMediaFilename($filename);
		// Create manifest
		return $this->createManifest($filename, $allMedia);
	}

	/**
	 * Create manifest with 1 or multiple images associated with an object PID
	 *
	 * @param string $pid
	 * @return string
	 */
	public function manifestPID(string $pid): array
	{
		// Get all Data for current PID
		$allMedia = $this->showMediaPID($pid);
		// Create manifest
		return $this->createManifest($pid, $allMedia);
	}

	/**
	 * List all media
	 *
	 * @return array
	 */
	public function listMedia(): array
	{
		$sp   = 'EXEC app.sp_List_Media';
		$stmt = $this->executeResult($sp);
		$res = [];
		if ($stmt === false) return $res;
		while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
			$res[] = $row;
		}
		return $res;
	}

	/**
	 * Get all media for a given PID
	 *
	 * @param string $pid
	 * @return array
	 */
	public function showMediaPID(string $pid): array
	{
		$sp   = 'EXEC app.sp_Show_Media_PID @PID = ?';
		$stmt = $this->executeResult($sp, [$pid]);
		$res = [];
		if ($stmt === false) return $res;
		while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
			$res[] = $row;
		}
		return $res;
	}

	/**
	 * Get media data for a given filename
	 *
	 * @param string $filename
	 * @return array
	 */
	public function showMediaFilename(string $filename): array
	{
		$sp   = 'EXEC app.sp_Show_Media_filename @filename = ?';
		$stmt = $this->executeResult($sp, [$filename]);
		$res = [];
		if ($stmt === false) return $res;
		if ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
			$res = $row;
		}
		return $res;
	}

	/**
	 * Create array of metadata from allowed fields
	 *
	 * @param array $data
	 * @return array
	 */
	private function metaData(array $data): array
	{
		$metaData = [];
		foreach ($data as $key => $value) {
			if (!array_key_exists($key, $this->metaDataMap) || !$data[$key]) continue;
			// Convert date objects
			if (gettype($value) === 'object') {
				$value = $value->format('Y-m-d H:i:s');
			}
			$metaData[] = [
				"label" => $this->metaDataMap[$key],
				"value" => $value,
			];
		}
		return $metaData;
	}

	private function createManifest(string $id, array $data): array
	{
		// Create manifest
		$manifest = $this->manifest;
		// Set manifest attributes
		$server = $this->f3->get('imageServer');

		// Set manifest attributes
		$manifest['@id'] = $this->f3->get('currentUrl');
		$manifest['label'] = $id;
		$manifest['attribution'] = "Natural History Museum Vienna";
		$manifest['logo'] = $server . "nhmw-logo.png/full/80,/0/default.png";
		$manifest['description'] = "Manifest for: $id";
		$canvasNr = 1;

		foreach ($data as $media) {
			// Create Copy of canvas
			$canvas = $this->canvas;
			// Filename on imageserver, change extension
			$info = pathinfo($media['file_name']);
			$fileName = $info['filename'] . $this->imageServerExtension;
			// Set canvas attributes
			$canvasId = "{$server}{$media['PID']}/canvas/$canvasNr";
			$canvas['@id'] = $canvasId;
			$canvas['label'] = $media['media_title'] ? $media['media_title'] : $media['PID'] . ' - ' . $fileName;
			$canvas['height'] = $media['height'];
			$canvas['width'] = $media['width'];
			// set first canvas as start canvas
			if ($canvasNr === 1) {
				$manifest['sequences'][0]['startCanvas'] = $canvas['@id'];
			}
			// calculate height of thumbnail
			$tnHeight = round($media['height'] / $media['width'] * $this->thumbNailWidth);
			// Thumbnail
			$canvas['thumbnail'] = [
				// example: http://localhost:8182/iiif/3/NHMW-BOT-W0273868.jpg/full/120,/0/default.jpg
				"@id" => "{$server}{$fileName}/full/120,/0/default.jpg",
				"@type" => "dctypes:Image",
				"height" => $tnHeight,
				"width" => $this->thumbNailWidth
			];

			// Metadata
			$metadata = $this->metaData($media);
			$canvas['metadata'] = $metadata;

			$canvas['images'][] =  [
				"@id" => $server . "$fileName/full/max/0/default.jpg",
				"@type" => "oa:Annotation",
				"motivation" => "sc:painting",
				"on" => $canvasId,
				"resource" => [
					"@id" => "{$server}{$fileName}/full/max/0/default.jpg",
					"@type" => "dctypes:Image",
					"height" => $media['height'],
					"width" => $media['width'],
				]
			];

			// add canvas to manifest
			$manifest['sequences'][0]['canvases'][] = $canvas;
			$canvasNr++;
		}

		return $manifest;
	}
}
