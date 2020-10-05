<?php 
use \Bitrix\Main\Loader;
use \Bitrix\Main\Localization\Loc;
Loc::loadMessages(__FILE__);
?>
<style type="text/css">
    .pay-button {
        z-index: 1;
        color: #fff;
        text-transform: uppercase;
        font-size: 16px;
        font-weight: 700;
        letter-spacing: .01em;
        margin-top: 5px;
        padding: 15px 67px;
        max-width: 220px;
        min-width: 220px;
        border-radius: 60px;
        background: linear-gradient(270deg,#3f75ff 3%,#244de0);
        text-align: center;
        cursor: pointer;
        transition: all .3s ease-in-out;
        border: 1px solid #244de0;
        display: block;
    }
    .pay-button:before {
        content: "";
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: #fff;
        z-index: -1;
        opacity: 0;
        transition: all .4s ease-in-out;
        border-radius: 60px;
    }
    .pay-button:hover {
        transition: all .3s ease-in-out;
        border: 1px solid #244de0;
        color: #244de0;
        background: transparent;
        text-decoration: none;
    }
    .error {
        color: red;
    }
</style>
<?if($params['PAY_URL'] != ''):?>
    <a href="<?=$params['PAY_URL']?>" class="pay-button" target="_blank"><?=Loc::getMessage("KASSA_FLAMIX_PAY")?></a>
<?else:?>
    <div class="error"><?=$params['ERROR']?></div>
<?endif;?>