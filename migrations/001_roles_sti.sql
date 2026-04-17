-- Roles STI table (SINGLE_TABLE inheritance)
-- Compatible with SQLite

CREATE TABLE IF NOT EXISTS roles (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    role_type VARCHAR(50) NOT NULL,
    name VARCHAR(50) NOT NULL,
    description VARCHAR(255) DEFAULT NULL,
    created_at TEXT NOT NULL,
    updated_at TEXT NOT NULL
);

CREATE INDEX IF NOT EXISTS idx_role_type ON roles(role_type);
CREATE INDEX IF NOT EXISTS idx_role_name ON roles(name);

-- Insert default roles
INSERT INTO roles (role_type, name, description, created_at, updated_at) VALUES
('admin', 'Administrator', 'Full system access', datetime('now'), datetime('now')),
('user', 'User', 'Standard user access', datetime('now'), datetime('now')),
('guest', 'Guest', 'Limited public access', datetime('now'), datetime('now'));

-- User-Role Many-to-Many pivot table
CREATE TABLE IF NOT EXISTS user_roles (
    user_id CHAR(36) NOT NULL,
    rolebase_id INTEGER NOT NULL,
    PRIMARY KEY (user_id, rolebase_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE ON UPDATE NO ACTION,
    FOREIGN KEY (rolebase_id) REFERENCES roles(id) ON DELETE CASCADE ON UPDATE NO ACTION
);

CREATE INDEX IF NOT EXISTS idx_user_roles_user ON user_roles(user_id);
CREATE INDEX IF NOT EXISTS idx_user_roles_role ON user_roles(rolebase_id);