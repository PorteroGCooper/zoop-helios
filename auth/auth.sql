    CREATE TABLE users (
            id 			  INTEGER PRIMARY KEY,
			username	  TEXT NOT NULL,
            password      TEXT NOT NULL,
            active        INTEGER
    );
    CREATE TABLE roles (
            id   		  INTEGER PRIMARY KEY,
			name		  TEXT NOT NULL,
            active        INTEGER
    );
    CREATE TABLE users_roles (
            user_id 	  INTEGER,
            role_id 	  INTEGER,
            PRIMARY KEY (user_id, role_id)
    );

	CREATE TABLE associations (
			id 		  	  INTEGER PRIMARY KEY,
			name		  TEXT NOT NULL,
            active        INTEGER
	);

	CREATE TABLE users_associations (
			user_id 	  INTEGER,
			association_id INTEGER,
			PRIMARY KEY (user_id, association_id)
	);

	INSERT INTO users (id, username, password, active) VALUES (NULL, 'bob', '098f6bcd4621d373cade4e832627b4f6', 1);
	INSERT INTO users (id, username, password, active) VALUES (NULL, 'steve', 'ae2b1fca515949e5d54fb22b8ed95575', 1);
	INSERT INTO users (id, username, password, active) VALUES (NULL, 'ryan', 'f5d1278e8109edd94e1e4197e04873b9', 0);

	INSERT INTO associations (id, name) VALUES (NULL, 'one');
	INSERT INTO associations (id, name) VALUES (NULL, 'two');
	INSERT INTO associations (id, name) VALUES (NULL, 'three');
	INSERT INTO associations (id, name) VALUES (NULL, 'four');

	INSERT INTO roles (id, name) VALUES (NULL, 'police');
	INSERT INTO roles (id, name) VALUES (NULL, 'fire');
	INSERT INTO roles (id, name) VALUES (NULL, 'mayor');
	INSERT INTO roles (id, name) VALUES (NULL, 'root');

	INSERT INTO users_associations (user_id, association_id) VALUES (1, 1);
	INSERT INTO users_associations (user_id, association_id) VALUES (1, 2);
	INSERT INTO users_associations (user_id, association_id) VALUES (1, 3);
	INSERT INTO users_associations (user_id, association_id) VALUES (2, 1);
	INSERT INTO users_associations (user_id, association_id) VALUES (2, 3);
	INSERT INTO users_associations (user_id, association_id) VALUES (3, 1);

	INSERT INTO users_roles (user_id, role_id) VALUES (1, 1);
	INSERT INTO users_roles (user_id, role_id) VALUES (1, 2);
	INSERT INTO users_roles (user_id, role_id) VALUES (1, 3);
	INSERT INTO users_roles (user_id, role_id) VALUES (2, 1);
	INSERT INTO users_roles (user_id, role_id) VALUES (2, 2);
	INSERT INTO users_roles (user_id, role_id) VALUES (3, 4);
