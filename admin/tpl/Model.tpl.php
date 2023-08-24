<?php
namespace Models;
use \NHM\SystemHelper as SH;
use \FormLib\Form;

class {routeUC1}Model extends Model
{
	private ${route}Form;
	private ${route}Data = [];

	/**
	 * Get {route} data associated with $id.
	 *
	 * @param   int  $id
	 *
	 * @return  array
	 */
	public function {route}(int $id) : array
	{
		if ($this->{route}Data) {
			return $this->{route}Data;
		}
		$sp = 'EXEC app.sp_Show_{routeUC1} ?';
		$params = [$id];
		$stmt = $this->executeResult($sp, $params);
		$res = [];
		if ($stmt === false) return $res;
		if ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
			return $row;
		}
		return $res;
	}

	/**
	 * Get all {route}s from Database
	 *
	 * @return  array
	 */
	public function {route}s() : array
	{
		$sp   = 'EXEC app.sp_List_{routeUC1}';
		$stmt = $this->executeResult($sp);
		$res = [];
		if ($stmt === false) return $res;
		while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
			$res[] = $row;
		}
		return $res;
	}

	/**
	 * Get {route}s as flat JSON data. Needed for jstree
	 *
	 * @return  string
	 */
	public function {route}sJSON()
	{
		$sp   = 'EXEC app.sp_List_{routeUC1}';
		$params = ["1"];
		$stmt = $this->executeResult($sp);
		$res = [];
		while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
			$res[] = [
				'id' => $row['ID'],
				'parentID' => '#',
				'text' => $row['display_full_name'],
				'data' => ["entryid" => $row['ID']]
			];
		}

		return json_encode($res);
	}

	/**
	 * Find {routeUC1} matching $search
	 *
	 * @param   string  $search
	 *
	 * @return  void
	 */
	public function search{routeUC1}(string $search)
	{
		$sp   = 'EXEC app.sp_Search_{routeUC1} ?';
		$params = [$search];
		$stmt = $this->executeResult($sp, $params);
		$res = [];
		while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
			$res[] = $row;
		}

		return $res;
	}

	/**
	 * Create new {routeUC1}. $data passed as JSON string
	 *
	 * @return  bool
	 */
	public function create(string $dataJSON) : bool
	{
		$sp = 'EXEC app.sp_Create_{routeUC1} ?';
		$params = [$dataJSON];
		return $this->execute($sp, $params);
	}

	/**
	 * Save {route}s data
	 *
	 * @param integer $id
	 * @param string $data	JSON String
	 * @return boolean
	 */
	public function save(int $id, string $data) : bool
	{
		$sp = 'EXEC app.sp_Save_{routeUC1} ?, ?';
		$params = [$id, $data];
		return $this->execute($sp, $params);
	}

	/**
	 * Create edit {route} form. Fill fields with current {routeUC1}s data.
	 *
	 * @return  mixed
	 */
	public function formEdit{routeUC1}(int $id)
	{
		$path = APP_ROOT . $this->f3->get('formConfigPath') . '{route}Edit.json';

		if (!file_exists($path)) {
			return null;
		}

		$config = file_get_contents($path);

		$this->createForm($config);
		$this->{route}Data = $this->{route}($id);
		$this->{route}Form->setFieldValues($this->{route}Data);

		return $this->{route}Form;
	}

	/**
	 * validate Edit {routeUC1} form
	 *
	 * @return  bool
	 */
	public function validateEdit{routeUC1}() : bool
	{
		$data = $_POST;
		$type = null;
		$file = $_FILES['file'] ?? null;

		if ($file) {
			$type = $file['type'];
		}

		// validate essential fields
		$gump = new \GUMP($_SESSION['locale']);

		$gump->validation_rules([
			// validation rules
		]);

		$gump->filter_rules([
			// filter rules
		]);

		$data = $gump->run($data);
		if ($data === false) {
			SH::setAppStatus('success', _tr('errorSave'));
			return false;
		}

		SH::emptyStringToNull($data);
		//////////////// Replace ID ///////////////////////
		$this->save($ID_FROM_INPUT, $data);
		SH::setAppStatus('success', _tr('successSave'));
		return true;
	}

	/**
	 * Creates form Object from $formConf path.
	 *
	 * @param   string  $formConf  path to json file
	 *
	 * @return  void
	 */
	private function createForm(string $formConf)
	{
		$this->{route}Form = new Form(json_decode($formConf, true));
	}

	/**
	 * Create new {route}
	 *
	 * @return void
	 */
	public function create{routeUC1}()
	{
		$sp   = 'EXEC app.sp_Create_{routeUC1}';

		$stmt = $this->executeResult($sp);
		$res = '';
		if ($stmt === false) return $res;
		if ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
			//////////////// Replace ID ///////////////////////
			$res = $row['ID_FROM_SP'];
		}
		return $res;
	}

	/**
	 * Deletes node in thesaurus
	 *
	 * @param integer $id
	 * @return boolean
	 */
	public function del{routeUC1}(int $id): bool
	{
		$sp   = 'EXEC app.sp_Del_{routeUC1} ?';
		$params = [$id];
		$stmt = $this->execute($sp, $params);
		return $stmt;
	}
}
