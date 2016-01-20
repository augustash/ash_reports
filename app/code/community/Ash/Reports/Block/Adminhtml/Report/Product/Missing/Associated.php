<?php
/**
 * Add reports for the following:
 *
 * + configurable products missing children (associated) products
 * + configurable products with mis-configured attributes that don't belong to the attribute set
 *
 * @category    Ash
 * @package     Ash_Reports
 * @copyright   Copyright (c) 2015 August Ash, Inc. (http://www.augustash.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Ash_Reports_Block_Adminhtml_Report_Product_Missing_Associated extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    protected $_blockGroup = 'ash_reports';

    public function __construct()
    {
        $this->_controller = 'adminhtml_report_product_missing_associated';
        $this->_headerText = Mage::helper('ash_reports')->__('Configurable Products Missing Associated Simple Products');
        parent::__construct();
        $this->_removeButton('add');
    }

    protected function _prepareLayout()
    {
        $this->setChild('store_switcher',
            $this->getLayout()->createBlock('adminhtml/store_switcher')
                ->setUseConfirm(false)
                ->setSwitchUrl($this->getUrl('*/*/*', array('store'=>null)))
                ->setTemplate('report/store/switcher.phtml')
        );

        return parent::_prepareLayout();
    }

    public function getStoreSwitcherHtml()
    {
        if (Mage::app()->isSingleStoreMode()) {
            return '';
        }
        return $this->getChildHtml('store_switcher');
    }

    public function getGridHtml()
    {
        return $this->getStoreSwitcherHtml() . parent::getGridHtml();
    }
}
