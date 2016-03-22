<?php

class Relay {
      public $nick;
      public $fingerprint;
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

                  $relays[] = $relay;
            }

            return $relays;
      }
}
