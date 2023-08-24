<?php

namespace FormLib;

use \FormLib\CSRF;
use GUMP\GUMP;

/**
 * Rendering and Validation of all formfields
 * Dependent on GUMP Validation Library
 *
 * Following attributes can be set
 * - method             post|get, default post
 * - action
 * - encType
 * - id
 * - lang               GUMP Errors language
 * - fields
 */
class Form
{
    private $method = 'post';
    private $action = '';
    private $encType = '';
    private $id = '';
    private $fieldOrder = [];
    private $fieldsets = [];
    private $tagAttributes = [];
    private $inputErrorClass = ''; // CSS class, will be set on all fields, if not stated otherwise
    private $fields = [];
    private $formErrors;
    private $lang = '';
    private $defaultLang = 'en';
    private $fileFields = []; // File fields are handled differently
    private $tokenField;        // Token input object
    private $tokenName = 'formtoken';
    private $textBefore = '';
    private $textAfter = '';
    private $validatedData = [];
    private $readOnly = false;
    private $f3;

    /**
     * Initialize form and its fields
     *
     * @param array $conf
     */
    public function __construct(array $conf)
    {
        $this->f3 = \Base::instance();
        $formConf = $conf['form'];

        $this->action = $formConf['action'] ?? '';
        $this->encType = $formConf['encType'] ?? '';
        $this->inputErrorClass = $formConf['errorClass'] ?? '';
        $this->lang = $formConf['lang'] ?? $this->defaultLang;
        $this->textBefore = $formConf['textBefore'] ?? '';
        $this->textAfter = $formConf['textAfter'] ?? '';

        $this->method = 'post';
        if (array_key_exists('method', $formConf) && strtolower($formConf['method']) === 'get') {
            $this->method = 'get';
        }
        // id
        if (array_key_exists('id', $formConf)) {
            $this->id = $formConf['id'];
        } else {
            die('Error: Form Id must be set');
        }

        // tagAttributes
        if (array_key_exists('tagAttributes', $formConf) && is_array($formConf['tagAttributes'])) {
            $this->tagAttributes = $formConf['tagAttributes'];
        }

        // add default bootstrap classes if needed
        array_key_exists('class', $this->tagAttributes) ?
            $this->tagAttributes['class'] .= ' needs-validation' :
            $this->tagAttributes['class'] = 'needs-validation';

        // fieldOrder
        if (array_key_exists('fieldOrder', $formConf) && is_array($formConf['fieldOrder'])) {
            $this->fieldOrder = $formConf['fieldOrder'];
        }
        // fields
        if (
            array_key_exists('fields', $conf) &&
            is_array($conf['fields']) &&
            count($conf['fields']) > 0
        ) {
            // create CSRF token
            $this->addCSRFToken($conf);
            // $this->fields fill with input objects
            $this->createFields($conf['fields']);
            // enable quick access to token field with getTokenField
            $this->tokenField = $this->getField($this->tokenName);
        }

        // fieldsets
        if (
            array_key_exists('fieldsets', $formConf) &&
            is_array($formConf['fieldsets']) &&
            count($formConf['fieldsets']) > 0
        ) {
            $this->fieldsets = $formConf['fieldsets'];
        }
    }

    /**
     * Creates all form fields and stores them in $this->fields
     *
     * @param array $fields
     * @return void
     */
    private function createFields(array $fields)
    {
        foreach ($fields as $field) {
            $fieldName = $field['name'];
            // add bootstrap classes
            $this->addBootstrapClasses($field);

            switch ($field['type']) {
                    // wenn $field['type'] select ist
                case 'checkboxgroup':
                    $field['displayAs'] = 'checkbox';
                    $field['multiple'] = true;
                    $this->fields[$fieldName] = new Choice($field);
                    break;
                case 'select':
                    $field['displayAs'] = 'select';
                    $this->fields[$fieldName] = new Choice($field);
                    break;
                case 'radio':
                    $field['displayAs'] = 'radio';
                    $field['multiple'] = false;
                    $this->fields[$fieldName] = new Choice($field);
                    break;
                case 'checkbox':
                    $this->fields[$fieldName] = new Checkbox($field);
                    break;
                case 'date':
                    $this->fields[$fieldName] = new Date($field);
                    break;
                case 'hidden':
                    $this->fields[$fieldName] = new Hidden($field);
                    break;
                case 'textarea':
                    $this->fields[$fieldName] = new Textarea($field);
                    break;
                case 'submit':
                    $this->fields[$fieldName] = new Submit($field);
                    break;
                case 'button':
                    $this->fields[$fieldName] = new Button($field);
                    break;
                case 'custom':
                    $this->fields[$fieldName] = new Custom($field);
                    break;
                case 'file':
                    $this->fields[$fieldName] = new File($field);
                    $this->fileFields[$fieldName] = $this->fields[$fieldName];
                    break;
                default:
                    $this->fields[$fieldName] = new Input($field);
            }

            // Check if input error Class is set. If yes, write it to the configuration, if not use $this->inputErrorClass
            if ($this->inputErrorClass !== '' && $this->fields[$field['name']]->errorClass === '') {
                $this->fields[$field['name']]->errorClass = $this->inputErrorClass;
            }
        }
    }

