<?php
namespace FormLib;

class Hidden extends Input {

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
	 * Override renderLabel, Leerstring wird zurÃ¼ck gegeben
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
	 * Override renderField, input reset wird zurÃ¼ck gegeben
	 *
	 * @return string
	 */
    public function renderField ():string {
        $out = "<input type=\"hidden\"
                       id=\"{$this->id}\"
                       name=\"{$this->name}\"
                       value=\"{$this->value}\" {$this->renderTagAttributes()}>";
        return $out;
    }
}
