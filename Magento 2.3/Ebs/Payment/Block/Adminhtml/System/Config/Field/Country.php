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
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Paypal
 * @copyright   Copyright (c) 2013 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Field renderer for PayPal merchant country selector
 *
 * @category   Mage
 * @package    Mage_Paypal
 * @author     Magento Core Team <core@magentocommerce.com>
 */
 
 
namespace Ebs\Payment\Block\Adminhtml\System\Config\Field;

//use Magento\Backend\Block\Widget\Form\Renderer;

class Country extends \Magento\Backend\Block\Widget\Form\Renderer\Fieldset
{
    /**#@+
     *
     * Request parameters names
     */
    const REQUEST_PARAM_COUNTRY = 'country';
    const REQUEST_PARAM_DEFAULT = 'default_country';
    /**#@-*/

    /**
     * Country of default scope
     *
     * @var string
     */
    protected $_defaultCountry;

    /**
     * Render country field considering request parameter
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    //public function render(AbstractElement $element)
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $country = $this->getRequest()->getParam(self::REQUEST_PARAM_COUNTRY);
        if ($country) {
            $element->setValue($country);
        }

        if ($element->getCanUseDefaultValue()) {
            $defaultConfigNode = Mage::getConfig()->getNode(null, 'default');
            if ($defaultConfigNode) {
                $this->_defaultCountry = (string)$defaultConfigNode->descend('paypal/general/merchant_country');
            }
            if (!$this->_defaultCountry) {
                $this->_defaultCountry = Mage::helper('core')->getDefaultCountry();
            }
            if ($country) {
                $shouldInherit = $country == $this->_defaultCountry
                    && $this->getRequest()->getParam(self::REQUEST_PARAM_DEFAULT);
                $element->setInherit($shouldInherit);
            }
            if ($element->getInherit()) {
                $this->_defaultCountry = null;
            }
        }

        return parent::render($element);
    }

    /**
     * Get country selector html
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        $configDataModel = Mage::getSingleton('adminhtml/config_data');
        $urlParams = array(
            'section' => $configDataModel->getSection(),
            'website' => $configDataModel->getWebsite(),
            'store' => $configDataModel->getStore(),
            self::REQUEST_PARAM_COUNTRY => '__country__',
        );
        $urlString = $this->helper('core')
            ->jsQuoteEscape(Mage::getModel('adminhtml/url')->getUrl('*/*/*', $urlParams));
        $jsString = '
            $("' . $element->getHtmlId() . '").observe("change", function () {
                location.href = \'' . $urlString . '\'.replace("__country__", this.value);
            });
        ';

        if ($this->_defaultCountry) {
            $urlParams[self::REQUEST_PARAM_DEFAULT] = '__default__';
            $urlString = $this->helper('core')
                ->jsQuoteEscape(Mage::getModel('adminhtml/url')->getUrl('*/*/*', $urlParams));
            $jsParentCountry = $this->helper('core')->jsQuoteEscape($this->_defaultCountry);
            $jsString .= '
                $("' . $element->getHtmlId() . '_inherit").observe("click", function () {
                    if (this.checked) {
                        location.href = \'' . $urlString . '\'.replace("__country__", \'' . $jsParentCountry . '\')
                            .replace("__default__", "1");
                    }
                });
            ';
        }

        return parent::_getElementHtml($element) . $this->helper('adminhtml/js')
            ->getScript('document.observe("dom:loaded", function() {' . $jsString . '});');
    }
}
