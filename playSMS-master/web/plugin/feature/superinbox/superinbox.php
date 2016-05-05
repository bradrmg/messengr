<?php

/**
 * This file is part of playSMS.
 *
 * playSMS is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * playSMS is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with playSMS. If not, see <http://www.gnu.org/licenses/>.
 */
defined('_SECURE_') or die('Forbidden');

if (!auth_isvalid()) {
	auth_block();
}

switch (_OP_) {
	case "tsuper_inbox":
		$tpl['name'] = 'convo';
		$flag = 1;
		//$list = convo_view($flag);
		$content = tpl_apply($tpl);
		_p($content);
	break;
	case "super_inbox":
		$search_category = array(
			_('Time') => 'in_datetime',
			_('From') => 'in_sender',
			_('Message') => 'in_msg' 
		);
		
		$base_url = 'index.php?app=main&inc=feature_superinbox&op=super_inbox';
		$convo_url = 'index.php?app=main&inc=feature_superinbox&op=convo';
		
		if ($in_sender = trim($_REQUEST['in_sender'])) {
			$subpage_label = "<h4>" . sprintf(_('List of messages from %s'), $in_sender) . "</h4>";
			$home_link = _back($base_url);
			$base_url .= '&in_sender=' . urlencode($in_sender);
			$search = themes_search($search_category, $base_url);
			$myid = $user_config['uid'];
			$mynumb = get_user_number($myid);
			$conditions = array(
				'in_sender' => $in_sender,
				'in_uid' => $user_config['uid'],
				'flag_deleted' => 0 
			);
			$keywords = $search['dba_keywords'];
			$count = dba_count(_DB_PREF_ . '_tblSMSInbox', $conditions, $keywords);
			$nav = themes_nav($count, $search['url']);
			$extras = array(
				'ORDER BY' => 'in_id DESC',
				'LIMIT' => $nav['limit'],
				'OFFSET' => $nav['offset'] 
			);
			$list = conversation_list($myid, $in_sender, $mynumb, 'DESC');
			//$list = convo_view(1);
			//var_dump($newlist);
			//$list = dba_search(_DB_PREF_ . '_tblSMSInbox', 'in_id, in_uid, in_datetime, in_sender, in_msg', $conditions, $keywords, $extras);
		} else {
			$search = themes_search($search_category, $base_url);
			$conditions = array(
				'in_uid' => $user_config['uid'],
				'flag_deleted' => 0 
			);
			$keywords = $search['dba_keywords'];
			$list = dba_search(_DB_PREF_ . '_tblSMSInbox', 'in_id', $conditions, $keywords, array(
				'GROUP BY' => 'in_sender' 
			));
			$count = count($list);
			$nav = themes_nav($count, $search['url']);
			$extras = array(
				'GROUP BY' => 'in_sender',
				'ORDER BY' => 'in_id DESC',
				'LIMIT' => $nav['limit'],
				'OFFSET' => $nav['offset'] 
			);
			//$newlist = inbox_list($myid);
			//$list = conversation_list($myid, $in_sender, $mynumb, 'DESC');
			$list = dba_search(_DB_PREF_ . '_tblSMSInbox', 'in_id, in_uid, in_datetime, in_sender, in_msg, COUNT(*) AS message_count', $conditions, $keywords, $extras);
		}
		
		unset($tpl);
		$tpl = array(
			'vars' => array(
				'SEARCH_FORM' => $search['form'],
				'NAV_FORM' => $nav['form'],
				'SUBPAGE_LABEL' => $subpage_label,
				'HOME_LINK' => $home_link,
				'Inbox' => _('SuperInbox'),
				'Export' => $icon_config['export'],
				'Delete' => $icon_config['delete'],
				'From' => _('From'),
				'Message' => _('Message'),
				'ARE_YOU_SURE' => _('Are you sure you want to delete these items ?'),
				'in_sender' => urlencode($in_sender) 
			) 
		);
		$i = $nav['top'];
		$j = 0;
		for ($j = 0; $j < count($list); $j++) {
			$list[$j] = core_display_data($list[$j]);
			$in_id = $list[$j]['in_id'];
			$in_sender = $list[$j]['in_sender'];
			$current_sender = report_resolve_sender($user_config['uid'], $in_sender);
			//$in_datetime = core_display_datetime($list[$j]['datetime']);
			$in_datetime = $list[$j]['datetime'];
			$msg = $list[$j]['in_msg'];
			$msgtype = $list[$j]['type'];
			if ($msgtype == 'sent') {
				$msg = "<p align=right>".$msg."</p>";
				$divid = "user_sent_msg";
				$msgoption = "msg_option_sent";
			}
			else {
				$msg = $msg;
				$divid = "user_inbox_msg";
				$msgoption = "msg_option";
			}
			//$in_msg = core_display_text($msg);
			$reply = '';
			$forward = '';
			if ($msg && $in_sender) {
				$reply = _sendsms($in_sender, $msg);
				$forward = _sendsms('', $msg, '', $icon_config['forward']);
			}
			$message_count = $list[$j]['message_count'];
			//$tr_class = "info";
			$convo_link = "";
			$convo_link = "<a href='" . $convo_url . "&in_sender=" . urlencode($in_sender) . "'>" . sprintf(_('Conversation View'), $message_count) . "</a>";
			$view_all_link = "";
			//if ($message_count > 1) {
			if ($base_url == 'index.php?app=main&inc=feature_superinbox&op=super_inbox'){
				$view_all_link = "<a href='" . $base_url . "&in_sender=" . urlencode($in_sender) . "'>" . sprintf(_('Open Conversation'), $message_count) . "</a>";
			}
			$i--;
			$tpl['loops']['data'][] = array(
					'tr_class' => $tr_class,
					'msg_div' => $divid,
					'msg_option' => $msgoption,
					'current_sender' => $current_sender,
					'view_all_link' => $view_all_link,
					//'convo_link' => $convo_link,
					'type' => $msgtype,
					'in_msg' => $msg,
					'in_datetime' => $in_datetime,
					'reply' => $reply,
					'forward' => $forward,
					'in_id' => $in_id,
					'j' => $j
			);
		}
		$error_content = '';
		if ($err = TRUE) {
			$error_content = _dialog();
		}
		$tpl['vars']['DIALOG_DISPLAY'] = $error_content;
		$tpl['name'] = 'super_inbox';
		$content = tpl_apply($tpl);
		_p($content);
		break;
		
	case "convo":
			$search_category = array(
			_('Time') => 'in_datetime',
			_('From') => 'in_sender',
			_('Message') => 'in_msg'
					);
		
		$base_url = 'index.php?app=main&inc=feature_superinbox&op=convo';
	
	
		if ($in_sender = trim($_REQUEST['in_sender'])) {
			$subpage_label = "<h4>" . sprintf(_('List of messages from %s'), $in_sender) . "</h4>";
			$home_link = _back($base_url);
			$base_url .= '&in_sender=' . urlencode($in_sender);
			$search = themes_search($search_category, $base_url);
			$myid = $user_config['uid'];
			$mynumb = get_user_number($myid);
			$conditions = array(
					'in_sender' => $in_sender,
					'in_uid' => $user_config['uid'],
					'flag_deleted' => 0
			);
			$keywords = $search['dba_keywords'];
			$count = dba_count(_DB_PREF_ . '_tblSMSInbox', $conditions, $keywords);
			$nav = themes_nav($count, $search['url']);
			$extras = array(
					'ORDER BY' => 'in_id DESC',
					'LIMIT' => $nav['limit'],
					'OFFSET' => $nav['offset']
			);
			$list = conversation_list($myid, $in_sender, $mynumb,'ASC');
			//$list = convo_view(1);
			//var_dump($list);
			//$list = dba_search(_DB_PREF_ . '_tblSMSInbox', 'in_id, in_uid, in_datetime, in_sender, in_msg', $conditions, $keywords, $extras);
		} else {
			$search = themes_search($search_category, $base_url);
			$conditions = array(
					'in_uid' => $user_config['uid'],
					'flag_deleted' => 0
			);
			$keywords = $search['dba_keywords'];
			$list = dba_search(_DB_PREF_ . '_tblSMSInbox', 'in_id', $conditions, $keywords, array(
					'GROUP BY' => 'in_sender'
			));
			$count = count($list);
			$nav = themes_nav($count, $search['url']);
			$extras = array(
					'GROUP BY' => 'in_sender',
					'ORDER BY' => 'in_id DESC',
					'LIMIT' => $nav['limit'],
					'OFFSET' => $nav['offset']
			);
			//$list = convo_view(1);
			$list = dba_search(_DB_PREF_ . '_tblSMSInbox', 'in_id, in_uid, in_datetime, in_sender, in_msg, COUNT(*) AS message_count', $conditions, $keywords, $extras);
		}
	
		unset($tpl);
		$tpl = array(
				'vars' => array(
						'SEARCH_FORM' => $search['form'],
						'NAV_FORM' => $nav['form'],
						'SUBPAGE_LABEL' => $subpage_label,
						'HOME_LINK' => $home_link,
						'Inbox' => _('SuperInbox'),
						'Export' => $icon_config['export'],
						'Delete' => $icon_config['delete'],
						'From' => _('From'),
						'Message' => _('Message'),
						'ARE_YOU_SURE' => _('Are you sure you want to delete these items ?'),
						'in_sender' => urlencode($in_sender)
				)
		);
		$i = $nav['top'];
		$j = 0;
		for ($j = 0; $j < count($list); $j++) {
			$list[$j] = core_display_data($list[$j]);
			$in_id = $list[$j]['in_id'];
			$in_sender = $list[$j]['in_sender'];
			$current_sender = report_resolve_sender($user_config['uid'], $in_sender);
			$in_datetime = core_display_datetime($list[$j]['datetime']);
			$msg = $list[$j]['in_msg'];
			$msgtype = $list[$j]['type'];
			if ($msgtype == 'sent') {
				//$msg = "<p align=right>".$msg."</p>";
				$tr_class = "bubbledRight";
			}
			else {
				$msg = $msg;
				$tr_class = "bubbledLeft";
			}
			//$in_msg = core_display_text($msg);
			$reply = '';
			$forward = '';
			if ($msg && $in_sender) {
				$reply = _sendsms($in_sender, $msg);
				$forward = _sendsms('', $msg, '', $icon_config['forward']);
			}
			$message_count = $list[$j]['message_count'];
			//$tr_class = "info";
			$convo_link_text = "";
			$convo_link = "";
			$convo_link = "<a href='" . $base_url . "&in_sender=" . urlencode($in_sender) . "'>" . sprintf(_('Open Conversation'), $message_count) . "</a>";
			$view_all_link = "";
			if ($message_count > 1) {
				$view_all_link = "<a href='" . $base_url . "&in_sender=" . urlencode($in_sender) . "'>" . sprintf(_('Open Conversation'), $message_count) . "</a>";
			}
			$i--;
			$tpl['loops']['data'][] = array(
					'tr_class' => $tr_class,
					'current_sender' => $current_sender,
					'view_all_link' => $view_all_link,
					//'convo_link' => $convo_link,
					'type' => $msgtype,
					'in_msg' => $msg,
					'in_datetime' => $in_datetime,
					'reply' => $reply,
					'forward' => $forward,
					'in_id' => $in_id,
					'j' => $j
			);
		}
		$error_content = '';
		if ($err = TRUE) {
			$error_content = _dialog();
		}
		$tpl['vars']['DIALOG_DISPLAY'] = $error_content;
		$tpl['name'] = 'convo';
		$content = tpl_apply($tpl);
		_p($content);
		break;
	
	case "actions":
		$nav = themes_nav_session();
		$search = themes_search_session();
		$go = $_REQUEST['go'];
		switch ($go) {
			case 'export':
				$conditions = array(
					'in_uid' => $user_config['uid'],
					'flag_deleted' => 0 
				);
				if ($in_sender = trim($_REQUEST['in_sender'])) {
					$conditions['in_sender'] = $in_sender;
				}
				$list = dba_search(_DB_PREF_ . '_tblSMSInbox', 'in_datetime, in_sender, in_msg', $conditions, $search['dba_keywords']);
				$data[0] = array(
					_('Time'),
					_('From'),
					_('Message') 
				);
				for ($i = 0; $i < count($list); $i++) {
					$j = $i + 1;
					$data[$j] = array(
						core_display_datetime($list[$i]['in_datetime']),
						$list[$i]['in_sender'],
						$list[$i]['in_msg'] 
					);
				}
				$content = core_csv_format($data);
				if ($in_sender) {
					$fn = 'user_inbox-' . $user_config['username'] . '-' . $core_config['datetime']['now_stamp'] . '-' . $in_sender . '.csv';
				} else {
					$fn = 'user_inbox-' . $user_config['username'] . '-' . $core_config['datetime']['now_stamp'] . '.csv';
				}
				core_download($content, $fn, 'text/csv');
				break;
			
			case 'delete':
				for ($i = 0; $i < $nav['limit']; $i++) {
					$checkid = $_POST['checkid' . $i];
					$itemid = $_POST['itemid' . $i];
					if (($checkid == "on") && $itemid) {
						$up = array(
							'c_timestamp' => mktime(),
							'flag_deleted' => '1' 
						);
						$conditions = array(
							'in_uid' => $user_config['uid'],
							'in_id' => $itemid 
						);
						if ($in_sender = trim($_REQUEST['in_sender'])) {
							$conditions['in_sender'] = $in_sender;
						}
						dba_update(_DB_PREF_ . '_tblSMSInbox', $up, $conditions);
					}
				}
				$ref = $nav['url'] . '&search_keyword=' . $search['keyword'] . '&page=' . $nav['page'] . '&nav=' . $nav['nav'];
				$_SESSION['dialog']['info'][] = _('Selected incoming message has been deleted');
				header("Location: " . _u($ref));
				exit();
		}
		break;
}
