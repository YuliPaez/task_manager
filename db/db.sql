-- Crear Base de Datos
CREATE DATABASE task_manager;
USE task_manager;

-- Tabla: Users
CREATE TABLE users (
    id_users INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabla: Tasks
CREATE TABLE tasks (
    id_tasks INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    user_id INT NOT NULL,
    status ENUM('Pendiente', 'En progreso', 'Completada') DEFAULT 'Pendiente',
    priority ENUM('Baja', 'Media', 'Alta') NOT NULL,
    deadline DATE NOT NULL,
    assigned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_user
        FOREIGN KEY (user_id)
        REFERENCES users(id_users)
        ON DELETE RESTRICT
        ON UPDATE CASCADE
);
DROP TRIGGER IF EXISTS trg_update_assigned_at;

DELIMITER $$

-- Trigger antes de actualizar una tarea
CREATE TRIGGER trg_update_assigned_at
BEFORE UPDATE ON tasks
FOR EACH ROW
BEGIN
    -- Si se cambia el responsable, actualizar assigned_at
    IF NEW.user_id != OLD.user_id THEN
        SET NEW.assigned_at = CURRENT_TIMESTAMP;
    END IF;
END$$

DELIMITER ;


