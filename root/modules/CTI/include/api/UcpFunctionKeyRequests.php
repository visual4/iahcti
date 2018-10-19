<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 *
 * @author brafreider
 */
interface UcpFunctionKeyRequests {
    /**
     * This request returns the user’s entire set of function keys 
     * including their current state. The result may be used for further 
     * requests regarding specific keys. 
     * response    functionKeys  List    The user’s keys as a list of FunctionKeyProperties   
     * 
     * @return array functionKeys
     */
    public Function getFunctionKeys();
    
    /**
     * getCallInfoForKey 
     * This request allows the UCI client to query information 
     * about the calls currently being performed or received by the user or 
     * group on a BUSY_LAMP_FIELD key. 
     * @param string $functionKeyId
     * @return Array A list containing entries of CallInfoProperties
     */
    public function getCallInfoForKey($functionKeyId);
    
    /**
     * getContactInfoForKey 
     * This request allows the UCI client to query for the 
     * contact information of the user on a BUSY_LAMP_FIELD key. 
     * 
     * @param string $functionKeyId
     * @return array A set of ContactInfoProperties   
     */
    public function getContactInfoForKey($functionKeyId);
    
    /**
     * getImageForKey 
     * This request retrieves the avatar image on a BUSY_LAMP_FIELD key, 
     * if its imageHash value is not emtpy.  
     * 
     * @param string $functionKeyId
     * @return string image, base64 encoded
     */
    public function getImageForKey($functionKeyId);
    
    
    
}

?>