    /**
     * Create hidden field for csrf token erzeugen. Will be generated out of configuration when set.
     * If not CSRF Class creates the token.
     */
    protected function addCSRFToken(array &$conf)
    {
        $token = $conf[$this->tokenName] ?? CSRF::csrf();

        $conf['fields'][] = [
            'name' => $this->tokenName,
            'id' => 'formToken',
            'type' => 'hidden',
            'label' => 'token',
            'required' => true,
            'validation' => 'required',
            'value' => $token
        ];
    }

    /**
     * Render the whole form to a string.
     *
     * @param array $exceptions         array with names of fields that should not be rendered.
     * @return string
     */
    public function render(array $exceptions=[]): string
    {
        $out = $this->renderFormOpen();

        if ($this->textBefore) {
            $out .= $this->textBefore;
        }

        $out .= $this->renderFields($exceptions);

        if ($this->textAfter) {
            $out .= $this->textAfter;
        }
        $out .= '</form>';

        return $out;
    }

    /**
     * Opening form Tag
     *
     * @return string
     */
    public function renderFormOpen(): string
    {
        if ($this->readOnly) {
            return '';
        }
        $out = <<<FRM
<form method="{$this->method}"  action="{$this->action}"  id="{$this->id}" enctype="{$this->encType}" {$this->renderTagAttributes()}>
FRM;
        return $out;
    }

    public function renderFormclose(): string
    {
        if ($this->readOnly) {
            return '';
        }
        return '</form>';
    }

    /**
     * Render all form fields
     *
     * @param array $exceptions         array with names of fields that should not be rendered.
     * @return string
     */
    private function renderFields(array $exceptions=[]): string
    {
        $out = '';

        if (count($this->fieldsets) > 0) {
            $out .= $this->renderFieldsets();
        }
        // wurde eine fieldOrder angegeben?  Es müssen alle Felder darin vorkommen
        elseif (count($this->fieldOrder) === count($this->fields)) {
            foreach ($this->fieldOrder as $fieldName) {
                if (!in_array($fieldName, $exceptions)){
                    $out .= $this->fields[$fieldName]->render();
                }
            }
        } else {
            foreach ($this->fields as $field) {
                if (!in_array($field->name, $exceptions)){
                    $out .= $field->render();
                }
            }
        }

        return $out;
    }

    /**
     * Output fieldset and its fields
     *
     * @return string
     */
    private function renderFieldsets(): string
    {
        $out = '';
        foreach ($this->fieldsets as $fs) {
            // fieldset Tag erstellen
            $out .= '<fieldset';
            if (array_key_exists('tagAttributes', $fs)) {
                $out .= $this->renderTagAttributes($fs);
            }
            $out .= '>';

            // Legend
            if (array_key_exists('legend', $fs)) {
                $out .= '<legend>' . $fs['legend'] . '</legend>';
            }
            // Felder müssen vorhanden sein
            if (!array_key_exists('fields', $fs)) {
                continue;
            }
            foreach ($fs['fields'] as $fieldName) {
                $out .= $this->fields[$fieldName]->render();
            }

            $out .= '</fieldset>';
        }
        return $out;
    }

    /**
     * Returns all fields
     *
     * @return  array
     */
    public function getFields(): array
    {
        return $this->fields;
    }



