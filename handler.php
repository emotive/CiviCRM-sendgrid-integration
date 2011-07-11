<?php
main();

function main() {

	$post_data = array();

	$post_data = $_POST;
	if(!isset($post_data) || empty($post_data)) {
		die('No data sent over');
	}
	
	if($_GET['key'] == 'emotive_sendgrid') {
		
		if(isset($post_data['category'])) {
			// the category name would be like wwwDOTexampleDOTcom-51
			// we will use that as the base to call the web service
			$_site = str_replace('DOT', '.', $post_data['category']);
			$site = substr($_site, 0, strrpos($_site, '-'));
			
			// get the mailing id
			$mid = substr($_site, strrpos($_site, '-')+1);
			
			// Pass on the key in the post data
			$post_data['key'] = 'emotive_sendgrid';
			$post_data['category'] = $site;
			$post_data['mid'] = $mid;
			
			// Do a CURL call to that site's location
			$service_url = sprintf("%s/sendgrid_sync", $site);
			$result = http($service_url, '',POST, $post_data);
			
			// file_put_contents('/var/www/sites/default/files/temp_data.txt', print_r($post_data, TRUE), FILE_APPEND);
			// file_put_contents('/var/www/sites/default/files/temp_result.txt', print_r($result, TRUE), FILE_APPEND);
			
			exit('normal exit...');
			
			// wonder if we can get some information from the return value from doing a 
			// post
		}
	} else {
		die('Invalid API key provided, exit');
	}

}


// initate a curl request
function http($target, $ref = '', $method = 'GET', $data_array = '', $incl_head = TRUE) {
	$ch = curl_init();

	if(is_array($data_array)) {
		foreach ($data_array as $key => $value) {
			if(strlen(trim($value))>0) {
				$temp_string[] = $key . "=" . urlencode($value);
			}
			else {
				$temp_string[] = $key;
			}
			}
		$query_string = join('&', $temp_string);
	}

	if($method == HEAD) {
		curl_setopt($ch, CURLOPT_HEADER, TRUE);                // No http head
		curl_setopt($ch, CURLOPT_NOBODY, TRUE);                // Return body
	} else {
			if($method == GET) {
				if(isset($query_string)) {
					$target = $target . "?" . $query_string;
					curl_setopt ($ch, CURLOPT_HTTPGET, TRUE); 
					curl_setopt ($ch, CURLOPT_POST, FALSE); 
					}
				}
			if($method == POST) {
				if(isset($query_string)) {
					curl_setopt ($ch, CURLOPT_POSTFIELDS, $query_string);
					curl_setopt ($ch, CURLOPT_POST, TRUE); 
					curl_setopt ($ch, CURLOPT_HTTPGET, FALSE); 
				}
			}
			curl_setopt($ch, CURLOPT_HEADER, $incl_head);   // Include head as needed
			curl_setopt($ch, CURLOPT_NOBODY, FALSE);        // Return body
		}

	curl_setopt($ch, CURLOPT_TIMEOUT, 1800);    // Timeout 30 minutes
	curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10.6; en-US; rv:1.9.1.5) Gecko/20091102 Firefox/3.5.5");   // Webbot name
	curl_setopt($ch, CURLOPT_URL, $target);             // Target site
	curl_setopt($ch, CURLOPT_REFERER, $ref);            // Referer value
	curl_setopt($ch, CURLOPT_VERBOSE, FALSE);           // Minimize logs
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);    // No certificate
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);     // Follow redirects
	curl_setopt($ch, CURLOPT_MAXREDIRS, 4);             // Limit redirections to four
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);     // Return in string

	# Create return array
	$return_array['FILE']   = curl_exec($ch); 
	$return_array['STATUS'] = curl_getinfo($ch);
	$return_array['ERROR']  = curl_error($ch);

	curl_close($ch);

	return $return_array;
}