<?php

namespace OPNsense\Goddy;

/*
 * Copyright (C) 2021 Olli-Pekka Wallin
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 * 1. Redistributions of source code must retain the above copyright notice,
 *    this list of conditions and the following disclaimer.
 *
 * 2. Redistributions in binary form must reproduce the above copyright
 *    notice, this list of conditions and the following disclaimer in the
 *    documentation and/or other materials provided with the distribution.
 *
 * THIS SOFTWARE IS PROVIDED ``AS IS'' AND ANY EXPRESS OR IMPLIED WARRANTIES,
 * INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY
 * AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
 * AUTHOR BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY,
 * OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
 * SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
 * INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
 * CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 */

@include_once("config.inc");
@include_once("interfaces.inc");
@include_once("util.inc");

class GdUtils
{

    /**
     * log runtime information
     */
    public static function log($msg)
    {
        syslog(LOG_NOTICE, "Goddy: ${msg}");
    }

    /**
     * log additional debug output
     */
    public static function log_debug($msg, bool $debug = false)
    {
        if ($debug) {
            syslog(LOG_NOTICE, "Goddy: ${msg}");
        }
    }


    public static function getDynDnsIP($if_index, $ip_family = 4)
    {
        $ip_address = $ip_family == 6 ? get_interface_ipv6($if_index) : get_interface_ip($if_index);
        if (empty($ip_address)) {
            log_error("Aborted IPv{$ip_family} detection: no address for {$if_index}");
            return 'down';
        }

        if ($ip_family != 6 && is_private_ip($ip_address)) {
            $ip_address = '';
        } elseif ($ip_family == 6 && is_linklocal($ip_address)) {
            log_error('Aborted IPv6 detection: cannot bind to link-local address');
            $ip_address = '';
        }

        if (($ip_family == 6 && !is_ipaddrv6($ip_address)) || ($ip_family != 6 && !is_ipaddrv4($ip_address))) {
            return 'down';
        }

        return $ip_address;

    }

    /**
     * log error messages
     */
    public static function log_error($msg)
    {
        syslog(LOG_ERR, "Goddy: ${msg}");
    }

    public static function dumpArrayToFile($content, $file)
    {
        ob_start();
        var_dump($content);
        $dump = ob_get_contents();
        file_put_contents($file, $dump);
        ob_end_clean();
    }

    public static function createUUIDv4($data = null)
    {
        // Generate 16 bytes (128 bits) of random data or use the data passed into the function.
        $data = $data ?? random_bytes(16);
        assert(strlen($data) == 16);

        // Set version to 0100
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
        // Set bits 6-7 to 10
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80);

        // Output the 36 character UUID.
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }
}
