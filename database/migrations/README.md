# Migration Naming Convention

Use timestamp-prefixed snake case migration file names:

- YYYYMMDDHHMMSS_description.php

Examples:

- 20260315101010_create_users_table.php
- 20260315102010_add_slug_to_pages_table.php

Rules:

- one migration concern per file
- class must return a Migration instance
- implement both up() and down() unless intentionally irreversible
- keep SQL explicit and engine-safe for MySQL 8+
