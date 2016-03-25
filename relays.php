<?php

class Relay {
      public $nick;
      public $fingerprint;
      public $or_addresses;
      public $dir_address;
      public $running;
      public $flags;
      public $platform;
      public $country;
      public $country_name;
      public $last_restarted;
      public $last_seen;

      public function is_running() {
            return $this->running;
      }

      public function get_uptime() {
            $value = new DateTime($this->is_running() ? $this->last_restarted : $this->last_seen);
            $now = new DateTime();

            $result = $now->diff($value)->format("%d days %H hours");

            if ($result == "0 days 00 hours") {
                  return "n/a";
            } else {
                  $result = $this->is_running() ? "Up for " . $result : "Down for " . $result;
                  
                  return $result;
            }
      }
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
                  $relay->country = $data["country"];
                  $relay->country_name = $data["country_name"];
                  $relay->last_restarted = $data["last_restarted"];
                  $relay->last_seen = $data["last_seen"];

                  $relays[] = $relay;
            }

            return $relays;
      }
}
