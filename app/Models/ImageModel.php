<?php
namespace Models;
use \NHM\SystemHelper as SH;
use \FormLib\Form;

class ImageModel
{

	/**
	 * Create image manifest
	 *
	 * @return  array
	 */
	public function manifest(string $id):string
	{
		$f3 = \Base::instance();
		$file = @file_get_contents(__DIR__ . "/../../manifests/manifest.json", 'r');
		if ($file === false) {
			return '';
		}
		// Search/Replace dynamic values
		// TODO: check out if performance is better when creating an array for the whole document with names and values

		$search = [
			'%id%',
			'%imageServer%',
		];

		$replace = [
			$id,
			$f3->get('imageServer'),
		];
		$file = str_replace($search, $replace, $file);

		return $file;

	}
}
