<?php

namespace FormLib;

class Choice extends Input
{
    protected $values = [];
    protected $options = [];
    protected $displayAs = "select"; // select, checkboxgroup, radio
    protected $displayOptions = ['select', 'checkbox', 'radio'];
    protected $displayThreshold = 0; // Show checkbox up to this number of options
    protected $inline = false; // when displaying checkboxes/radios

    /*
        Options array entries can be passed in these ways:

        - array - ['value' => value, 'text' => optionText]
        - string - string is value and optionText
    */

    public function __construct($conf)
    {
        parent::__construct($conf);

        if (array_key_exists('options', $conf) && is_array($conf['options'])) {
            $this->options = $conf['options'];
        }

        if (array_key_exists('displayThreshold', $conf) && filter_var($conf['displayThreshold'], FILTER_VALIDATE_INT)) {
            $this->displayThreshold = $conf['displayThreshold'];
        }

        if (array_key_exists('displayAs', $conf) && in_array($conf['displayAs'], $this->displayOptions)) {
            $this->displayAs = $conf['displayAs'];
        }

        if (array_key_exists('inline', $conf) && $conf['inline'] == true) {
            $this->inline = true;
        }

        $opts = [];
        if (array_key_exists('options', $conf) && !empty($conf['options'])) {
            $opts = $this->filterOptions($conf['options']);
        }

        $this->options = $opts;

        // values can be array or string
        if (array_key_exists('value', $conf)) {
            // wenn array: einfach zuweisen
            if (is_array($conf['value'])) {
                $this->values = $conf['value'];
            }
            // wenn string: string in array kapseln
            elseif (is_string($conf['value'])) {
                $this->values = [$conf['value']];
            }
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
            $out .= $this->renderField() .
                $this->renderError();
        }

        $out = $this->wrapInput($out);
        return $out;
    }

    /**
     * Render either as select, radio group or checkbox group.
     *
     * @return  string
     */
    public function renderField(): string
    {
        // Select can be output as group of checkboxes (always multiple checks)
        if (
            $this->displayAs === 'checkbox' ||
            ($this->multiple === true && count($this->options) <= $this->displayThreshold)
        ) {
            return $this->renderAsCheckboxGroup();
        } elseif ($this->displayAs === 'radio') {
            return $this->renderAsRadio();
        }

        // prevent double labels when not readOnly
        if ($this->readOnly) {
            return $this->renderDescription() . $this->renderAsSelect();
        }
        else {
            return $this->renderLabel() . $this->renderDescription().  $this->renderAsSelect();
        }
    }

    /**
     * Render field as select box
     *
     * @return void
     */
    public function renderAsSelect()
    {
        $name = $this->name;
        $id = $this->id;
        if ($this->readOnly) {
            $name = '';
            $id = '';
            $this->tagAttributes['readonly'] = true;
            $this->tagAttributes['disabled'] = true;
        }
        $out =  $multiple = $this->multiple ? ' multiple' : '';
        $out = <<<OUT
<select name="{$this->name}"  id="{$this->id}" {$this->renderTagAttributes()}{$multiple}>
    {$this->renderOptions()}
</select>
OUT;

        return $out;
    }

    /**
     * Rendering als Checkboxgruppe
     *
     * @return  string
     */
    public function renderAsCheckboxGroup(): string
    {
        if (count($this->options) === 0) {
            return '';
        }
        $out = <<<GROUP
<div class="checkbox-group" role="checkboxgroup" aria-labelledby="{$this->id}_label">
    <div><strong id="{$this->id}_label">{$this->label}</strong></div>
    {$this->renderCheckboxOptions()}
</div>
GROUP;
        return $out;
    }

    /**
     * Rendering als Radio Buttons
     *
     * @return  string
     */
    public function renderAsRadio(): string
    {
        if (count($this->options) === 0) {
            return '';
        }
        $out = <<<GROUP
<div class="radio-group my-4" role="radiogroup" aria-labelledby="{$this->id}_label">
    <div><strong id="{$this->id}_label">{$this->label}</strong></div>
    {$this->renderRadioOptions()}
</div>
GROUP;
        return $out;
    }

    /**
     * Rendering der Select Options
     *
     * @return string
     */
    public function renderOptions(): string
    {
        $out = '';
        foreach ($this->options as $option) {
            $val = $option['value'] ?? '';
            $text = $option['text'] ?? '';
            $selected = in_array($val, $this->values) ? ' selected' : '';
            $out .= "<option value=\"$val\"$selected>$text</option>";
        }
        return $out;
    }

