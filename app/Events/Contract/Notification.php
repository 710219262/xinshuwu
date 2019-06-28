<?php
/**
 * Created by PhpStorm.
 * User: coderxiao
 * Date: 16/04/2019
 * Time: 23:17
 */

namespace App\Events\Contract;

interface Notification
{
    /**
     * @return array
     */
    public function buildPayload();
    
    /**
     * @return integer
     */
    public function getActionUserId();
    
    /**
     * @return integer
     */
    public function getReceiverUserId();
    
    /**
     * @return string
     */
    public function getAction();
    
    /**
     * @return string
     */
    public function getJump();
    
    /**
     * @return string
     */
    public function getTarget();
    
    /**
     * @return string
     */
    public function getReferId();
    
    /**
     * @return string
     */
    public function getCreatedAt();
}
