
<?php
$mc = new Memcached();
$mc->addServer("127.0.0.1", 11211);
 
$result = $mc->get("test_key");
 
if($result) {
  echo $result;
} else {
  echo "No data on Cache. Please refresh page pressing F5";
  $mc->set("test_key", "test data pulled from Cache2014!") or die ("Failed to save data at Memcached server");
}
?>

<!--  

<?php
echo "111hehlfe";
$memcache = new Memcache;
$memcache->connect('localhost', 11211) or die ("Could not connect");
$tmp = (object) array(
		'a' => 'asdf',
		'b' => 564,
		'c' => 59.2,
		'd' => array(0, true, null, 'h'),
);
$memcache->set('key', $tmp) or die ("Failed to memcache it");
echo var_dump($memcache->get('key'));
?>
-->