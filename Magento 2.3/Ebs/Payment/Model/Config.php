<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * @category   Mage
 * @package    Mage_Secureebs
 * @copyright  Copyright (c) 2008 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
/**
 * Secureebs Configuration Model
 *
 * @category   Mage
 * @package    Mage_Secureebs
 * @name       Mage_Secureebs_Model_Config
 * @author     Magento Core Team <core@magentocommerce.com>
 */

namespace Ebs\Payment\Model;

use \Magento\Framework\App\Config\ScopeConfigInterface;

class Config
{
	protected $_scopeConfig;

	public function __construct(ScopeConfigInterface $scopeConfig)
	{
		$this->_scopeConfig = $scopeConfig;
	}

    /**
     *  Return config var
     *
     *  @param    string Var key
     *  @param    string Default value for non-existing key
     *  @return	  mixed
     */
    public function getConfigData($key, $default=false)
    {
        $key = 'payment/securestandardpayment/'.$key;
        $objectManager =   \Magento\Framework\App\ObjectManager::getInstance();
        $connection = $objectManager->get('Magento\Framework\App\ResourceConnection')->getConnection('\Magento\Framework\App\ResourceConnection::DEFAULT_CONNECTION'); 
        
        $deploymentConfig = $objectManager->get('Magento\Framework\App\DeploymentConfig');
        $prefix = $deploymentConfig->get('db/table_prefix');

        $query = 'SELECT value FROM '.$prefix.'core_config_data WHERE path = "'.$key.'"';
        $result = $connection->fetchAll($query);
        return $result[0]['value'];
    }

    /**
     *  Return Transaction Mode registered in Secure Ebs Admnin Panel
     *
     *  @param    none
     *  @return	  string Transaction Mode
     */
    public function getTransactionMode ()
    {
        return $this->getConfigData('mode');
    }
    
	public function getHashType ()
    {
        return $this->getConfigData('hash_type');
    }
    
	public function getPageId ()
    {
        return $this->getConfigData('page_id');
    }

    /**
     *  Return Secret Key registered in Secure Ebs Admnin Panel
     *
     *  @param    none
     *  @return	  string Secret Key
     */
    public function getSecretKey ()
    {
        return $this->getConfigData('secret_key');
    }


 /**
     *  Return Account ID (general type payments) registered in Secure Ebs Admnin Panel
     *
     *  @param    none
     *  @return	  string Account ID
     */
    public function getAccountId ()
    {
        return $this->getConfigData('account_id');
    }



    /**
     *  Return Store description sent to Secureebs
     *
     *  @return	  string Description
     */
    public function getDescription ()
    {
        $description = $this->getConfigData('description');
        return $description;
    }

    /**
     *  Return new order status
     *
     *  @return	  string New order status
     */
    public function getNewOrderStatus ()
    {
        return $this->getConfigData('order_status');
    }

    /**
     *  Return debug flag
     *
     *  @return	  boolean Debug flag (0/1)
     */
    public function getDebug ()
    {
        return $this->getConfigData('debug_flag');
    }

    /**
     *  Return accepted currency
     *
     *  @param    none
     *  @return	  string Currenc
     */
    public function getCurrency ()
    {
        return $this->getConfigData('currency');
    }

    /**
     *  Return client interface language
     *
     *  @param    none
     *  @return	  string(2) Accepted language
     */
    public function getLanguage ()
    {
        return $this->getConfigData('language');
    }
}