    /**
     * Returns all fieldsets
     *
     * @return  array
     */
    public function getFieldSets(): array
    {
        return $this->fieldsets;
    }

    /**
     * Render field by name
     *
     * @param string $fieldName
     * @return string
     */
    public function renderField(string $fieldName): string
    {
        if (!array_key_exists($fieldName, $this->fields)) return '';
        return $this->fields[$fieldName]->render();
    }

    /**
     * Return field by name
     *
     * @param string $fieldName
     * @return mixed Input or null
     */
    public function getField(string $fieldName)
    {
        if (!array_key_exists($fieldName, $this->fields)) return null;
        return $this->fields[$fieldName];
    }

    /**
     * Enable quick access to token field. Needed when manually rendering the form.
     * $form->getTokenField()->render();
     *
     * @return void
     */
    public function getTokenField()
    {
        return $this->tokenField;
    }

    /**
     * Return this forms id
     *
     * @return void
     */
    public function getFormId()
    {
        return $this->id;
    }



    /**
     * Return type of field by name
     *
     * @param string $fieldName
     * @return string
     */
    public function getFieldType(string $fieldName): string
    {
        if (!array_key_exists($fieldName, $this->fields)) return '';
        return $this->fields[$fieldName]->type;
    }

    /**
     * Weitere HTML Attribute eines Tags erstellen. Optional. Per default wird
     * $this->tagAttributes verwendet, es kann aber ein beliebiges assoziatives
     * array übergeben werden, z. B. für fieldsets
     *
     *
     * @param array $attr   array mit key/value pairs
     * @return string   optinale Attribute eines HTML Tags
     */
    private function renderTagAttributes(array $attr = []): string
    {
        $attributes = array_merge($this->tagAttributes, $attr);

        $out = '';
        foreach ($this->tagAttributes as $key => $value) {
            $out .= " $key";
            // wenn value auf true gesetzt ist, wird nur das Attribut geschrieben
            if ($value === true) continue;
            $out .= "=\"$value\"";
        }

        return $out;
    }

    /**
     * Validated $data with GUMP Library.
     * On Error the error messages will be set on each field.
     *
     * @param array $data
     * @return bool
     */
    public function isValid(array $data)
    {
        //$this->consolidateData($data);
        if (!CSRF::isValidToken($data[$this->tokenName])) {
            $this->fields[$this->tokenName]->error = 'Invalid form.';
            $this->formErrors[$this->tokenName] = 'Invalid form.';
            return false;
        }

        $gump = new \GUMP($this->lang);
        $rules = $this->fieldValidations();
        $filters = $this->fieldFilters();

        // Set field names to labels
        foreach ($this->fields as $field) {
            $gump->set_field_name($field->name, $field->label);
        }
        $errors = [];
        // enable validation of single fields, multiple value fields and uploads
        $validSingleData = [];
        $validMultipleData = [];
        $validatedFiles = [];

        // Validate fields that allow only single values
        if (!empty($rules['singleValues'])) {
            $gump->validation_rules($rules['singleValues']);
            $data = \GUMP::filter_input($data, $filters['singleValues']);
            // validate form
            $validSingleData = $gump->run($data);
            $errors = $gump->get_errors_array();
        }

        /*
            If a field can receive multiple values (checkboxgroup, attribute multiple = true),
            each of the passed  values will be validated
        */
        if (!empty($rules['multipleValues'])) {
            // get each validation rule
            foreach ($rules['multipleValues'] as $key => $value) {
                // catch checkboxes not sent/empty values. So Required can be validated
                if (!array_key_exists($key, $data)) {
                    $data[$key] = [''];
                } elseif (is_string($data[$key])) {
                    // convert single string to array
                    $data[$key] = [$data[$key]];
                }

                // validate each value by same rule
                foreach ($data[$key] as $index => $value) {
                    $gump = new \GUMP();
                    $gump->validation_rules([$key => $rules['multipleValues'][$key]]);

                    // add filter if set
                    if (array_key_exists($key, $filters['multipleValues'])) {
                        $gump->filter_rules([$key => $filters['multipleValues'][$key]]);
                    }
                    $validData = $gump->run([$key => $value]);

                    if ($validData === false) {
                        $validMultipleData[$key][] = false;
                        $errors[$key] = $gump->get_errors_array()[$key];
                    } else {
                        $validMultipleData[$key][] = $validData[$key];
                    }
                }
            }
        }


        // TODO: Validate  File Uploads
        $validatedFiles = $this->validateFiles();

        // Errors
        if (
            $validSingleData === false ||
            $validMultipleData === false ||
            $validatedFiles === false
        ) {
            $this->formErrors = $errors;

            foreach ($this->fields as $fieldName => $field) {
                if (array_key_exists($fieldName, $this->formErrors)) {
                    $field->error = $errors[$fieldName];
                }

                // set sent value
                if (array_key_exists($fieldName, $data)) {
                    $field->value = $data[$fieldName];
                }
            }

            // array_key_exists('class', $this->tagAttributes) ?
            //     $this->tagAttributes['class'] .= ' was-validated' :
            //     $this->tagAttributes['class'] = 'was-validated';

            return false;
        }

        // Add files to validatedData
        if (count($this->fileFields) > 0) {
            foreach ($this->fileFields as $name => $file) {
                $file = $_FILES[$name];
                $validSingleData[$name] = $file;
            }
        }

        $this->validatedData =  array_merge($validSingleData, $validMultipleData);
        return true;
    }

