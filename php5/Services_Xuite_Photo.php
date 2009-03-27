<?php

/**
 * Services_Xuite_Photo, a PHP5 API for accessing Xuite Photo
 *
 * PHP Version 5
 *
 * LICENSE:
 *  Copyright (c) 2009 Xuite Photo.
 *   
 *  Permission is hereby granted, free of charge, to any person
 *  obtaining a copy of this software and associated documentation
 *  files (the "Software"), to deal in the Software without
 *  restriction, including without limitation the rights to use,
 *  copy, modify, merge, publish, distribute, sublicense, and/or sell
 *  copies of the Software, and to permit persons to whom the
 *  Software is furnished to do so, subject to the following
 *  conditions:
 *  
 *  The above copyright notice and this permission notice shall be
 *  included in all copies or substantial portions of the Software.
 *  
 *  THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
 *  EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES
 *  OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
 *  NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT
 *  HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY,
 *  WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
 *  FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR
 *  OTHER DEALINGS IN THE SOFTWARE.
 * 
 * @category    Services
 * @package     Services_Xuite_Photo
 * @author      Lin-Chieh Shangkuan (ericsk@cht.com.tw)
 * @link        http://photo.xuite.net/
 * @link        http://photo.xuite.net/_dev
 */

class Services_Xuite_Photo {

    /**
     * The XMLRPC endpoint URL of Xuite Photo Service
     */
    public $endpointUrl = 'http://photo.xuite.net/_service/xmlrpc';
    
    /**
     * Public API key
     */
    public static $publicKey = '';
    
    /**
     * Private API Key
     */
    public static $privateKey = '';
    
    
    /**
     * the service instance
     */
    protected static $_service = null;
    
    
    /**
     * Do not use new to create an instance, Use 
     * Services_Xuite_Photo::getService().
     */
    private function __construct() {
    }
    
    
    /**
     * Get the service instance
     */
    public static function getService() {
        if ($_service == null) {
            $_service = new Services_Xuite_Photo();
        }
        return $_service;
    }
        
    /**
     * Create the API signature and insert the api_sig parameter automatically.
     *
     * @param $methodName The name of invoking method.
     * @param $params The parameters array.
     */
    public function createSignature($methodName, &$params = array()) {
        // If the params doesn't contain the api_key key, add it.
        if (!array_key_exists('api_key', $params)) {
            $params['api_key'] = Services_Xuite_Photo::$publicKey;
        }
        
        if (!array_key_exists('method', $params)) {
            $params['method'] = $methodName;
        }
        
        // sort the parameters and combine them
        ksort($params);
        $rawStr = Services_Xuite_Photo::$privateKey;
        foreach ($params as $key => $value) {
            $rawStr .= $key.$value;
        }
        
        // Use SHA-256 to hash the raw string
        $params['api_sig'] = hash('sha256', $rawStr);
        unset($params['method']);
    }
    
    /**
     * Create an album
     *
     * @param $title The title of the album
     * @param $desc The description of the album
     * @param $authToken The authorized token
     * @return {
     *    'album_id' => The ID of the created album
     * }
     */
    public function createAlbum($title, $desc, $authToken) {
        return $this->_invokeMethod('xuite.photo.album.create', array(
            'title' => $title,
            'desc' => $desc,
            'auth_token' => $authToken
        ));
    }
    
    
    /**
     * Get the frob
     * 
     * @return The frob value.
     */
    public function getFrob() {
        return $this->_invokeMethod('xuite.photo.auth.getFrob');
    }
    
    /**
     * Get Auth Token
     */
    public function getToken($frob) {
        return $this->_invokeMethod('xuite.photo.auth.getToken', array('frob' => $frob));
    }
    
    /**
     * Get user's album list
     */
    public function getAlbums($authToken) {
        return $this->_invokeMethod('xuite.photo.user.getAlbums', array('auth_token' => $authToken));
    }
    
    /**
     * Get Xuite Photo usages
     */
    public function getQuota($authToken) {
        return $this->_invokeMethod('xuite.photo.user.getQuota', array('auth_token' => $authToken));
    }
    

    /**
     * Invoke API method helper
     *
     * @param $methodName The name of invoking method.
     * @param $params The parameters for invoking method.
     * @return The response of invoking the method.
     */
    protected function _invokeMethod($methodName, $params = array()) {
        Services_Xuite_Photo::createSignature($methodName, $params);
        $requestBody = xmlrpc_encode_request($methodName, $params);
        
        $context = stream_context_create(array(
            'http' => array(
                'method' => 'POST',
                'header' => 'Content-Type: text/xml; charset=utf-8',
                'content' => $requestBody
            )
        ));
        
        $rawResponse = file_get_contents($this->endpointUrl, false, $context);
        return xmlrpc_decode($rawResponse);
    }
}
