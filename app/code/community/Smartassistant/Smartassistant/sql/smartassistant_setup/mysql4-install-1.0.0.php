<?php

$installer = $this;
$installer->startSetup();

$installer->run("DROP TABLE IF EXISTS {$this->getTable('smartassistant_exportconfig')}");
$installer->run("DROP TABLE IF EXISTS {$this->getTable('smartassistant_export')}");
$installer->run("DROP TABLE IF EXISTS {$this->getTable('smartassistant_exportfield')}");
$installer->run("DROP TABLE IF EXISTS {$this->getTable('smartassistant_rule')}");
$installer->run("DROP TABLE IF EXISTS {$this->getTable('smartassistant_task_status')}");
$installer->run("DROP TABLE IF EXISTS {$this->getTable('smartassistant_task')}");
$installer->run("DROP TABLE IF EXISTS {$this->getTable('smartassistant_task_config')}");
$installer->run("DROP TABLE IF EXISTS {$this->getTable('smartassistant_task_log')}");

$installer->run("
CREATE TABLE {$this->getTable('smartassistant_export')} (
    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `store_id` INT(11) UNSIGNED NOT NULL,
    `name` VARCHAR(256) NOT NULL,
    `filename` VARCHAR(256) NOT NULL,
    `days` TEXT DEFAULT NULL,
    `hours` TEXT DEFAULT NULL,
    `autogenerate` BOOLEAN NOT NULL DEFAULT TRUE,
    `autosend` BOOLEAN NOT NULL DEFAULT TRUE
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

CREATE TABLE {$this->getTable('smartassistant_exportfield')} (
    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `export_id` INT(11) UNSIGNED NOT NULL,
    `fieldname` VARCHAR(256) NOT NULL,
    `attribute_code` VARCHAR(256) NOT NULL,
    `enabled` BOOLEAN NOT NULL,
    `when_empty` VARCHAR(256) NOT NULL,
    `position` INT(11) UNSIGNED NOT NULL DEFAULT 1
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

CREATE TABLE IF NOT EXISTS {$this->getTable('smartassistant_rule')} (
    `id` int(10) unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `export_id` int(10) unsigned NOT NULL,
    `conditions_serialized` mediumtext NOT NULL,
    `actions_serialized` mediumtext NOT NULL
) ENGINE=InnoDB CHARSET=utf8 AUTO_INCREMENT=26;

CREATE TABLE IF NOT EXISTS {$this->getTable('smartassistant_task_status')} (
    `id` int(10) unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(256) NOT NULL,
    `display` VARCHAR(256) NOT NULL
) ENGINE=InnoDB CHARSET=utf8 AUTO_INCREMENT=1;

CREATE TABLE IF NOT EXISTS {$this->getTable('smartassistant_task')} (
    `id` int(10) unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `status_id` VARCHAR(256),
    `time` VARCHAR(256),
    `progress` INT NOT NULL DEFAULT 0,
    `items` INT NOT NULL DEFAULT 0
) ENGINE=InnoDB CHARSET=utf8 AUTO_INCREMENT=1;

CREATE TABLE IF NOT EXISTS {$this->getTable('smartassistant_task_config')} (
    `id` int(10) unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `export_id` INT NOT NULL,
    `task_id` INT NOT NULL,
    `status_id` VARCHAR(256),
    `generate` BOOLEAN NOT NULL,
    `send` BOOLEAN NOT NULL,
    `progress` INT NOT NULL DEFAULT 0,
    `items` INT NOT NULL DEFAULT 0
) ENGINE=InnoDB CHARSET=utf8 AUTO_INCREMENT=1;

INSERT INTO {$this->getTable('smartassistant_task_status')}
    (`id`, `name`, `display`)
VALUES
    (1, 'Waiting', ''),
    (2, 'Inicjalizowanie', ''),
    (3, 'Generation in process', ''),
    (4, 'Generation is finished', ''),
    (5, 'Generated', ''),
    (6, 'Send', ''),
    (7, 'Failed', '');

CREATE TABLE IF NOT EXISTS {$this->getTable('smartassistant_task_log')} (
    `id` int(10) unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `type` INT NOT NULL DEFAULT 1,
    `task_id` INT NOT NULL,
    `task_config_id` INT NOT NULL,
    `message` TEXT,
    `datetime` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB CHARSET=utf8 AUTO_INCREMENT=1;
");

$installer->endSetup();
