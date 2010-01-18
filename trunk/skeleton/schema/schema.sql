
CREATE TABLE roles(
    role_id bigserial PRIMARY KEY,
    name varchar(40) NOT NULL,
    permission int NOT NULL
);

CREATE TABLE users(
    user_id bigserial PRIMARY KEY,
    first_name varchar(20),
    last_name varchar(20),
    email_address varchar(50),
    username varchar(30),
    password varchar(256),
    reset_key varchar(32),
    status varchar(20) DEFAULT 'active',
    created_on timestamp with time zone,
    created_by_id bigint REFERENCES users(user_id),
    last_updated_on timestamp with time zone,
    last_updated_by_id bigint REFERENCES users(user_id)
);

CREATE TABLE user_roles(
    user_id bigint REFERENCES users(user_id) NOT NULL,
    role_id bigint REFERENCES roles(role_id) NOT NULL,
    PRIMARY KEY(user_id, role_id)
);


