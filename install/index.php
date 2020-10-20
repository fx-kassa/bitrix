<?php
global $MESS;
$PathInstall = str_replace("\\", "/", __FILE__);
$PathInstall = substr($PathInstall, 0, strlen($PathInstall) - strlen("/index.php"));
IncludeModuleLangFile($PathInstall . "/install.php");

if(class_exists("flamix_kassa")) return;

class flamix_kassa extends CModule
{
	public $MODULE_ID = "flamix.kassa";
	public $MODULE_VERSION;
	public $MODULE_VERSION_DATE;
	public $MODULE_NAME;
	public $MODULE_DESCRIPTION;
	public $MODULE_GROUP_RIGHTS = "Y";
	
	public function __construct()
	{
		$arModuleVersion = array();
		include(dirname(__FILE__)."/version.php");
		$this->MODULE_VERSION = $arModuleVersion["VERSION"];
		$this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];
		$this->MODULE_NAME = GetMessage("flamix.kassa_MODULE_NAME");
		$this->MODULE_DESCRIPTION = GetMessage("flamix.kassa_MODULE_DESC");
		$this->PARTNER_NAME = GetMessage("flamix.kassa_PARTNER_NAME");
		$this->PARTNER_URI = GetMessage("flamix.kassa_PARTNER_URI");
	}
  
	public function DoInstall()
	{	
		$this->InstallFiles();
		RegisterModule($this->MODULE_ID);
		return true;
	}

	public function InstallDB()
	{
	
		return true;
	}

	public function InstallFiles($arParams = array())
	{
		CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/".$this->MODULE_ID."/install/images", $_SERVER["DOCUMENT_ROOT"]."/bitrix/images",true,true);
		CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/".$this->MODULE_ID."/install/php_interface",  $_SERVER["DOCUMENT_ROOT"]."/bitrix/php_interface",true,true);
		return true;
	}
	public function UnInstallFiles()
	{
		DeleteDirFilesEx('/bitrix/php_interface/include/sale_payment/kassa');
		
		return true;
	}

	public function DoUninstall()
	{	
		$this->UnInstallDB();
		$this->UnInstallFiles();
		UnRegisterModule($this->MODULE_ID);
		return true;
	}
	public function UnInstallDB()
	{	
		return true;
	}
}?>