    /**
     * Getter for validatedData. Will only be set if form is validated successfully.
     *
     * @return  array
     */
    public function getValidatedData()
    {
        return $this->validatedData;
    }

    /**
     * Get form errors
     *
     * @return array
     */
    public function getErrors()
    {
        return $this->formErrors;
    }

    /**
     * Set fields error message by name
     *
     * @param string $fieldName
     * @param string $errorMsg
     * @return void
     */
    public function setFieldError(string $fieldName, string $errorMsg)
    {
        if (array_key_exists($fieldName, $this->fields)) {
            $this->getField($fieldName)->error = $errorMsg;
        }
    }

    /**
     * Komfort Funktion zum nachträglichen Setzen des Values von Feldern
     *
     * @param string $name
     * @param [type] $value
     * @return void
     */
    public function setFieldValue(string $name, $value)
    {
        if (array_key_exists($name, $this->fields)) {
            $this->fields[$name]->value = $value;
        }
    }

    /**
     * Set values of multiple fields. $values has to have the keys set to the names of the
     * formfields.
     *
     * Although $values will be passed, the actual value may be one of the following:
     * - If a field value was passed by the REQUEST, always write this value
     * - If not and $values passed a value, write this value
     * - If none of the above exist, the value from the configuration is set
     *
     * @param array $values
     * @return void
     */
    public function setFieldValues(array $values = [])
    {
        $formVals = [];
        if (strtolower($this->method) === 'post' && !empty($_POST)) {
            $formVals = $_POST;
        } elseif (strtolower($this->method) === 'get' && !empty($_GET)) {
            $formVals = $_GET;
        }

        foreach ($this->fields as $name => $field) {
            $newValue = null;

            // Check if passed value or value from Request will be set
            if (array_key_exists($name, $formVals) && is_string($formVals[$name])) {
                $newValue = trim($formVals[$name]);
            } elseif (array_key_exists($name, $values)) {
                $newValue = $values[$name];
            }

            if ($newValue !== null) {
                switch ($field->type) {
                    case 'checkbox':
                        if ($newValue == $field->value) {
                            $field->setChecked(true);
                        }
                        break;
                    case 'select':
                        $field->setValues($newValue);
                        break;
                    case 'radio':
                        $field->setValues($newValue);
                        break;
                    default:
                        $field->value = $newValue;
                }
            }
        }
    }

    /**
     * Set options of checkboxgroup and select
     *
     * @param   array  $fieldOptions
     *
     * @return  void
     */
    public function setFieldOptions(array $fieldOptions)
    {
        foreach ($fieldOptions as $fieldName => $options) {
            $field = $this->getField($fieldName);

            if ($field) {
                $type = $field->type;
                if (in_array($type, ['checkboxgroup', 'select'])) {
                    $field->setOptions($options);
                }
            }
        }
    }

