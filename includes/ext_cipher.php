<?php

namespace MGVO;

use UnexpectedValueException;

class Cipher
{

    private string $sslkey;

    /** @var string Initialization Vector */
    private string $iv;

    /** @var int Initialization Vector length */
    private int $ivlen;

    private const METHOD = 'AES-256-CBC';

    /**
     * @param   string  $textkey  Key zum verschlüsseln
     */
    public function __construct(string $textkey)
    {
        $this->ivlen = openssl_cipher_iv_length(self::METHOD);
        if (false === $this->ivlen) {
            throw new UnexpectedValueException('Es konnte nicht mit ' . self::METHOD . ' verschlüsselt werden');
        }

        $str = openssl_random_pseudo_bytes($this->ivlen);
        if (false === $this->ivlen) {
            throw new UnexpectedValueException('Es konnte nicht mit ' . self::METHOD . ' verschlüsselt werden');
        }

        $this->iv = substr(hash('sha256', $str), 0, $this->ivlen);
        if (false === $this->iv) {
            throw new UnexpectedValueException('Es konnte nicht mit ' . self::METHOD . ' verschlüsselt werden');
        }

        $this->sslkey = hash('sha256', $textkey);
    }

    /**
     * @param   string  $input   der zu verschlüsselnde Text
     * @param   bool    $base64  false um nicht base64 zu encoden
     *
     * @return string Der verschlüsselte Text
     */
    public function encrypt(string $input, $base64 = true): string
    {
        $encrypted = openssl_encrypt(
            $input,
            self::METHOD,
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
    public function decrypt(string $input, $binarymode = false, $nobase64 = false): false|string
    {
        if (!$nobase64) {
            $input = base64_decode($input);
        }

        $iv        = substr($input, 0, $this->ivlen);
        $input     = substr($input, $this->ivlen);
        $decrypted = openssl_decrypt(
            $input,
            self::METHOD,
            $this->sslkey,
            0,
            $iv
        );

        if (!$binarymode) {
            $decrypted = rtrim($decrypted, "\0");
        }

        return $decrypted;
    }
}