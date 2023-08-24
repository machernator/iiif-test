<?php

namespace Controllers;

use \NHM\SystemHelper as SH;
use \Models\{routeUC1}Model;


class {routeUC1}Controller extends Controller
{
	private ${route}Model;

	public function __construct($f3, $params)
	{
		parent::__construct($f3, $params);
		$this->{route}Model = new {routeUC1}Model();
	}

	public function index($f3, $params)
	{
		$f3->set('sitetitle', _tr('manage{routeUC1}s'));
		$f3->set('content', $this->content('{route}'));
		$f3->set('contentSidebar', 'sidebar-{route}');
		$f3->set('actionbar', 'actionbar-{route}.html');

		// CSS/JS Files
		$f3->config($f3->get('controllerConfigPath') . '{route}-edit.ini', true);
		$this->setJS($f3->get('controllerjs'));
		$this->appendCSS($f3->get('controllercss'));

		echo $this->renderPage();
	}

	/**
	 * Create new {routeUC1}, redirect to newly created {route} edit page
	 *
	 * @param   object  $f3
	 * @param   array  $params
	 *
	 * @return  void
	 */
	public function add($f3, $params)
	{
		$newId = $this->{route}Model->create{routeUC1}();
		if ($newId > 1) {
			SH::saveAppStatus('success', _tr('successAdd'));
			$f3->reroute("@{route}edit(@{char1}id=$newId)");
		}
		else {
			SH::saveAppStatus('error', _tr('errorAdd'));
			$f3->reroute("@{route}");
		}
	}

	/**
	 * Save {route} data
	 *
	 * @param   object  $f3
	 * @param   array  $params
	 *
	 * @return  void
	 */
	public function save($f3, $params)
	{
		${char1}id = $params['{char1}id'] ?? null;
		if (!filter_var(${char1}id, FILTER_VALIDATE_INT)) return;
		$this->{route}Model->formEdit{routeUC1}(${char1}id);
		$this->{route}Model->validateEdit{routeUC1}();

		$this->edit($f3, $params);
	}

	/**
	 * Edit a single {route} dataset
	 *
	 * @param   object  $f3
	 * @param   array  $params
	 *
	 * @return  void
	 */
	public function edit($f3, $params)
	{
		${char1}id = $params['{char1}id'] ?? null;
		if (!filter_var(${char1}id, FILTER_VALIDATE_INT)) return;

		$f3->set('{char1}id', ${char1}id);
		$f3->set('sitetitle', _tr('edit{routeUC1}'));
		$f3->set('content', $this->content('{route}-edit'));
		$f3->set('contentSidebar', 'sidebar-{route}');
		$f3->set('actionbar', 'actionbar-{route}.html');

		${route}Form = $this->{route}Model->formEdit{routeUC1}(${char1}id);
		${route}Data = $this->{route}Model->{route}(${char1}id);
		$f3->set('{route}Data', ${route}Data);

		$f3->set('form', ${route}Form);
		$f3->config($f3->get('controllerConfigPath') . '{route}-edit.ini', true);
		$this->setJS($f3->get('controllerjs'));
		$this->appendCSS($f3->get('controllercss'));
		echo $this->renderPage();
	}

	public function {route}sJSON($f3, $params)
	{
		$data = $this->{route}Model->{route}sJSON();
		header("Content-type: application/json; charset=utf-8");
		echo $data;
	}

	/**
	 * Delete a {route}
	 *
	 * @param object $f3
	 * @param array $params
	 * @return void
	 */
	public function delete(\Base $f3, array $params) {
		$id = $params['{char1}id'] ?? null;
		$success = $this->{route}Model->del{routeUC1}($id);
		if($success) {
			SH::saveAppStatus('success', _tr('successDelete'));
			$f3->reroute("@{route}");
		}
		else {
			SH::saveAppStatus('error', _tr('errorDelete'));
			$f3->reroute("@{route}edit(@{char1}id=$id)");
		}
	}

	/**
	 * Search {routeUC1}
	 *
	 * @param object $f3
	 * @param array $params
	 * @return void
	 */
	public function search(\Base $f3, array $params)
	{
		$search = $_POST['search'] ?? '';
		$data = $this->{route}Model->search{routeUC1}($search);
		$f3->set('data', $data);
		echo \Template::instance()->render($f3->tpl_components . 'search-{route}.html');
	}
}
