<?php

declare(strict_types=1);

return [
    'binaries' => [
        'mysql' => env('MYSQLDUMP_BINARY', 'mysqldump'),
        'pgsql' => env('PG_DUMP_BINARY', 'pg_dump'),
        'sqlite' => env('SQLITE3_BINARY', 'sqlite3'),
    ],
];
