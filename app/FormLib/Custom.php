<?php
namespace FormLib;
/**
 * Custom fields render a template path defined in the template attribute of the field.
 * Further data needed in the template is passed in the templateVars array.
 *
 * All rendering is done in the template.
 *
 */
class Custom extends Input {
    protected $template = null;
    protected $templateVars = [];

    /**
     * Additional settings for template
     *
     * @param array $conf
     */
    public function __construct ($conf) {
        $this->template = $conf['template'] ?? null;
        if (array_key_exists('templateVars', $conf) && is_array($conf)) {
            $this->templateVars = $conf['templateVars'];
        }
        // if no template is passed, show text input as fallback
        if (!$this->template) {
            $conf['type'] = 'text';
        }
        parent::__construct($conf);
    }

    /**
	 * Override render, Output without label
	 *
	 * @return string
	 */
    public function render ():string {
        $out = $this->renderField();
        return $out;
    }

    /**
	 * Override renderLabel
	 *
	 * @return string
	 */
    public function renderLabel ():string {
        return '';
    }

    /**
	 * Override renderError
	 *
	 * @return string
	 */
    public function renderError():string {
        return '';
    }

    /**
	 * Override renderField
	 *
	 * @return string
	 */
    public function renderField ():string {
        $f3 = \Base::instance();
        $out = '';
        // pass data to template
        $f3->set('templateVars', $this->templateVars);
        $f3->set('formField', $this);
        // render template
        $out =  \Template::instance()->render($this->template);
        // clear data so they do not clutter the hive
        $f3->clear('templateVars');
        $f3->clear('formField');
        return $out;
    }

    /**
     * Add new attributes to template vars. If it is not possible to fill them at startup, because the data is dynamic, this method
     * can be used to append further data
     *
     * @param   array  $data
     *
     * @return  void
     */
    public function appendTemplateVars(array $data)
    {
        $this->templateVars = array_merge($this->templateVars, $data);
    }

    /**
     * Set templateVars. This will overwrite all existing data.
     *
     * @param   array  $templateVars
     *
     * @return  void
     */
    public function setTemplateVars(array $templateVars)
    {
        $this->templateVars = $templateVars;
    }
}
