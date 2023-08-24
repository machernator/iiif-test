<?php
namespace Models;
use \NHM\SystemHelper as SH;
use \FormLib\Form;

class WizardModel extends Model
{
	private $wizardForm;
	private $wizardData = [];

	/**
	 * Get wizard data associated with $id.
	 *
	 * @param   int  $id
	 *
	 * @return  array
	 */
	public function wizard(int $id) : array
	{
		if ($this->wizardData) {
			return $this->wizardData;
		}
		$sp = 'EXEC app.sp_Show_Wizard ?';
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
	 * Get all wizards from Database
	 *
	 * @return  array
	 */
	public function wizards() : array
	{
		$sp   = 'EXEC app.sp_List_Wizard';
		$stmt = $this->executeResult($sp);
		$res = [];
		if ($stmt === false) return $res;
		while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
			$res[] = $row;
		}
		return $res;
	}

	/**
	 * Get wizards as flat JSON data. Needed for jstree
	 *
	 * @return  string
	 */
	public function wizardsJSON()
	{
		$sp   = 'EXEC app.sp_List_Wizard';
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
	 * Find Wizard matching $search
	 *
	 * @param   string  $search
	 *
	 * @return  void
	 */
	public function searchWizard(string $search)
	{
		$sp   = 'EXEC app.sp_Search_Wizard ?';
		$params = [$search];
		$stmt = $this->executeResult($sp, $params);
		$res = [];
		while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
			$res[] = $row;
		}

		return $res;
	}

	/**
	 * Create new Wizard. $data passed as JSON string
	 *
	 * @return  bool
	 */
	public function create(string $dataJSON) : bool
	{
		$sp = 'EXEC app.sp_Create_Wizard ?';
		$params = [$dataJSON];
		return $this->execute($sp, $params);
	}

	/**
	 * Save wizards data
	 *
	 * @param integer $id
	 * @param string $data	JSON String
	 * @return boolean
	 */
	public function save(int $id, string $data) : bool
	{
		$sp = 'EXEC app.sp_Save_Wizard ?, ?';
		$params = [$id, $data];
		return $this->execute($sp, $params);
	}

	/**
	 * Create edit wizard form. Fill fields with current Wizards data.
	 *
	 * @return  mixed
	 */
	public function formEditWizard(int $id)
	{
		$path = APP_ROOT . $this->f3->get('formConfigPath') . 'wizardEdit.json';

		if (!file_exists($path)) {
			return null;
		}

		$config = file_get_contents($path);

		$this->createForm($config);
		$this->wizardData = $this->wizard($id);
		$this->wizardForm->setFieldValues($this->wizardData);

		return $this->wizardForm;
	}

	/**
	 * validate Edit Wizard form
	 *
	 * @return  bool
	 */
	public function validateEditWizard() : bool
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
		$this->wizardForm = new Form(json_decode($formConf, true));
	}

	/**
	 * Create new wizard
	 *
	 * @return void
	 */
	public function createWizard()
	{
		$sp   = 'EXEC app.sp_Create_Wizard';

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
	public function delWizard(int $id): bool
	{
		$sp   = 'EXEC app.sp_Del_Wizard ?';
		$params = [$id];
		$stmt = $this->execute($sp, $params);
		return $stmt;
	}
}
