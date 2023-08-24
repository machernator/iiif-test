<?php
namespace FormLib;

class Reset extends Input {

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
	 * Override renderField, input reset wird zurück gegeben
	 *
	 * @return string
	 */
    public function renderField ():string {
        $out = '<input type="reset" ' .
            'id="' .
            $this->id .
            '" ' .
            'value="' .
            $this->value .
            '"';
        $out .= $this->renderTagAttributes();
        $out .= '>';
        return $out;
    }
}
