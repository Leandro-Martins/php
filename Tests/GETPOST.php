<?php
header ('Content-type: text/plain');

if ( $_SERVER['REQUEST_METHOD'] == 'POST' && !isset($_GET['msg'])) {
    print 'POST';
} elseif ( isset($_GET['msg']) ) {
	print $_GET['msg'];
} else {
	print 'GET';
}
