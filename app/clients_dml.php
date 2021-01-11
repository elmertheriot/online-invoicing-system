<?php

// Data functions (insert, update, delete, form) for table clients

// This script and data application were generated by AppGini 5.93
// Download AppGini for free from https://bigprof.com/appgini/download/

function clients_insert(&$error_message = '') {
	global $Translation;

	// mm: can member insert record?
	$arrPerm = getTablePermissions('clients');
	if(!$arrPerm['insert']) return false;

	$data = [
		'name' => Request::val('name', ''),
		'contact' => Request::val('contact', ''),
		'title' => Request::val('title', ''),
		'address' => br2nl(Request::val('address', '')),
		'city' => Request::val('city', ''),
		'country' => Request::val('country', ''),
		'phone' => Request::val('phone', ''),
		'email' => Request::val('email', ''),
		'website' => Request::val('website', ''),
		'comments' => Request::val('comments', ''),
	];


	// hook: clients_before_insert
	if(function_exists('clients_before_insert')) {
		$args = [];
		if(!clients_before_insert($data, getMemberInfo(), $args)) {
			if(isset($args['error_message'])) $error_message = $args['error_message'];
			return false;
		}
	}

	$error = '';
	// set empty fields to NULL
	$data = array_map(function($v) { return ($v === '' ? NULL : $v); }, $data);
	insert('clients', backtick_keys_once($data), $error);
	if($error)
		die("{$error}<br><a href=\"#\" onclick=\"history.go(-1);\">{$Translation['< back']}</a>");

	$recID = db_insert_id(db_link());

	update_calc_fields('clients', $recID, calculated_fields()['clients']);

	// hook: clients_after_insert
	if(function_exists('clients_after_insert')) {
		$res = sql("SELECT * FROM `clients` WHERE `id`='" . makeSafe($recID, false) . "' LIMIT 1", $eo);
		if($row = db_fetch_assoc($res)) {
			$data = array_map('makeSafe', $row);
		}
		$data['selectedID'] = makeSafe($recID, false);
		$args=[];
		if(!clients_after_insert($data, getMemberInfo(), $args)) { return $recID; }
	}

	// mm: save ownership data
	set_record_owner('clients', $recID, getLoggedMemberID());

	// if this record is a copy of another record, copy children if applicable
	if(!empty($_REQUEST['SelectedID'])) clients_copy_children($recID, $_REQUEST['SelectedID']);

	return $recID;
}

function clients_copy_children($destination_id, $source_id) {
	global $Translation;
	$requests = []; // array of curl handlers for launching insert requests
	$eo = ['silentErrors' => true];
	$safe_sid = makeSafe($source_id);

	// launch requests, asynchronously
	curl_batch($requests);
}

function clients_delete($selected_id, $AllowDeleteOfParents = false, $skipChecks = false) {
	// insure referential integrity ...
	global $Translation;
	$selected_id = makeSafe($selected_id);

	// mm: can member delete record?
	if(!check_record_permission('clients', $selected_id, 'delete')) {
		return $Translation['You don\'t have enough permissions to delete this record'];
	}

	// hook: clients_before_delete
	if(function_exists('clients_before_delete')) {
		$args = [];
		if(!clients_before_delete($selected_id, $skipChecks, getMemberInfo(), $args))
			return $Translation['Couldn\'t delete this record'] . (
				!empty($args['error_message']) ?
					'<div class="text-bold">' . strip_tags($args['error_message']) . '</div>'
					: '' 
			);
	}

	// child table: invoices
	$res = sql("SELECT `id` FROM `clients` WHERE `id`='{$selected_id}'", $eo);
	$id = db_fetch_row($res);
	$rires = sql("SELECT COUNT(1) FROM `invoices` WHERE `client`='" . makeSafe($id[0]) . "'", $eo);
	$rirow = db_fetch_row($rires);
	if($rirow[0] && !$AllowDeleteOfParents && !$skipChecks) {
		$RetMsg = $Translation["couldn't delete"];
		$RetMsg = str_replace('<RelatedRecords>', $rirow[0], $RetMsg);
		$RetMsg = str_replace('<TableName>', 'invoices', $RetMsg);
		return $RetMsg;
	} elseif($rirow[0] && $AllowDeleteOfParents && !$skipChecks) {
		$RetMsg = $Translation['confirm delete'];
		$RetMsg = str_replace('<RelatedRecords>', $rirow[0], $RetMsg);
		$RetMsg = str_replace('<TableName>', 'invoices', $RetMsg);
		$RetMsg = str_replace('<Delete>', '<input type="button" class="button" value="' . $Translation['yes'] . '" onClick="window.location = \'clients_view.php?SelectedID=' . urlencode($selected_id) . '&delete_x=1&confirmed=1\';">', $RetMsg);
		$RetMsg = str_replace('<Cancel>', '<input type="button" class="button" value="' . $Translation[ 'no'] . '" onClick="window.location = \'clients_view.php?SelectedID=' . urlencode($selected_id) . '\';">', $RetMsg);
		return $RetMsg;
	}

	sql("DELETE FROM `clients` WHERE `id`='{$selected_id}'", $eo);

	// hook: clients_after_delete
	if(function_exists('clients_after_delete')) {
		$args = [];
		clients_after_delete($selected_id, getMemberInfo(), $args);
	}

	// mm: delete ownership data
	sql("DELETE FROM `membership_userrecords` WHERE `tableName`='clients' AND `pkValue`='{$selected_id}'", $eo);
}

