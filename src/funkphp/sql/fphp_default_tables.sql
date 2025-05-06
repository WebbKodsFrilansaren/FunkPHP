-- This SQL File includes all the default fphp Tables such as:
-- "fphp_users", "fphp_roles", "fphp_claims", "fphp_sessions",
-- "fphp_csrf", "fphp_password_resets"
-- Copy & paste into a created database called "fphp" or any other name!

-- Table: fphp_users
CREATE TABLE fphp_users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id VARCHAR(255) NOT NULL UNIQUE,
    user_username VARCHAR(255) NOT NULL UNIQUE,
    user_fullname VARCHAR(255) DEFAULT NULL,
    user_email VARCHAR(255) NOT NULL UNIQUE,
    user_password VARCHAR(512) NOT NULL,
    user_locked BOOLEAN DEFAULT FALSE,
    user_login_attempts INT DEFAULT 0,
    user_last_login_attempt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    user_created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    user_updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table: fphp_roles
CREATE TABLE fphp_roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id_ref VARCHAR(255) NOT NULL UNIQUE,
    user_role_name VARCHAR(255) NOT NULL UNIQUE,
    user_role_only_specific_ips VARCHAR(2048) DEFAULT NULL,
    user_role_created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    user_role_updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id_ref) REFERENCES fphp_users(user_id)
);

-- Table: fphp_claims
CREATE TABLE fphp_claims (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id_ref VARCHAR(255) NOT NULL UNIQUE,
    user_claim_name VARCHAR(255) NOT NULL UNIQUE,
    user_claim_only_specific_ips VARCHAR(2048) DEFAULT NULL,
    user_claim_created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    user_claim_updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id_ref) REFERENCES fphp_users(user_id)
);

-- Table: fphp_sessions
CREATE TABLE fphp_sessions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id_ref VARCHAR(255) NOT NULL UNIQUE,
    user_session_id VARCHAR(512) NOT NULL UNIQUE,
    user_session_ip VARCHAR(255) NOT NULL,
    user_session_agent VARCHAR(255) NOT NULL,
    user_session_created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id_ref) REFERENCES fphp_users(user_id)
);

-- Table: fphp_csrf
CREATE TABLE fphp_csrf (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id_ref VARCHAR(255) NOT NULL UNIQUE,
    user_csrf_uri VARCHAR(2048) NOT NULL,
    user_csrf_token VARCHAR(512) NOT NULL UNIQUE,
    user_csrf_expire_at BIGINT NOT NULL,
    user_csrf_valid_data_to_crud VARCHAR(2048) NOT NULL,
    user_csrf_ip VARCHAR(255) NOT NULL,
    user_csrf_agent VARCHAR(255) NOT NULL,
    user_csrf_created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id_ref) REFERENCES fphp_users(user_id)
);

-- Table: fphp_password_resets
CREATE TABLE fphp_password_resets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id_ref VARCHAR(255) NOT NULL UNIQUE,
    user_password_reset_token VARCHAR(512) NOT NULL UNIQUE,
    user_password_reset_expire_at BIGINT NOT NULL,
    user_password_reset_ip VARCHAR(255) NOT NULL,
    user_password_reset_agent VARCHAR(255) NOT NULL,
    user_password_reset_created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id_ref) REFERENCES fphp_users(user_id)
);