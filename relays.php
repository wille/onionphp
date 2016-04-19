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
            $url = "https://onionoo.torproject.org/details" . (strlen($search) == 0 ? "" : "?search=" . $search);

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_URL, $url);
            $raw = curl_exec($ch);
            curl_close($ch);

            $json = json_decode($raw, true);

            $relays = array();

            function get($data, $key) {
                  return isset($data[$key]) ? $data[$key] : null;
            }

            for ($i = 0; $i < count($json["relays"]); $i++) {
                  $data = $json["relays"][$i];

                  $relay = new Relay();
                  $relay->nick = get($data, "nickname"); // Omitted if relay nickname is "Unnamed"
                  $relay->fingerprint = $data["fingerprint"]; // Required

                  $include = strlen($search) == 0 || (strpos(strtolower($relay->nick), strtolower($search)) !== false || strpos(strtolower($relay->fingerprint), strtolower($search)) !== false);

                  if ($include) {
                        $relay->or_addresses = get($data, "or_addresses"); // Omitted if array is empty
                        $relay->dir_address = get($data, "dir_address"); // Omitted if the relay does not accept directory connections.
                        $relay->contact = get($data, "contact"); // Omitted if empty or if descriptor containing this information cannot be found.
                        $relay->running = $data["running"] == "true"; // Required
                        $relay->flags = get($data, "flags"); // Omitted if empty.
                        $relay->platform = get($data, "platform"); // Omitted if empty or if descriptor containing this information cannot be found.
                        $relay->country = get($data, "country"); // Omitted if the relay IP address could not be found in the GeoIP database.
                        $relay->country_name = get($data, "country_name"); // Omitted if the relay IP address could not be found in the GeoIP database, or if the GeoIP database did not contain a country name.
                        $relay->last_restarted = get($data, "last_restarted"); // Missing if router descriptor containing this information cannot be found.
                        $relay->last_seen = $data["last_seen"];
                        $relay->bandwidth = get($data, "observed_bandwidth"); // Missing if router descriptor containing this information cannot be found.
                        $relay->consensus_weight_fraction = get($data, "consensus_weight_fraction"); // Omitted if the relay is not running.
                        $relay->guard_probability = get($data, "guard_probability"); // Omitted if the relay is not running, or the consensus does not contain bandwidth weights.
                        $relay->middle_probability = get($data, "middle_probability"); // Omitted if the relay is not running, or the consensus does not contain bandwidth weights.
                        $relay->exit_probability = get($data, "exit_probability"); // Omitted if the relay is not running, or the consensus does not contain bandwidth weights.

                        $relays[] = $relay;
                  } else {
                        unset($relay);
                  }
            }

            return $relays;
      }
}
