<?php

class Jirafe_Platform_Prestashop15 extends Jirafe_Platform_Prestashop14
{
    /**
     * Get the value of a persistent variable stored in the ecommerce platform
     * @param name the name of the variable
     * @return the value of the variable
     */
    public function get($name)
    {
        return Configuration::getGlobalValue('JIRAFE_'.strtoupper($name));
    }

    /**
     * Set persistently a variable in the ecommerce platform
     * @param name the name of the variable
     * @param value the value in which to set the variable
     */
    public function set($name, $value)
    {
        return Configuration::updateGlobalValue('JIRAFE_'.strtoupper($name), $value);
    }

    /**
     * Get all site information - both Jirafe specific and general
     * @return array $sites An array of site information as per Jirafe API spec
     */
    public function getSites()
    {
        $sites = array();

        // First, get the general site info from the Prestashop database
        Shop::cacheShops(true); // we must refresh cache to get last insertion
        $psShops = Shop::getShops();

        // Get the Jirafe specific information about Prestashop sites
        $jsites = unserialize(base64_decode($this->get('sites')));

        if (!empty($psShops)) {
            foreach ($psShops as $psShop) {
                if ($psShop['active']) {
                    $shopId = $psShop['id_shop'];
                    $site = array();
                    $site['external_id'] = $shopId;
                    $site['description'] = $psShop['name'];
                    $site['url'] = 'http://' . $psShop['domain'] . $psShop['uri'];
                    $site['timezone'] = Configuration::get('PS_TIMEZONE');
                    $site['currency'] = $this->_getCurrency($shopId);

                    if (!empty($jsites[$shopId])) {
                        $site += $jsites[$shopId];
                    }

                    // new sites in prestashop are created without url
                    // api require a valid unique url
                    if ($site['url'] === 'http://') {
                        $site['url'] = 'http://example.' . md5(time() + $site['external_id']) . '.com';
                    }

                    // Add the site to the list of sites to return
                    $sites[] = $site;
                }
            }
        }

        return $sites;
    }

    public function getCurrentSiteId()
    {
        $currentShopId = Shop::getContext('shop');
        $jsites = unserialize(base64_decode($this->get('sites')));

        return $jsites[$currentShopId]['site_id'];
    }

    /**
     * Check to see if something has changed, so that we can sync this information with the Jirafe service
     *
     * @params mixed params passed by the prestashop hook
     */
    public function isDataChanged($params)
    {
        if (array_key_exists('object', $params)) {
            $object = $params['object'];

            if ($object instanceof Employee || $object instanceof Shop) {
                return true;
            }
        }

        // Saving general configuration (enable store, timezone)
        if (Tools::isSubmit('submitOptionsconfiguration')) {
            // This is the list of fields we care about
            if (Tools::getValue('PS_SHOP_ENABLE') != Configuration::get('PS_SHOP_ENABLE')) {
                return true;
            }
            if (Tools::getValue('PS_TIMEZONE') != Configuration::get('PS_TIMEZONE')) {
                return true;
            }
        }

        // Saving currencies
        if (Tools::isSubmit('submitOptionscurrency')) {
            if (Tools::getValue('PS_CURRENCY_DEFAULT') != Configuration::get('PS_CURRENCY_DEFAULT')) {
                return true;
            }
        }

        return false;
    }

}
