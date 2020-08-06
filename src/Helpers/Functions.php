<?php

namespace Vlinde\StopWord\Helpers;

class Functions {
    public static function powerSet($in, $minLength = 1, $filterMinCombinationLength = 2) {
        $count = count($in);

        $members = pow(2, $count);

        $return = [];

        for ($i = 0; $i < $members; $i++) {
            $b = sprintf("%0" . $count . "b", $i);

            $out = [];

            for ($j = 0; $j < $count; $j++) {
                if ($b[$j] == '1' && strlen($in[$j]) >= $filterMinCombinationLength) {
                    $out[] = $in[$j];
                }
            }

            if (count($out) >= $minLength) {
                $return[] = implode(" ", $out);
            }
        }

        return $return;
    }
}
