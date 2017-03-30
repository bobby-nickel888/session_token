<?php
/*
 * get token
 * if token not exists, creates a new one and returns
 * Solution for the multiple domains sharing same database
 * e.g There is a main domain which searches resorts and hotels by date and other specifications like bedroom counts and guest numbers and so on.
 * And the search covers multiple domains and the data on main domain should be trasfered to other domains.
 */
function getEscapiaToken() {	// token will store data and will be shared by multiple domains
	global $db, $org_id;

	$escapia_table_name = "escapia_session_states";
	$tmp_token = (isset($_GET['token'])) ? $_GET['token'] : "";

	if (!empty($tmp_token)) {	// if token is transfered from main domain by token param check for valid token and return
		$where = " escapia_session_org_id = '{$org_id}' and escapia_session_token = '{$tmp_token}' ";
		$escapia_session_states_qry = "SELECT * FROM $escapia_table_name WHERE {$where}";
		$escapia_session_states = $db->get_results( $escapia_session_states_qry );
		if (count($escapia_session_states) > 0) {
			return $tmp_token;
		} else {
			// expired or invalid token
		}
	}
	// if not create new token and return
	$db_arr = array(
		"escapia_session_org_id" => $org_id
	);
	$id = $db->insert($escapia_table_name, $db_arr, 0, 1);
	$where = " escapia_session_org_id = '{$org_id}' and escapia_session_id = '{$id}' ";
	$escapia_session_states_qry = "SELECT * FROM $escapia_table_name WHERE {$where}";
	$escapia_session_states = $db->get_results( $escapia_session_states_qry );

	if (count($escapia_session_states) > 0) {
		return $escapia_session_states[0]['escapia_session_token'];
	}

	return null;
}

// for Destroy session
function destroySessionData_escapia() {
	global $db, $org_id, $escapia_session_token;

	$escapia_table_name = "escapia_session_states";

	$db->delete($escapia_table_name, array("escapia_session_token" => $escapia_session_token), 1);
}

// for saving session data into `escapia_session_states` table
function saveSessionData_escapia($data) {
	global $db, $org_id, $escapia_session_token;

	$escapia_table_name = "escapia_session_states";

	$db_arr = array(
			"escapia_session_org_id" 	=> $org_id,
			"escapia_session_data" 		=> json_encode($data)
		);
	$result = $db->update($escapia_table_name, $db_arr, array("escapia_session_token" => $escapia_session_token));
}

// get session data from `escapia_session_states` table
function restoreSessionData_escapia() {
	global $db, $org_id, $escapia_session_token;

	$escapia_table_name = "escapia_session_states";
	
	$where = " escapia_session_org_id = '{$org_id}' and escapia_session_token = '{$escapia_session_token}' ";
	$escapia_session_states_qry = "SELECT * FROM $escapia_table_name WHERE {$where}";
	$escapia_session_states = $db->get_results( $escapia_session_states_qry );

	if (count($escapia_session_states) > 0) {
		return json_decode($escapia_session_states[0]['escapia_session_data'], true);
	}
	return null;
}

/*
I have used this solution for the resort booking website
There are multiple resort places each have it's own domains and there is a main domain which searches through the resort domains.
There are also other solutions like appending long long get params in the url but it will make things complicated and will make lots of holes
So by appending token param to the url we can freely access through the pages restricted like Checkout Pages and transfer data from page to page freely.
And also each page state is saved on db so it will be really nice to track and restore pages.
*/
?>