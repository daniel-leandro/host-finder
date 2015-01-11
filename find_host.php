<?php

$hostname = $_GET["hostname"];
$dns_records = dns_get_record($hostname, DNS_NS);

$resp = array(
    "domain" => $hostname
);
if ($dns_records == FALSE) {
    $resp["error"] = "An error occured";
} else if (count($dns_records) == 0) {
    $resp["error"] = "No data found";
} else {
    $hosts = host_by_nameserver();
    $nameservers = array();
    $host = array(
        "accurate" => NULL,
        "guess" => NULL
    );
    foreach($dns_records as $dns_record) {
        $ns = strtolower($dns_record["target"]);
        $ns_parts = explode(".", $ns);
        $guess = implode(".", array_slice($ns_parts, count($ns_parts) - 2, 2));
        $host["accurate"] = $hosts[$ns] ? $hosts[$ns] : $host["accurate"];
        $host["guess"] = $guess ? $guess  : $host["guess"];
        $nameservers[] = $ns;
    }
    $resp["host"] = $host;
    $resp["nameservers"] = $nameservers;
}
echo json_encode($resp);

function host_by_nameserver() {
    $hosts = array();
    $hosts_file = fopen("hosts.csv", "r");
    while (($line = fgetcsv($hosts_file)) !== FALSE) {
        $host = $line[0];
        for ($i=1; $i<count($line); $i++) {
            $hosts[strtolower($line[$i])] = $host;
        }
    }
    fclose($hosts_file);
    return $hosts;
}
?>