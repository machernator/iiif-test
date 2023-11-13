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

		// REMINDER:
		// use array_values for numbered arrays when converting back to json.
		// Otherwise the numbers will be used as keys.
		$this->manifest = json_decode($manifestFile, true);
		$this->sequence = json_decode($sequenceFile, true);
		$this->canvas = json_decode($canvasFile, true);
		$this->image = json_decode($imageFile, true);
	}
	/**
	 * Create image manifest
	 *
	 * @return  array
	 */
	public function manifest(string $id): string
	{
		// Set manifest attributes
		if ($id === '') return '';
		$server = $this->f3->get('manifestServer');
		$this->manifest['@id'] = $server . $id . '/manifest.json';

		return json_encode($this->manifest);
	}

	/**
	 * Create manifest with 1 or multiple images associated with an object PID
	 *
	 * @param string $pid
	 * @return string
	 */
	public function manifestPID(string $pid): array
	{
		// echo  @file_get_contents(__DIR__ . "{$this->manifestPath}manifest-copy.json", 'r');
		// Create Copy of manifest
		$manifest = $this->manifest;
		// Set manifest attributes
		$server = $this->f3->get('imageServer');
		// Get all Data for current PID
		$allMedia = $this->showMediaPID($pid);

		// Set manifest attributes
		$manifest['@id'] = $this->f3->get('currentUrl');
		$manifest['label'] = $pid;
		$manifest['logo'] = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . $manifest['logo'];
		$canvasNr = 1;
		foreach ($allMedia as $media) {
			// Create Copy of canvas
			$canvas = $this->canvas;

			// Filename on imageserver, change extension
			$info = pathinfo($media['file_name']);
			$fileName = $info['filename'] . $this->imageServerExtension;

			// Set canvas attributes
			$canvas['@id'] = "{$server}{$media['PID']}/canvas{$canvasNr}.json";
			$canvas['label'] = $media['media_title'] ? $media['media_title'] : $media['PID'] . ' - ' . $fileName;
			$canvas['height'] = $media['height'];
			$canvas['width'] = $media['width'];
			// calculate height of thumbnail
			$tnHeight = round($media['height'] / $media['width'] * $this->thumbNailWidth);
			// Thumbnail
			$canvas['thumbnail'] = [
				// example: http://localhost:8182/iiif/3/NHMW-BOT-W0273868.jpg/full/120,/0/default.jpg
				"@id" => $server . "$fileName/full/{$this->thumbNailWidth},/0/default.jpg",
				"@type"=> "dctypes:Image",
				"height"=> $tnHeight,
				"width"=> $this->thumbNailWidth
			];

			// Metadata
			$metadata = $this->metaData($media);
			$canvas['metadata'] = $metadata;

			// images
			$canvas['images'] = $this->images(
				fileName: $fileName,
				width: $media['width'],
				height: $media['height'],
				canvasNr: $canvasNr
			);

			// add canvas to manifest
			$manifest['sequences'][0]['canvases'][] = $canvas;
			$canvasNr++;
		}

		return $manifest;
	}

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
	 * Create array of metadata from allowed fields
	 *
	 * @param array $data
	 * @return array
	 */
	private function metaData(array $data):array
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

	private function images(
		string $fileName,
		int $width,
		int $height,
		int $canvasNr = 1
		):array
	{
		$server = $this->f3->get('imageServer');
		$images = [];

		// Image
		$images[] = [
			"@id" => $server . "$fileName/full/max/0/default.jpg",
			"@type" => "dctypes:Image",

			"on" => "{$server}$fileName/canvas/$canvasNr",
			"resource" => [
				"@id" => "{$server}{$fileName}/full/max/0/default.jpg",
				"height" => $height,
				"width" => $width,
			]
		];
		return $images;
	}
}
