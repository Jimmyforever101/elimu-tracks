-- Migration: Add tea_cups_count column to kitchen_plates table
ALTER TABLE kitchen_plates ADD COLUMN tea_cups_count INT NOT NULL DEFAULT 0;
