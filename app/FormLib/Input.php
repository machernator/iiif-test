<?php

namespace FormLib;

class Input
{
	protected string $id = '';
	protected string $name = '';
	protected string $label = '';
	protected string $type = '';
	protected string $description = '';
	protected string $error = '';
	protected string $errorClass = ''; // class for errormessages
	protected string $fieldErrorClass = ''; // class for inputfields with errors
	protected string $value = '';
	protected string $validation = '';
	protected string $filters = '';

	protected mixed $group = null;

	protected array $labelAttributes = [];
	protected array $tagAttributes = [];
	protected array $labelAsDiv = ['checkbox', 'checkboxgroup', 'radio'];

	protected string $wrapperTag = '';
	protected array $wrapperTagAttributes = [];

	protected bool $required = false;
	protected bool $multiple = false;
	protected bool $readOnly = false;


	/**
	 * Initialize the input field with the following entries in conf:
	 * - id 	 					string 		required
	 * - name 						string 		required
	 * - label 						string 		required	Dependent on _tr global function
	 * - type 						string 		required 	HTML input type
	 * - value 		 				string 		optional
	 * - description 				string 		optional
	 * - error	 					string 		optional 	error message
	 * - errorClass					string 		optional 	CSS error class for error message.
	 * - fieldErrorClass			string 		optional 	CSS error class for error field.
	 * - readOnly 					bool 		optional 	display field readonly
	 * - tagAttributes				array 		optional 	key/value pairs of html attributes for input tag
	 * - labelAttributes			array 		optional 	key/value pairs of html attributes for label tag
	 * - wrapperTag					string 		optional 	tag name of wrapping tag. e.g. div
	 * - wrapperTagAttributes		array 		optional 	key/value pairs of html attributes for wrapper tag
	 * - validation	 				array 		optional 	GUMP Validation rules
	 * - filterRules				array 		optional 	GUMP Filter rules	 *
	 * - group					  	string 		optional 	Group name
	 *
	 * @param   array  $conf
	 *
	 * @return  void
	 */
	public function __construct(array $conf)
	{
		$trExists = function_exists('_tr');

		// simple attribues
		$this->type			 			= $conf['type'] ?? 'text';
		$this->value					= $conf['value'] ?? '';
		$this->error					= $conf['error'] ?? '';
		$this->errorClass	   			= $conf['errorClass'] ?? '';
		$this->fieldErrorClass  		= $conf['fieldErrorClass'] ?? '';
		$this->validation	   			= $conf['validation'] ?? '';
		$this->validation	   			= $conf['filters'] ?? '';
		$this->readOnly		 			= $conf['readonly'] ?? false;
		$this->group					= $conf['group'] ?? null;
		$this->wrapperTag	   			= $conf['wrapperTag'] ?? '';
		$this->wrapperTagAttributes		= $conf['wrapperTagAttributes'] ?? [];

		// essential Attributes
		if (array_key_exists('id', $conf) && $conf['id'] !== '') {
			$this->id = $conf['id'];
		} else {
			if ($this->type !== 'hidden') die("Konfigurationsfehler: id");
		}

		// name
		if (array_key_exists('name', $conf) && $conf['name'] !== '') {
			$this->name = $conf['name'];
		} else {
			die("Konfigurationsfehler: name");
		}

		if (array_key_exists('label', $conf) && $conf['label'] !== '') {
			if ($trExists) {
				$this->label = _tr($conf['label']);
			} else {
				$this->label = $conf['label'];
			}
		} else {
			if ($this->type !== 'hidden') die("Konfigurationsfehler: label");
		}



		// further Attributes
		if (array_key_exists('description', $conf) && $conf['description']) {
			if ($trExists) {
				$this->description = _tr($conf['description']);
			} else {
				$this->description = $conf['description'];
			}
		}

		if (array_key_exists('tagAttributes', $conf) && is_array($conf['tagAttributes'])) {
			$this->tagAttributes = $conf['tagAttributes'];
		}

		if (array_key_exists('labelAttributes', $conf) && is_array($conf['labelAttributes'])) {
			$this->labelAttributes = $conf['labelAttributes'];
		}

		if (array_key_exists('required', $conf)) {
			$required = $conf['required'] === true ? true : false;
			$this->setRequiredValidation($required);
		}

		if (array_key_exists('multiple', $conf) && $conf['multiple'] == true) {
			$this->multiple = true;
		}
	}

