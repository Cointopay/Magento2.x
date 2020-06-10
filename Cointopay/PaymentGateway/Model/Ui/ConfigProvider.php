<?php
/**
 * Copyright © 2018 Cointopay. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Cointopay\PaymentGateway\Model\Ui;

use Magento\Checkout\Model\ConfigProviderInterface;
use Cointopay\PaymentGateway\Gateway\Http\Client\ClientMock;

/**
 * Class ConfigProvider
 */
class ConfigProvider implements ConfigProviderInterface
{
    const CODE = 'cointopay_gateway';

    protected $_jsonDecoder;

    /**
   * @var \Magento\Framework\App\Config\ScopeConfigInterface
   */
   protected $scopeConfig;

    /**
    * @var \Magento\Framework\HTTP\Client\Curl
    */
    protected $_curl;

    /**
    * @var $merchantId
    **/
    protected $merchantId;

    /**
    * @var $_curlUrl
    **/
    protected $_curlUrl;

    /**
    * Merchant ID
    */
    const XML_PATH_MERCHANT_ID = 'payment/cointopay_gateway/merchant_gateway_id';
    const XML_PATH_DEFAULT_COIN = 'payment/cointopay_gateway/supported_coins';

    /**
    * @param \Magento\Framework\App\Config\ScopeConfigInterface    $scopeConfig
    * @param \Magento\Framework\Json\DecoderInterface $decoder
    * @param \Magento\Framework\HTTP\Client\Curl                   $curl
    */
    public function __construct (
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Json\DecoderInterface $decoder,
        \Magento\Framework\HTTP\Client\Curl $curl
    )
    {
        $this->scopeConfig = $scopeConfig;
        $this->_jsonDecoder = $decoder;
        $this->_curl = $curl;
    }

    /**
     * Retrieve assoc array of checkout configuration
     *
     * @return array
     */
    public function getConfig()
    {
        return [
            'payment' => [
                self::CODE => [
                    'transactionResults' => $this->toOptionArray()
                ]
            ]
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        $this->merchantId = $this->scopeConfig->getValue(self::XML_PATH_MERCHANT_ID, $storeScope);
        $defaultCoin = $this->scopeConfig->getValue(self::XML_PATH_DEFAULT_COIN, $storeScope);
        if (isset($this->merchantId))
        {
            return $this->getSupportedCoins($defaultCoin);
        } else
        {
            return [];
        }
    }

    private function getSupportedCoins ($defaultCoin)
    {
        $this->_curlUrl = 'https://cointopay.com/CloneMasterTransaction?MerchantID='.$this->merchantId.'&output=json';
        $this->_curl->get($this->_curlUrl);
        $response = $this->_curl->getBody();
        $supportedCoins = $this->_jsonDecoder->decode($response);
        $coins = [];
        if (count($supportedCoins) > 0)
        {
            foreach ($supportedCoins as $k => $title)
            {
                if ($k % 2 == 0)
                {
                    $coins[$supportedCoins[$k+1]] = $title;
                }
            } 
        }
        return $coins;
    }
}
