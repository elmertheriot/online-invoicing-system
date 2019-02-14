<?php
	function detect_config($redirect_to_setup = true){
		$config_exists = is_readable(dirname(__FILE__) . '/config.php');

		if(!$config_exists && $redirect_to_setup){
			$url = (request_outside_admin_folder() ? '' : '../') . 'setup.php';

			if(!headers_sent()){
				@header("Location: $url");
			}else{
				echo '<META HTTP-EQUIV="Refresh" CONTENT="0;url=' . $url . '">' .
					 '<script>window.location = "' . $url . '";</script>';
			}
			exit;
		}

		if($redirect_to_setup) update_config_app_uri();
		return $config_exists;
	}

	function migrate_config(){
		$curr_dir = dirname(__FILE__);
		if(!is_readable($curr_dir . '/admin/incConfig.php') || !detect_config(false)){
			return false; // nothing to migrate
		}

		@include($curr_dir . '/admin/incConfig.php');
		@include($curr_dir . '/config.php');

		$config_array = array(
			'dbServer' => $dbServer,
			'dbUsername' => $dbUsername,
			'dbPassword' => $dbPassword,
			'dbDatabase' => $dbDatabase,
			'adminConfig' => $adminConfig
		);

		if(isset($appURI)) $config_array['appURI'] = $appURI;

		if(save_config($config_array)){
			@rename($curr_dir . '/admin/incConfig.php', $curr_dir . '/admin/incConfig.bak.php');
			@unlink($curr_dir . '/admin/incConfig.php');
			return true;
		}

		return false;
	}

	function save_config($config_array = array()){
		$curr_dir = dirname(__FILE__);
		if(!count($config_array) || !count($config_array['adminConfig'])) return array('error' => 'Invalid config array');

		$new_admin_config = '';
		foreach($config_array['adminConfig'] as $admin_var => $admin_val){
			$new_admin_config .= "\t\t'" . addslashes($admin_var) . "' => \"" . str_replace(array("\n", "\r", '"', '$'), array('\n', '\r', '\"', '\$'), $admin_val) . "\",\n";
		}
		$new_admin_config = substr($new_admin_config, 0, -2) . "\n";

		$new_config = "<?php\n" . 
			"\t\$dbServer = '" . addslashes($config_array['dbServer']) . "';\n" .
			"\t\$dbUsername = '" . addslashes($config_array['dbUsername']) . "';\n" .
			"\t\$dbPassword = '" . addslashes($config_array['dbPassword']) . "';\n" .
			"\t\$dbDatabase = '" . addslashes($config_array['dbDatabase']) . "';\n" .

			(isset($config_array['appURI']) ? "\t\$appURI = '" . addslashes($config_array['appURI']) . "';\n" : '') .

			"\n\t\$adminConfig = array(\n" . 
				$new_admin_config .
			"\t);";

		if(detect_config(false)){
			// attempt to back up config
			@copy($curr_dir . '/config.php', $curr_dir . '/config.bak.php');
		}

		if(!$fp = @fopen($curr_dir . '/config.php', 'w')) return array('error' => 'Unable to write to config file', 'config' => $new_config);
		fwrite($fp, $new_config);
		fclose($fp);

		return true;
	}

	function request_outside_admin_folder(){
		return (realpath(dirname(__FILE__)) == realpath(dirname($_SERVER['SCRIPT_FILENAME'])) ? true : false);
	}

	function config($var, $force_reload = false){
		static $config;

		$default_config = array(
			'dbServer' => '',
			'dbUsername' => '',
			'dbPassword' => '',
			'dbDatabase' => '',
			'appURI' => '',

			'adminConfig' => array(
				'adminUsername' => '',
				'adminPassword' => '',
				'notifyAdminNewMembers' => false,
				'defaultSignUp' => 1,
				'anonymousGroup' => 'anonymous',
				'anonymousMember' => 'guest',
				'groupsPerPage' => 10,
				'membersPerPage' => 10,
				'recordsPerPage' => 10,
				'custom1' => 'Full Name',
				'custom2' => 'Address',
				'custom3' => 'City',
				'custom4' => 'State',
				'MySQLDateFormat' => '%d/%m/%Y',
				'PHPDateFormat' => 'j/n/Y',
				'PHPDateTimeFormat' => 'd/m/Y, h:i a',
				'senderName' => 'Membership management',
				'senderEmail' => '',
				'approvalSubject' => 'Your membership is now approved',
				'approvalMessage' => "Dear member,\n\nYour membership is now approved by the admin. You can log in to your account here:\nhttp://{$_SERVER['HTTP_HOST']}" . rtrim(dirname($_SERVER['PHP_SELF']), '/\\') . "\n\nRegards,\nAdmin",
				'hide_twitter_feed' => false,
				'maintenance_mode_message' => '<b>Our website is currently down for maintenance</b><br>\r\nWe expect to be back in a couple hours. Thanks for your patience.',
				'mail_function' => 'mail',
				'smtp_server' => '',
				'smtp_encryption' => '',
				'smtp_port' => 25,
				'smtp_user' => '',
				'smtp_pass' => ''
			)
		);

		if(!isset($config) || $force_reload){
			@include(dirname(__FILE__) . '/config.php');

			$config['dbServer'] = $dbServer;
			$config['dbDatabase'] = $dbDatabase;
			$config['dbPassword'] = $dbPassword;
			$config['dbUsername'] = $dbUsername;
			$config['appURI'] = $appURI;
			$config['adminConfig'] = $adminConfig;
		}

		return (isset($config[$var]) && $config[$var] ? $config[$var] : $default_config[$var]);
	}

	/* 
		handling password hashing in old PHP versions
			to hash a password, store the return val of 
				password_hash($pass, PASSWORD_DEFAULT) 

			to verify:
				if(!password_match($pass, $hash)) { no_match_code_here }

			to migrate old hashes:
				password_harden($user, $pass, $hash);
	*/
	if(!defined('PASSWORD_DEFAULT')) {
		define('PASSWORD_DEFAULT', 1);
	}

	if (!function_exists('password_hash')) {
		function password_hash($password, $algo, $options = array()) {
			return md5($password);
		}
	}

	if (!function_exists('password_verify')) {
		function password_verify($password, $hash) {
			return (md5($password) == $hash);
		}
	}

	/**
	 *  @brief check if given password matches given hash, preserving backward compatibility with MD5
	 *  
	 *  @param [in] $password Description for $password
	 *  @param [in] $hash Description for $hash
	 *  @return Boolean indicating match or no match
	 */
	function password_match($password, $hash) {
		if(strlen($hash) == 32) return (md5($password) == $hash);
		return password_verify($password, $hash);
	}

	/**
	 *  @brief Migrate MD5 pass hashes to BCrypt if supported
	 *  
	 *  @param [in] $user username to migrate
	 *  @param [in] $pass current password
	 *  @param [in] $hash stored hash of password
	 */
	function password_harden($user, $pass, $hash) {
		/* continue only if PHP 5.5+ and hash is 32 chars (md5) */
		if(version_compare(PHP_VERSION, '5.5.0') == -1 || strlen($hash) > 32) return;

		$new_hash = password_hash($pass, PASSWORD_DEFAULT);
		$suser = makeSafe($user, false);
		sql("update `membership_users` set `passMD5`='{$new_hash}' where `memberID`='{$suser}'", $eo);
	}

	function update_config_app_uri() {
		// update only if we're on homepage
		if(!defined('HOMEPAGE')) return;
		if(!preg_match('/index\.php$/', $_SERVER['SCRIPT_NAME'])) return;

		// config exists?
		@include(dirname(__FILE__) . '/config.php');
		if(!isset($dbServer)) return;

		// check if appURI defined
		if(isset($appURI)) return;

		// now set appURI, knowing that we're on homepage
		save_config(array(
			'dbServer' => $dbServer,
			'dbUsername' => $dbUsername,
			'dbPassword' => $dbPassword,
			'dbDatabase' => $dbDatabase,
			'appURI' => trim(dirname($_SERVER['SCRIPT_NAME']), '/'),
			'adminConfig' => $adminConfig
		));
	}

