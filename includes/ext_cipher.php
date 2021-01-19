<?php

class Cipher {

	/** @var string */
	private string $sslkey;

	/** @var false|string Initialization Vector (kann false sein) */
	private false|string $iv;

	/** @var false|int Initialization Vector length (kann false sein) */
	private false|int $ivlen;

	const method = "AES-256-CBC";

	/**
	 * @param   string  $textkey  Key zum verschlüsseln
	 */
	function __construct(string $textkey) {
		$this->ivlen  = openssl_cipher_iv_length(self::method);
		$str          = openssl_random_pseudo_bytes($this->ivlen);
		$this->iv     = substr(hash('sha256', $str), 0, $this->ivlen);
		$this->sslkey = hash('sha256', $textkey);
	}

	/**
	 * @param   string  $input     der zu verschlüsselnde Text
	 * @param   bool    $base64  false um nicht base64 zu encoden
	 *
	 * @return string Der verschlüsselte Text
	 */
	function encrypt(string $input, $base64 = true): string {
		$encrypted = openssl_encrypt(
		  $input,
		  self::method,
		  $this->sslkey,
		  0,
		  $this->iv
		);
		$encrypted = $this->iv . $encrypted;
		return $base64 ? base64_encode($encrypted) : $encrypted;
	}


	/**
	 * @param   string  $input     Der zu entschlüsselde Text
	 * @param   bool    $binarymode
	 * @param   bool    $nobase64  ist der input in base 64 encoded?
	 *
	 * @return false|string
	 */
	function decrypt(string $input, $binarymode = false, $nobase64 = false): false|string {
		if (!$nobase64)
			$input = base64_decode($input);

		$iv        = substr($input, 0, $this->ivlen);
		$input     = substr($input, $this->ivlen);
		$decrypted = openssl_decrypt(
		  $input,
		  self::method,
		  $this->sslkey,
		  0,
		  $iv
		);

		if (!$binarymode)
			$decrypted = rtrim($decrypted, "\0");

		return $decrypted;
	}

}

