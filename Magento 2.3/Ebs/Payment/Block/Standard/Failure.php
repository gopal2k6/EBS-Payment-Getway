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
 * Failure Response from Secureebs
 *
 * @category   Mage
 * @package    Mage_Secureebs
 * @name       Mage_Secureebs_Block_Standard_Failure
 * @author     Magento Core Team <core@magentocommerce.com>
*/

namespace Ebs\Payment\Block\Standard;

use Magento\Framework\View\Element\Template;

class Failure extends Template
{
    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Checkout\Model\Session $checkoutSession,
        array $data = []
    ) {
        $this->checkoutSession = $checkoutSession;
        parent::__construct($context, $data);
        $this->_isScopePrivate = true;
    }
    
    /*protected function _prepareLayout()
    {
 
    }*/
    
    /**
     * @return mixed
     */
    public function getRealOrderId()
    {
        return $this->checkoutSession->getLastRealOrderId();
        //return $this->checkoutSession->getLastOrderId();
    }

    /**
     *  Payment custom error message
     *
     * @return string
     */
    public function getErrorMessage()
    {
        $error = $this->checkoutSession->getErrorMessage();
        return $error;
    }

    /**
     * Continue shopping URL
     *
     * @return string
     */
    public function getContinueShoppingUrl()
    {
        //return $this->getUrl('checkout/cart');
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        return $objectManager->create('\Magento\Store\Model\StoreManagerInterface')->getStore()->getBaseUrl();
    }
}