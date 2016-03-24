<?php

require_once "awards/awards.php";

class Relay {
      public $nick;
      public $fingerprint;
      public $or_addresses;
      public $dir_address;
      public $running;
      public $flags;
      public $platform;
}

class Relays {

      public static function query_relays($search) {
            $url = "https://onionoo.torproject.org/details?search=" . htmlspecialchars($search);

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_URL, $url);
            $raw = curl_exec($ch);
            curl_close($ch);

            $json = json_decode($raw, true);

            $relays = array();

            for ($i = 0; $i < count($json["relays"]); $i++) {
                  $data = $json["relays"][$i];

                  $relay = new Relay();
                  $relay->nick = $data["nickname"];
                  $relay->fingerprint = $data["fingerprint"];
                  $relay->or_addresses = $data["or_addresses"];
                  $relay->dir_address = $data["dir_address"];
                  $relay->running = $data["running"] == "true";
                  $relay->flags = $data["flags"];
                  $relay->platform = $data["platform"];

                  $relays[] = $relay;
            }

            return $relays;
      }
}
