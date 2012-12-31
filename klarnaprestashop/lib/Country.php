<?php

/**
 * KlarnaCountry
 *
 * PHP Version 5.3
 *
 * @category  Payment
 * @package   KlarnaAPI
 * @author    MS Dev <ms.modules@klarna.com>
 * @copyright 2012 Klarna AB (http://klarna.com)
 * @license   http://opensource.org/licenses/BSD-2-Clause BSD-2
 * @link      http://integration.klarna.com/
 */

/**
 * Country Constants class
 *
 * @category  Payment
 * @package   KlarnaAPI
 * @author    MS Dev <ms.modules@klarna.com>
 * @copyright 2012 Klarna AB (http://klarna.com)
 * @license   http://opensource.org/licenses/BSD-2-Clause BSD-2
 * @link      http://integration.klarna.com/
 */
class KlarnaCountry
{

    /**
     * Country constant for Denmark (DK).<br>
     * ISO3166_DK
     *
     * @var int
     */
    const DK = 59;

    /**
     * Country constant for Finland (FI).<br>
     * ISO3166_FI
     *
     * @var int
     */
    const FI = 73;

    /**
     * Country constant for Germany (DE).<br>
     * ISO3166_DE
     *
     * @var int
     */
    const DE = 81;

    /**
     * Country constant for Netherlands (NL).<br>
     * ISO3166_NL
     *
     * @var int
     */
    const NL = 154;

    /**
     * Country constant for Norway (NO).<br>
     * ISO3166_NO
     *
     * @var int
     */
    const NO = 164;

    /**
     * Country constant for Sweden (SE).<br>
     * ISO3166_SE
     *
     * @var int
     */
    const SE = 209;

    /**
     * Converts a country code, e.g. 'de' or 'deu' to the KlarnaCountry constant.
     *
     * @param string $val country code iso-alpha-2 or iso-alpha-3
     *
     * @return int|null
     */
    public static function fromCode($val)
    {
        switch(strtolower($val)) {
        case 'swe':
        case 'se':
            return self::SE;
        case 'nor':
        case 'no':
            return self::NO;
        case 'dnk':
        case 'dk':
            return self::DK;
        case 'fin':
        case 'fi':
            return self::FI;
        case 'deu':
        case 'de':
            return self::DE;
        case 'nld':
        case 'nl':
            return self::NL;
        default:
            return null;
        }
    }

    /**
     * Converts a KlarnaCountry constant to the respective country code.
     *
     * @param int  $val    KlarnaCountry constant
     * @param bool $alpha3 Whether to return a ISO-3166-1 alpha-3 code
     *
     * @return string|null
     */
    public static function getCode($val, $alpha3 = false)
    {
        switch($val) {
        case KlarnaCountry::SE:
            return ($alpha3) ? 'swe' : 'se';
        case KlarnaCountry::NO:
            return ($alpha3) ? 'nor' : 'no';
        case KlarnaCountry::DK:
            return ($alpha3) ? 'dnk' : 'dk';
        case KlarnaCountry::FI:
            return ($alpha3) ? 'fin' : 'fi';
        case KlarnaCountry::DE:
            return ($alpha3) ? 'deu' : 'de';
        case self::NL:
            return ($alpha3) ? 'nld' : 'nl';
        default:
            return null;
        }
    }

    /**
     * Checks country against currency and returns true if they match.
     *
     * @param int $country  {@link KlarnaCountry}
     * @param int $language {@link KlarnaLanguage}
     *
     * @return bool
     */
    public static function checkLanguage($country, $language)
    {
        switch($country) {
        case KlarnaCountry::DE:
            return ($language === KlarnaLanguage::DE);
        case KlarnaCountry::NL:
            return ($language === KlarnaLanguage::NL);
        case KlarnaCountry::FI:
            return ($language === KlarnaLanguage::FI);
        case KlarnaCountry::DK:
            return ($language === KlarnaLanguage::DA);
        case KlarnaCountry::NO:
            return ($language === KlarnaLanguage::NB);
        case KlarnaCountry::SE:
            return ($language === KlarnaLanguage::SV);
        default:
            //Country not yet supported by Klarna.
            return false;
        }
    }

    /**
     * Checks country against language and returns true if they match.
     *
     * @param int $country  {@link KlarnaCountry}
     * @param int $currency {@link KlarnaCurrency}
     *
     * @return bool
     */
    public static function checkCurrency($country, $currency)
    {
        switch($country) {
        case KlarnaCountry::DE:
        case KlarnaCountry::NL:
        case KlarnaCountry::FI:
            return ($currency === KlarnaCurrency::EUR);
        case KlarnaCountry::DK:
            return ($currency === KlarnaCurrency::DKK);
        case KlarnaCountry::NO:
            return ($currency === KlarnaCurrency::NOK);
        case KlarnaCountry::SE:
            return ($currency === KlarnaCurrency::SEK);
        default:
            //Country not yet supported by Klarna.
            return false;
        }
    }

    /**
     * Get language for supplied country. Defaults to English.
     *
     * @param int $country KlarnaCountry constant
     *
     * @return int
     */
    public static function getLanguage($country)
    {
        switch($country) {
        case KlarnaCountry::DE:
            return KlarnaLanguage::DE;
        case KlarnaCountry::NL:
            return KlarnaLanguage::NL;
        case KlarnaCountry::FI:
            return KlarnaLanguage::FI;
        case KlarnaCountry::DK:
            return KlarnaLanguage::DA;
        case KlarnaCountry::NO:
            return KlarnaLanguage::NB;
        case KlarnaCountry::SE:
            return KlarnaLanguage::SV;
        default:
            return KlarnaLanguage::EN;
        }
    }

    /**
     * Get currency for supplied country
     *
     * @param int $country KlarnaCountry constant
     *
     * @return int|false
     */
    public static function getCurrency($country)
    {
        switch($country) {
        case KlarnaCountry::DE:
        case KlarnaCountry::NL:
        case KlarnaCountry::FI:
            return KlarnaCurrency::EUR;
        case KlarnaCountry::DK:
            return KlarnaCurrency::DKK;
        case KlarnaCountry::NO:
            return KlarnaCurrency::NOK;
        case KlarnaCountry::SE:
            return KlarnaCurrency::SEK;
        default:
            return false;
        }
    }
}
