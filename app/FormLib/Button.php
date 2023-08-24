<?php
namespace FormLib;

class Button extends Input {
    public function __construct ($conf) {
        parent::__construct($conf);
    }

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
	 * Override renderField, button Element wird zurück gegeben
	 *
	 * @return string
	 */
    public function renderField ():string {
        $tagAttributes = $this->renderTagAttributes();
        $out = <<<BTN
<button id="{$this->id}" {$this->renderTagAttributes()}>{$this->label}</button>
BTN;
        return $out;
    }
}
