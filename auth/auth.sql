    CREATE TABLE users (
            user_id            INTEGER PRIMARY KEY,
            username      TEXT,
            password      TEXT,
            email_address TEXT,
            first_name    TEXT,
            last_name     TEXT,
            active        INTEGER
    );
    CREATE TABLE role (
            role_id   INTEGER PRIMARY KEY,
            name TEXT
    );
    CREATE TABLE user_role (
            user_id INTEGER,
            role_id INTEGER,
            PRIMARY KEY (user_id, role_id)
    );

	CREATE TABLE group (
			group_id 	INTEGER PRIMARY KEY,
			name		TEXT
	);

	CREATE TABLE user_group (
			user_id INTEGER,
			group_id INTEGER,
			PRIMARY KEY (user_id, group_id)
	);
