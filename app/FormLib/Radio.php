<?php
namespace FormLib;

class Radio extends Input {
    private $values = [];
    private $inlineClass = false;
    private $wrapperClass = '';

    public function __construct ($conf) {
        parent::__construct($conf);

        if (array_key_exists('values', $conf) &&
            is_array($conf['values']) &&
            count($conf['values']) > 0) {
            $this->values = $conf['values'];
        }

        if (array_key_exists('inlineClass', $conf) && $conf['inlineClass'] === true) {
            $this->inlineClass =  $conf['inlineClass'];
        }
        if (array_key_exists('wrapperClass', $conf)) {
            $this->wrapperClass = $conf['wrapperClass'];
        }
    }

    /**
     * Render Methode ohne explizites Label Tag. Dieses wird für die
     * legend des fieldsets verwendet.
     *
     * @return string
     */
    public function render():string {
        $out = '';
        $out .= $this->renderField();
        $out .= $this->renderError();
        return $out;
    }

    /**
     * Die Radio Buttons werden mit fieldset umschlossen erstellt.
     * @return string
     */
    public function renderField():string {
        // CSS Klasse
        $wrapperClass = join(' ', [$this->wrapperClass, $this->inlineClass]);
        if ($this->wrapperClass !== '' ) {
            $wrapperClass = " class=\"{$wrapperClass}\"";
        }
        $out = "<div{$wrapperClass}><strong id=\"radioGroup_{$this->id}\">{$this->label}</strong>";

        foreach ($this->values as $value => $text) {
            $out .= '<label>';
            $out .= '<input type="radio" name="' .
                    $this->name .
                    '" ' .
                    // TODO: wie soll ID erstellt werden?
                    // 'id="' . $this->name . '_' . $value . '"' .
                    ' value="' .
                    $value . '"';
                    // Vorausgewählt, wenn aktueller Wert dem in der
                    // Konfiguration entspricht
                    if ($value === $this->value) {
                        $out .= ' checked';
                    }

                    $out .= '> ' .
                    $text . '</label>';
        }
        $out .= '</div>';

        return $out;
    }
}
