<?php

namespace FormLib;

class File extends Input
{
    private     array $allowedMimeTypes = [];
    protected   bool $required = false;
    private     int $maxSize = 0;
    private     string $uploadTo = '';  // Upload Path
    private     string $fileId = '';

    public function __construct(array $conf)
    {
        parent::__construct($conf);

        if (array_key_exists('maxSize', $conf)) {
            $this->maxSize = filter_var($conf['maxSize'], FILTER_VALIDATE_INT);
        }

        if (array_key_exists('allowedMimeTypes', $conf) && is_array($conf['allowedMimeTypes'])) {
            $this->allowedMimeTypes = $conf['allowedMimeTypes'];
        }

        $this->fileId = $conf['fileId'] ?? '';

        // Uploadpfad setzen
        if (array_key_exists('uploadTo', $conf)) {
            $this->uploadTo = $conf['uploadTo'];
        }

        // GUMP Reset
        $this->filters = '';
    }

    /**
     * Getter für die erlaubten mime-types
     *
     * @return array
     */
    public function getAllowedMimeTypes(): array
    {
        return $this->allowedMimeTypes;
    }

    /**
     * Getter für die maximale Dateigröße in Bytes
     *
     * @return integer
     */
    public function getMaxSize(): int
    {
        return $this->maxSize;
    }

    /**
	 * Render the input tag
	 *
	 * @return string
	 */
	public function renderField(): string
	{
		$out = parent::renderField();

        $out .= $this->renderCurrentFile();
		return $out;
	}

    /**
     * Override label rendering to show already uploaded file
     *
     * @return string
     */
    public function renderLabel(): string
    {
        $out = '';
        $labelAttrs = $this->renderHTMLAttributes($this->labelAttributes);
        if (in_array($this->type, $this->labelAsDiv)) {
            $out = <<<FLD
<div{$labelAttrs}>{$this->label}</div>
FLD;
        } else {
            $out = <<<FLD
<label for="{$this->id}" {$labelAttrs}>{$this->label}</label>
FLD;
        }

        return $out;
    }


    /**
     * If available show currently uploaded file name
     *
     * @return void
     */
    public function renderCurrentFile()
    {

        $f3 = \Base::instance();
        $out = '';
        if ($this->value && $this->fileId !== '') {
            $out .= <<<VALUE
            <p class="current-file mt-3">Aktuelle Datei: <a href="{$f3->alias('file', 'fileid=' . $this->fileId)}" target="_blank" download>{$this->value}</a> <button type="button" class="btn btn-delete-file lh-1 fs-3 text-danger pt-0" title="Datei löschen" data-fileid="{$this->fileId}" onclick="FormUtilities.deleteFile(this)">&times;</button></p>
VALUE;
        }

        return $out;
    }
}
