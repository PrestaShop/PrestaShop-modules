<?php

class Jirafe_Platform_Prestashop14 extends Jirafe_Platform_Ecommerce
{
    /**
     * Get the value of a persistent variable stored in the ecommerce platform
     * @param name the name of the variable
     * @return the value of the variable
     */
    public function get($name)
    {
        return Configuration::get('JIRAFE_'.strtoupper($name));
    }

    /**
     * Set persistently a variable in the ecommerce platform
     * @param name the name of the variable
     * @param value the value in which to set the variable
     */
    public function set($name, $value)
    {
        return Configuration::updateValue('JIRAFE_'.strtoupper($name), $value);
    }

    /**
     * Remove a previously stored persistent variable
     * @param name the name of the variable to delete
     * @return whether the action was successful
     */
    public function delete($name)
    {
        return Configuration::deleteByName('JIRAFE_'.strtoupper($name));
    }

    /**
     * Get Prestashop language information
     * @return string
     */
    public function getLanguage()
    {
        return $this->_getLanguage();
    }

    /**
     * Get Jirafe application information, including app_id and token
     * @return array Jirafe application information
     */
    public function getApplication()
    {
        // First, get the application info from Prestashop
        $data = array(
            'name' => Configuration::get('PS_SHOP_NAME'),
            'url' => Tools::getShopDomain(true)
        );

        // Next, get the Jirafe-specific application info stored in Prestashop
        $token = $this->get('token');
        if (!empty($token)) {
            $data['token'] = $token;
        }
        $appId = $this->get('app_id');
        if (!empty($appId)) {
            $data['app_id'] = $appId;
        }

        return ($data);
    }

    /**
     * Set Jirafe application information into the ecommerce database
     * @param array $app Application information key value pairs
     */
    public function setApplication($app)
    {
        if (!empty($app['app_id'])) {
            $this->set('app_id', $app['app_id']);
        }
        if (!empty($app['token'])) {
            $this->set('token', $app['token']);
        }
    }

    /**
     * Get the Jirafe users, which are the PS employees with their Jirafe tokens
     * @return array A list of Jirafe users that are allowed to see the dashboard
     */
    public function getUsers()
    {
        $users = array();

        // Get the Prestashop Employees
        $employees = $this->_getEmployees();

        // Get the Jirafe specific information about PS Employees
        $jusers = unserialize(base64_decode($this->get('users')));

        foreach ($employees as $employee) {

            // Get the ID of the employee
            $id = $employee['id_employee'];

            // Only active employees can see the dashboard
            if ($employee['active']) {
                // Set the information into the user
                $user = array(
                    'email' => $employee['email'],
                    'first_name' => $employee['firstname'],
                    'last_name' => $employee['lastname']
                );

                // Check to see if there is a token already for this employee - if so, add to the array
                if (!empty($jusers[$employee['email']])) {
                    $user += $jusers[$employee['email']];
                }

                // Add this user to the list of users to return
                $users[] = $user;
            }
        }

        return $users;
    }

    /**
     * Set the Jirafe-specific information about ecommerce platform users into the database
     * @param $users Array of users with Jirafe information
     */
    public function setUsers($users)
    {
        $jUsers = array();

        foreach ($users as $user) {
            if (null !== $user['email']) {
                $email = $user['email'];
                $jUsers[$email]['token'] = $user['token'];
                $jUsers[$email]['email'] = $user['email'];
            }
        }

        $this->set('users', base64_encode(serialize($jUsers)));
    }

