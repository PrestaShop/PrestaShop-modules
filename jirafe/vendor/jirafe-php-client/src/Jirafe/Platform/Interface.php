<?php
interface Jirafe_Platform_Interface
{
    /**
     * Get the value of a variable
     * @param name the name of the variable
     * @return the value of the variable
     */
    public function get($name);

    /**
     * Set a variable to a value
     * @param name the name of the variable
     * @param value the value in which to set the variable
     */
    public function set($name, $value);

    /**
     * Remove a previously saved variable
     * @param name the name of the variable to delete
     * @return whether the action was successful
     */
    public function delete($name);

    public function getApplication();

    public function setApplication($app);

    /**
     * Get current language of the platform
     *
     * @return string
     */
    public function getLanguage();

    /**
     * Get the Jirafe users, which are the PS employees with their Jirafe tokens
     *
     * @return array A list of Jirafe users
     */
    public function getUsers();

    public function setUsers($users);

    /**
     * Get Jirafe specific information from PS
     *
     * @return array $sites An array of site information as per Jirafe API spec
     */
    public function getSites();

    /**
     * Get the current site ID (e.g. the front end site that is currently being viewed by this user)
     *
     * @return int $sites The current site ID
     */
    public function getCurrentSiteId();

    public function getSearch();

    public function getCategory();

    public function getCart();

    public function logCartUpdate($cart);


    /**
     * Set Jirafe specific information from a list of sites
     *
     * @param array $sites An array of site information as per Jirafe API spec
     */
    public function setSites($sites);

    /**
     * Check to see if something is about to change, so that we can sync
     */
    public function isDataChanged($params);

    public function getTag();
}
