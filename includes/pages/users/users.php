<?php
/*
 * WiND - Wireless Nodes Database
 *
 * Copyright (C) 2005 Nikolaos Nikalexis <winner@cube.gr>
 * 
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; version 2 dated June, 1991.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 *
 */

if (get('action') == 'restore') include_once($root_path."includes/pages/users/users_restore.php");

class users {

	var $tpl;
	var $restore;
	
	function users() {
		if (get('action') == 'restore') {
			$this->restore = new users_restore;
		}
	}
	
	function form_user() {
		global $main;
		$form_user = new form(array('FORM_NAME' => 'form_user'));
		$form_user->db_data('users.username, users.password, users.surname, users.name, users.email, users.phone, users.info'.($main->userdata->privileges['admin'] === TRUE?', rights.type, users.status':''));
		$form_user->db_data_values("users", "id", get('user'));
		$form_user->data[1]['value'] = '';
		array_splice($form_user->data, 2, 0, array($form_user->data[1]));
		$form_user->data[2]['Field'] .= '_c';
		$form_user->data[2]['fullField'] .= '_c';
		if ($main->userdata->privileges['admin'] === TRUE) {
			$form_user->data[8]['Type'] = 'enum_multi';
			$form_user->db_data_values_multi("rights", "user_id", get('user'), 'type');			
		}
		return $form_user;
	}

	function output() {
		global $main, $construct, $db;
		if (get('action') == 'activate') {
			$t = $db->get('account_code', 'users', "id = '".get('user')."'");
			if ($t[0]['account_code'] == get('account_code')) {
				$db->set('users', array('status' => 'activated'), "id = '".get('user')."'");
				$main->message->set_fromlang('info', 'activation_success');
			} else {
				$main->message->set_fromlang('info', 'activation_failed');
			}
			return;
		}
		if (get('action') == 'logout') {
			$main->userdata->logout();
			$redirect = get('redirect');
			$redirect = ($redirect == ""?makelink():$redirect);
			$main->message->set_fromlang('info', 'logout_success', $redirect);
			return;
		}
		if (get('action') == 'restore') {
			return $this->restore->output();
		}
		if ($_SERVER['REQUEST_METHOD'] == 'POST' && method_exists($this, 'output_onpost_'.$_POST['form_name'])) return call_user_func(array($this, 'output_onpost_'.$_POST['form_name']));
		if (get('user') != '') {
			$this->tpl['user_method'] = (get('user') == 'add' ? 'add' : 'edit');
			$this->tpl['form_user'] = $construct->form($this->form_user(), __FILE__);
		}
		return template($this->tpl, __FILE__);
	}
	
	function output_onpost_form_user() {
		global $main, $db, $vars, $lang;
		
		if ($_POST['users__password'] != $_POST['users__password_c']) {
			$main->message->set_fromlang('error', 'password_not_match');
			return;
		}					
		if ($_POST['users__password'] == '' && get('user') != 'add') {
			unset($_POST['users__password']);
		} else {
			if ($_POST['users__password'] == '') {
				$main->message->set_fromlang('error', 'password_not_valid');
				return;
			}
			$_POST['users__password'] = md5($_POST['users__password']);
		}
		if (get('user') != 'add') $v_old = $db->get('email', 'users', "id = '".get('user')."'");
		$ret = TRUE;
		$form_user = $this->form_user();
		array_splice($form_user->data, 2, 1);
		if ($_POST['users__password'] == '') array_splice($form_user->data, 1, 1);
		$a = array('date_in' => date_now());
		if (get('user') == 'add') {
			$a['status'] = 'pending';	
			$a['account_code'] = generate_account_code();
		}
		$ret = $form_user->db_set($a, "users", "id", get('user'));
		if (get('user') == 'add') {
			$ins_id = $db->insert_id;
		} else {
			$ins_id = get('user');
			$a['account_code'] = generate_account_code();
		}
		if ($ret && $main->userdata->privileges['admin'] === TRUE) {
			$ret = $form_user->db_set_multi(array(), "rights", "user_id", get('user'));
		}
		if ($ret && (get('user') == 'add' || $v_old[0]['email'] != $_POST['users__email'])) {
			if (get('user') == 'add') {
				$t = 'user_activation';
			} else {
				$t = 'user_change_email';
			}
			$subject = $lang['email'][$t]['subject'];
			$subject = str_replace('##username##', $_POST['users__username'], $subject);
			$body = $lang['email'][$t]['body'];
			$body = str_replace('##username##', $_POST['users__username'], $body);
			$body = str_replace('##act_link##', $vars['site']['url']."?page=users&user=".$ins_id."&action=activate&account_code=".$a['account_code'], $body);
			$ret = sendmail($_POST['users__email'], $subject, $body);
			if ($ret && (get('user') != 'add' && $v_old[0]['email'] != $_POST['users__email'])) {
				$ret = $db->set('users', array('status' => 'pending', 'account_code' => $a['account_code']), "id = '".get('user')."'");
			}
		}
		if ($ret) {
			$main->message->set_fromlang('info', (get('user') == 'add'?'signup':'edit').'_success', makelink());
		} else {
			$main->message->set_fromlang('error', 'generic');		
		}
	}

}

?>