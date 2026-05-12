-- Grant tenant database creation permissions.
-- The MySQL env vars (MYSQL_USER/MYSQL_PASSWORD/MYSQL_DATABASE) already grant
-- the user access to MYSQL_DATABASE. We need the additional wildcard grant so
-- stancl/tenancy can CREATE new databases for each tenant.
--
-- Note: This file runs once on first MySQL container start only.

GRANT ALL PRIVILEGES ON `tenant\_%`.* TO 'ezstore'@'%';
FLUSH PRIVILEGES;
