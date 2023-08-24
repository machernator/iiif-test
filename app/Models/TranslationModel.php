<?php
namespace Models;
use \NHM\SystemHelper as SH;
use \FormLib\Form;

class TranslationModel extends Model
{

	/**
	 * Get all persons from Database
	 *
	 * @return  array
	 */
	public function listTranslation(string $lang=null):array
	{
		$sp   = 'EXEC app.sp_List_Translation ?';
		$params = [$lang];
		$stmt = $this->executeResult($sp, $params);
		$res = [];
		if ($stmt === false) return $res;
		while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
			$res[$row['ID']] = $row;
		}
		return $res;
	}

	/**
	 * Get all persons from Database
	 *
	 * @return  array
	 */
	public function listTranslationLang(string $lang='de'):array
	{
		$sp   = 'EXEC app.sp_List_Translation ?';
		$params = [$lang];
		$stmt = $this->executeResult($sp, $params);
		$res = [];
		if ($stmt === false) return $res;
		while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
			$res[$row['ID']] = $row['content'];
		}
		return $res;
	}




	/**
	 * Update existing translation
	 *
	 * @param string $id
	 * @param string $de
	 * @param string $en
	 * @param string $cat
	 * @return boolean
	 */
	public function saveTranslation(string $id, string $de, string $en, string $cat):bool
	{
		$sp = 'EXEC app.sp_Save_Translation ?, ?, ?, ?';
		$params = [$id, $de, $en, $cat];
		return $this->execute($sp, $params);
	}

	/**
	 * Create new Translation
	 *
	 * @param string $id
	 * @param string $de
	 * @param string $en
	 * @param string $cat
	 * @return boolean
	 */
	public function createTranslation(string $id, string $de, string $en, string $cat):bool
	{
		$sp = 'EXEC app.sp_Create_Translation ?, ?, ?, ?';
		$params = [$id, $de, $en, $cat];
		return $this->execute($sp, $params);
	}

	/**
	 * Deletes translation
	 *
	 * @param string $id
	 * @return boolean
	 */
	public function delTranslation(string $id): bool
	{
		$sp   = 'EXEC app.sp_Del_Translation ?';
		$params = [$id];
		$stmt = $this->execute($sp, $params);
		return $stmt;
	}
}
