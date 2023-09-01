<?php
namespace Models;
use \NHM\DB as DB;
use \NHM\SystemHelper as SH;

abstract class Model {
	protected $db;
    protected $conn;
    protected $f3;

    private $sqlErrors = [];
    private $lastSqlError;

	public function __construct() {
        $this->f3 = \Base::instance();
		$this->db = DB::instance();
        $this->conn = $this->db->getConnection();

        /*
            Trigger error page
        */
        if (!$this->conn) {
           $this->f3->error(500, 'Could not connect to Database.');
        }
    }

	/**
     * Statement ausführen, keine Rückgabe
     *
     * @param string $sp    Stored Procedure
     * @param array $params
     * @return bool
     */
    public function execute(string $sp, array $params = []):bool {
        SH::emptyStringToNull($params);
        $stmt = sqlsrv_prepare($this->conn, $sp, $params);
        $result = sqlsrv_execute($stmt);
        if ($result === false) {
            $this->lastSqlError = [];
            $this->checkSqlErrors();
            sqlsrv_free_stmt($stmt);
            return false;
        }

        sqlsrv_free_stmt($stmt);
        return true;
    }

    /**
     * Execute statement, return result set or false
     *
     * @param string $sp    Stored Procedure
     * @param array $params
     * @return mixed $result
     */
    public function executeResult(string $sp, array $params = []) {
        SH::emptyStringToNull($params);
        $stmt = sqlsrv_prepare($this->conn, $sp, $params, array("Scrollable" => SQLSRV_CURSOR_CLIENT_BUFFERED));
        $result = sqlsrv_execute($stmt);
        $this->lastSqlError = [];
        $hasErrors = $this->checkSqlErrors();
        return $hasErrors === true ? false : $stmt;
    }

    /**
     * Checks if the current request created sql errors. Writes error message into SESSION.error.
     *
     * @return boolean
     */
    protected function checkSqlErrors():bool {
        $errors = sqlsrv_errors();
        if (is_array($errors) && count($errors) > 0) {
            $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS,3)[2];
            $error = $this->formatSqlErrors($errors[0]);
            $this->lastSqlError = array_merge($error, $trace);
            $this->sqlErrors[] = $this->lastSqlError;
            $errCode = $error['code'];
            if ($errCode) {
                // Redirect zur Error Page
                $this->f3->set('SESSION.error', $error);
            }
            bdump($this->getSqlErrors());
            return true;
        }
       return false;
    }

    /**
     * Getter für $this->sqlErrors;
     *
     * @return array
     */
    public function getSqlErrors():array {
        return $this->sqlErrors;
    }

    /**
     * Getter für letzten aufgetretenen Sql Error;
     *
     * @return array
     */
    public function getLastSqlError():array {
        return $this->lastSqlError;
    }

    /**
     * Creates readable sql error format
     *
     * @param array $errors
     * @return array
     */
    private function formatSqlErrors(array $errors):array{
        if (count($errors) === 0) return [];

        $tmpMsg = $errors['message'];
        // Teilen des Strings bei custom error messages nötig
        $msg = explode('||', $tmpMsg);

        if (count($msg) === 2) {
            $msg = $msg[1];
        }
        else {
            $msg = $msg[0];
        }

        $split = explode("]", $msg);
        $msg = $split[count($split) - 1];
        return [
            'code' => $errors['code'],
            'message' => $msg
        ];
    }
}
