 #!/bin/bash
vendor/bin/testbench config:clear
vendor/bin/testbench migrate:fresh
vendor/bin/phpunit