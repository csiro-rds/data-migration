# Support IP v6 addresses in log tables
# Backport of https://github.com/collectiveaccess/providence/pull/321
ALTER TABLE ca_search_log MODIFY ip_addr VARCHAR(39);
ALTER TABLE ca_download_log MODIFY ip_addr VARCHAR(39);
ALTER TABLE ca_items_x_tags MODIFY ip_addr VARCHAR(39);
