CREATE TABLE roles (
    role_id integer PRIMARY KEY,
    name varchar(40) NOT NULL,
    permission integer UNIQUE NOT NULL,
    status varchar(20) DEFAULT 'active'
);

CREATE TABLE users (
    user_id integer PRIMARY KEY,
    first_name varchar(20),
    last_name varchar(20),
    email_address varchar(50),
    username varchar(30),
    password varchar(256),
    reset_key varchar(32),
    status varchar(20) DEFAULT 'active',
    created_on timestamp DEFAULT CURRENT_TIMESTAMP,
    created_by_id bigint,
    last_updated_on timestamp,
    last_updated_by_id bigint,
    login_key varchar(32), 
    FOREIGN KEY(created_by_id) REFERENCES users(user_id),
    FOREIGN KEY(last_updated_by_id) REFERENCES users(user_id)
);

CREATE TABLE user_roles (
    user_id integer NOT NULL,
    role_id integer NOT NULL,
    PRIMARY KEY(user_id, role_id),
    FOREIGN KEY(user_id) REFERENCES users(user_id), 
    FOREIGN KEY(role_id) REFERENCES roles(role_id)
);

