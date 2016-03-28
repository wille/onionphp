<?php

function data_display_str($bytes) {
      $s = $bytes . " B";
      $units = [ "B", "K", "M", "G", "T" ];

      for ($i = 5; $i > 0; $i--) {
            $step = pow(1024, $i);

            if ($bytes > $step) {
                  $abs = abs($bytes / $step);

                  return round($abs, 2) . " " . $units[$i] . "B";
            }
      }

      return $s;
}
