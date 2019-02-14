<?php
// This script and data application were generated by AppGini 5.74
// Download AppGini for free from https://bigprof.com/appgini/download/

	$currDir=dirname(__FILE__);
	include("$currDir/defaultLang.php");
	include("$currDir/language.php");
	include("$currDir/lib.php");
	@include("$currDir/hooks/item_prices.php");
	include("$currDir/item_prices_dml.php");

	// mm: can the current member access this page?
	$perm=getTablePermissions('item_prices');
	if(!$perm[0]){
		echo error_message($Translation['tableAccessDenied'], false);
		echo '<script>setTimeout("window.location=\'index.php?signOut=1\'", 2000);</script>';
		exit;
	}

	$x = new DataList;
	$x->TableName = "item_prices";

	// Fields that can be displayed in the table view
	$x->QueryFieldsTV = array(   
		"`item_prices`.`id`" => "id",
		"IF(    CHAR_LENGTH(`items1`.`item_description`), CONCAT_WS('',   `items1`.`item_description`), '') /* Item */" => "item",
		"`item_prices`.`price`" => "price",
		"if(`item_prices`.`date`,date_format(`item_prices`.`date`,'%d/%m/%Y'),'')" => "date"
	);
	// mapping incoming sort by requests to actual query fields
	$x->SortFields = array(   
		1 => '`item_prices`.`id`',
		2 => '`items1`.`item_description`',
		3 => '`item_prices`.`price`',
		4 => '`item_prices`.`date`'
	);

	// Fields that can be displayed in the csv file
	$x->QueryFieldsCSV = array(   
		"`item_prices`.`id`" => "id",
		"IF(    CHAR_LENGTH(`items1`.`item_description`), CONCAT_WS('',   `items1`.`item_description`), '') /* Item */" => "item",
		"`item_prices`.`price`" => "price",
		"if(`item_prices`.`date`,date_format(`item_prices`.`date`,'%d/%m/%Y'),'')" => "date"
	);
	// Fields that can be filtered
	$x->QueryFieldsFilters = array(   
		"`item_prices`.`id`" => "ID",
		"IF(    CHAR_LENGTH(`items1`.`item_description`), CONCAT_WS('',   `items1`.`item_description`), '') /* Item */" => "Item",
		"`item_prices`.`price`" => "Price",
		"`item_prices`.`date`" => "Date"
	);

	// Fields that can be quick searched
	$x->QueryFieldsQS = array(   
		"`item_prices`.`id`" => "id",
		"IF(    CHAR_LENGTH(`items1`.`item_description`), CONCAT_WS('',   `items1`.`item_description`), '') /* Item */" => "item",
		"`item_prices`.`price`" => "price",
		"if(`item_prices`.`date`,date_format(`item_prices`.`date`,'%d/%m/%Y'),'')" => "date"
	);

	// Lookup fields that can be used as filterers
	$x->filterers = array(  'item' => 'Item');

	$x->QueryFrom = "`item_prices` LEFT JOIN `items` as items1 ON `items1`.`id`=`item_prices`.`item` ";
	$x->QueryWhere = '';
	$x->QueryOrder = '';

	$x->AllowSelection = 1;
	$x->HideTableView = ($perm[2]==0 ? 1 : 0);
	$x->AllowDelete = $perm[4];
	$x->AllowMassDelete = false;
	$x->AllowInsert = $perm[1];
	$x->AllowUpdate = $perm[3];
	$x->SeparateDV = 1;
	$x->AllowDeleteOfParents = 0;
	$x->AllowFilters = 1;
	$x->AllowSavingFilters = 0;
	$x->AllowSorting = 1;
	$x->AllowNavigation = 1;
	$x->AllowPrinting = 1;
	$x->AllowCSV = 1;
	$x->RecordsPerPage = 10;
	$x->QuickSearch = 1;
	$x->QuickSearchText = $Translation["quick search"];
	$x->ScriptFileName = "item_prices_view.php";
	$x->RedirectAfterInsert = "item_prices_view.php?SelectedID=#ID#";
	$x->TableTitle = "Prices History";
	$x->TableIcon = "resources/table_icons/card_money.png";
	$x->PrimaryKey = "`item_prices`.`id`";
	$x->DefaultSortField = '`item_prices`.`date`';
	$x->DefaultSortDirection = 'desc';

	$x->ColWidth   = array(  150, 80, 150);
	$x->ColCaption = array("Item", "Price", "Date");
	$x->ColFieldName = array('item', 'price', 'date');
	$x->ColNumber  = array(2, 3, 4);

	// template paths below are based on the app main directory
	$x->Template = 'templates/item_prices_templateTV.html';
	$x->SelectedTemplate = 'templates/item_prices_templateTVS.html';
	$x->TemplateDV = 'templates/item_prices_templateDV.html';
	$x->TemplateDVP = 'templates/item_prices_templateDVP.html';

	$x->ShowTableHeader = 1;
	$x->TVClasses = "";
	$x->DVClasses = "";
	$x->HighlightColor = '#FFF0C2';

	// mm: build the query based on current member's permissions
	$DisplayRecords = $_REQUEST['DisplayRecords'];
	if(!in_array($DisplayRecords, array('user', 'group'))){ $DisplayRecords = 'all'; }
	if($perm[2]==1 || ($perm[2]>1 && $DisplayRecords=='user' && !$_REQUEST['NoFilter_x'])){ // view owner only
		$x->QueryFrom.=', membership_userrecords';
		$x->QueryWhere="where `item_prices`.`id`=membership_userrecords.pkValue and membership_userrecords.tableName='item_prices' and lcase(membership_userrecords.memberID)='".getLoggedMemberID()."'";
	}elseif($perm[2]==2 || ($perm[2]>2 && $DisplayRecords=='group' && !$_REQUEST['NoFilter_x'])){ // view group only
		$x->QueryFrom.=', membership_userrecords';
		$x->QueryWhere="where `item_prices`.`id`=membership_userrecords.pkValue and membership_userrecords.tableName='item_prices' and membership_userrecords.groupID='".getLoggedGroupID()."'";
	}elseif($perm[2]==3){ // view all
		// no further action
	}elseif($perm[2]==0){ // view none
		$x->QueryFields = array("Not enough permissions" => "NEP");
		$x->QueryFrom = '`item_prices`';
		$x->QueryWhere = '';
		$x->DefaultSortField = '';
	}
	// hook: item_prices_init
	$render=TRUE;
	if(function_exists('item_prices_init')){
		$args=array();
		$render=item_prices_init($x, getMemberInfo(), $args);
	}

	if($render) $x->Render();

	// hook: item_prices_header
	$headerCode='';
	if(function_exists('item_prices_header')){
		$args=array();
		$headerCode=item_prices_header($x->ContentType, getMemberInfo(), $args);
	}  
	if(!$headerCode){
		include_once("$currDir/header.php"); 
	}else{
		ob_start(); include_once("$currDir/header.php"); $dHeader=ob_get_contents(); ob_end_clean();
		echo str_replace('<%%HEADER%%>', $dHeader, $headerCode);
	}

	echo $x->HTML;
	// hook: item_prices_footer
	$footerCode='';
	if(function_exists('item_prices_footer')){
		$args=array();
		$footerCode=item_prices_footer($x->ContentType, getMemberInfo(), $args);
	}  
	if(!$footerCode){
		include_once("$currDir/footer.php"); 
	}else{
		ob_start(); include_once("$currDir/footer.php"); $dFooter=ob_get_contents(); ob_end_clean();
		echo str_replace('<%%FOOTER%%>', $dFooter, $footerCode);
	}
?>