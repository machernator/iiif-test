<?php

namespace NHM;

use NHM\SystemHelper as SH;

/**
 * Creates configuration for ObjectForm
 */
class ObjectFormConfig
{
	private array $config = [];
	private string $customFieldPath;
	private string $lang = 'de';
	private \Base $f3;

	/**
	 * Constructor,
	 *
	 * @param mixed $src
	 * @param array $conf
	 * @return array
	 */
	public function __construct(array $conf)
	{
		$this->f3 = \Base::instance();
		$this->customFieldPath = $this->f3->get('tpl_views') . 'custom-inputs/';
		$this->lang = $_SESSION['locale'] ?? $this->lang;
		$this->createConfigFromArray($conf);
	}

	/**
	 * Creates configuration data from array
	 *
	 * @param array $fieldConfig
	 * @return array
	 */
	private function createConfigFromArray(array $fieldConfig)
	{
		$config = [];
		$fields = [];
		$hasFileField = false;
		//Form Defaults, können später überschrieben werden
		$config = [
			'form' => [
				'method' => 'post',
				'action' => '',
				'enctype' => 'multipart/form-data',
				'id' => 'objectForm',
				'errorClass' => 'is-invalid',
				'tagAttributes' => [
					'class' => ''
				]
			]
		];

		$config['form'] = array_merge($config['form']);

		foreach ($fieldConfig as $conf) {
			// set type
			$field = [];

			$field['name'] = "fields[{$conf['FIELD_ID']}][value]";
			$field['id'] = $conf['FIELD_ID'];
			$lang = $conf['lang'] ?? $this->lang;
			$field['label'] = $conf['name_' . $lang];
			$conf['group'] !== null ? $field['group'] = $conf['group'] : null;

			// set fieldtypes
			switch ($conf['field_type']) {
				case 'bool':
					$field['type'] = 'checkbox';
					// checked depending on value 1 or 0
					$field['checked'] = $conf['value'] == 1 ? true : false;
					break;
				case 'text':
					$field['type'] = 'textarea';
					$field['value'] = $conf['value'];
					$field['tagAttributes'] = [
						'rows' => 1,
						'data-ismultiline' => 1,
						'onfocus' => "this.value=this.value;this.style.height = this.scrollHeight + 'px';",
						'oninput' => "this.style.height = this.scrollHeight + 'px'",
						'onblur' => "this.style.removeProperty('height')",
					];
					break;
				case 'search':
					$field['type'] =		'custom';
					$field['template'] = 	$this->customFieldPath . 'object-systematics.html';
					$field['templateVars'] = [
						'systId' =>			$conf['Tab_ID'],
						'title' => 			$conf['name_' . $lang],
						'id' => 			$conf['FIELD_ID'],
						'display_as' => 	$conf['display_as'],
						'value' => 			$conf['value_json'] !== null ? $conf['value_json'] : '[]',
						'preferred' => 		$conf['value'] ?? ''
					];
					break;
				default:
					$field['type'] =		'custom';
					$field['value'] =		$conf['value'];
					$field['template'] = 	$this->customFieldPath . 'grouped-fields.html';
					$field['templateVars'] = [
						'fieldId' =>		$conf['FIELD_ID'],
						'value' =>			$conf['value'],
						'unit_id' => 		$conf['UNIT_ID'],
						'unit' => 			$conf['unit'],
						'unit_lookup' => 	json_decode($conf['unit_lookup'], true),
					];
					break;
			}

			// Feld hinzufügen
			$fields[] = $field;
		}

		// enctype für uploads setzen

		if ($hasFileField) {
			$config['form']["encType"] = "multipart/form-data";
		}

		// Alle Felder hinzufügen
		$config['fields'] = $fields;
		$this->config = $config;
	}

	public function getConfig()
	{
		return $this->config;
	}
}
