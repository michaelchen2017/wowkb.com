<?php
//memcache test script
//modify

$mem = new Memcached();
$mem->addServer("127.0.0.1", 11211);
 
$result = $mem->get("blah");
 
if ($result) {
    echo $result;
} else {
    echo "No matching key found yet. Let's start adding that now!";
    $mem->set("blah", "I am data!  I am held in memcached!") or die("Couldn't save anything to memcached...");
}
?>
