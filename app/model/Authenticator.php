<?php

use Nette\Security;

class Authenticator implements Security\IAuthenticator {

    /** @var Nette\Database\Connection */
	private $database;

	public function __construct(Nette\Database\Connection $database) {
		$this->database = $database;
	}

	/**
	 * Performs an authentication.
	 * @return Nette\Security\Identity
	 * @throws Nette\Security\AuthenticationException
	 */
	public function authenticate(array $credentials) {

		[$username, $password] = $credentials;
		$row = $this->database->table('user')->where('username', $username)->fetch();

		if (!$row) {
			throw new Security\AuthenticationException('The username is incorrect.', self::IDENTITY_NOT_FOUND);
		}

		$oldHash = base64_decode($row->password_hash);
		$pswd_salt = $row->password_salt;
		$newHash = static::calculateHash($password, $pswd_salt);
		$result = static::slow_equals($oldHash, $newHash);

		if (!$result) {
			throw new Security\AuthenticationException('The password is incorrect.', self::INVALID_CREDENTIAL);
		}

		unset($row->password_hash);
		unset($row->password_salt);
		return new Security\Identity($row->id, NULL, $row->toArray());
	}

	/**
	 * Computes salted password hash.
	 * @param  string
	 * @return string
	 */
	public static function calculateHash($password, $salt) {

		$algorithm = "sha256";
		$count = 1000;
		$key_length = 24;
		$raw_output = true;
		$password = $password;
		$salt = $salt;

		$algorithm = strtolower($algorithm);
	    if(!in_array($algorithm, hash_algos(), true))
        die('PBKDF2 ERROR: Invalid hash algorithm.');
	    if($count <= 0 || $key_length <= 0)
        die('PBKDF2 ERROR: Invalid parameters.');

	    $hash_length = strlen(hash($algorithm, "", true));
	    $block_count = ceil($key_length / $hash_length);

	    $output = "";
	    for($i = 1; $i <= $block_count; $i++) {
        // $i encoded as 4 bytes, big endian.
        $last = $salt . pack("N", $i);
        // first iteration
        $last = $xorsum = hash_hmac($algorithm, $last, $password, true);
        // perform the other $count - 1 iterations
        for ($j = 1; $j < $count; $j++) {
            $xorsum ^= ($last = hash_hmac($algorithm, $last, $password, true));
        }
        $output .= $xorsum;
	    }

	    //if($raw_output)
			$hash = substr($output, 0, $key_length);
	    //else
	      //  $hash = bin2hex(substr($output, 0, $key_length));

	    return $hash;
	}

	 // Compares two strings $a and $b in length-constant time.
 	public static function slow_equals($a, $b) {
 	    $diff = strlen($a) ^ strlen($b);
 	    for($i = 0; $i < strlen($a) && $i < strlen($b); $i++) {
        $diff |= ord($a[$i]) ^ ord($b[$i]);
 	    }
 	    return $diff === 0;
 	}

	/**
	* Generuje nový salt
	*/
	public static function generateSalt() {
  	return base64_encode(mcrypt_create_iv(24, MCRYPT_DEV_URANDOM));
	}

	/**
	* Generuje nový salt
	*/
	public static function generateToken() {
		$chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
		$count = mb_strlen($chars);
    $length = 24;

    for ($i = 0, $result = ''; $i < $length; $i++) {
      $index = random_int(0, $count - 1);
      $result .= mb_substr($chars, $index, 1);
    }

  	return $result;
	}

	/**
	* Generuje nové heslo
	*/
	public static function generatePassword() {
  	$chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $count = mb_strlen($chars);
    $length = 6;

    for ($i = 0, $result = ''; $i < $length; $i++) {
      $index = random_int(0, $count - 1);
      $result .= mb_substr($chars, $index, 1);
    }

    return $result;
	}
}
