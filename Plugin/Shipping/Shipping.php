<?php

namespace Excellence\UkOnly\Plugin\Shipping;
use Magento\Framework\Exception\StateException;

class Shipping extends \Magento\Shipping\Model\Shipping
{
    protected $_cart;

    protected $_productFactory;

    protected $_messageManager;

    protected $_checkoutSession;

    protected $_jsonResultFactory;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Checkout\Model\Cart $cart,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Framework\Controller\Result\JsonFactory $jsonResultFactory,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Checkout\Model\Session $checkoutSession
    ) {
        $this->_cart = $cart;
        $this->_productFactory = $productFactory;
        $this->_messageManager = $messageManager;
        $this->_checkoutSession = $checkoutSession;
        $this->_jsonResultFactory = $jsonResultFactory;
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
                $this->_messageManager->addErrorMessage(__($message));
                $flag = true;
            }
        }
        if($flag)
        {
            /*
             if fount any cart item with uk_only attribute then empty all available shipping methods
            */

            $methods = [];
            throw new StateException(__($message)); 
        }
        return $methods;
    }


    // public function beforeSaveAddressInformation(
    //     \Magento\Checkout\Model\ShippingInformationManagement $subject,
    //     $cartId,
    //     \Magento\Checkout\Api\Data\ShippingInformationInterface $addressInformation

    // )
    // {
    //     die("sdfghjkl");
    //     $address = $addressInformation->getShippingAddress();
    //     $postcode = $address->getData('postcode');
    //     $objectManager =   \Magento\Framework\App\ObjectManager::getInstance();
    //     $result = $this->jsonResultFactory->create();
    //     $stat="no sevice";
    //     throw new StateException(__($stat));             
    // }
}