    /**
     * Get all site information - both Jirafe specific and general
     * @return array $sites An array of site information as per Jirafe API spec
     */
    public function getSites()
    {
        $sites = array();

        // First, get the general site info from the Prestashop database
        // currently this returns: array('id_shop' => 1, 'name' => 'Default shop')
        // (harcoded in Shop.php)
        $psShops = Shop::getShops();

        // Get the Jirafe specific information about Prestashop sites
        $jsites = unserialize(base64_decode($this->get('sites')));

        foreach ($psShops as $psShop) {
            if (Configuration::get('PS_SHOP_ENABLE')) {
                $shopId = $psShop['id_shop'];
                $site = array();
                $site['external_id'] = $shopId;
                $site['description'] = Configuration::get('PS_SHOP_NAME');
                $site['url'] = 'http://' . Configuration::get('PS_SHOP_DOMAIN');
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

        return $sites;
    }

    /**
     * Set Jirafe specific information from a list of sites
     * @param array $sites An array of site information as per Jirafe API spec
     */
    public function setSites($sites)
    {
        $jsites = array();

        foreach ($sites as $site) {

            // Save Jirafe specific information to the DB
            if (!empty($site['site_id'])) {
                $id = $site['external_id'];
                $jsites[$id]['site_id'] = $site['site_id'];
            }
            if (!empty($site['checkout_goal_id'])) {
                $jsites[$id]['checkout_goal_id'] = $site['checkout_goal_id'];
            }
        }

        $this->set('sites', base64_encode(serialize($jsites)));
    }

    public function getCurrentSiteId()
    {
        $sites = $this->getSites();
        return $sites[0]['site_id'];
    }

    /**
     * Check to see if something has changed, so that we can sync this information with the Jirafe service
     *
     * @params mixed params passed by the prestashop hook
     */
    public function isDataChanged($params)
    {
        $sync = false;

        // Saving employee information
        if (Tools::isSubmit('submitAddemployee')) {
            $employeeId = Tools::getValue('id_employee');
            if (!$employeeId) {
                // Always sync a new user
                $sync = true;
            } else {
                // Otherwise sync if one of the following attributes changes:
                $employee = new Employee($employeeId);
                if (empty($employee) ||
                    $employee->lastname != Tools::getValue('lastname') ||
                    $employee->firstname != Tools::getValue('firstname') ||
                    $employee->email != Tools::getValue('email') ||
                    // $employee->id_profile != Tools::getValue('id_profile') ||
                    // $employee->id_lang != Tools::getValue('id_lang') ||
                    $employee->active != Tools::getValue('active')) {
                        $sync = true;
                    }
            }
        }

        // Changing employee status, or deleting an employee
        if (Tools::isSubmit('statusemployee') || Tools::isSubmit('status') || Tools::isSubmit('deleteemployee')) {
            $sync = true;
        }

        // Saving shop information
        if (Tools::isSubmit('submitShopconfiguration')) {
            if (Tools::getValue('PS_SHOP_NAME') != Configuration::get('PS_SHOP_NAME')) {
                $sync = true;
            }
        }

        // Saving general configuration (enable store, timezone)
        if (Tools::isSubmit('submitGeneralconfiguration')) {
            // This is the list of fields we care about
            if (Tools::getValue('PS_SHOP_ENABLE') != Configuration::get('PS_SHOP_ENABLE')) {
                $sync = true;
            }
            if (Tools::getValue('PS_TIMEZONE') != Configuration::get('PS_TIMEZONE')) {
                $sync = true;
            }
        }

        // Saving currencies
        if (Tools::isSubmit('submitOptionscurrency')) {
            if (Tools::getValue('PS_CURRENCY_DEFAULT') != Configuration::get('PS_CURRENCY_DEFAULT')) {
                $sync = true;
            }
        }

        return $sync;
    }

    public function getPageType()
    {
        // Check if we are on a product page
        $productId = Tools::getValue('id_product');
        if (!empty($productId)) {
            return self::PAGE_PRODUCT;
        }

        // Check if we are on a search page
        if (Tools::isSubmit('submit_search')) {
            return self::PAGE_SEARCH;
        }

        // Check if we are on a category page
        $categoryId = Tools::getValue('id_category');
        if (!empty($categoryId)) {
            return self::PAGE_CATEGORY;
        }

        return self::PAGE_OTHER;
    }

    public function getCategory($params = null)
    {
        $category = array();
        $shop = Context::getContext()->shop;
        $id_category = (int)(Tools::getValue('id_category'));
        $defaultLanguage = (int)(Configuration::get('PS_LANG_DEFAULT'));
        $psCategory = new Category($id_category, $defaultLanguage, $shop->id);
        if (null !== $psCategory) {
            $category['name'] = $psCategory->name;
        }

        return $category;
    }

    public function getProduct($params = null)
    {
        $product = array();
        $shop = Context::getContext()->shop;
        $id_product = (int)(Tools::getValue('id_product'));
        $defaultLanguage = (int)(Configuration::get('PS_LANG_DEFAULT'));
        $psProduct = new Product($id_product, false, $defaultLanguage, $shop->id);
        if (null !== $psProduct) {
            $product['sku'] = $psProduct->id;
            $product['name'] = $psProduct->name;
            $product['price'] = $psProduct->price;
            $product['categories'] = array($psProduct->category);
        }

        return $product;
    }

    public function getSearch($params = null)
    {
        $search = array();

        $keyword = Tools::getValue('search_query');
        if (null !== $keyword) {
            $search['keyword'] = $keyword;
        }

        return $search;
    }

    public function getOrder($params = null)
    {
        $jforder = array();

        if (!empty($params['objOrder'])) {
            $order = $params['objOrder'];
            $psproducts = $order->getProducts();
            $jfproducts = array();
            if (!empty($psproducts)) {
                foreach ($psproducts as $psproduct) {
                    $sku = $psproduct['product_id'];
                    if (!empty($psproduct['product_reference'])) {
                        $sku = $psproduct['product_reference'];
                    }
                    if (!empty($psproduct['product_upc'])) {
                        $sku = $psproduct['product_upc'];
                    }
                    $jfproducts[] = array(
                        'unique_id' => $psproduct['product_id'],
                        'sku' => $sku,  // sku cannot be null - so set it to the product id if it is empty
                        'name' => $psproduct['product_name'],
                        'qty' => $psproduct['product_quantity'],
                        'price' => $psproduct['product_price'],
                        //'categories' => array($psproduct['category'])  // A product can belong to only 1 category
                    );
                }
            }
            $jforder['subtotal'] = $order->total_products; // 1504.18
            $jforder['shipping'] = $order->total_shipping; // 7.00
            $jforder['discount'] = $order->total_discounts; // 0.00
            $jforder['tax'] = ($order->total_products_wt - $order->total_products) + $order->carrier_tax_rate;
            $jforder['total'] = $order->total_paid; // 1571.35
            $jforder['orderid'] = $order->id;
            $jforder['products'] = $jfproducts;
        }

        return $jforder;
    }
    public function getCart($params = null)
    {
        $jfcart = array();

        if (!empty($params['cart'])) {
            $cart = $params['cart'];
            $psproducts = $cart->getProducts();
            $jfproducts = array();
            $total = 0;
            if (!empty($psproducts)) {
                foreach ($psproducts as $psproduct) {
                    $sku = $psproduct['id_product'];
                    if (!empty($psproduct['reference'])) {
                        $sku = $psproduct['reference'];
                    }
                    if (!empty($psproduct['upc'])) {
                        $sku = $psproduct['upc'];
                    }
                    $jfproducts[] = array(
                        'sku' => $sku,  // sku cannot be null - so set it to the product id if it is empty
                        'name' => $psproduct['name'],
                        'qty' => $psproduct['quantity'],
                        'price' => $psproduct['price'],
                        'categories' => array($psproduct['category'])  // A product can belong to only 1 category
                    );
                    $total += $psproduct['total'];
                }
            }
            $jfcart['total'] = $total;
            $jfcart['products'] = $jfproducts;
        }

        return $jfcart;
    }

    public function getVisitor($params = null)
    {
        $visitor = array();
        $tracker = $this->_getTracker($this->getCurrentSiteId());
        return $visitor;
    }


    public function logCartUpdate($cart)
    {
        // Next, log the cart to the server
        $tracker = $this->_getTracker($this->getCurrentSiteId());
        // Set the visitor type
        $tracker->setCustomVariable(1, 'U', self::VISITOR_READY2BUY);
        // Set the order id (or quote ID if we have it)
        if (!empty($cart['orderid'])) {
            $tracker->setCustomVariable(5, 'orderId', $cart['orderid']);
        }
        $tracker->setIp($_SERVER['REMOTE_ADDR']);

        foreach ($cart['products'] as $product) {
            // Get the category - we support only 1 for now
            $category = empty($product['categories'][0]) ? null : $product['categories'][0];
            // Add the item to the tracker
            $tracker->addEcommerceItem(
                $product['sku'],
                $product['name'],
                $category,
                $product['price'],
                $product['qty']
            );
        }

        try {
            $response = $tracker->doTrackEcommerceCartUpdate($cart['total']);
        } catch (Exception $e) {
            // Do nothing for now
        }
    }

    public function logOrder($order)
    {
        // Next, log the cart to the server
        $tracker = $this->_getTracker($this->getCurrentSiteId());
        // Set the visitor type
        $tracker->setCustomVariable(1, 'U', self::VISITOR_CUSTOMER);
        // Set the order id (or quote ID if we have it)
        if (!empty($order['orderid'])) {
            $tracker->setCustomVariable(5, 'orderId', $order['orderid']);
        }
        $tracker->setIp($_SERVER['REMOTE_ADDR']);

        foreach ($order['products'] as $product) {
            // Get the category - we support only 1 for now
            $category = empty($product['categories'][0]) ? null : $product['categories'][0];
            // Add the item to the tracker
            $tracker->addEcommerceItem(
                $product['sku'],
                $product['name'],
                $category,
                $product['price'],
                $product['qty']
            );
        }

        try {
            $response = $tracker->doTrackEcommerceOrder(
                $order['orderid'],
                $order['total'],
                $order['subtotal'],
                $order['tax'],
                $order['shipping'],
                $order['discount']
            );
        } catch (Exception $e) {
            // Do nothing for now
        }
    }

    /**
     * @todo There must be a better way to get Employees from PS than go to the DB direct!
     *
     * @return array a list of PS Employees which will be Jirafe users
     */
    protected function _getEmployees()
    {
        $dbEmployees = Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS('
            SELECT `id_employee`, `email`, `firstname`, `lastname`, `active`, `id_lang`, `id_profile`
            FROM `'._DB_PREFIX_.'employee`
            ORDER BY `id_employee` ASC
            ');

        return $dbEmployees;
    }

    /**
     * Gets the default currency for this store
     *
     * @return string The ISO Currency code
     */
    protected function _getCurrency($shopId = null)
    {
        // fallback to USD is mandatory as currency cannot be null in Jirafe
        $currency = new Currency((int)(Configuration::get('PS_CURRENCY_DEFAULT', null, null, $shopId)));
        if (!$code = $currency->iso_code) {
            $code = 'USD';
        }

        return $code;
    }

    /**
     * Gets the default language
     *
     * @return string The ISO Currency code
     */
    protected function _getLanguage()
    {
        $language = new Language((int)(Configuration::get('PS_LANG_DEFAULT')));
        if (!$code = $language->iso_code) {
            $code = 'en';
        }

        return $code;
    }

    /**
     * Get a unique username based on the email and the Jirafe token for this site.  This is needed because usernames and emails must be unique in Jirafe, and for now, we are not allowing multi-site access in Jirafe.
     *
     * @return string the username generated from the application token and email, so should be unique across all Jirafe sites
     */
    protected function _getUsername($email)
    {
        $token = Configuration::get('JIRAFE_TOKEN');
        return substr($token, 0, 6) . '_' . $email;
    }
}