    /**
     * Create validations Array needed for GUMP. Reads all fields and returns validation instructions
     * as GUMP readable array.
     * @return array
     */
    private function fieldValidations(): array
    {
        $rules = [
            'singleValues' => [],
            'multipleValues' => []
        ];

        // get rules of each field, if they are set.
        foreach ($this->fields as $fieldName => $field) {
            $fieldRules = $field->validation;
            if ($fieldRules && $field->isMultiple()) {
                $rules['multipleValues'][$fieldName] = $fieldRules;
            } elseif ($fieldRules) {
                $rules['singleValues'][$fieldName] = $fieldRules;
            }
        }

        return $rules;
    }

    /**
     * Erstellt Array mit Filtern für GUMP. Es werden die Filter
     * jedes einzelnen Feldes ausgelesen und in einem Array zusammengefasst.
     * @return array
     */
    private function fieldFilters(): array
    {
        $filters = [
            'singleValues' => [],
            'multipleValues' => []
        ];

        // Filter aus jedem einzelnen Feld auslesen
        foreach ($this->fields as $fieldName => $field) {
            // Files werden nicht per GUMP validiert und sind somit ausgenommen
            if ($field->type === 'file') continue;


            $filter = $field->filters;

            if ($filter && $field->isMultiple()) {
                $filters['multipleValues'][$fieldName] = $filter;
            } elseif ($filter && !$field->isMultiple()) {
                $filters['singleValues'][$fieldName] = $filter;
            }
        }

        return $filters;
    }

    /**
     * Validate required files
     *
     * @return boolean
     */
    private function validateFiles(): bool
    {
        $filesOk = true;
        foreach ($this->fileFields as $name => $file) {
            if ($file->required === true && array_key_exists($name, $_FILES) && $_FILES[$name]['error'] !== UPLOAD_ERR_OK) {
                $file->error = 'Geben Sie eine Datei zum Hochladen an';
                $filesOk = false;
            }
        }

        return $filesOk;
    }

    /**
     * Add bootstrap classes to field configuration. Makes it easier to use bootstrap.
     *
     * @param array $conf
     * @return void
     */
    private function addBootstrapClasses(&$conf)
    {
        // input
        $bsClass = '';
        switch ($conf['type']) {
            case 'select':
                $bsClass = 'form-select';
                break;
            case 'radio':
            case 'checkbox':
                $bsClass = 'form-check-input';
                $conf['wrapperClass'] = 'form-check';
                break;
            case 'submit':
            case 'button':
                $bsClass = $conf['tagAttributes']['class'] ?? 'btn btn-secondary d-inline-block mt-4';
                break;
            case 'hidden':
                return;
            default:
                $bsClass = 'form-control';
        }

        // tagAttributes
        if (!array_key_exists('tagAttributes', $conf)) {
            $conf['tagAttributes'] = [];
        }

        if (!array_key_exists('class', $conf['tagAttributes'])) {
            $conf['tagAttributes']['class'] = $bsClass;
        } else {
            $conf['tagAttributes']['class'] .= " $bsClass";
        }
        // errors
        $conf['errorClass'] = 'invalid-feedback';
        $conf['fieldErrorClass'] = 'is-invalid';

        // Label
        $labelClass = 'form-label';

        if (!array_key_exists('labelAttributes', $conf)) {
            $conf['labelAttributes'] = [];
        }

        if (!array_key_exists('class', $conf['labelAttributes'])) {
            $conf['labelAttributes']['class'] = $labelClass;
        } else {
            $conf['labelAttributes']['class'] .= " $labelClass";
        }
    }

    /**
     * Sets templateVars for custom fields. The index of the array entry must be the name of the field.
     *
     * @param array $templateVars
     * @return void
     */
    public function setTemplateVars(array $templateVars)
    {
        foreach ($templateVars as $name => $value) {
            $field = $this->getField($name);
            if ($field && $field->type === 'custom') {
                $field->setTemplateVars($templateVars);
            }
        }
    }

    /**
     * Set all fields to readOnly
     *
     * @param boolean $readOnly
     * @return void
     */
    public function setReadOnly(bool $readOnly)
    {
        $this->readOnly = $readOnly;
        foreach ($this->fields as $field) {
            $field->readOnly = $readOnly;
            if ($readOnly) {
                $field->addTagAttribute('disabled', '');
            }
        }
    }
}
