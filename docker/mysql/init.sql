-- MySQL initialization script
-- This runs on first container startup

-- Create database if not exists
CREATE DATABASE IF NOT EXISTS laravel CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Set timezone
SET GLOBAL time_zone = '+00:00';

-- Performance tuning for Laravel
SET GLOBAL innodb_buffer_pool_size = 256*1024*1024;
SET GLOBAL innodb_log_file_size = 64*1024*1024;
