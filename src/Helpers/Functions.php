<?php

namespace Vlinde\StopWord\Helpers;

class Functions {
    public static function powerSet($in, $minLength = 1) {
        $count = count($in);
        $members = pow(2, $count);
        $return = [];
        for ($i = 0; $i < $members; $i++) {
            $b = sprintf("%0" . $count . "b", $i);
            $out = [];
            for ($j = 0; $j < $count; $j++) {
                if ($b{
                    $j} == '1') {
                    $out[] = $in[$j];
                }
            }
            $out_val = implode(" ", $out);
            if (count($out) >= $minLength) {
                $return[] = $out_val;
            }
        }

        return $return;
    }
}
