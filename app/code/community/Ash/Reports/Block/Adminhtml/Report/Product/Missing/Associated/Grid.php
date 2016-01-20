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
class Ash_Reports_Block_Adminhtml_Report_Product_Missing_Associated_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    // need to set this otherwise you're going to have a bad time
    protected $_blockGroup = 'ash_reports';

    // protected $_saveParametersInSession = true;

    public function __construct()
    {
        $this->_blockGroup = 'ash_reports';
        parent::__construct();
        $this->setId('gridMissingAssociated');
        $this->setUseAjax(false);
    }

    protected function _prepareCollection()
    {
        if ($this->getRequest()->getParam('website')) {
            $storeIds   = Mage::app()->getWebsite($this->getRequest()->getParam('website'))->getStoreIds();
            $storeId    = array_pop($storeIds);
        } else if ($this->getRequest()->getParam('group')) {
            $storeIds   = Mage::app()->getGroup($this->getRequest()->getParam('group'))->getStoreIds();
            $storeId    = array_pop($storeIds);
        } else if ($this->getRequest()->getParam('store')) {
            $storeId = (int)$this->getRequest()->getParam('store');
        } else {
            $storeId = '';
        }

        /** @var $collection Mage_Reports_Model_Resource_Product_Collection  */
        $collection = Mage::getResourceModel('reports/product_collection');
        $collection->addAttributeToSelect(array('entity_id', 'sku', 'name', 'eav_attribute_set.attribute_set_name'));
        $collection->setStoreId($storeId);

        // Get the Zend_Db_Select b/c it's easier to work with
        $select = $collection->getSelect();
        $select->join('eav_entity_type', 'e.entity_type_id = eav_entity_type.entity_type_id');
        $select->join('eav_attribute_set', 'e.attribute_set_id = eav_attribute_set.attribute_set_id', array('attribute_set_name'));

        $select->where("eav_entity_type.entity_type_code = 'catalog_product'")
            ->where("e.type_id = 'configurable'");

        $subquery = new Zend_Db_Expr("SELECT catalog_product_super_link.parent_id FROM catalog_product_super_link INNER JOIN catalog_product_entity ON catalog_product_super_link.parent_id = catalog_product_entity.entity_id");

        $select->where('e.entity_id NOT IN (' . $subquery . ')');

        // Mage::log('FROM ' . __CLASS__ . '::' . __FUNCTION__ . ' AT LINE ' . __LINE__);
        // Mage::log('REPORT SQL QUERY: ' . print_r($select->__toString(), true));

        if( $storeId ) {
            $collection->addStoreFilter($storeId);
        }

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('id', array(
            'header'    => Mage::helper('ash_reports')->__('Product ID'),
            'sortable'  => false,
            'index'     => 'entity_id',
            'filter'    => 'adminhtml/widget_grid_column_filter_range',
        ));

        $this->addColumn('name', array(
            'header'    => Mage::helper('ash_reports')->__('Product Name'),
            'sortable'  => false,
            'index'     => 'name'
        ));

        $this->addColumn('sku', array(
            'header'    => Mage::helper('ash_reports')->__('Product SKU'),
            'sortable'  => false,
            'index'     => 'sku'
        ));

        $this->addColumn('attribute_set_name', array(
            'header'    => Mage::helper('ash_reports')->__('Attribute Set Name'),
            'sortable'  => false,
            'index'     => 'attribute_set_name'
        ));

        $this->addExportType('*/*/exportMissingassociatedCsv', Mage::helper('ash_reports')->__('CSV'));

        return parent::_prepareColumns();
    }
}
