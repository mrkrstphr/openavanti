CREATE TABLE roles (
    role_id int PRIMARY KEY AUTO_INCREMENT,
    name varchar(40) NOT NULL,
    permission integer NOT NULL,
    status varchar(20) DEFAULT 'active',
    CONSTRAINT roles_permission_key UNIQUE(permission)
) ENGINE=INNODB;

CREATE TABLE users (
    user_id int PRIMARY KEY AUTO_INCREMENT,
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
    login_key varchar(32)
) ENGINE=INNODB;

CREATE TABLE user_roles (
    user_id int ,
    role_id int,
    PRIMARY KEY(user_id, role_id),
    FOREIGN KEY(user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY(role_id) REFERENCES roles(role_id) ON DELETE CASCADE
) ENGINE=INNODB;
