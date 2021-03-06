<?php
/**
 * Customer address entity resource model
 *
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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Customer\Model\Resource;

class Address extends \Magento\Eav\Model\Entity\AbstractEntity
{
    /**
     * @var \Magento\Core\Model\Validator\Factory
     */
    protected $_validatorFactory;

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $_customerFactory;

    /**
     * @param \Magento\App\Resource $resource
     * @param \Magento\Eav\Model\Config $eavConfig
     * @param \Magento\Eav\Model\Entity\Attribute\Set $attrSetEntity
     * @param \Magento\Core\Model\LocaleInterface $locale
     * @param \Magento\Eav\Model\Resource\Helper $resourceHelper
     * @param \Magento\Validator\UniversalFactory $universalFactory
     * @param \Magento\Core\Model\Validator\Factory $validatorFactory
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param array $data
     */
    public function __construct(
        \Magento\App\Resource $resource,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Eav\Model\Entity\Attribute\Set $attrSetEntity,
        \Magento\Core\Model\LocaleInterface $locale,
        \Magento\Eav\Model\Resource\Helper $resourceHelper,
        \Magento\Validator\UniversalFactory $universalFactory,
        \Magento\Core\Model\Validator\Factory $validatorFactory,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        $data = array()
    ) {
        $this->_validatorFactory = $validatorFactory;
        $this->_customerFactory = $customerFactory;
        parent::__construct($resource, $eavConfig, $attrSetEntity, $locale, $resourceHelper, $universalFactory, $data);
    }

    /**
     * Resource initialization.
     */
    protected function _construct()
    {
        $resource = $this->_resource;
        $this->setType('customer_address')->setConnection(
            $resource->getConnection('customer_read'),
            $resource->getConnection('customer_write')
        );
    }

    /**
     * Set default shipping to address
     *
     * @param \Magento\Object $address
     * @return \Magento\Customer\Model\Resource\Address
     */
    protected function _afterSave(\Magento\Object $address)
    {
        if ($address->getIsCustomerSaveTransaction()) {
            return $this;
        }
        if ($address->getId() && ($address->getIsDefaultBilling() || $address->getIsDefaultShipping())) {
            $customer = $this->_createCustomer()->load($address->getCustomerId());

            if ($address->getIsDefaultBilling()) {
                $customer->setDefaultBilling($address->getId());
            }
            if ($address->getIsDefaultShipping()) {
                $customer->setDefaultShipping($address->getId());
            }
            $customer->save();
        }
        return $this;
    }

    /**
     * Check customer address before saving
     *
     * @param \Magento\Object $address
     * @return \Magento\Customer\Model\Resource\Address
     */
    protected function _beforeSave(\Magento\Object $address)
    {
        parent::_beforeSave($address);

        $this->_validate($address);

        return $this;
    }

    /**
     * Validate customer address entity
     *
     * @param \Magento\Customer\Model\Customer $address
     * @throws \Magento\Validator\ValidatorException when validation failed
     */
    protected function _validate($address)
    {
        $validator = $this->_validatorFactory->createValidator('customer_address', 'save');

        if (!$validator->isValid($address)) {
            throw new \Magento\Validator\ValidatorException($validator->getMessages());
        }
    }

    /**
     * @return \Magento\Customer\Model\Customer
     */
    protected function _createCustomer()
    {
        return $this->_customerFactory->create();
    }
}
