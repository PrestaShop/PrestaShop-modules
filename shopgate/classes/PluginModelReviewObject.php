<?php

/**
 * User: awesselburg
 * Date: 17.09.14
 * Time: 14:35
 * E-Mail: awesselburg <wesselburg@me.com>
 */
class PluginModelReviewObject
    extends Shopgate_Model_Review
{
    /**
     * set uid
     */
    public function setUid()
    {
        parent::setUid($this->item['id_product_comment']);
    }

    /**
     * set item uid
     */
    public function setItemUid()
    {
        parent::setItemUid($this->item['id_product']);
    }

    /**
     * set score
     */
    public function setScore()
    {
        parent::setScore($this->item['grade']);
    }

    /**
     * set reviewer name
     */
    public function setReviewerName()
    {
        parent::setReviewerName($this->item['customer_name']);
    }

    /**
     * set date
     */
    public function setDate()
    {
        parent::setDate($this->item['date_add']);
    }

    /**
     * set title
     */
    public function setTitle()
    {
        parent::setTitle($this->item['title']);
    }

    /**
     * set text
     */
    public function setText()
    {
        parent::setText($this->item['content']);
    }
}