<?php

namespace Application\OCISchema\Util;

/**
 * Description of Cipher
 *
 * @author Roman
 */
class Cipher {
    
    private static $KEY = '~el:|m[>t,m9lt{Dez!I$|P8bM,=!v6%';
    
    /**
     * Encrypt method
     *
     * This method encrypts the input string using a symmetric algorithm.
     * 
     * @param string $string	a string (plain text).
     *
     * @return string	a encrypted string.
     * 
     * @access static
     */
    public static function encrypt($string) {
        return strtr(rtrim(base64_encode(
            mcrypt_encrypt(
                MCRYPT_RIJNDAEL_256, self::$KEY, $string, MCRYPT_MODE_ECB, mcrypt_create_iv(
                    mcrypt_get_iv_size(
                        MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB
                    ), MCRYPT_RAND
                )
            )
        ), "\0"), '+/', '[}');
    }

    /**
     * Dencrypt method
     *
     * This method dencrypts the input string using a symmetric algorithm.
     * 
     * @param string $string a string (encrypted text).
     *
     * @return string a string (plain text).
     * 
     * @access static
     */
    public static function decrypt($string) {
        return rtrim(mcrypt_decrypt(
            MCRYPT_RIJNDAEL_256, self::$KEY, base64_decode(
                strtr($string, '[}', '+/')
            ), MCRYPT_MODE_ECB, mcrypt_create_iv(
                mcrypt_get_iv_size(
                    MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB
                ), MCRYPT_RAND)
        ), "\0");
    }
    
}