function clients_update(&$selected_id, &$error_message = '') {
	global $Translation;

	// mm: can member edit record?
	if(!check_record_permission('clients', $selected_id, 'edit')) return false;

	$data = [
		'name' => Request::val('name', ''),
		'contact' => Request::val('contact', ''),
		'title' => Request::val('title', ''),
		'address' => br2nl(Request::val('address', '')),
		'city' => Request::val('city', ''),
		'country' => Request::val('country', ''),
		'phone' => Request::val('phone', ''),
		'email' => Request::val('email', ''),
		'website' => Request::val('website', ''),
		'comments' => Request::val('comments', ''),
	];

	// get existing values
	$old_data = getRecord('clients', $selected_id);
	if(is_array($old_data)) {
		$old_data = array_map('makeSafe', $old_data);
		$old_data['selectedID'] = makeSafe($selected_id);
	}

	$data['selectedID'] = makeSafe($selected_id);

	// hook: clients_before_update
	if(function_exists('clients_before_update')) {
		$args = ['old_data' => $old_data];
		if(!clients_before_update($data, getMemberInfo(), $args)) {
			if(isset($args['error_message'])) $error_message = $args['error_message'];
			return false;
		}
	}

	$set = $data; unset($set['selectedID']);
	foreach ($set as $field => $value) {
		$set[$field] = ($value !== '' && $value !== NULL) ? $value : NULL;
	}

	if(!update(
		'clients', 
		backtick_keys_once($set), 
		['`id`' => $selected_id], 
		$error_message
	)) {
		echo $error_message;
		echo '<a href="clients_view.php?SelectedID=' . urlencode($selected_id) . "\">{$Translation['< back']}</a>";
		exit;
	}


	$eo = ['silentErrors' => true];

	update_calc_fields('clients', $data['selectedID'], calculated_fields()['clients']);

	// hook: clients_after_update
	if(function_exists('clients_after_update')) {
		$res = sql("SELECT * FROM `clients` WHERE `id`='{$data['selectedID']}' LIMIT 1", $eo);
		if($row = db_fetch_assoc($res)) $data = array_map('makeSafe', $row);

		$data['selectedID'] = $data['id'];
		$args = ['old_data' => $old_data];
		if(!clients_after_update($data, getMemberInfo(), $args)) return;
	}

	// mm: update ownership data
	sql("UPDATE `membership_userrecords` SET `dateUpdated`='" . time() . "' WHERE `tableName`='clients' AND `pkValue`='" . makeSafe($selected_id) . "'", $eo);
}

