<?php
namespace NHM;

class CSRF {
	static private $salt;
	static private $saltBase = 'The NHM Repository Admin Application';
	static private $tokenName = 'formtoken';

	/**
	 * Create CSRF key and save it to Session if it does not exist
	 *
	 * @return string
	 */
	static public function csrf():string {
		if (!array_key_exists(self::$tokenName, $_SESSION) || empty($_SESSION[self::$tokenName])) {
			$_SESSION[self::$tokenName] = self::createCsrfKey();
		}
		// TODO: regenerieren des Token nach einem timeout
		return $_SESSION[self::$tokenName];
	}

	/**
	 * Delete and regnerate currently stored session value
	 *
	 * @return  void
	 */
	static public function flush() {
		unset($_SESSION[self::$tokenName]);
		self::csrf();
	}

	/**
	 * Name of CSRF token.
	 *
	 * @return void
	 */
	static public function getTokenName() {
		return self::$tokenName;
	}

	/**
	 * Check if $token is equal to token  in Session.
	 *
	 * @param string $token
	 * @return boolean
	 */
	static public function isValidToken(string $token):bool {
		return hash_equals($_SESSION[self::$tokenName], $token);
	}

	/**
	 * Erstellt einen 64 Byte langen zufälligen String
	 *
	 * @return string
	 */
	static private function createCsrfKey():string {
		return substr(bin2hex(random_bytes(32) . self::getSalt()), 0, 64);
	}

	/**
	 * Creates Salt string based on self::$saltBase
	 *
	 * @return void
	 */
	static private function getSalt() {
		if (!self::$salt) {
			self::$salt = hash('sha256', self::$saltBase);
		}

		return self::$salt;
	}
}
