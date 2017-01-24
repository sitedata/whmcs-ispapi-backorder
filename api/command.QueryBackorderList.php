<?php // $command, $userid

if ( !$userid )	return backorder_api_response(531);

if ( !isset($command["LIMIT"]) ) $command["LIMIT"] = 100;
if ( !isset($command["FIRST"]) ) $command["FIRST"] = 0;

$r = backorder_api_response(200);

$limit = isset($command["LIMIT"])? $command["FIRST"].",".$command["LIMIT"] : "";

$condition = array("userid" => $userid);
$fields = 'SQL_CALC_FOUND_ROWS *';
if ( $userid < 0 ) {
	$condition = array();
	$fields = 'SQL_CALC_FOUND_ROWS backorder_domains.*,(SELECT email FROM tblclients WHERE tblclients.id=backorder_domains.userid LIMIT 1) as useremail';
}

if(isset($command['STATUS']) && $command['STATUS']!="")
{
	$condition['status'] = $command['STATUS'];
}
if(isset($command['TLD']) && $command['TLD']!="")
{
	$condition['tld'] = $command['TLD'];
}
if(isset($command['TYPE']) && $command['TYPE']!="")
{
	$condition['type'] = $command['TYPE'];
}






$orderby = "";
$orders = array(
	"ID" => "id",
	"DOMAIN" => "domain",
	"DOMAINDESC" => "domain",
	"DROPDATE" => "DATE(whois_updated_date)",
	"DROPDATEDESC" => "DATE(whois_updated_date)",
	"NUMBEROFCHARACTERS" => "domain_number_of_characters",
	"NUMBEROFCHARACTERSDESC" => "domain_number_of_characters",
	"NUMBEROFDIGITS" => "domain_number_of_digitse",
	"NUMBEROFDIGITSDESC" => "domain_number_of_digits",
	"NUMBEROFHYPHENS" => "domain_number_of_hyphens",
	"NUMBEROFHYPHENSDESC" => "domain_number_of_hyphens",
	"NUMBEROFUMLAUTS" => "domain_number_of_umlauts",
	"NUMBEROFUMLAUTSDESC" => "domain_number_of_umlauts",
	"GOOGLEPAGERANK" => "google_pagerank",
	"GOOGLEPAGERANKDESC" => "google_pagerank",
	"ALEXATRAFFICGLOBALRANK" => "alexa_traffic_global_rank",
	"ALEXATRAFFICGLOBALRANKDESC" => "alexa_traffic_global_rank",
	"ALEXATRAFFICSITESLINKINGIN" => "alexa_traffic_sites_linking_in",
	"ALEXATRAFFICSITESLINKINGINDESC" => "alexa_traffic_sites_linking_in",
);

if ( isset($command["ORDERBY"]) && isset($orders[$command["ORDERBY"]]) ) {
	$order = $orders[$command["ORDERBY"]];
	$sortorder = "ASC";
	if($command["ORDERBY"]=="ID") $sortorder = "DESC";
	if($command["ORDERBY"]=="DOMAINDESC") $sortorder = "DESC";
	if($command["ORDERBY"]=="DROPDATEDESC") $sortorder = "DESC";
	if($command["ORDERBY"]=="NUMBEROFCHARACTERSDESC") $sortorder = "DESC";
	if($command["ORDERBY"]=="NUMBEROFDIGITSDESC") $sortorder = "DESC";
	if($command["ORDERBY"]=="NUMBEROFHYPHENSDESC") $sortorder = "DESC";
	if($command["ORDERBY"]=="GOOGLEPAGERANKDESC") $sortorder = "DESC";
	if($command["ORDERBY"]=="ALEXATRAFFICGLOBALRANKDESC") $sortorder = "DESC";
	if($command["ORDERBY"]=="ALEXATRAFFICSITESLINKINGINDESC") $sortorder = "DESC";
}





$result = select_query('backorder_domains',$fields,$condition, $order, $sortorder, $limit);

while ($data = mysql_fetch_assoc($result)) {
	$r["PROPERTY"]["ID"][] = $data["id"];
	$r["PROPERTY"]["DOMAIN"][] = $data["domain"].".".$data["tld"];
	$r["PROPERTY"]["LABEL"][] = $data["domain"];
	$r["PROPERTY"]["TLD"][] = $data["tld"];
	$r["PROPERTY"]["STATUS"][] = strtoupper($data["status"]);
	$r["PROPERTY"]["DROPDATE"][] = strtoupper($data["dropdate"]);
	$r["PROPERTY"]["CREATEDDATE"][] = strtoupper($data["createddate"]);
	$r["PROPERTY"]["UPDATEDDATE"][] = strtoupper($data["updateddate"]);

	if ( $userid < 0 ) {
		$r["PROPERTY"]["USER"][] = $data["userid"];
		$r["PROPERTY"]["USEREMAIL"][] = $data["useremail"];
	}
}


if ( isset($r["PROPERTY"]["DOMAIN"]) && $userid ) {
	foreach ( $r["PROPERTY"]["DOMAIN"] as $index => $domain ) {
		if ( preg_match('/^(.*)\.(.*)$/', $domain, $m) ) {
			$result = select_query('backorder_domains','*',array("userid" => $userid, "domain" => $m[1], "tld" => $m[2] ));
			$data = mysql_fetch_assoc($result);
			if ( $data ) {
				$r["PROPERTY"]["STATUS"][$index] = strtoupper($data["status"]);
				$r["PROPERTY"]["BACKORDERTYPE"][$index] = strtoupper($data["type"]);
			}
		}
	}
}



$result = select_query('backorder_domains',$fields,$condition); //ADD DM 14.08.2015
$data = mysql_fetch_assoc(mysql_query("SELECT FOUND_ROWS() AS `found_rows`;"));
$r["PROPERTY"]["TOTAL"][] = $data['found_rows'];



return $r;

?>
