<?php
namespace Leazycms\Web\Support;

class DeviceDetector
{
public static function detect(?string $userAgent): string
{
$ua = strtolower($userAgent ?? '');

$mobileKeywords = [
'android', 'iphone', 'ipad', 'ipod', 'mobile',
'windows phone', 'opera mini', 'blackberry'
];

foreach ($mobileKeywords as $keyword) {
if (str_contains($ua, $keyword)) {
return 'mobile';
}
}

return 'desktop';
}
}