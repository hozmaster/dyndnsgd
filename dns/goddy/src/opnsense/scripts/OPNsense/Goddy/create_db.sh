#!/bin/sh

#
# Copyright (c) 2021, Olli-Pekka Wallin
# All rights reserved.
#
# Redistribution and use in source and binary forms, with or without
# modification, are permitted provided that the following conditions are met:
#
# 1. Redistributions of source code must retain the above copyright notice, this
#    list of conditions and the following disclaimer.
#
# 2. Redistributions in binary form must reproduce the above copyright notice,
#    this list of conditions and the following disclaimer in the documentation
#    and/or other materials provided with the distribution.
#
# THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
# AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
# IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
# DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE
# FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL
# DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
# SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
# CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY,
# OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
# OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.

sqlite3 /var/db/goddy.db <<EOF
CREATE TABLE IF NOT EXISTS record (uuid TEXT NOT NULL, type TEXT NOT NULL, name TEXT NOT NULL, ipv4_address TEXT, ipv6_address TEXT, \
 insert_at TEXT DEFAULT (datetime()), updated_at TEXT DEFAULT (datetime()), active BOOLEAN DEFAULT (TRUE), PRIMARY KEY (uuid, type, name) );
CREATE TABLE IF NOT EXISTS migration (id INT PRIMARY KEY, description TEXT NOT NULL, insert_at TEXT DEFAULT (datetime()) );
INSERT OR IGNORE INTO migration (id, description) VALUES (1, "Created a database and default content.");
EOF
