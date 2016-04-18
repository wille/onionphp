<?php

include_once "utils.php";

class Relay {
      public $nick;
      public $fingerprint;
      public $or_addresses;
      public $dir_address;
      public $contact;
      public $running;
      public $flags;
      public $platform;
      public $country;
      public $country_name;
      public $last_restarted;
      public $last_seen;
      public $bandwidth;
      public $consensus_weight_fraction;
      public $guard_probability;
      public $middle_probability;
      public $exit_probability;
      public $data = [];

      public function is_running() {
            return $this->running;
      }

      public function get_uptime() {
            $value = new DateTime($this->is_running() ? $this->last_restarted : $this->last_seen);
            $now = new DateTime();

            $days = $now->diff($value)->format("%a");
            $hours = $now->diff($value)->format("%H");

            if ($days == "0" && $hours == "00") {
                  return false;
            }

            return [
                  "days" => $days,
                  "hours" => $hours
            ];
      }

      public function get_current_bandwidth() {
            return data_display_str($this->bandwidth) . "/s";
      }
}

class Relays {

      public static function query_relays($search) {
            $url = "https://onionoo.torproject.org/details?search=" . $search;

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

                  if (strlen($search) == 0 || (strpos(strtolower($relay->nick), strtolower($search)) !== false || strpos(strtolower($relay->fingerprint), strtolower($search)) !== false)) {
                        $relay->or_addresses = $data["or_addresses"];
                        $relay->dir_address = $data["dir_address"];
                        $relay->contact = $data["contact"];
                        $relay->running = $data["running"] == "true";
                        $relay->flags = $data["flags"];
                        $relay->platform = $data["platform"];
                        $relay->country = $data["country"];
                        $relay->country_name = $data["country_name"];
                        $relay->last_restarted = $data["last_restarted"];
                        $relay->last_seen = $data["last_seen"];
                        $relay->bandwidth = $data["observed_bandwidth"];
                        $relay->consensus_weight_fraction = $data["consensus_weight_fraction"];
                        $relay->guard_probability = $data["guard_probability"];
                        $relay->middle_probability = $data["middle_probability"];
                        $relay->exit_probability = $data["exit_probability"];

                        $relays[] = $relay;
                  } else {
                        unset($relay);
                  }
            }

            return $relays;
      }
}
