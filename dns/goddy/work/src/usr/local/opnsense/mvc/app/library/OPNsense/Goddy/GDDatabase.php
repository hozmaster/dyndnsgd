<?php

/*
 * Copyright (c) 2021, Olli-Pekka Wallin
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 * 1. Redistributions of source code must retain the above copyright notice, this
 *    list of conditions and the following disclaimer.
 *
 * 2. Redistributions in binary form must reproduce the above copyright notice,
 *    this list of conditions and the following disclaimer in the documentation
 *    and/or other materials provided with the distribution.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE
 * FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL
 * DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
 * SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY,
 * OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 */

namespace OPNsense\Goddy;

use \SQLite3;

class GDDatabase
{
    private $db_name = "/var/dyndnsgd/dyndnsgd.db";
    private $db = null;

    public function __construct()
    {
        $this->db = new \SQLite3($this->db_name);
        if (is_null($this->db)) {
            GdUtils::log_error("Database connection cannot be established. Verify that the database exist and run setup routines again if needed.");
        }
    }

    public function setCachedIpForDomain($uuid, $type, $name, $ipv4_address, $ipv6_address = "")
    {
        $query = $this->db->prepare('INSERT OR REPLACE into record (uuid, type, name, ipv4_address, ipv6_address, active) values (:uuid, :type, :name, :ipv4_address, :ipv6_address, :active)');
        $query->bindValue(':uuid', $uuid);
        $query->bindValue(':type', $type);
        $query->bindValue(':name', $name);
        $query->bindValue(':ipv4_address', $ipv4_address);
        $query->bindValue(':ipv6_address', $ipv6_address);
        $query->bindValue(':active', true);
        $result = $query->execute();
        $op_status = false;
        if ($result != false) {
            $op_status = true;
        }
        return $op_status;
    }

    public function getsSingleDomainRecord ($d_uuid)
    {
        // get cached record for specific domain.
        $query = $this->db->prepare('SELECT uuid, type, name, ipv4_address, ipv6_address FROM record WHERE uuid = :id;');
        $query->bindValue(':id', $d_uuid);
        $result = $query->execute();
        // get assoc array
        return $result->fetchArray(1);
    }

    public function getCachedDomainRecords(): array
    {
        // get cached record for all domains.
        $query = "SELECT uuid, type, name, ipv4_address, ipv6_address FROM record";
        $result = $this->db->query($query);
        $data = [];
        while ($row = $result->fetchArray(1)) {
            array_push($data, $row);
        }
        return $data;
    }

    private function getUuid(): string
    {
        return GdUtils::createUUIDv4();
    }

}
