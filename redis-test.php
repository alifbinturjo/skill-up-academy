<?php
$redis = new Redis();
try {
    $redis->connect('127.0.0.1', 6379); // Try 'localhost' too if needed
    $redis->set("test_key", "Hello Redis!");
    echo "Redis test value: " . $redis->get("test_key");
} catch (Exception $e) {
    echo "Redis connection failed: " . $e->getMessage();
}
?>