	/**
	 * Allow public readonly Access to some class attributes
	 * - id
	 * - name
	 * - label
	 * - type
	 * - type
	 * - required
	 * - validation
	 * - filters
	 * - errorClass
	 * - readOnly
	 *
	 * @param   string  $name
	 *
	 * @return  mixed
	 */
	public function __get(string $name = null)
	{
		if (!property_exists($this, $name)) {
			return null;
		}

		switch ($name) {
			case 'id':
				return $this->id;
			case 'name':
				return $this->name;
			case 'label':
				return $this->label;
			case 'type':
				return $this->type;
			case 'required':
				return $this->required;
			case 'validation':
				return $this->validation;
			case 'filters':
				return $this->filters;
			case 'error':
				return $this->error;
			case 'errorClass':
				return $this->errorClass;
			case 'description':
				return $this->description;
			case 'readOnly':
				return $this->readOnly;
			case 'fieldVars':
				return $this->fieldVars;
			case 'group':
				return $this->group;
			case 'values':
				$this->values ?? null;
				break;
			case 'value':
				return $this->value ?? '';
				break;
			case 'templateVars':
				return $this->templateVars ?? null;
				break;

		}

		return null;
	}

	/**
	 * Allow setters for some attributes. Only String values are allowed
	 *
	 * @param   string  $name
	 * @param   string $value
	 *
	 * @return  void
	 */
	public function __set(string $name, string $value)
	{
		if (!property_exists($this, $name) || !is_string($value)) {
			return;
		}

		switch ($name) {
			case 'required':
				$this->setRequiredValidation($value);
				break;
			case 'error':
				$this->error = $value;
				break;
			case 'errorClass':
				$this->errorClass = $value;
				break;
			case 'value':
				// Choice fields can have multiple values
				$hasMultiValues = property_exists($this, 'values');
				if ($hasMultiValues && is_array($value)) {
					$this->values = $value;
				}
				elseif($hasMultiValues) {
					$this->values = [$value];
				}
				else {
					$this->value = $value;
				}
			case 'readOnly':
				$this->readOnly = filter_var($value, FILTER_VALIDATE_BOOLEAN);
				break;
		}
	}

	/**
	 * Output input field including label, description and error message
	 *
	 * @return string
	 */
	public function render(): string
	{
		$out = '';
		if ($this->readOnly) {
			$out .=
				$this->renderLabel() .
				$this->renderField();
		} else {
			$out .= $this->renderLabel() .
				$this->renderDescription() .
				$this->renderField() .
				$this->renderError();
		}

		$out = $this->wrapInput($out);
		return $out;
	}

	/**
	 * Rendering of the label tag
	 *
	 * @return string
	 */
	public function renderLabel(): string
	{
		$out = '';
		$labelAttrs = $this->renderHTMLAttributes($this->labelAttributes);
		if (in_array($this->type, $this->labelAsDiv)) {
			$out = <<<FLD
<div{$labelAttrs}>{$this->label}</div>
FLD;
		} else {
			$out = <<<FLD
<label for="{$this->id}" {$labelAttrs}>{$this->label}</label>
FLD;
		}
		return $out;
	}

	/**
	 * Additional descriptions field
	 *
	 * @return  [type]  [return description]
	 */
	public function renderDescription()
	{
		$out = '';
		if ($this->description && !$this->readOnly) :
			$out .= <<<DESC
				<div class="field-description" id="{$this->id}_description"><small>{$this->description}</small></div>
DESC;
		endif;
		return $out;
	}

