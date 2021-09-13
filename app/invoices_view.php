<?php
// This script and data application were generated by AppGini 6.0
// Download AppGini for free from https://bigprof.com/appgini/download/

	$currDir = dirname(__FILE__);
	include_once("{$currDir}/lib.php");
	@include_once("{$currDir}/hooks/invoices.php");
	include_once("{$currDir}/invoices_dml.php");

	// mm: can the current member access this page?
	$perm = getTablePermissions('invoices');
	if(!$perm['access']) {
		echo error_message($Translation['tableAccessDenied']);
		exit;
	}

	$x = new DataList;
	$x->TableName = 'invoices';

	// Fields that can be displayed in the table view
	$x->QueryFieldsTV = [
		"`invoices`.`id`" => "id",
		"`invoices`.`code`" => "code",
		"`invoices`.`status`" => "status",
		"if(`invoices`.`date_due`,date_format(`invoices`.`date_due`,'%d/%m/%Y'),'')" => "date_due",
		"IF(    CHAR_LENGTH(`clients1`.`name`), CONCAT_WS('',   `clients1`.`name`), '') /* Client */" => "client",
		"IF(    CHAR_LENGTH(`clients1`.`contact`), CONCAT_WS('',   `clients1`.`contact`), '') /* Client contact */" => "client_contact",
		"IF(    CHAR_LENGTH(`clients1`.`address`), CONCAT_WS('',   `clients1`.`address`), '') /* Client address */" => "client_address",
		"IF(    CHAR_LENGTH(`clients1`.`phone`), CONCAT_WS('',   `clients1`.`phone`), '') /* Client phone */" => "client_phone",
		"IF(    CHAR_LENGTH(`clients1`.`email`), CONCAT_WS('',   `clients1`.`email`), '') /* Client email */" => "client_email",
		"IF(    CHAR_LENGTH(`clients1`.`website`), CONCAT_WS('',   `clients1`.`website`), '') /* Client website */" => "client_website",
		"IF(    CHAR_LENGTH(`clients1`.`comments`), CONCAT_WS('',   `clients1`.`comments`), '') /* Client comments */" => "client_comments",
		"`invoices`.`subtotal`" => "subtotal",
		"`invoices`.`discount`" => "discount",
		"FORMAT(`invoices`.`tax`, 2)" => "tax",
		"`invoices`.`total`" => "total",
		"`invoices`.`comments`" => "comments",
		"`invoices`.`invoice_template`" => "invoice_template",
		"`invoices`.`created`" => "created",
		"`invoices`.`last_updated`" => "last_updated",
	];
	// mapping incoming sort by requests to actual query fields
	$x->SortFields = [
		1 => '`invoices`.`id`',
		2 => 2,
		3 => 3,
		4 => '`invoices`.`date_due`',
		5 => '`clients1`.`name`',
		6 => '`clients1`.`contact`',
		7 => '`clients1`.`address`',
		8 => '`clients1`.`phone`',
		9 => '`clients1`.`email`',
		10 => '`clients1`.`website`',
		11 => '`clients1`.`comments`',
		12 => '`invoices`.`subtotal`',
		13 => '`invoices`.`discount`',
		14 => '`invoices`.`tax`',
		15 => '`invoices`.`total`',
		16 => 16,
		17 => 17,
		18 => 18,
		19 => 19,
	];

	// Fields that can be displayed in the csv file
	$x->QueryFieldsCSV = [
		"`invoices`.`id`" => "id",
		"`invoices`.`code`" => "code",
		"`invoices`.`status`" => "status",
		"if(`invoices`.`date_due`,date_format(`invoices`.`date_due`,'%d/%m/%Y'),'')" => "date_due",
		"IF(    CHAR_LENGTH(`clients1`.`name`), CONCAT_WS('',   `clients1`.`name`), '') /* Client */" => "client",
		"IF(    CHAR_LENGTH(`clients1`.`contact`), CONCAT_WS('',   `clients1`.`contact`), '') /* Client contact */" => "client_contact",
		"IF(    CHAR_LENGTH(`clients1`.`address`), CONCAT_WS('',   `clients1`.`address`), '') /* Client address */" => "client_address",
		"IF(    CHAR_LENGTH(`clients1`.`phone`), CONCAT_WS('',   `clients1`.`phone`), '') /* Client phone */" => "client_phone",
		"IF(    CHAR_LENGTH(`clients1`.`email`), CONCAT_WS('',   `clients1`.`email`), '') /* Client email */" => "client_email",
		"IF(    CHAR_LENGTH(`clients1`.`website`), CONCAT_WS('',   `clients1`.`website`), '') /* Client website */" => "client_website",
		"IF(    CHAR_LENGTH(`clients1`.`comments`), CONCAT_WS('',   `clients1`.`comments`), '') /* Client comments */" => "client_comments",
		"`invoices`.`subtotal`" => "subtotal",
		"`invoices`.`discount`" => "discount",
		"FORMAT(`invoices`.`tax`, 2)" => "tax",
		"`invoices`.`total`" => "total",
		"`invoices`.`comments`" => "comments",
		"`invoices`.`invoice_template`" => "invoice_template",
		"`invoices`.`created`" => "created",
		"`invoices`.`last_updated`" => "last_updated",
	];
	// Fields that can be filtered
	$x->QueryFieldsFilters = [
		"`invoices`.`id`" => "ID",
		"`invoices`.`code`" => "Code",
		"`invoices`.`status`" => "Status",
		"`invoices`.`date_due`" => "Date due",
		"IF(    CHAR_LENGTH(`clients1`.`name`), CONCAT_WS('',   `clients1`.`name`), '') /* Client */" => "Client",
		"IF(    CHAR_LENGTH(`clients1`.`contact`), CONCAT_WS('',   `clients1`.`contact`), '') /* Client contact */" => "Client contact",
		"IF(    CHAR_LENGTH(`clients1`.`address`), CONCAT_WS('',   `clients1`.`address`), '') /* Client address */" => "Client address",
		"IF(    CHAR_LENGTH(`clients1`.`phone`), CONCAT_WS('',   `clients1`.`phone`), '') /* Client phone */" => "Client phone",
		"IF(    CHAR_LENGTH(`clients1`.`email`), CONCAT_WS('',   `clients1`.`email`), '') /* Client email */" => "Client email",
		"IF(    CHAR_LENGTH(`clients1`.`website`), CONCAT_WS('',   `clients1`.`website`), '') /* Client website */" => "Client website",
		"IF(    CHAR_LENGTH(`clients1`.`comments`), CONCAT_WS('',   `clients1`.`comments`), '') /* Client comments */" => "Client comments",
		"`invoices`.`subtotal`" => "Subtotal",
		"`invoices`.`discount`" => "Discount %",
		"`invoices`.`tax`" => "Tax %",
		"`invoices`.`total`" => "Total",
		"`invoices`.`comments`" => "Comments",
		"`invoices`.`invoice_template`" => "Invoice template",
		"`invoices`.`created`" => "Created",
		"`invoices`.`last_updated`" => "Last updated",
	];

	// Fields that can be quick searched
	$x->QueryFieldsQS = [
		"`invoices`.`id`" => "id",
		"`invoices`.`code`" => "code",
		"`invoices`.`status`" => "status",
		"if(`invoices`.`date_due`,date_format(`invoices`.`date_due`,'%d/%m/%Y'),'')" => "date_due",
		"IF(    CHAR_LENGTH(`clients1`.`name`), CONCAT_WS('',   `clients1`.`name`), '') /* Client */" => "client",
		"IF(    CHAR_LENGTH(`clients1`.`contact`), CONCAT_WS('',   `clients1`.`contact`), '') /* Client contact */" => "client_contact",
		"IF(    CHAR_LENGTH(`clients1`.`address`), CONCAT_WS('',   `clients1`.`address`), '') /* Client address */" => "client_address",
		"IF(    CHAR_LENGTH(`clients1`.`phone`), CONCAT_WS('',   `clients1`.`phone`), '') /* Client phone */" => "client_phone",
		"IF(    CHAR_LENGTH(`clients1`.`email`), CONCAT_WS('',   `clients1`.`email`), '') /* Client email */" => "client_email",
		"IF(    CHAR_LENGTH(`clients1`.`website`), CONCAT_WS('',   `clients1`.`website`), '') /* Client website */" => "client_website",
		"IF(    CHAR_LENGTH(`clients1`.`comments`), CONCAT_WS('',   `clients1`.`comments`), '') /* Client comments */" => "client_comments",
		"`invoices`.`subtotal`" => "subtotal",
		"`invoices`.`discount`" => "discount",
		"FORMAT(`invoices`.`tax`, 2)" => "tax",
		"`invoices`.`total`" => "total",
		"`invoices`.`comments`" => "comments",
		"`invoices`.`invoice_template`" => "invoice_template",
		"`invoices`.`created`" => "created",
		"`invoices`.`last_updated`" => "last_updated",
	];

	// Lookup fields that can be used as filterers
	$x->filterers = ['client' => 'Client', ];

	$x->QueryFrom = "`invoices` LEFT JOIN `clients` as clients1 ON `clients1`.`id`=`invoices`.`client` ";
	$x->QueryWhere = '';
	$x->QueryOrder = '';

	$x->AllowSelection = 1;
	$x->HideTableView = ($perm['view'] == 0 ? 1 : 0);
	$x->AllowDelete = $perm['delete'];
	$x->AllowMassDelete = true;
	$x->AllowInsert = $perm['insert'];
	$x->AllowUpdate = $perm['edit'];
	$x->SeparateDV = 1;
	$x->AllowDeleteOfParents = 0;
	$x->AllowFilters = 1;
	$x->AllowSavingFilters = 1;
	$x->AllowSorting = 1;
	$x->AllowNavigation = 1;
	$x->AllowPrinting = 1;
	$x->AllowPrintingDV = 1;
	$x->AllowCSV = 1;
	$x->RecordsPerPage = 50;
	$x->QuickSearch = 1;
	$x->QuickSearchText = $Translation['quick search'];
	$x->ScriptFileName = 'invoices_view.php';
	$x->RedirectAfterInsert = 'invoices_view.php?SelectedID=#ID#';
	$x->TableTitle = 'Invoices';
	$x->TableIcon = 'resources/table_icons/attributes_display.png';
	$x->PrimaryKey = '`invoices`.`id`';
	$x->DefaultSortField = '2';
	$x->DefaultSortDirection = 'desc';

	$x->ColWidth = [60, 70, 100, 250, 200, 100, 70, ];
	$x->ColCaption = ['Code', 'Status', 'Date due', 'Client', 'Client contact', 'Client phone', 'Total', ];
	$x->ColFieldName = ['code', 'status', 'date_due', 'client', 'client_contact', 'client_phone', 'total', ];
	$x->ColNumber  = [2, 3, 4, 5, 6, 8, 15, ];

	// template paths below are based on the app main directory
	$x->Template = 'templates/invoices_templateTV.html';
	$x->SelectedTemplate = 'templates/invoices_templateTVS.html';
	$x->TemplateDV = 'templates/invoices_templateDV.html';
	$x->TemplateDVP = 'templates/invoices_templateDVP.html';

	$x->ShowTableHeader = 1;
	$x->TVClasses = "";
	$x->DVClasses = "";
	$x->HasCalculatedFields = true;
	$x->AllowConsoleLog = false;
	$x->AllowDVNavigation = true;

	// hook: invoices_init
	$render = true;
	if(function_exists('invoices_init')) {
		$args = [];
		$render = invoices_init($x, getMemberInfo(), $args);
	}

	if($render) $x->Render();

	// column sums
	if(strpos($x->HTML, '<!-- tv data below -->')) {
		// if printing multi-selection TV, calculate the sum only for the selected records
		$record_selector = Request::val('record_selector');
		if(Request::val('Print_x') && is_array($record_selector)) {
			$QueryWhere = '';
			foreach($record_selector as $id) {   // get selected records
				if($id != '') $QueryWhere .= "'" . makeSafe($id) . "',";
			}
			if($QueryWhere != '') {
				$QueryWhere = 'where `invoices`.`id` in ('.substr($QueryWhere, 0, -1).')';
			} else { // if no selected records, write the where clause to return an empty result
				$QueryWhere = 'where 1=0';
			}
		} else {
			$QueryWhere = $x->QueryWhere;
		}

		$sumQuery = "SELECT SUM(`invoices`.`total`) FROM {$x->QueryFrom} {$QueryWhere}";
		$res = sql($sumQuery, $eo);
		if($row = db_fetch_row($res)) {
			$sumRow = '<tr class="success sum">';
			if(!Request::val('Print_x')) $sumRow .= '<th class="text-center sum">&sum;</th>';
			$sumRow .= '<td class="invoices-code sum"></td>';
			$sumRow .= '<td class="invoices-status sum"></td>';
			$sumRow .= '<td class="invoices-date_due sum"></td>';
			$sumRow .= '<td class="invoices-client sum"></td>';
			$sumRow .= '<td class="invoices-client_contact sum"></td>';
			$sumRow .= '<td class="invoices-client_phone sum"></td>';
			$sumRow .= "<td class=\"invoices-total text-right sum\">{$row[0]}</td>";
			$sumRow .= '</tr>';

			$x->HTML = str_replace('<!-- tv data below -->', '', $x->HTML);
			$x->HTML = str_replace('<!-- tv data above -->', $sumRow, $x->HTML);
		}
	}

	// hook: invoices_header
	$headerCode = '';
	if(function_exists('invoices_header')) {
		$args = [];
		$headerCode = invoices_header($x->ContentType, getMemberInfo(), $args);
	}

	if(!$headerCode) {
		include_once("{$currDir}/header.php"); 
	} else {
		ob_start();
		include_once("{$currDir}/header.php");
		echo str_replace('<%%HEADER%%>', ob_get_clean(), $headerCode);
	}

	echo $x->HTML;

	// hook: invoices_footer
	$footerCode = '';
	if(function_exists('invoices_footer')) {
		$args = [];
		$footerCode = invoices_footer($x->ContentType, getMemberInfo(), $args);
	}

	if(!$footerCode) {
		include_once("{$currDir}/footer.php"); 
	} else {
		ob_start();
		include_once("{$currDir}/footer.php");
		echo str_replace('<%%FOOTER%%>', ob_get_clean(), $footerCode);
	}
