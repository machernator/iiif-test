<?php
namespace FormLib;

class Submit extends Input {

    /**
	 * Override render, Ausgabe ohne label
	 *
	 * @return string
	 */
    public function render ():string {
        $out = $this->renderField();
        return $out;
    }

    /**
	 * Override renderLabel, Leerstring wird zurück gegeben
	 *
	 * @return string
	 */
    public function renderLabel ():string {
        return '';
    }

    /**
	 * Override renderError, Reset kann keinen Error ausgeben
	 *
	 * @return string
	 */
    public function renderError():string {
        return '';
    }

    /**
	 * Override renderField, input submit wird zurück gegeben
	 *
	 * @return string
	 */
    public function renderField ():string {
        // Input Tag
        $out = <<<OUT
<button type="submit" id={$this->id}"{$this->renderTagAttributes()}>{$this->value}</button>
OUT;
        return $out;
    }
}
