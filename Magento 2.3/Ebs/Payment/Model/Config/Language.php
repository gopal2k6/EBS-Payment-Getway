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
 * Secureebs Allowed languages Resource
 *
 * @category   Mage
 * @package    Mage_Secureebs
 * @name       Mage_Secureebs_Model_Source_Language
 * @author     Magento Core Team <core@magentocommerce.com>
 */

namespace Ebs\Payment\Model\Config;

class Language
{
    public function toOptionArray()
    {
        return array(
            array('value' => 'EN', 'label' => __('English')),
            array('value' => 'RU', 'label' => __('Russian')),
            array('value' => 'NL', 'label' => __('Dutch')),
            array('value' => 'DE', 'label' => __('German')),
        );
    }
}



