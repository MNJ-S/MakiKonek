CREATE TABLE IF NOT EXISTS password_reset_otps (
    reset_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id INT(11) NOT NULL,
    reset_token_hash CHAR(64) NOT NULL,
    otp_hash VARCHAR(255) NOT NULL,
    expires_at DATETIME NOT NULL,
    verified_at DATETIME DEFAULT NULL,
    consumed_at DATETIME DEFAULT NULL,
    attempts TINYINT UNSIGNED NOT NULL DEFAULT 0,
    requested_ip VARCHAR(45) DEFAULT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (reset_id),
    UNIQUE KEY uq_password_reset_token (reset_token_hash),
    KEY idx_password_reset_user (user_id),
    KEY idx_password_reset_expiry (expires_at),
    KEY idx_password_reset_status (user_id, consumed_at, expires_at),

    CONSTRAINT fk_password_reset_user
        FOREIGN KEY (user_id) REFERENCES users(user_id)
        ON DELETE CASCADE
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_general_ci;

