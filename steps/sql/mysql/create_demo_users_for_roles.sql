INSERT INTO ca_users (user_name, userclass, password, fname, lname, email, vars, volatile_vars, active, sms_number)
	SELECT
		code                                                                            AS user_name,
		0                                                                               AS userclass,
		''                                                                              AS password,
		name                                                                            AS fname,
		'Test User'                                                                     AS lname,
		CONCAT(code, '@csiro.au')                                                       AS email,
		TO_BASE64('a:0:{}')                                                             AS vars,
		TO_BASE64('a:0:{}')                                                             AS volatile_vars,
		1                                                                               AS active,
		''                                                                              AS sms_number
	FROM ca_user_roles WHERE code NOT IN (SELECT DISTINCT user_name FROM ca_users);
# Using IGNORE as we're just ensuring that these users are assigned the correct roles.
# Makes it possible to run this script more than once. MySQL still issues a warning which is passed on to the user.
INSERT IGNORE INTO ca_users_x_roles (user_id, role_id)
	SELECT
		u.user_id,
		r.role_id
	FROM ca_users u
		JOIN ca_user_roles r ON u.user_name = r.code;
INSERT IGNORE INTO ca_users_x_groups (user_id, group_id)
	SELECT u.user_id, g.group_id
	FROM ca_users u
		JOIN ca_user_groups g ON u.user_name = g.code;
