<?php
namespace app\lib;

class Encryption
{
    private $key;

    const CYPHER = MCRYPT_RIJNDAEL_256;
    const MODE   = MCRYPT_MODE_CBC;

    function __construct($key = 'defaultkeythatwillnotbeusedatall')
    {
        $this->key = $key;
    }

    public function encrypt($plaintext)
    {
        $plaintext = gzcompress($plaintext);

        $td = mcrypt_module_open(self::CYPHER, '', self::MODE, '');
        $iv = mcrypt_create_iv(mcrypt_enc_get_iv_size($td), MCRYPT_RAND);
        mcrypt_generic_init($td, $this->key, $iv);
        $crypttext = mcrypt_generic($td, $plaintext);
        mcrypt_generic_deinit($td);
        return $iv.$crypttext;
    }

    public function decrypt($crypttext)
    {
        $plaintext = '';
        $td        = mcrypt_module_open(self::CYPHER, '', self::MODE, '');
        $ivsize    = mcrypt_enc_get_iv_size($td);
        $iv        = substr($crypttext, 0, $ivsize);
        $crypttext = substr($crypttext, $ivsize);
        if ($iv)
        {
            mcrypt_generic_init($td, $this->key, $iv);
            $plaintext = mdecrypt_generic($td, $crypttext);
        }

        $plaintext = gzuncompress($plaintext);

        return $plaintext;
    }
}
