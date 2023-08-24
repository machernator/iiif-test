<?php

namespace FormLib;

class CheckBox extends Input
{
    private string $checked = '';
    private bool $inline = false;
    // value is handled differently to other input types. It is always set to 1.
    // An additional hidden input field is created with the same name and value 0.
    // This way, the checkbox is always submitted, even if it is not checked.
    protected string $value = '1';

    public function __construct(array $conf)
    {
        parent::__construct($conf);
        if (array_key_exists('inline', $conf) && $conf['inline'] === true) {
            $this->inline = true;
        }

        // set checked.
        $value = $conf['value'] ?? '0';
        $this->value = '1';
        if (array_key_exists('checked', $conf) && $conf['checked'] === true || $value == 1) {
            $this->checked = ' checked';
        }
    }

    /**
     * Überschriebene render Methode
     *
     * @return string
     */
    public function render(): string
    {
        $out = $this->renderField();
        $out .= $this->renderError();
        $out = $this->wrapInput($out);
        return $out;
    }

    /**
     * Overwrite __parent class method. Generates Bootstrap specific output.     *
     *
     * @return string
     */
    public function renderField(): string
    {
        $inline = $this->inline ? ' form-check-inline' : '';
        if ($this->readOnly) {
            $out = <<<OUT
<div class="form-check{$inline}">
    <input class="form-check-input" type="checkbox"  {$this->renderTagAttributes()} id="{$this->id}"{$this->checked} disabled>
    <label class="form-check-label" for="{$this->id}">{$this->label}</label>
</div>
OUT;
        } else {
            $out = <<<OUT
<div class="form-check{$inline}">
    <input class="form-check-input" name="{$this->name}" type="hidden" value="0">
    <input class="form-check-input" name="{$this->name}" type="checkbox" value="{$this->value}" id="{$this->id}"{$this->checked}>
    <label class="form-check-label" for="{$this->id}">{$this->label}</label>
</div>
OUT;
        }
        return $out;
    }

    /**
     * Setzt Checkbox auf checked. Muss vor Render aufgerufen werden.
     *
     * @param boolean $checked
     * @return void
     */
    public function setChecked(bool $checked)
    {
        if ($checked) {
            $this->checked = ' checked';
        } else {
            $this->checked = '';
        }
    }

    /**
	 * Special handling of vallue attribute. We do not want to change it, but set checked instead.
	 *
	 * @param   string  $name
	 * @param   string $value
	 *
	 * @return  void
	 */
	public function __set(string $name, string $value)
	{
        parent::__set($name, $value);

		switch ($name) {
			case 'value':
				$checked = $value == 1 ? $this->setChecked(true) : $this->setChecked(false);
		}
	}
}