	/**
	 * Render the input tag
	 *
	 * @return string
	 */
	public function renderField(): string
	{
		$name = $this->name;
		$id = $this->id;
		if ($this->readOnly) {
			$name = '';
			$id = '';
			$this->tagAttributes['readonly'] = true;
			$this->tagAttributes['disabled'] = true;
		}

		$attrVal = " value=\"{$this->value}\"";
		$out = <<<FLD
<input type="{$this->type}" id="$id" name="{$name}" $attrVal {$this->renderTagAttributes()}>
FLD;
		return $out;
	}

	/**
	 * Check if field allows multiple values to be sent. Needed for validation.
	 *
	 * @return  bool
	 */
	public function isMultiple(): bool
	{
		if ($this->type === 'checkboxgroup' || (isset($this->multiple) && $this->multiple === true)) {
			return true;
		}
		return false;
	}

	/**
	 * In rare cases the label needs to be set after initialisation. For example when using
	 * the same configuration for edit/new. The label of the submit button can then be
	 * changed.
	 *
	 * @param   string  $label
	 *
	 * @return  void
	 */
	public function setLabel(string $label)
	{
		$this->label = $label;
	}

	/**
	 * Return fields errormessage
	 *
	 * @return string
	 */
	public function renderError(): string
	{
		if ($this->readOnly) {
			return '';
		}

		$errorClass = $this->errorClass !== '' ? " class=\"{$this->errorClass}\"" : '';

		if ($this->error !== '') {
			return "<div $errorClass>{$this->error}</div>";
		}

		return '';
	}

	/**
	 * Erzeugt String mit beliebigen weiteren Attributen des Input Tags.
	 *
	 * @return string
	 */
	protected function renderTagAttributes(): string
	{
		$out = '';
		foreach ($this->tagAttributes as $key => $value) {
			$out .= " $key";
			// wenn value auf true gesetzt ist, wird nur das Attribut geschrieben
			if ($value === true) continue;

			if ($key === 'class' &&  $this->error && $this->fieldErrorClass) {
				$value .= " {$this->fieldErrorClass}";
			}
			$out .= "=\"$value\"";
		}

		return $out;
	}

	/**
	 * Create string containing attributes (name="value") of an HTML Tag.
	 *
	 * @param   array  $attributes
	 *
	 * @return string
	 */
	protected function renderHTMLAttributes(array $attributes): string
	{
		$out = '';
		foreach ($attributes as $key => $value) {
			$out .= " $key";
			// wenn value auf true gesetzt ist, wird nur das Attribut geschrieben
			if ($value === true) continue;
			$out .= "=\"$value\"";
		}
		return $out;
	}

	public function addTagattribute(string $key, string $value)
	{
		$this->tagAttributes[$key] = $value;
	}

	/**
	 * If required is set and not set in tagAttributes and/or validation, add it.
	 * If required is not set and set in tagAttributes and/or validation remove it.
	 *
	 * @param   bool  $required
	 * @param   true
	 *
	 * @return  void
	 */
	protected function setRequiredValidation(bool $required = true)
	{
		if ($required === true) {
			if (!str_contains($this->validation, 'required')) {
				if (strlen($this->validation > 0)) {
					$this->validation = 'required|' . $this->validation;
				} else {
					$this->validation = 'required';
				}
			}
			if (!array_key_exists('required', $this->tagAttributes)) {
				$this->tagAttributes['required'] = true;
			}
		} else {
			if (str_contains($this->validation, 'required|')) {
				$this->validation = str_replace('required|', '', $this->validation);
			} elseif (str_contains($this->validation, 'required')) {
				$this->validation = str_replace('required', '', $this->validation);
			}
			if (array_key_exists('required', $this->tagAttributes)) {
				unset($this->tagAttributes['required']);
			}
		}
	}

	/**
	 * If wrappertag is set, wrap input with it.
	 *
	 * @param string $input
	 * @return string
	 */
	protected function wrapInput(string $input): string
	{
		if ($this->wrapperTag === '') {
			return $input;
		}

		$wrapperTagAttributes = $this->renderHTMLAttributes($this->wrapperTagAttributes);
		return "<{$this->wrapperTag}{$wrapperTagAttributes}>$input</{$this->wrapperTag}>";
	}
}
