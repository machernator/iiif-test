<?php
namespace NHM;

/**
 * Klasse für die Kommunikation mit einer MS SQL Datenbank
 * Die Ausgaben werden standardmäßig in UTF-8 konvertiert
 */
class DB {
    private static $instance = null;
    private $conn;
    private $error;

    /**
     * Konstruktor
     */
    public function __construct() {
        $f3 = \Base::instance();

        $host =     $f3->get('dbServer');
        $database = $f3->get('dbDatabase');
        $options =  $f3->get('dbOptions');
        $username = $f3->get('dbUser') ?? '';
        $password = $f3->get('dbPassword') ?? '';

        $options['Database'] = $database;
        // Wenn Username und Passwort übergeben wurden, dan SQL Authentifizierung mittels User/Pass
        if ($username || $password) {
            $options["UID"] = $username;
            $options["PWD"] = $password;
        }
        // $options["UID"] = '$username';
        // $options["PWD"] = '$password';
        // sonst Windows-Authentifizierung ohne User/Pass mittels AD
        $this->conn = sqlsrv_connect($host, $options);
        sqlsrv_configure("WarningsReturnAsErrors", 0);
        $this->error = sqlsrv_errors();

        if (!$this->conn) {
            $error = $this->error[0] ?? ['code' => 0, 'message' => ''];
            $msg = '';
            // Login error
            if (array_key_exists('code', $error)) {
                bdump($this->error);
                $msg = "Database connection Error {$error['code']}: Could not connect to database.";
            }
            $_SESSION['error'] = $msg;

            $f3->error(500, $msg);
        }
    }

    /**
     * Nur eine Instanz der Klasse existiert
     */
    public static function instance() : DB {
        self::getInstance();
        return self::$instance;
    }

    /**
     * Erstellt Instanz, falls noch keine exisitiert
     *
     * @return void
     */
    private static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
    }

    /**
     * DB Verbindung
     *
     * @return ressource
     */
    public function getConnection() {
        self::getInstance();
        return $this->conn;
    }

    /**
     * Array mit Fehlermeldungen der DB
     *
     * @return array
     */
    public function getError():array {
        self::getInstance();
        return $this->error;
    }
}
