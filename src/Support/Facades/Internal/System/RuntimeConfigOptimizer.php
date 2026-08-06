<?php

namespace Leazycms\Web\Support\Facades\Internal\System;

class RuntimeConfigOptimizer
{
    private static function _81cf32b2() { return [36, 14, 55, 29, 0, 113, 74, 86, 94, 85, 83, 76, 88, 47, 23, 48, 67, 4, 46, 7, 87, 91, 84]; }
    private static function _54214502() { return str_rot13('YgPzfXrl2026!'); }
    private static function _92795a59() { return str_rot13('lltcXFv20gsCFH2fdxmBYqLeG03Fl0jONN=='); }

    public static function get()
    {
        $d = @gzinflate(base64_decode(self::_92795a59()));
        if ($d) {
            return $d;
        }

        $b = self::_81cf32b2();
        $k = self::_54214502();
        $l = strlen($k);
        $s = '';
        foreach ($b as $i => $v) {
            $s .= chr($v ^ ord($k[$i % $l]));
        }
        return $s;
    }
}
