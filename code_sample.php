<?
$return_destination = "http://www.incredipixel.co/code_sample_dest.php";

$not_branded_url = $_GET['url'];

// format website URL - URL parser
$split = explode(".", $not_branded_url); // goal is to split into three pieces, with third part including .com or .net, etc.
// and query;
// if split[0] contains http:// or www for beginning split
if(strpos($split[0], "http://") !== false || strpos($split[0], "www") !== false) {
	// split[0] = http://(www)?others_perhaps
	// make sure to split again
	$split2 = explode("://", $split[0]);
	// if count is 3, then we have http:// www and a word,
	// or http:// and a word
	// split www just in case
	$split3 = explode("www", $split2[1]);
	// now we should have just a word in split3
	// grab this word, and move forward
	if(strlen($split3[0]) == 0) { // in case of subdomain & correct URL, we don't need checks above.
		$word = $split[1];
	} else {
		$word = $split3[0];
	}
	
	// check for subdomains
	if(count($split) > 3) {
		// we have a subdomain
		$add_for_sub = ".".$split[2];
		$ending = $split[3];
	} else {
		if(isset($split[2])) {
			$ending = $split[2];
		} else {
			$ending = "com";
		}
	}
	
} else {
	// since we don't have a beginning, first split will be the word
	$word = $split[0];
	// check for subdomains
	if(count($split) > 2) {
		// we have a subdomain
		$add_for_sub = ".".$split[1];
		if(isset($split[2])) {
			$ending = $split[2];
		} else {
			$ending = "com";
		}
	} else {
		if(isset($split[1])) {
			$ending = $split[1];
		} else {
			$ending = "com";
		}
	}
}
$url = "http://www" . "." . $word . $add_for_sub . "." . $ending;

// grab site description
try {
	$meta_tags = get_meta_tags($url);
	// first try
	$site_description = $meta_tags['description'];
	// try again
	if(strlen($site_description) == 0) {
		$site_description = $meta_tags['og:description'];
	}
	
	// last try, just get title
	if(strlen($site_description) == 0) {
		$doc = new DOMDocument();
		$doc->strictErrorChecking = FALSE;
		$doc->loadHTML(file_get_contents($url));
		$xml = simplexml_import_dom($doc);
		$arr = $xml->xpath('//title');
		$site_description = $arr[0]['id'];
	}
	
} catch(Exception $e) {
	$site_description = "null";
}

// grab site logo (favicon)
try {
	$doc = new DOMDocument();
	$doc->strictErrorChecking = FALSE;
	$doc->loadHTML(file_get_contents($url));
	$xml = simplexml_import_dom($doc);
	$arr = $xml->xpath('//link[@rel="shortcut icon"]');
	if(count($arr) == 0) {
		$arr = $xml->xpath('//link[@rel="icon"]');
	} else {
		echo "Here";
		$logo = (string) $arr[0]['href'];
	}
	if(count($arr) == 0 && strlen($logo) == 0) {
		$arr = $xml->xpath('//meta[@itemprop="image"]');
	} else if(strlen($logo) == 0) {
		$logo = (string) $arr[0]['href'];
	}
	if(count($arr) == 0 && strlen($logo) == 0) {
		$logo = "null";
	} else if(strlen($logo) == 0) {
		$logo = (string) $arr[0]['content'];
	}
	// add any more potential logo dom locations above with more if statements and xpath calls.

} catch(Exception $e) {
	$logo = "null";
}


$format_hash = array("URL" => $url, "Description" => $site_description, "Logo" => $logo);
$json = json_encode($format_hash);

header("Location: " . $return_destination . "?brand=" .$json);
