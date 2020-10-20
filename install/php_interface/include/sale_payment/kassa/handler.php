<?php

namespace Sale\Handlers\PaySystem;

use Bitrix\Main\Error;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Request;
use Bitrix\Main\Text\Encoding;
use Bitrix\Main\Type\DateTime;
use Bitrix\Main\Web\HttpClient;
use Bitrix\Sale\{BusinessValue, Order, PaySystem, Payment, PriceMaths};
use Bitrix\Main\Loader;
Loc::loadMessages(__FILE__);

/**
 * Class kassaHandler
 * @package Sale\Handlers\PaySystem
 */
class kassaHandler extends PaySystem\ServiceHandler
{
    /**
     * @param Payment $payment
     * @param Request|null $request
     * @return PaySystem\ServiceResult
     */
    public function initiatePay(Payment $payment, Request $request = null)
    {
        $extParams = [
            'PS_MODE' => $this->service->getField('PS_MODE'),
            'BX_PAYSYSTEM_CODE' => $this->service->getField('ID')
        ];
            
        
        try {
            $extParams['PAY_URL'] = $this->getPayUrl($payment);
        } catch (\Exception $e) {
            $extParams['ERROR'] = $e->getMessage();

            $this->setExtraParams($extParams);

            return $this->showTemplate($payment, "template");
        }

        $this->setExtraParams($extParams);

        return $this->showTemplate($payment, "template");
    }

    /**
     * @param Payment $payment
     * @return string
     */
    public function getPayUrl(Payment $payment)
    {
        if (!Loader::includeModule( "flamix.kassa" )) {
            throw new \Exception(Loc::getMessage("SALE_HPS_KASSA_MODULE_NOT_FOUND"));
        }

        if ($this->getBusinessValue($payment, 'CASHBOX_CODE') == '') {
            throw new \Exception(Loc::getMessage("SALE_HPS_KASSA_CASHBOX_CODE_EMPTY"));
        }

        $sum = roundEx($payment->getSum(), 2);
        if ($sum <= 0) {
            throw new \Exception(Loc::getMessage("SALE_HPS_KASSA_BAD_SUM"));
        }
            
        $order = $payment->getOrder();

        $kassa = new \Flamix\Kassa\API( $this->getBusinessValue($payment, 'CASHBOX_CODE') );

        return $kassa
            ->setAmount($sum)
            ->setCurrency($order->getCurrency())
            ->setOrderId($order->getId())
            ->setPaymentType('link')
            ->setItems($this->prepareItems($order))
            ->getPaymentRequest();

    }

    public function prepareItems($order): array
    {
        $resItems = [];

        $basket = \Bitrix\Sale\Basket::loadItemsForOrder($order);

        foreach ($basket->getBasketItems() as $basketItem) {
            $resItems[] = [
                'name' => $basketItem->getField('NAME'),
                'price' => number_format($basketItem->getField('PRICE'),2,".",''),
                'quantity' => $basketItem->getQuantity(),
                'measure' => $basketItem->getField('MEASURE_NAME')
            ];
        }
        
        if ((float)$order->getDeliveryPrice() > 0) {
            $shipmentCollection = $order->getShipmentCollection();
            foreach ($shipmentCollection as $shipment){
                if ($shipment->isSystem())
                  continue;
                if((float)$order->getDeliveryPrice() <= 0)
                  continue;
                $deliveryName = $shipment->getField('DELIVERY_NAME');
            }
            $resItems[] = [
                'name' => Loc::getMessage("SALE_HPS_KASSA_DELIVERY_SUM", ["#DELIVERY_NAME#"=>$deliveryName]),
                'price' => number_format($order->getDeliveryPrice(),2,".",''),
                'quantity' => 1,
                'measure' => Loc::getMessage("SALE_HPS_KASSA_MEASURE")
            ];
        }

        return $resItems;
    }

    /**
     * @return array
     */
    public static function getIndicativeFields()
    {
        return ['cashbox_code', 'order_id'];
    }

    /**
     * @param Request $request
     * @param $paySystemId
     * @return bool
     */
    static public function isMyResponse(Request $request, $paySystemId)
    {
        return true;
    }

