<?php

/**
 * @version 1.0
 */
class CS {

    private $siteId;
    private $scheme;
    private $imageDomain = 'c.cnzz.com';

    /**
     * 
     * @param Integer $siteId 绔欑偣ID
     */
    public function __construct($siteId) {
        $this->setAccount($siteId);
        $this->initScheme();
    }

    /**
     * 璁剧疆绔欑偣ID
     * @param type $siteId
     */
    public function setAccount($siteId) {
        $this->siteId = $siteId;
    }

    private function initScheme() {
        $this->scheme = $this->getScheme();
    }

    /**
     * 寰楀埌url涓殑scheme
     * @return String
     */
    private function getScheme() {
        return (isset($_SERVER["HTTPS"]) && ($_SERVER["HTTPS"] !== "off") ? 'https://' : 'http://');
    }

    /**
     * 
     * @return String 鍥炰紶鏁版嵁鐨勮姹傚瓧绗︿覆
     */
    public function trackPageView() {
        return $this->getImageUrl();
    }

    private function getImageUrl() {
        $imageLocation = $this->scheme . $this->imageDomain . '/wapstat.php';
        $referer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
        $query = array();
        array_push($query, 'siteid=' . $this->siteId * 1);
        array_push($query, 'r=' . urlencode($referer));
        array_push($query, 'rnd=' . mt_rand(1, 2147483647));
        $imageUrl = $imageLocation . '?' . implode('&', $query);
        return $imageUrl;
    }

}

function _cnzzTrackPageView($siteId) {
    $cs = new CS($siteId);
    return $cs->trackPageView();
}

?>