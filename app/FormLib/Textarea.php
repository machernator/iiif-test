<?php

namespace FormLib;

class Textarea extends Input
{
    /**
     * Textarea wird ausgegeben
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

        $out = <<<OUT
<textarea name="{$name}" id="{$id}"{$this->renderTagAttributes()}>{$this->value}</textarea>
OUT;
        return $out;
    }
}
