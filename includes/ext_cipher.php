<?php

namespace MGVO;

use UnexpectedValueException;

const ENCRYPTIONMETHOD = 'AES-256-CBC';

class Cipher
{

    private $sslkey;

    /** @var string Initialization Vector */
    private $iv;

    /** @var int Initialization Vector length */
    private $ivlen;

    /**
     * @param   string  $textkey  Key zum verschlüsseln
     */
    public function __construct($textkey)
    {
        $this->ivlen = openssl_cipher_iv_length(ENCRYPTIONMETHOD);
        if (false === $this->ivlen) {
            throw new UnexpectedValueException('Es konnte nicht mit ' . ENCRYPTIONMETHOD . ' verschlüsselt werden');
        }

        $str = openssl_random_pseudo_bytes($this->ivlen);
        if (false === $this->ivlen) {
            throw new UnexpectedValueException('Es konnte nicht mit ' . ENCRYPTIONMETHOD . ' verschlüsselt werden');
        }

        $this->iv = substr(hash('sha256', $str), 0, $this->ivlen);
        if (false === $this->iv) {
            throw new UnexpectedValueException('Es konnte nicht mit ' . ENCRYPTIONMETHOD . ' verschlüsselt werden');
        }

        $this->sslkey = hash('sha256', $textkey);
    }

    /**
     * @param   string  $input   der zu verschlüsselnde Text
     * @param   bool    $base64  false um nicht base64 zu encoden
     *
     * @return string Der verschlüsselte Text
     */
    public function encrypt($input, $base64 = true)
    {
        $encrypted = openssl_encrypt(
            $input,
            ENCRYPTIONMETHOD,
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
    public function decrypt($input, $binarymode = false, $nobase64 = false)
    {
        if (!$nobase64) {
            $input = base64_decode($input);
        }

        $iv        = substr($input, 0, $this->ivlen);
        $input     = substr($input, $this->ivlen);
        $decrypted = openssl_decrypt(
            $input,
            ENCRYPTIONMETHOD,
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
