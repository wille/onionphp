<?php

require_once "relays.php";

$data = Relays::query_relays("test");

var_dump($data);
