<?php

namespace Controllers;

use GUMP;
use \NHM\SystemHelper as SH;
use \Models\translationModel;
use \Models\ReferencesModel;


class TranslationController extends Controller
{
	private $translationModel;

	public function __construct($f3, $params)
	{
		parent::__construct($f3, $params);
		$this->translationModel = new TranslationModel();
	}

	public function index($f3, $params)
	{
		$f3->set('sitetitle', _tr('manageTranslations'));
		$f3->set('content', $this->content('translation'));
		$f3->set('contentSidebar', 'sidebar-translation');
		$translations = $this->translationModel->listTranslation();
		$f3->set('translations', $translations);

		// CSS/JS Files
		$f3->config($f3->get('controllerConfigPath') . 'translation-edit.ini', true);
		$this->setJS($f3->get('controllerjs'));
		$this->appendCSS($f3->get('controllercss'));

		echo $this->renderPage();
	}

	/**
	 * Create new Person, redirect to newly created person edit page
	 *
	 * @param   object  $f3
	 * @param   array  $params
	 *
	 * @return  void
	 */
	public function create($f3, $params)
	{
		$data = $_POST;
		SH::trimArray($data);
		SH::emptyStringToNull($data);

		$gump = new \GUMP();

		$gump->validation_rules([
			'ID' => 'required|max_len,50',
			'category' => 'required|max_len,50',
			'en' => 'required|max_len,2000',
			'de' => 'required|max_len,2000'
		]);

		$data = $gump->run($data);

		if ($data === false) {
			SH::saveAppStatus('error', _tr('errorNotSaved'));
			$f3->reroute("@translation");
		}

		$newId = $this->translationModel->createTranslation(
			id: $data['ID'],
			de: $data['de'],
			en: $data['en'],
			cat: $data['category']
		);
		if ($newId === true) {
			SH::saveAppStatus('success', _tr('successAdd'));
			$f3->reroute("@translation");
		} else {
			SH::saveAppStatus('error', _tr('errorAdd'));
			$f3->reroute("@translation");
		}
	}

	/**
	 * Save person data
	 *
	 * @param   object  $f3
	 * @param   array  $params
	 *
	 * @return  void
	 */
	public function save($f3, $params)
	{
		$data = $_POST;
		SH::trimArray($data);
		SH::emptyStringToNull($data);

		$gump = new \GUMP();

		$gump->validation_rules([
			'ID' => 'required|max_len,50',
			'category' => 'required|max_len,50',
			'en' => 'required|max_len,2000',
			'de' => 'required|max_len,2000'
		]);

		$data = $gump->run($data);

		if ($data === false) {
			echo json_encode(['errors' => _tr('errorAdd')]);
			return;
		}

		$success = $this->translationModel->saveTranslation(
			id: $data['ID'],
			de: $data['de'],
			en: $data['en'],
			cat: $data['category']
		);
		if ($success === true) {
			echo json_encode(['success' => _tr('successAdd')]);
			return;
		} else {
			echo json_encode(['errors' => _tr('errorNotSaved')]);
			return;
		}
	}



	/**
	 * Delete a person
	 *
	 * @param object $f3
	 * @param array $params
	 * @return void
	 */
	public function delete(\Base $f3, array $params)
	{
		$id = $params['tid'] ?? null;
		$success = $this->translationModel->delTranslation($id);
		if ($success) {
			SH::saveAppStatus('success', _tr('successDelete'));
			$f3->reroute("@translation");
		} else {
			SH::saveAppStatus('error', _tr('errorDelete'));
			$f3->reroute("@translation");
		}
	}

	/**
	 * Search Person
	 *
	 * @param object $f3
	 * @param array $params
	 * @return void
	 */
	public function search(\Base $f3, array $params)
	{
		$search = $_POST['search'] ?? '';
		$data = $this->translationModel->searchPerson($search);
		$f3->set('data', $data);
		echo \Template::instance()->render($f3->tpl_components . 'search-person.html');
	}
}
