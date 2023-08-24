<?php
session_start();
define ('APP_ROOT', __DIR__ );
require_once APP_ROOT . '/vendor/autoload.php';
\Tracy\Debugger::enable(mode: false, logDirectory: APP_ROOT . '/logs');

// global translate function
function _tr(string $key) : string
{
	$f3 = \Base::instance();
	return $f3->get('_tr')[$key] ?? $key;
}

// Fat Free Framework Singleton
$f3 = Base::instance();

$f3->set('csrftoken', \NHM\CSRF::csrf());
$f3->set('csrftokenname', \NHM\CSRF::getTokenName());

// paths
$cfgPath = APP_ROOT . '/config/';
$f3->set('configPath', $cfgPath);

$formConfig = APP_ROOT . '/formconfig/';
$f3->set('formConfigPath', $formConfig);

$controllerConfig = APP_ROOT . '/controllerconfig/';
$f3->set('controllerConfigPath', $controllerConfig);

$appPath = $cfgPath . 'app.ini';
$customPath = $cfgPath . 'config.ini';
$routesPath = $cfgPath . 'routes.ini';
$defaultAssestsPath = $controllerConfig . 'default.ini';
if (!file_exists($appPath) || !file_exists($routesPath) || !file_exists($customPath)) {
	die('Configuration File Error');
}

// configs
$f3->config($appPath, true);
$f3->config($customPath, true);
$f3->config($defaultAssestsPath, true);
$f3->config($routesPath);

// language settings
$f3->set('LANGUAGE',$_SESSION['locale'] ?? $f3->get('defaultLanguage'));

$f3->run();
