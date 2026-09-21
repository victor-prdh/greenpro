#!/bin/bash
# Grants the app DB user rights on its PHPUnit test database(s):
# config/packages/doctrine.yaml sets dbname_suffix '_test%env(TEST_TOKEN)%' when@test,
# so ParaTest-parallel runs create api_test1, api_test2, etc.
# MySQL only runs files in docker-entrypoint-initdb.d on first container init
# (i.e. when the data volume is empty).
set -euo pipefail

mysql -uroot -p"${MYSQL_ROOT_PASSWORD}" <<-SQL
	GRANT ALL PRIVILEGES ON \`${MYSQL_DATABASE}\_test%\`.* TO '${MYSQL_USER}'@'%';
	FLUSH PRIVILEGES;
SQL