function clients_form($selected_id = '', $AllowUpdate = 1, $AllowInsert = 1, $AllowDelete = 1, $ShowCancel = 0, $TemplateDV = '', $TemplateDVP = '') {
	// function to return an editable form for a table records
	// and fill it with data of record whose ID is $selected_id. If $selected_id
	// is empty, an empty form is shown, with only an 'Add New'
	// button displayed.

	global $Translation;

	// mm: get table permissions
	$arrPerm = getTablePermissions('clients');
	if(!$arrPerm['insert'] && $selected_id=='') { return ''; }
	$AllowInsert = ($arrPerm['insert'] ? true : false);
	// print preview?
	$dvprint = false;
	if($selected_id && $_REQUEST['dvprint_x'] != '') {
		$dvprint = true;
	}


	// populate filterers, starting from children to grand-parents

	// unique random identifier
	$rnd1 = ($dvprint ? rand(1000000, 9999999) : '');
	// combobox: country
	$combo_country = new Combo;
	$combo_country->ListType = 0;
	$combo_country->MultipleSeparator = ', ';
	$combo_country->ListBoxHeight = 10;
	$combo_country->RadiosPerLine = 1;
	if(is_file(dirname(__FILE__).'/hooks/clients.country.csv')) {
		$country_data = addslashes(implode('', @file(dirname(__FILE__).'/hooks/clients.country.csv')));
		$combo_country->ListItem = explode('||', entitiesToUTF8(convertLegacyOptions($country_data)));
		$combo_country->ListData = $combo_country->ListItem;
	} else {
		$combo_country->ListItem = explode('||', entitiesToUTF8(convertLegacyOptions("Afghanistan;;Albania;;Algeria;;American Samoa;;Andorra;;Angola;;Anguilla;;Antarctica;;Antigua, Barbuda;;Argentina;;Armenia;;Aruba;;Australia;;Austria;;Azerbaijan;;Bahamas;;Bahrain;;Bangladesh;;Barbados;;Belarus;;Belgium;;Belize;;Benin;;Bermuda;;Bhutan;;Bolivia;;Bosnia, Herzegovina;;Botswana;;Bouvet Is.;;Brazil;;Brunei Darussalam;;Bulgaria;;Burkina Faso;;Burundi;;Cambodia;;Cameroon;;Canada;;Canary Is.;;Cape Verde;;Cayman Is.;;Central African Rep.;;Chad;;Channel Islands;;Chile;;China;;Christmas Is.;;Cocos Is.;;Colombia;;Comoros;;Congo, D.R. Of;;Congo;;Cook Is.;;Costa Rica;;Croatia;;Cuba;;Cyprus;;Czech Republic;;Denmark;;Djibouti;;Dominica;;Dominican Republic;;Ecuador;;Egypt;;El Salvador;;Equatorial Guinea;;Eritrea;;Estonia;;Ethiopia;;Falkland Is.;;Faroe Is.;;Fiji;;Finland;;France;;French Guiana;;French Polynesia;;French Territories;;Gabon;;Gambia;;Georgia;;Germany;;Ghana;;Gibraltar;;Greece;;Greenland;;Grenada;;Guadeloupe;;Guam;;Guatemala;;Guernsey;;Guinea-bissau;;Guinea;;Guyana;;Haiti;;Heard, Mcdonald Is.;;Honduras;;Hong Kong;;Hungary;;Iceland;;India;;Indonesia;;Iran;;Iraq;;Ireland;;Israel;;Italy;;Ivory Coast;;Jamaica;;Japan;;Jersey;;Jordan;;Kazakhstan;;Kenya;;Kiribati;;Korea, D.P.R Of;;Korea, Rep. Of;;Kuwait;;Kyrgyzstan;;Lao Peoples D.R.;;Latvia;;Lebanon;;Lesotho;;Liberia;;Libyan Arab Jamahiriya;;Liechtenstein;;Lithuania;;Luxembourg;;Macao;;Macedonia, F.Y.R Of;;Madagascar;;Malawi;;Malaysia;;Maldives;;Mali;;Malta;;Mariana Islands;;Marshall Islands;;Martinique;;Mauritania;;Mauritius;;Mayotte;;Mexico;;Micronesia;;Moldova;;Monaco;;Mongolia;;Montserrat;;Morocco;;Mozambique;;Myanmar;;Namibia;;Nauru;;Nepal;;Netherlands Antilles;;Netherlands;;New Caledonia;;New Zealand;;Nicaragua;;Niger;;Nigeria;;Niue;;Norfolk Island;;North Korea;;Norway;;Oman;;Pakistan;;Palau;;Palestinian Terr.;;Panama;;Papua New Guinea;;Paraguay;;Peru;;Philippines;;Pitcairn;;Poland;;Portugal;;Puerto Rico;;Qatar;;Reunion;;Romania;;Russia;;Russian Federation;;Rwanda;;Samoa;;San Marino;;Sao Tome, Principe;;Saudi Arabia;;Senegal;;Serbia;;Seychelles;;Sierra Leone;;Singapore;;Slovakia;;Slovenia;;Solomon Is.;;Somalia;;South Africa;;South Georgia;;South Sandwich Is.;;Spain;;Sri Lanka;;St. Helena;;St. Kitts, Nevis;;St. Lucia;;St. Pierre, Miquelon;;St. Vincent, Grenadines;;Sudan;;Suriname;;Svalbard, Jan Mayen;;Swaziland;;Sweden;;Switzerland;;Syrian Arab Republic;;Taiwan;;Tajikistan;;Tanzania;;Thailand;;Timor-leste;;Togo;;Tokelau;;Tonga;;Trinidad, Tobago;;Tunisia;;Turkey;;Turkmenistan;;Turks, Caicoss;;Tuvalu;;Uganda;;Ukraine;;United Arab Emirates;;United Kingdom;;United States;;Uruguay;;Uzbekistan;;Vanuatu;;Vatican City;;Venezuela;;Viet Nam;;Virgin Is. British;;Virgin Is. U.S.;;Wallis, Futuna;;Western Sahara;;Yemen;;Yugoslavia;;Zambia;;Zimbabwe")));
		$combo_country->ListData = $combo_country->ListItem;
	}
	$combo_country->SelectName = 'country';

	if($selected_id) {
		// mm: check member permissions
		if(!$arrPerm['view']) return '';

		// mm: who is the owner?
		$ownerGroupID = sqlValue("SELECT `groupID` FROM `membership_userrecords` WHERE `tableName`='clients' AND `pkValue`='" . makeSafe($selected_id) . "'");
		$ownerMemberID = sqlValue("SELECT LCASE(`memberID`) FROM `membership_userrecords` WHERE `tableName`='clients' AND `pkValue`='" . makeSafe($selected_id) . "'");

		if($arrPerm['view'] == 1 && getLoggedMemberID() != $ownerMemberID) return '';
		if($arrPerm['view'] == 2 && getLoggedGroupID() != $ownerGroupID) return '';

		// can edit?
		$AllowUpdate = 0;
		if(($arrPerm['edit'] == 1 && $ownerMemberID == getLoggedMemberID()) || ($arrPerm['edit'] == 2 && $ownerGroupID == getLoggedGroupID()) || $arrPerm['edit'] == 3) {
			$AllowUpdate = 1;
		}

		$res = sql("SELECT * FROM `clients` WHERE `id`='" . makeSafe($selected_id) . "'", $eo);
		if(!($row = db_fetch_array($res))) {
			return error_message($Translation['No records found'], 'clients_view.php', false);
		}
		$combo_country->SelectedData = $row['country'];
		$urow = $row; /* unsanitized data */
		$hc = new CI_Input(datalist_db_encoding);
		$row = $hc->xss_clean($row); /* sanitize data */
	} else {
		$combo_country->SelectedText = ( $_REQUEST['FilterField'][1] == '7' && $_REQUEST['FilterOperator'][1] == '<=>' ? $_REQUEST['FilterValue'][1] : '');
	}
	$combo_country->Render();

	ob_start();
	?>

	<script>
		// initial lookup values

		jQuery(function() {
			setTimeout(function() {
			}, 50); /* we need to slightly delay client-side execution of the above code to allow AppGini.ajaxCache to work */
		});
	</script>
	<?php

	$lookups = str_replace('__RAND__', $rnd1, ob_get_contents());
	ob_end_clean();


	// code for template based detail view forms

	// open the detail view template
	if($dvprint) {
		$template_file = is_file("./{$TemplateDVP}") ? "./{$TemplateDVP}" : './templates/clients_templateDVP.html';
		$templateCode = @file_get_contents($template_file);
	} else {
		$template_file = is_file("./{$TemplateDV}") ? "./{$TemplateDV}" : './templates/clients_templateDV.html';
		$templateCode = @file_get_contents($template_file);
	}

	// process form title
	$templateCode = str_replace('<%%DETAIL_VIEW_TITLE%%>', 'Client data', $templateCode);
	$templateCode = str_replace('<%%RND1%%>', $rnd1, $templateCode);
	$templateCode = str_replace('<%%EMBEDDED%%>', ($_REQUEST['Embedded'] ? 'Embedded=1' : ''), $templateCode);
	// process buttons
	if($arrPerm[1] && !$selected_id) { // allow insert and no record selected?
		if(!$selected_id) $templateCode = str_replace('<%%INSERT_BUTTON%%>', '<button type="submit" class="btn btn-success" id="insert" name="insert_x" value="1" onclick="return clients_validateData();"><i class="glyphicon glyphicon-plus-sign"></i> ' . $Translation['Save New'] . '</button>', $templateCode);
		$templateCode = str_replace('<%%INSERT_BUTTON%%>', '<button type="submit" class="btn btn-default" id="insert" name="insert_x" value="1" onclick="return clients_validateData();"><i class="glyphicon glyphicon-plus-sign"></i> ' . $Translation['Save As Copy'] . '</button>', $templateCode);
	} else {
		$templateCode = str_replace('<%%INSERT_BUTTON%%>', '', $templateCode);
	}

	// 'Back' button action
	if($_REQUEST['Embedded']) {
		$backAction = 'AppGini.closeParentModal(); return false;';
	} else {
		$backAction = '$j(\'form\').eq(0).attr(\'novalidate\', \'novalidate\'); document.myform.reset(); return true;';
	}

	if($selected_id) {
		if(!$_REQUEST['Embedded']) $templateCode = str_replace('<%%DVPRINT_BUTTON%%>', '<button type="submit" class="btn btn-default" id="dvprint" name="dvprint_x" value="1" onclick="$j(\'form\').eq(0).prop(\'novalidate\', true); document.myform.reset(); return true;" title="' . html_attr($Translation['Print Preview']) . '"><i class="glyphicon glyphicon-print"></i> ' . $Translation['Print Preview'] . '</button>', $templateCode);
		if($AllowUpdate) {
			$templateCode = str_replace('<%%UPDATE_BUTTON%%>', '<button type="submit" class="btn btn-success btn-lg" id="update" name="update_x" value="1" onclick="return clients_validateData();" title="' . html_attr($Translation['Save Changes']) . '"><i class="glyphicon glyphicon-ok"></i> ' . $Translation['Save Changes'] . '</button>', $templateCode);
		} else {
			$templateCode = str_replace('<%%UPDATE_BUTTON%%>', '', $templateCode);
		}
		if(($arrPerm[4]==1 && $ownerMemberID==getLoggedMemberID()) || ($arrPerm[4]==2 && $ownerGroupID==getLoggedGroupID()) || $arrPerm[4]==3) { // allow delete?
			$templateCode = str_replace('<%%DELETE_BUTTON%%>', '<button type="submit" class="btn btn-danger" id="delete" name="delete_x" value="1" onclick="return confirm(\'' . $Translation['are you sure?'] . '\');" title="' . html_attr($Translation['Delete']) . '"><i class="glyphicon glyphicon-trash"></i> ' . $Translation['Delete'] . '</button>', $templateCode);
		} else {
			$templateCode = str_replace('<%%DELETE_BUTTON%%>', '', $templateCode);
		}
		$templateCode = str_replace('<%%DESELECT_BUTTON%%>', '<button type="submit" class="btn btn-default" id="deselect" name="deselect_x" value="1" onclick="' . $backAction . '" title="' . html_attr($Translation['Back']) . '"><i class="glyphicon glyphicon-chevron-left"></i> ' . $Translation['Back'] . '</button>', $templateCode);
	} else {
		$templateCode = str_replace('<%%UPDATE_BUTTON%%>', '', $templateCode);
		$templateCode = str_replace('<%%DELETE_BUTTON%%>', '', $templateCode);
		$templateCode = str_replace('<%%DESELECT_BUTTON%%>', ($ShowCancel ? '<button type="submit" class="btn btn-default" id="deselect" name="deselect_x" value="1" onclick="' . $backAction . '" title="' . html_attr($Translation['Back']) . '"><i class="glyphicon glyphicon-chevron-left"></i> ' . $Translation['Back'] . '</button>' : ''), $templateCode);
	}

	// set records to read only if user can't insert new records and can't edit current record
	if(($selected_id && !$AllowUpdate) || (!$selected_id && !$AllowInsert)) {
		$jsReadOnly .= "\tjQuery('#name').replaceWith('<div class=\"form-control-static\" id=\"name\">' + (jQuery('#name').val() || '') + '</div>');\n";
		$jsReadOnly .= "\tjQuery('#contact').replaceWith('<div class=\"form-control-static\" id=\"contact\">' + (jQuery('#contact').val() || '') + '</div>');\n";
		$jsReadOnly .= "\tjQuery('#title').replaceWith('<div class=\"form-control-static\" id=\"title\">' + (jQuery('#title').val() || '') + '</div>');\n";
		$jsReadOnly .= "\tjQuery('#address').replaceWith('<div class=\"form-control-static\" id=\"address\">' + (jQuery('#address').val() || '') + '</div>');\n";
		$jsReadOnly .= "\tjQuery('#city').replaceWith('<div class=\"form-control-static\" id=\"city\">' + (jQuery('#city').val() || '') + '</div>');\n";
		$jsReadOnly .= "\tjQuery('#country').replaceWith('<div class=\"form-control-static\" id=\"country\">' + (jQuery('#country').val() || '') + '</div>'); jQuery('#country-multi-selection-help').hide();\n";
		$jsReadOnly .= "\tjQuery('#phone').replaceWith('<div class=\"form-control-static\" id=\"phone\">' + (jQuery('#phone').val() || '') + '</div>');\n";
		$jsReadOnly .= "\tjQuery('#email').replaceWith('<div class=\"form-control-static\" id=\"email\">' + (jQuery('#email').val() || '') + '</div>');\n";
		$jsReadOnly .= "\tjQuery('#email, #email-edit-link').hide();\n";
		$jsReadOnly .= "\tjQuery('#website').replaceWith('<div class=\"form-control-static\" id=\"website\">' + (jQuery('#website').val() || '') + '</div>');\n";
		$jsReadOnly .= "\tjQuery('#website, #website-edit-link').hide();\n";
		$jsReadOnly .= "\tjQuery('.select2-container').hide();\n";

		$noUploads = true;
	} elseif(($AllowInsert && !$selected_id) || ($AllowUpdate && $selected_id)) {
		$jsEditable .= "\tjQuery('form').eq(0).data('already_changed', true);"; // temporarily disable form change handler
			$jsEditable .= "\tjQuery('form').eq(0).data('already_changed', false);"; // re-enable form change handler
	}

	// process combos
	$templateCode = str_replace('<%%COMBO(country)%%>', $combo_country->HTML, $templateCode);
	$templateCode = str_replace('<%%COMBOTEXT(country)%%>', $combo_country->SelectedData, $templateCode);

	/* lookup fields array: 'lookup field name' => array('parent table name', 'lookup field caption') */
	$lookup_fields = array();
	foreach($lookup_fields as $luf => $ptfc) {
		$pt_perm = getTablePermissions($ptfc[0]);

		// process foreign key links
		if($pt_perm['view'] || $pt_perm['edit']) {
			$templateCode = str_replace("<%%PLINK({$luf})%%>", '<button type="button" class="btn btn-default view_parent hspacer-md" id="' . $ptfc[0] . '_view_parent" title="' . html_attr($Translation['View'] . ' ' . $ptfc[1]) . '"><i class="glyphicon glyphicon-eye-open"></i></button>', $templateCode);
		}

		// if user has insert permission to parent table of a lookup field, put an add new button
		if($pt_perm['insert'] && !$_REQUEST['Embedded']) {
			$templateCode = str_replace("<%%ADDNEW({$ptfc[0]})%%>", '<button type="button" class="btn btn-success add_new_parent hspacer-md" id="' . $ptfc[0] . '_add_new" title="' . html_attr($Translation['Add New'] . ' ' . $ptfc[1]) . '"><i class="glyphicon glyphicon-plus-sign"></i></button>', $templateCode);
		}
	}

	// process images
	$templateCode = str_replace('<%%UPLOADFILE(id)%%>', '', $templateCode);
	$templateCode = str_replace('<%%UPLOADFILE(name)%%>', '', $templateCode);
	$templateCode = str_replace('<%%UPLOADFILE(contact)%%>', '', $templateCode);
	$templateCode = str_replace('<%%UPLOADFILE(title)%%>', '', $templateCode);
	$templateCode = str_replace('<%%UPLOADFILE(address)%%>', '', $templateCode);
	$templateCode = str_replace('<%%UPLOADFILE(city)%%>', '', $templateCode);
	$templateCode = str_replace('<%%UPLOADFILE(country)%%>', '', $templateCode);
	$templateCode = str_replace('<%%UPLOADFILE(phone)%%>', '', $templateCode);
	$templateCode = str_replace('<%%UPLOADFILE(email)%%>', '', $templateCode);
	$templateCode = str_replace('<%%UPLOADFILE(website)%%>', '', $templateCode);
	$templateCode = str_replace('<%%UPLOADFILE(comments)%%>', '', $templateCode);
	$templateCode = str_replace('<%%UPLOADFILE(unpaid_sales)%%>', '', $templateCode);
	$templateCode = str_replace('<%%UPLOADFILE(paid_sales)%%>', '', $templateCode);
	$templateCode = str_replace('<%%UPLOADFILE(total_sales)%%>', '', $templateCode);

	// process values
	if($selected_id) {
		if( $dvprint) $templateCode = str_replace('<%%VALUE(id)%%>', safe_html($urow['id']), $templateCode);
		if(!$dvprint) $templateCode = str_replace('<%%VALUE(id)%%>', html_attr($row['id']), $templateCode);
		$templateCode = str_replace('<%%URLVALUE(id)%%>', urlencode($urow['id']), $templateCode);
		if( $dvprint) $templateCode = str_replace('<%%VALUE(name)%%>', safe_html($urow['name']), $templateCode);
		if(!$dvprint) $templateCode = str_replace('<%%VALUE(name)%%>', html_attr($row['name']), $templateCode);
		$templateCode = str_replace('<%%URLVALUE(name)%%>', urlencode($urow['name']), $templateCode);
		if( $dvprint) $templateCode = str_replace('<%%VALUE(contact)%%>', safe_html($urow['contact']), $templateCode);
		if(!$dvprint) $templateCode = str_replace('<%%VALUE(contact)%%>', html_attr($row['contact']), $templateCode);
		$templateCode = str_replace('<%%URLVALUE(contact)%%>', urlencode($urow['contact']), $templateCode);
		if( $dvprint) $templateCode = str_replace('<%%VALUE(title)%%>', safe_html($urow['title']), $templateCode);
		if(!$dvprint) $templateCode = str_replace('<%%VALUE(title)%%>', html_attr($row['title']), $templateCode);
		$templateCode = str_replace('<%%URLVALUE(title)%%>', urlencode($urow['title']), $templateCode);
		if($dvprint || (!$AllowUpdate && !$AllowInsert)) {
			$templateCode = str_replace('<%%VALUE(address)%%>', safe_html($urow['address']), $templateCode);
		} else {
			$templateCode = str_replace('<%%VALUE(address)%%>', html_attr($row['address']), $templateCode);
		}
		$templateCode = str_replace('<%%URLVALUE(address)%%>', urlencode($urow['address']), $templateCode);
		if( $dvprint) $templateCode = str_replace('<%%VALUE(city)%%>', safe_html($urow['city']), $templateCode);
		if(!$dvprint) $templateCode = str_replace('<%%VALUE(city)%%>', html_attr($row['city']), $templateCode);
		$templateCode = str_replace('<%%URLVALUE(city)%%>', urlencode($urow['city']), $templateCode);
		if( $dvprint) $templateCode = str_replace('<%%VALUE(country)%%>', safe_html($urow['country']), $templateCode);
		if(!$dvprint) $templateCode = str_replace('<%%VALUE(country)%%>', html_attr($row['country']), $templateCode);
		$templateCode = str_replace('<%%URLVALUE(country)%%>', urlencode($urow['country']), $templateCode);
		if( $dvprint) $templateCode = str_replace('<%%VALUE(phone)%%>', safe_html($urow['phone']), $templateCode);
		if(!$dvprint) $templateCode = str_replace('<%%VALUE(phone)%%>', html_attr($row['phone']), $templateCode);
		$templateCode = str_replace('<%%URLVALUE(phone)%%>', urlencode($urow['phone']), $templateCode);
		if( $dvprint) $templateCode = str_replace('<%%VALUE(email)%%>', safe_html($urow['email']), $templateCode);
		if(!$dvprint) $templateCode = str_replace('<%%VALUE(email)%%>', html_attr($row['email']), $templateCode);
		$templateCode = str_replace('<%%URLVALUE(email)%%>', urlencode($urow['email']), $templateCode);
		if( $dvprint) $templateCode = str_replace('<%%VALUE(website)%%>', safe_html($urow['website']), $templateCode);
		if(!$dvprint) $templateCode = str_replace('<%%VALUE(website)%%>', html_attr($row['website']), $templateCode);
		$templateCode = str_replace('<%%URLVALUE(website)%%>', urlencode($urow['website']), $templateCode);
		if($AllowUpdate || $AllowInsert) {
			$templateCode = str_replace('<%%HTMLAREA(comments)%%>', '<textarea name="comments" id="comments" rows="5">' . html_attr($row['comments']) . '</textarea>', $templateCode);
		} else {
			$templateCode = str_replace('<%%HTMLAREA(comments)%%>', '<div id="comments" class="form-control-static">' . $row['comments'] . '</div>', $templateCode);
		}
		$templateCode = str_replace('<%%VALUE(comments)%%>', nl2br($row['comments']), $templateCode);
		$templateCode = str_replace('<%%URLVALUE(comments)%%>', urlencode($urow['comments']), $templateCode);
		$templateCode = str_replace('<%%VALUE(unpaid_sales)%%>', safe_html($urow['unpaid_sales']), $templateCode);
		$templateCode = str_replace('<%%URLVALUE(unpaid_sales)%%>', urlencode($urow['unpaid_sales']), $templateCode);
		$templateCode = str_replace('<%%VALUE(paid_sales)%%>', safe_html($urow['paid_sales']), $templateCode);
		$templateCode = str_replace('<%%URLVALUE(paid_sales)%%>', urlencode($urow['paid_sales']), $templateCode);
		$templateCode = str_replace('<%%VALUE(total_sales)%%>', safe_html($urow['total_sales']), $templateCode);
		$templateCode = str_replace('<%%URLVALUE(total_sales)%%>', urlencode($urow['total_sales']), $templateCode);
	} else {
		$templateCode = str_replace('<%%VALUE(id)%%>', '', $templateCode);
		$templateCode = str_replace('<%%URLVALUE(id)%%>', urlencode(''), $templateCode);
		$templateCode = str_replace('<%%VALUE(name)%%>', '', $templateCode);
		$templateCode = str_replace('<%%URLVALUE(name)%%>', urlencode(''), $templateCode);
		$templateCode = str_replace('<%%VALUE(contact)%%>', '', $templateCode);
		$templateCode = str_replace('<%%URLVALUE(contact)%%>', urlencode(''), $templateCode);
		$templateCode = str_replace('<%%VALUE(title)%%>', '', $templateCode);
		$templateCode = str_replace('<%%URLVALUE(title)%%>', urlencode(''), $templateCode);
		$templateCode = str_replace('<%%VALUE(address)%%>', '', $templateCode);
		$templateCode = str_replace('<%%URLVALUE(address)%%>', urlencode(''), $templateCode);
		$templateCode = str_replace('<%%VALUE(city)%%>', '', $templateCode);
		$templateCode = str_replace('<%%URLVALUE(city)%%>', urlencode(''), $templateCode);
		$templateCode = str_replace('<%%VALUE(country)%%>', '', $templateCode);
		$templateCode = str_replace('<%%URLVALUE(country)%%>', urlencode(''), $templateCode);
		$templateCode = str_replace('<%%VALUE(phone)%%>', '', $templateCode);
		$templateCode = str_replace('<%%URLVALUE(phone)%%>', urlencode(''), $templateCode);
		$templateCode = str_replace('<%%VALUE(email)%%>', '', $templateCode);
		$templateCode = str_replace('<%%URLVALUE(email)%%>', urlencode(''), $templateCode);
		$templateCode = str_replace('<%%VALUE(website)%%>', '', $templateCode);
		$templateCode = str_replace('<%%URLVALUE(website)%%>', urlencode(''), $templateCode);
		$templateCode = str_replace('<%%HTMLAREA(comments)%%>', '<textarea name="comments" id="comments" rows="5"></textarea>', $templateCode);
		$templateCode = str_replace('<%%VALUE(unpaid_sales)%%>', '', $templateCode);
		$templateCode = str_replace('<%%URLVALUE(unpaid_sales)%%>', urlencode(''), $templateCode);
		$templateCode = str_replace('<%%VALUE(paid_sales)%%>', '', $templateCode);
		$templateCode = str_replace('<%%URLVALUE(paid_sales)%%>', urlencode(''), $templateCode);
		$templateCode = str_replace('<%%VALUE(total_sales)%%>', '', $templateCode);
		$templateCode = str_replace('<%%URLVALUE(total_sales)%%>', urlencode(''), $templateCode);
	}

	// process translations
	$templateCode = parseTemplate($templateCode);

	// clear scrap
	$templateCode = str_replace('<%%', '<!-- ', $templateCode);
	$templateCode = str_replace('%%>', ' -->', $templateCode);

	// hide links to inaccessible tables
	if($_REQUEST['dvprint_x'] == '') {
		$templateCode .= "\n\n<script>\$j(function() {\n";
		$arrTables = getTableList();
		foreach($arrTables as $name => $caption) {
			$templateCode .= "\t\$j('#{$name}_link').removeClass('hidden');\n";
			$templateCode .= "\t\$j('#xs_{$name}_link').removeClass('hidden');\n";
		}

		$templateCode .= $jsReadOnly;
		$templateCode .= $jsEditable;

		if(!$selected_id) {
			$templateCode.="\n\tif(document.getElementById('emailEdit')) { document.getElementById('emailEdit').style.display='inline'; }";
			$templateCode.="\n\tif(document.getElementById('emailEditLink')) { document.getElementById('emailEditLink').style.display='none'; }";
			$templateCode.="\n\tif(document.getElementById('websiteEdit')) { document.getElementById('websiteEdit').style.display='inline'; }";
			$templateCode.="\n\tif(document.getElementById('websiteEditLink')) { document.getElementById('websiteEditLink').style.display='none'; }";
		}

		$templateCode.="\n});</script>\n";
	}

	// ajaxed auto-fill fields
	$templateCode .= '<script>';
	$templateCode .= '$j(function() {';


	$templateCode.="});";
	$templateCode.="</script>";
	$templateCode .= $lookups;

	// handle enforced parent values for read-only lookup fields

	// don't include blank images in lightbox gallery
	$templateCode = preg_replace('/blank.gif" data-lightbox=".*?"/', 'blank.gif"', $templateCode);

	// don't display empty email links
	$templateCode=preg_replace('/<a .*?href="mailto:".*?<\/a>/', '', $templateCode);

	/* default field values */
	$rdata = $jdata = get_defaults('clients');
	if($selected_id) {
		$jdata = get_joined_record('clients', $selected_id);
		if($jdata === false) $jdata = get_defaults('clients');
		$rdata = $row;
	}
	$templateCode .= loadView('clients-ajax-cache', array('rdata' => $rdata, 'jdata' => $jdata));

	// hook: clients_dv
	if(function_exists('clients_dv')) {
		$args=[];
		clients_dv(($selected_id ? $selected_id : FALSE), getMemberInfo(), $templateCode, $args);
	}

	return $templateCode;
}