    static protected function isMyResponseExtended(Request $request, $paySystemId)
    {
        $id = $request->get('BX_PAYSYSTEM_CODE');
        return $id == $paySystemId;
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function getPaymentIdFromRequest(Request $request)
    {
        if ($request->get('order_id') === null) {
            return false;
        }

        $order=\Bitrix\Sale\Order::load($request->get('order_id'));
        foreach($order->getPaymentCollection() as $payment){
            $l[]=$payment->getField("ID");
        }

        return current($l);
    }

    /**
     * @param Payment $payment
     * @param Request $request
     * @return bool
     */
    private function isCorrectSum(Payment $payment, Request $request)
    {
        $sum = $request->getPost('amount');
        $paymentSum = $this->getBusinessValue($payment, 'PAYMENT_SHOULD_PAY');
        return PriceMaths::roundPrecision($paymentSum) === PriceMaths::roundPrecision($sum);
    }

    /**
     * @param Payment $payment
     * @param Request $request
     * @return PaySystem\ServiceResult
     */
    private function processNoticeAction(Payment $payment, Request $request)
    {
        $result = new PaySystem\ServiceResult();

        $fields = array(
            "PS_STATUS" => "Y",
            "PS_STATUS_CODE" => "-",
            "PS_STATUS_DESCRIPTION" => 'Success',
            "PS_STATUS_MESSAGE" => 'Success',
            "PS_SUM" => $request->getPost('amount'),
            "PS_CURRENCY" => $request->getPost('payment_currency'),
            "PS_RESPONSE_DATE" => new DateTime()
        );

        $result->setPsData($fields);

        if ($this->isCorrectSum($payment, $request))
        {
            if ($this->getBusinessValue($payment, 'PS_CHANGE_STATUS_PAY') == 'Y')
            {
                $result->setOperationType(PaySystem\ServiceResult::MONEY_COMING);
            }
        }
        else
        {
            PaySystem\Logger::addError('Flamix.Kassa: ' . Loc::getMessage("SALE_HPS_KASSA_INCORRECT_SUM"));
            $result->addError(new Error('Flamix.Kassa: ' . Loc::getMessage("SALE_HPS_KASSA_INCORRECT_SUM")));
        }

        return $result;
    }

    /**
     * @param Payment $payment
     * @param Request $request
     * @return PaySystem\ServiceResult
     */
    public function processRequest(Payment $payment, Request $request)
    {
        $result = new PaySystem\ServiceResult();

        Loader::includeModule( "flamix.kassa" );

        try {
            $kassa = new \Flamix\Kassa\API( $request->get('cashbox_code') );
            $isCheckSuccess = $kassa
                ->setSecretKey($this->getBusinessValue($payment, 'TEST_SECRET_CODE'))
                ->setTestSecretKey($this->getBusinessValue($payment, 'TEST_SECRET_CODE'))
                ->isPaymentSuccess($_POST);

            if (!$isCheckSuccess) {
                PaySystem\Logger::addError('Flamix.Kassa: ' . Loc::getMessage("SALE_HPS_KASSA_INCORRECT_HASH"));
                $result->addError(new Error('Flamix.Kassa: ' . Loc::getMessage("SALE_HPS_KASSA_UNKNOWN_ERROR")));

                return $result;
            }

            return $this->processNoticeAction($payment, $request);
    

        } catch(\Exception $e) {
            PaySystem\Logger::addError('Flamix.Kassa: ' . Loc::getMessage("SALE_HPS_KASSA_UNKNOWN_ERROR"));
            $result->addError(new Error(Loc::getMessage("SALE_HPS_KASSA_UNKNOWN_ERROR") . ': ' . $e->getMessage()));
            return $result;
        }

        return $result;
    }

    /**
     * @param Payment $payment
     * @return bool
     */
    protected function isTestMode(Payment $payment = null)
    {
        return ($this->getBusinessValue($payment, 'PS_IS_TEST') == 'Y');
    }

    /**
     * @return array
     */
    public function getCurrencyList()
    {
        return array('RUB', 'UAH', 'USD');
    }
}