    /**
     * Rendering der Checkboxgruppen Options
     *
     * @return  string
     */
    public function renderCheckboxOptions(): string
    {
        if ($this->readOnly) {
            $name = '';
            $id = '';
            $this->tagAttributes['readonly'] = true;
            $this->tagAttributes['disabled'] = true;
        }
        $out = '';
        // labelAttributes - if class is set, merge with default classes
        $this->labelAttributes['class'] = 'form-check-label fw-normal ' . $this->labelAttributes['class'] ?? '';

        $labelAttrs = $this->renderHTMLAttributes($this->labelAttributes);
        foreach ($this->options as $key => $option) {
            $name = $this->name .  "[]";
            $val = $option['value'] ?? '';
            $text = $option['text'] ?? '';
            $checked = in_array($val, $this->values) ? ' checked' : '';
            $inline = $this->inline ? ' form-check-inline' : '';
            $out .= <<<OUT
<div class="form-check{$inline}">
    <input class="form-check-input" name="{$name}" type="checkbox" value="$val" id="{$this->id}_{$key}"$checked {$this->renderTagAttributes()}>
    <label for="{$this->id}_$key"$labelAttrs>$text</label>
</div>
OUT;
        }
        return $out;
    }

    /**
     * Rendering of Radio Options
     *
     * @return  string
     */
    public function renderRadioOptions(): string
    {
        $name = $this->name;
        if ($this->readOnly) {
            $name = '';
            $id = '';
            $this->tagAttributes['readonly'] = true;
            $this->tagAttributes['disabled'] = true;
        }
        $out = '';
        // if value is array, get first value only
        if (!$this->value && $this->values) {
            $this->value = $this->values[0];
        }

        if (is_array($this->value)) {
            $this->value = $this->value[0];
        }
        foreach ($this->options as $key => $option) {
            $val = $option['value'] ?? '';
            $text = $option['text'] ?? '';
            $checked = $val == $this->value ? ' checked' : '';
            $inline = $this->inline ? ' form-check-inline' : '';
            $out .= <<<OUT
<div class="form-check{$inline}">
    <input class="form-check-input" name="{$this->name}" type="radio" value="{$val}" id="{$this->id}_$key"$checked {$this->renderTagAttributes()}>
    <label class="form-check-label fw-normal" for="{$this->id}_{$key}">$text</label>
</div>
OUT;
        }
        return $out;
    }

    /**
     * Allow options to be set from outside. Each option can be the following formats:
     * string: "value and Option Text"
     * string: value;option Text
     * array: ['value', 'option Text']
     *
     *
     * @param   array  $options
     *
     * @return  void
     */
    public function setOptions(array $options)
    {
        $opts = [];
        foreach ($options as $key => $value) {
            if (is_string($value)) {
                $line = explode(';', $value);
                if (count($line) === 1) {
                    $line = [$line[0], $line[0]];
                }
                $opts[] = $line;
            } elseif (is_array($value) && count($value) === 2) {
                $opts[] = $value;
            }
        }
        $this->options = $opts;
    }

    public function setValues(mixed $values)
    {
        if (!is_array($values)) {
            $this->values = [$values];
        } else {
            $this->values = $values;
        }
    }

    /**
     * Can set validation string to whitelist of options.
     *
     * @return  void
     */
    public function setWhitelistValidation()
    {
        $this->validation = $this->required ? 'required|contains_list,' : 'contains_list,';
        $listVals = [];
        foreach ($this->options as $option) {
            $listVals[] = $option['value'];
        }
        $this->validation .= implode(';', $listVals);
    }

    /**
     * Choice uses multiple values instead of value. Method ensures that values is always an array.
     * A string might be passed but it will be transformed to an array with a single entry
     *
     * @param   string|array  $value
     *
     * @return void
     */
    public function setValue($value)
    {
        if (is_array($value)) {
            $this->values = $value;
        } else {
            $this->values = [$value];
        }
    }

    /**
     * Wird in einer zeile nur ein String übergeben, wird dieser in einen name/value Eintrag gewandelt
     * If an entry contains only a string, convert it to array [value, text]
     *
     * @param   array  $options
     *
     * @return  void
     */
    private function filterOptions(array $options)
    {
        $opts = [];
        foreach ($options as $option) {
            if (!is_array($option)) {
                $opts[] = ['value' => $options, 'text' => $options];
            } else {
                $opts[] = $option;
            }
        }
        return $opts;
    }
}
