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
 * Secureebs Standard Model
 *
 * @category   Mage
 * @package    Mage_Secureebs
 * @name       Mage_Secureebs_Model_Standard
 * @author     Magento Core Team <core@magentocommerce.com>
 */

namespace Ebs\Payment\Model;

class Standard extends \Magento\Payment\Model\Method\AbstractMethod
{
    protected $_code  = 'secureebs_standard';
    protected $_formBlockType = 'secureebs/standard_form';

    protected $_isGateway               = false;
    protected $_canAuthorize            = true;
    protected $_canCapture              = true;
    protected $_canCapturePartial       = false;
    protected $_canRefund               = false;
    protected $_canVoid                 = false;
    protected $_canUseInternal          = false;
    protected $_canUseCheckout          = true;
    protected $_canUseForMultishipping  = false;

    protected $_order = null;
    protected $_checkoutSession = null;


    /**
     * Get Config model
     *
     * @return object Mage_Secureebs_Model_Config
     */
    public function getConfig()
    {
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		return $objectManager->create('\Ebs\Payment\Model\Config');
    }

    /**
     * Payment validation
     *
     * @param   none
     * @return  Mage_Secureebs_Model_Standard
     */
    public function validate()
    {
        parent::validate();
        $paymentInfo = $this->getInfoInstance();
        if ($paymentInfo instanceof Mage_Sales_Model_Order_Payment) {
            $currency_code = $paymentInfo->getOrder()->getBaseCurrencyCode();
        } else {
            $currency_code = $paymentInfo->getQuote()->getBaseCurrencyCode();
        }
     
        return $this;
    }

    /**
     * Capture payment
     *
     * @param   Varien_Object $orderPayment
     * @return  Mage_Payment_Model_Abstract
     */
    public function capture (\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        $payment->setStatus(self::STATUS_APPROVED)
            ->setLastTransId($this->getTransactionId());

        return $this;
    }

    /**
     *  Returns Target URL
     *
     *  @return	  string Target URL
     */
    public function getSecureebsUrl ()
    {
        return 'https://secure.ebs.in/pg/ma/sale/pay/';
    }

    /**
     *  Return URL for Secureebs success response
     *
     *  @return	  string URL
     */
    protected function getSuccessURL ()
    {
        return Mage::getUrl('secureebs/standard/success', array('_secure' => true));
    }

    /**
     *  Return URL for Secureebs notification
     *
     *  @return	  string Notification URL
     */
    protected function getNotificationURL ()
    {
        return Mage::getUrl('secureebs/standard/notify', array('_secure' => true));
    }

    /**
     *  Return URL for Secureebs failure response
     *
     *  @return	  string URL
     */
    protected function getFailureURL ()
    {
        return Mage::getUrl('secureebs/standard/failure', array('_secure' => true));
    }

    /**
     *  Form block description
     *
     *  @return	 object
     */
    public function createFormBlock($name)
    {
        $block = $this->getLayout()->createBlock('secureebs/form_standard', $name);
        $block->setMethod($this->_code);
        $block->setPayment($this->getPayment());
        return $block;
    }

    /**
     *  Return Order Place Redirect URL
     *
     *  @return	  string Order Redirect URL
     */
    public function getOrderPlaceRedirectUrl()
    {
        return Mage::getUrl('secureebs/standard/redirect');
    }

	/**
	* Retrieve current order
	*
	* @return \Magento\Sales\Model\Order
	*/
	public function getOrder()
	{
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$order = $objectManager->create('\Magento\Sales\Model\Order');
		$checkoutSession = $objectManager->create('\Magento\Checkout\Model\Session');
		$orderId = $checkoutSession->getLastOrderId();
		return $order->load($orderId);
	}
	
    /**
     *  Return Standard Checkout Form Fields for request to Secureebs
     *
     *  @return	  array Array of hidden form fields
     */
    public function getStandardCheckoutFormFields ()
    {
        $order = $this->getOrder();

        $billingAddress = $order->getBillingAddress();
		$shippingAddress = $order->getShippingAddress();
        
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$countryObj = $objectManager->create('\Magento\Directory\Model\Country');
		$billingAddressCountryCode = $countryObj->loadByCode($billingAddress->getCountryId())->getData('iso3_code');
		$shippingAddressCountryCode = $countryObj->loadByCode($shippingAddress->getCountryId())->getData('iso3_code');

        $billingStreet = trim($billingAddress->getStreetLine(1). "  ".$billingAddress->getStreetLine(2));
        $shippingStreet = trim($shippingAddress->getStreetLine(1). "  ".$shippingAddress->getStreetLine(2));
        
        if ($this->getConfig()->getDescription()) {
            $transDescription = $this->getConfig()->getDescription();
        } else {
            $transDescription = 'Order #'. $order->getRealOrderId();
        }

        if ($order->getCustomerEmail()) {
            $email = $order->getCustomerEmail();
        } elseif ($billingAddress->getEmail()) {
            $email = $billingAddress->getEmail();
        } else {
            $email = '';
        }

		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$baseUrl = $objectManager->create('\Magento\Store\Model\StoreManagerInterface')->getStore()->getBaseUrl();

        $fields = array(       	
        	'channel'		=> '0',
			'account_id'	=> $this->getConfig()->getAccountId(), 
			//'return_url'	=> $this->getUrl('secureebs/standard/success',array('_secure' => true)),
			'return_url'	=> $baseUrl .'ebs/standard/success',
        	'reference_no'	=> $order->getRealOrderId(),
        	'amount' 		=> number_format($order->getBaseGrandTotal(), 2, '.', ''),
        	'currency' 		=> "INR",
			'description'	=> $transDescription,
       		'name'			=> $billingAddress->getFirstname()." ".$billingAddress->getLastname(),
        	'address'		=> $billingAddress->getStreetLine(1),
        	'city'			=> $billingAddress->getCity(),
        	'state'			=> $billingAddress->getRegion(),
        	'postal_code'	=> $billingAddress->getPostcode(),
        	'country'		=> $billingAddressCountryCode,
        	'phone'			=> $billingAddress->getTelephone(),
        	'email'			=> $email,
			'ship_name'		=> $shippingAddress->getFirstname()." ".$shippingAddress->getLastname(),
			'ship_address'	=> $shippingAddress->getStreetLine(1),
			'ship_city'		=> $shippingAddress->getCity(),
			'ship_state'	=> $shippingAddress->getRegion(),
			'ship_postal_code'	=> $shippingAddress->getPostcode(),
			'ship_country'	=> $shippingAddressCountryCode,
			'ship_phone'	=> $shippingAddress->getTelephone()
        );
        
      

        return $fields;
    }

}