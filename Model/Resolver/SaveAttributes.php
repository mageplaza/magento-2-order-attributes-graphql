<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_OrderAttributesGraphQl
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

declare(strict_types=1);

namespace Mageplaza\OrderAttributesGraphQl\Model\Resolver;

use Exception;
use Magento\Framework\Exception\InputException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Quote\Model\Quote;
use Magento\QuoteGraphQl\Model\Cart\GetCartForUser;
use Mageplaza\OrderAttributes\Helper\Data;
use Mageplaza\OrderAttributes\Model\AttributesRepository;
use Mageplaza\OrderAttributes\Model\QuoteFactory as QuoteAttributes;
use Mageplaza\OrderAttributes\Model\AttributeFactory;

/**
 * Class SaveAttributes
 * @package Mageplaza\OrderAttributesGraphQl\Model\Resolver
 */
class SaveAttributes implements ResolverInterface
{
    /**
     * @var AttributesRepository
     */
    protected $attributeRepository;

    /**
     * @var Data
     */
    protected $helperData;

    /**
     * @var GetCartForUser
     */
    protected $getCartForUser;

    /**
     * @var QuoteAttributes
     */
    protected $quoteAttribute;

    /**
     * @var AttributeFactory
     */
    protected $attributeFactory;

    /**
     * SaveAttributes constructor.
     * @param AttributesRepository $attributeRepository
     * @param Data $helperData
     * @param GetCartForUser $getCartForUser
     * @param QuoteAttributes $quoteAttribute
     * @param AttributeFactory $attributeFactory
     */
    public function __construct(
        AttributesRepository $attributeRepository,
        Data $helperData,
        GetCartForUser $getCartForUser,
        QuoteAttributes $quoteAttribute,
        AttributeFactory $attributeFactory
    ) {
        $this->attributeRepository = $attributeRepository;
        $this->helperData          = $helperData;
        $this->getCartForUser      = $getCartForUser;
        $this->quoteAttribute      = $quoteAttribute;
        $this->attributeFactory    = $attributeFactory;
    }

    /**
     * @inheritdoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        if (!$this->helperData->isEnabled()) {
            throw new GraphQlInputException(__('The module is disabled.'));
        }

        $maskedCartId = $args['input']['cart_id'];

        if ($this->helperData->versionCompare('2.3.3')) {
            $storeId = (int)$context->getExtensionAttributes()->getStore()->getId();
            $cart    = $this->getCartForUser->execute($maskedCartId, $context->getUserId(), $storeId);
        } else {
            $cart = $this->getCartForUser->execute($maskedCartId, $context->getUserId());
        }

        $attributes = $args['input']['attributes'];
        $attributeSubmit = $this->validateAttributes($attributes, $cart);

        return $this->saveAttributes($attributeSubmit, $cart);
    }

    /**
     * @param array $attributes
     * @param Quote $cart
     * @return array
     * @throws GraphQlNoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function validateAttributes($attributes, $cart)
    {
        $attributeSubmit = [];
        $storeId         = $cart->getStoreId() ?: 0;
        $customerGroupId = $cart->getCustomerGroupId();
        foreach ($attributes as $attribute) {

            $attributeModel = $this->attributeFactory->create()->load($attribute['attribute_code'], 'attribute_code');
            if (!$attributeModel->getId() ||
                !$this->helperData->isVisible($attributeModel, $storeId, $customerGroupId)) {
                throw new GraphQlNoSuchEntityException(__('Invalid attribute (%1)', $attribute['attribute_code']));
            }
            $attributeSubmit[$attribute['attribute_code']] = $attribute['value'];
        }

        return $attributeSubmit;
    }

    /**
     * @param array $attributeSubmit
     * @param Quote $cart
     * @return bool
     * @throws InputException
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function saveAttributes($attributeSubmit, $cart)
    {
        $quoteAttribute = $this->quoteAttribute->create()->load($cart->getId());

        $attributesCollection = $this->helperData->getOrderAttributesCollection(
            $cart->getStoreId(),
            $cart->getCustomerGroupId(),
            false
        );
        $result               = [];
        $storeId              = $cart->getStoreId() ?: 0;
        foreach ($attributesCollection as $attribute) {
            if ($this->helperData->isVisible($attribute, $storeId, $cart->getCustomerGroupId())) {
                $attrCode      = $attribute->getAttributeCode();
                $frontendInput = $attribute->getFrontendInput();
                if (empty($attributeSubmit[$attrCode]) && $attribute->getIsRequired()) {
                    throw new GraphQlInputException(__('%1 is required'));
                }

                if (!isset($attributeSubmit[$attrCode])) {
                    continue;
                }

                if (!$attributeSubmit[$attrCode]) {
                    $result[$attrCode] = '';
                    continue;
                }

                $value             = $attributeSubmit[$attrCode];
                $result[$attrCode] = $value;
                switch ($frontendInput) {
                    case 'boolean':
                        $this->helperData->validateBoolean($attrCode, $value);
                        break;
                    case 'select':
                    case 'multiselect':
                    case 'select_visual':
                    case 'multiselect_visual':
                        $this->helperData->prepareOptionValue($attribute->getOptions(), $value, $storeId);
                        if ($this->helperData->getOptionsInvalid()) {
                            throw new GraphQlInputException(
                                __('Invalid options %1. Details: %1 ', implode($this->helperData->getOptionsInvalid()), $attrCode)
                            );
                        }

                        break;
                    case 'date':
                        if ($this->helperData->isValidDate($value)) {
                            $result[$attrCode] = $this->helperData->prepareDateValue($value);
                        }
                        break;
                    case 'image':
                    case 'file':
                        $this->helperData->validateFile($value, $quoteAttribute->getData($attrCode), $attrCode);
                        break;
                }
            }
        }

        try {
            $quoteAttribute->saveAttributeData($cart->getId(), $result);
        } catch (Exception $e) {
            throw new GraphQlInputException(__($e->getMessage()));
        }

        return true;
    }
}
