<?php

namespace Excellence\UkOnly\Plugin\Shipping;

class Shipping extends \Magento\Shipping\Model\Shipping
{
    protected $_cart;

    protected $_productFactory;

    protected $_messageManager;

    protected $_checkoutSession;

    public function __construct(
        \Magento\Checkout\Model\Cart $cart,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Checkout\Model\Session $checkoutSession
    ) {
        $this->_cart = $cart;
        $this->_productFactory = $productFactory;
        $this->_messageManager = $messageManager;
        $this->_checkoutSession = $checkoutSession;
    }


   /**
     * Allow shipping methods
     *
     * @param \Magento\Quote\Api\ShipmentEstimationInterface $subject
     * @param \Magento\Quote\Api\Data\ShippingMethodInterface[] $methods
     * @return \Magento\Quote\Api\Data\ShippingMethodInterface[] $methods
     */
    public function afterEstimateByExtendedAddress(\Magento\Quote\Api\ShipmentEstimationInterface $subject, $methods) 
    {
        $flag = false; //assume uk is not the shipping country
        $items = $this->_cart->getQuote()->getAllItems();
        //looping through cart items
        foreach ($items as $item) {
            $productSku = $item->getSku();
            $productCollection = $this->_productFactory->create()
                ->loadByAttribute('sku', $productSku);
            //product attribute should be uk_only and country id should be 'GB' for UK
            if ($productCollection->getData('uk_only') and $this->_checkoutSession->getQuote()->getShippingAddress()->getCountryId() === 'GB') {
                // found some product with attribute uk_only
                $message = "cart contain " . $productCollection->getData('name') . " whose shipping is restricted to UK only";
                $this->_messageManager->addError(__($message));
                $flag = true;
            }
        }
        if($flag)
        {
            /*
             if fount any cart item with uk_only attribute then empty all available shipping methods
            */

            $methods = [];
        }
        return $methods;
    }
}
