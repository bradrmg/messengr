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

function convo_view($flag){
	$ret = array();
	if ($flag == 1) {
		$db_query = <<<EOD
		SELECT
		Type as type,
		in_id as ioID,
		in_receiver as numb1,
		in_sender as in_sender,
		in_datetime as datetime,
		in_msg as in_msg,
		in_uid as userID
		FROM  `cnvIn` 
		WHERE in_uid = 1
		AND in_sender = '18018367839'
		UNION ALL 
		SELECT * 
		FROM cnvOut
		WHERE uid = 1
		AND p_dst = '18018367839'
		AND p_src = '12258008051'
		ORDER BY DateTime DESC
EOD;
		//$db_query = "SELECT " . $q_fields . " FROM " . $db_table . " " . $join . " " . $q_where . " " . $q_sql_where . " " . $q_extras;
		// logger_print("q: ".$db_query, 3, "dba_search");
		$db_result = dba_query($db_query);
		while ($db_row = dba_fetch_array($db_result)) {
			$ret[] = $db_row;
		}
		return $ret;
	}
}
function conversation_list($userid, $in_sender, $insrc, $sort){
	$ret = array();
	if (isset($userid)) {
		$db_query = <<<EX
		(SELECT
		Type as type,
		in_id as ioID,
		in_receiver as numb1,
		in_sender as in_sender,
		in_datetime as datetime,
		in_msg as in_msg,
		in_uid as userID
		FROM  `cnvIn`
		WHERE in_uid = {$userid}
		AND in_sender = '{$in_sender}')
		UNION ALL
		(SELECT
		Type,
		id,
		p_dst,
		p_src,
		p_datetime,
		p_msg,
		uid
		FROM cnvOut
		WHERE uid = {$userid}
		AND p_dst = '{$in_sender}'
		AND p_src = '{$insrc}')
		ORDER BY 5 DESC
EX;
		//$db_query = "SELECT " . $q_fields . " FROM " . $db_table . " " . $join . " " . $q_where . " " . $q_sql_where . " " . $q_extras;
		// logger_print("q: ".$db_query, 3, "dba_search");
		$db_result = dba_query($db_query);
		while ($db_row = dba_fetch_array($db_result)) {
			$ret[] = $db_row;
		}
		return $ret;
	}
}
function inbox_list($userid){
	$ret = array();
	if (isset($userid)) {
		$db_query = <<<EX
		(SELECT
		Type as type,
		in_id as ioID,
		in_receiver as numb1,
		in_sender as in_sender,
		in_datetime as datetime,
		in_msg as in_msg,
		in_uid as userID
		FROM  `cnvIn`
		WHERE in_uid = {$userid})
		UNION ALL
		(SELECT
		Type,
		id,
		p_dst,
		p_src,
		p_datetime,
		p_msg,
		uid
		FROM cnvOut
		WHERE uid = {$userid})
		ORDER BY 5 DESC
EX;
		//$db_query = "SELECT " . $q_fields . " FROM " . $db_table . " " . $join . " " . $q_where . " " . $q_sql_where . " " . $q_extras;
		// logger_print("q: ".$db_query, 3, "dba_search");
		$db_result = dba_query($db_query);
		while ($db_row = dba_fetch_array($db_result)) {
			$ret[] = $db_row;
		}
		return $ret;
	}
}
//function to return the logged in users mobile phone number
function get_user_number($userid){
	$db_query = <<<EX
	SELECT Mobile FROM playsms_tblUser
	WHERE uid = {$userid}
EX;
	$db_result = dba_query($db_query);
	while ($db_row = dba_fetch_array($db_result)) {
		$ret[] = $db_row;
		$retnum = $db_row['Mobile'];
	}
	return $retnum;
}






