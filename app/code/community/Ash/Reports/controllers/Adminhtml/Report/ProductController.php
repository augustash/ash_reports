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

// include_once("Mage/Adminhtml/controllers/Report/ProductController.php");
class Ash_Reports_Adminhtml_Report_ProductController extends Mage_Adminhtml_Controller_Report_Abstract
{
    /**
     * Add report/products breadcrumbs
     *
     * @return Ash_Reports_Adminhtml_Report_ProductController
     */
    public function _initAction()
    {
        parent::_initAction();
        $this->_addBreadcrumb(Mage::helper('ash_reports')->__('Products (Custom)'), Mage::helper('ash_reports')->__('Products (Custom)'));
        return $this;
    }

    /**
     * Missing Associated action
     *
     */
    public function missingassociatedAction()
    {
        $this->_title($this->__('Reports'))
             ->_title($this->__('Products'))
             ->_title($this->__('Missing Associated Simple Products'));

        $this->_initAction()
            ->_setActiveMenu('report/product/missingassociated')
            ->_addBreadcrumb(Mage::helper('ash_reports')->__('Missing Associated'), Mage::helper('ash_reports')->__('Missing Associated'))
            ->_addContent($this->getLayout()->createBlock('ash_reports/adminhtml_report_product_missing_associated'))
            ->renderLayout();
    }

    /**
     * Export products missing associated simple products report to CSV format
     *
     */
    public function exportMissingassociatedCsvAction()
    {
        $fileName   = 'products_missingassociated_simple.csv';
        $content    = $this->getLayout()->createBlock('ash_reports/adminhtml_report_product_missing_associated_grid')
            ->setSaveParametersInSession(true)
            ->getCsv();

        $this->_prepareDownloadResponse($fileName, $content);
    }


    /**
     * Missing Associated action
     *
     */
    public function missingattributeAction()
    {
        $this->_title($this->__('Reports'))
             ->_title($this->__('Products'))
             ->_title($this->__("Missing Super Attribute from Product's Attribute Set"));

        $this->_initAction()
            ->_setActiveMenu('report/product/missingattribute')
            ->_addBreadcrumb(Mage::helper('ash_reports')->__('Missing Super Attribute from Attribute Set'), Mage::helper('ash_reports')->__('Missing Super Attribute from Attribute Set'))
            ->_addContent($this->getLayout()->createBlock('ash_reports/adminhtml_report_product_missing_superAttribute'))
            ->renderLayout();
    }

    /**
     * Export mis-configured configurable products report to CSV format
     *
     * (super attributes not actually part of the product's attribute set)
     *
     */
    public function exportMissingattributeCsvAction()
    {
        $fileName   = 'products_missingattribute.csv';
        $content    = $this->getLayout()->createBlock('ash_reports/adminhtml_report_product_missing_superAttribute_grid')
            ->setSaveParametersInSession(true)
            ->getCsv();

        $this->_prepareDownloadResponse($fileName, $content);
    }
}
