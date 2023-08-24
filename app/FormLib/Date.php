<?php

namespace FormLib;

/**
 * Date konvertiert value from Iso Format nach $format. format kann in config gesetzt werden.
 * Der  von außen übergebene Wert muss immer im ISO 8601 Format sein.
 */
class Date extends Input
{
	private $format = 'Y-m-d';
	private $valueIso = '';

	public function __construct($conf)
	{
		$conf['type'] = 'date';
		parent::__construct($conf);
		// Anderes Format kann gesetzt werden.
		if (isset($conf['format'])) {
			$this->format = $conf['format'];
		}
		if (isset($conf['value'])) {
			$this->setValue($this->value);
		}
	}

	/**
	 * Wert von Datum setzen. $value muss im ISO 8601 Format sein.
	 *
	 * @param   mixed  $value
	 */
	public function setValue($value)
	{
		// Value muss ISO 8601sein.
		if ($this->checkIsoDate($value)) {
			$this->valueIso = $value;
			$this->value =  date($this->format, strtotime($value));
		}
	}

	public function getIsoValue(): string
	{
		return '';
	}

	public function getFormat(): string
	{
		return $this->format;
	}

	/**
	 * Override setter to catch iso date. Handle DateTime object, convert ist to Y-m-d String.
	 *
	 * @param string $name
	 * @param mixed $value
	 */
	public function __set(string $name, mixed $value)
	{
		if (!property_exists($this, $name)) {
			return;
		}

		if ($name === 'value') {
			if (is_string($value) && !self::checkIsoDate($value)) {
				return;
			}

			if (is_object($value) && get_class($value) === 'DateTime') {
				$value = $value->format('Y-m-d');
			}
		}

		parent::__set($name, $value);
	}

	/**
	 * Prüft, ob der String $date ein gültiges Datum im ISO Format ist.
	 *
	 * @param   string  $date  Dataum im ISO 8601 Format
	 *
	 * @return  bool
	 */
	private function checkIsoDate(string $date): bool
	{
		$d = explode('-', $date);
		if (count($d) !== 3) {
			return false;
		}

		// Auf gültiges Datum validieren
		return checkdate($d[1], $d[2], $d[0]);
	}
}
