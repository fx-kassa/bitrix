<?php
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

$description = array(
	'RETURN' => Loc::getMessage('SALE_HPS_KASSA_RETURN'),
	'RESTRICTION' => Loc::getMessage('SALE_HPS_KASSA_RESTRICTION'),
	'COMMISSION' => Loc::getMessage('SALE_HPS_KASSA_COMMISSION'),
	'MAIN' => Loc::getMessage('KASSA_DDESCR')
);

$data = array(
	'NAME' => Loc::getMessage('SALE_HPS_KASSA'),
	'SORT' => 500,
	'DOMAIN' => 'BOX',
	'CODES' => array(
        "CASHBOX_CODE" => array(
            "NAME" => Loc::getMessage("SALE_HPS_KASSA_CASHBOX_CODE"),
            "DESCRIPTION" => Loc::getMessage("SALE_HPS_KASSA_CASHBOX_CODE_DESC"),
            'SORT' => 300,
            'GROUP' => 'PAYMENT',
        ),
        "SECRET_CODE" => array(
            "NAME" => Loc::getMessage("SALE_HPS_KASSA_SECRET_CODE"),
            "DESCRIPTION" => Loc::getMessage("SALE_HPS_KASSA_SECRET_CODE_DESC"),
            'SORT' => 300,
            'GROUP' => 'PAYMENT',
        ),
        "TEST_SECRET_CODE" => array(
            "NAME" => Loc::getMessage("SALE_HPS_KASSA_TEST_SECRET_CODE"),
            "DESCRIPTION" => Loc::getMessage("SALE_HPS_KASSA_TEST_SECRET_CODE_DESC"),
            'SORT' => 300,
            'GROUP' => 'PAYMENT',
        ),
		"PAYMENT_ID" => array(
			"NAME" => Loc::getMessage("SALE_HPS_KASSA_PAYMENT_ID"),
			'SORT' => 400,
			'GROUP' => 'PAYMENT',
			'DEFAULT' => array(
				'PROVIDER_KEY' => 'ORDER',
				'PROVIDER_VALUE' => 'ID'
			)
		),
		"PAYMENT_DATE_INSERT" => array(
			"NAME" => Loc::getMessage("SALE_HPS_KASSA_PAYMENT_DATE"),
			'SORT' => 500,
			'GROUP' => 'PAYMENT',
			'DEFAULT' => array(
				'PROVIDER_KEY' => 'ORDER',
				'PROVIDER_VALUE' => 'DATE_INSERT'
			)
		),
		"PAYMENT_SHOULD_PAY" => array(
			"NAME" => Loc::getMessage("SALE_HPS_KASSA_SHOULD_PAY"),
			'SORT' => 600,
			'GROUP' => 'PAYMENT',
			'DEFAULT' => array(
				'PROVIDER_KEY' => 'PAYMENT',
				'PROVIDER_VALUE' => 'SUM'
			)
		),
		"PS_CHANGE_STATUS_PAY" => array(
			"NAME" => Loc::getMessage("SALE_HPS_KASSA_CHANGE_STATUS_PAY"),
			'SORT' => 700,
			'GROUP' => 'GENERAL_SETTINGS',
			"INPUT" => array(
				'TYPE' => 'Y/N'
			),
			'DEFAULT' => 'Y'
		),
		"PS_IS_TEST" => array(
			"NAME" => Loc::getMessage("SALE_HPS_KASSA_IS_TEST"),
			'SORT' => 900,
			'GROUP' => 'GENERAL_SETTINGS',
			"INPUT" => array(
				'TYPE' => 'Y/N'
			)
		),
        "PAYMENT_BUYER_EMAIL" => array(
            "NAME" => Loc::getMessage("SALE_HPS_KASSA_BUYER_EMAIL"),
            'SORT' => 1000,
            'GROUP' => 'PAYMENT',
            'DEFAULT' => array(
                'PROVIDER_KEY' => 'USER',
                'PROVIDER_VALUE' => 'EMAIL'
            )
        ),
	)
);