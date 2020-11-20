<?php


namespace App\Helpers;

use Illuminate\Support\Facades\DB;

/**
 * Class IpAddressInversion
 * @package App\Helpers
 *
 * Replacements for built-in functions "ip2long", "long2ip"
 * Compatibility with 32-bit machines
 * Uses built-in functions "INET_ATON", "INET_NTOA" in the database
 */
class IpAddressInversion
{
    private static function stripSelectQuery(string $query): string
    {
        return substr($query, -(strlen($query)-strlen('SELECT ')));
    }

    public static function ip2long(string $ip_address)
    {
        $query = 'SELECT INET_ATON(\''.$ip_address.'\')';
        $result = DB::select($query);
        $long = $result[0]->{substr($query, -(strlen($query)-strlen('SELECT ')))};
        return $long;
    }

    public static function long2ip(int $proper_address)
    {
        $query = 'SELECT INET_NTOA('.$proper_address.')';
        $result = DB::select($query);
        $string = $result[0]->{substr($query, -(strlen($query)-strlen('SELECT ')))};
        return $string;
    }
}
