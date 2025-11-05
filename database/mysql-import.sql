-- Day2Day-Manager MySQL Import
-- Exportiert: 2025-11-03 08:10:35
-- SQLite → MySQL Migration

SET FOREIGN_KEY_CHECKS = 0;

-- Tabelle: projects
TRUNCATE TABLE `projects`;
INSERT INTO `projects` (`id`, `moco_id`, `moco_created_at`, `responsible_id`, `name`, `description`, `status`, `start_date`, `end_date`, `estimated_hours`, `hourly_rate`, `progress`, `created_at`, `updated_at`) VALUES (1, NULL, NULL, NULL, 'Gantt-Testprojekt', 'Ein Projekt zum Testen und Entwickeln der neuen Gantt-Funktionen.', 'in_progress', '1970-01-01', '2026-04-30', NULL, NULL, 0, '2025-10-30 10:29:18', '2025-10-30 10:29:18');

-- Tabelle: employees
TRUNCATE TABLE `employees`;
INSERT INTO `employees` (`id`, `first_name`, `last_name`, `department`, `weekly_capacity`, `email`, `phone`, `role`, `position`, `hourly_rate`, `is_active`, `timeline_order`, `created_at`, `updated_at`) VALUES (1, 'Elisha', 'Kuhic', 'Entwicklung', 40, NULL, NULL, NULL, NULL, NULL, 1, 0, '2025-10-30 10:29:18', '2025-10-30 10:29:18');
INSERT INTO `employees` (`id`, `first_name`, `last_name`, `department`, `weekly_capacity`, `email`, `phone`, `role`, `position`, `hourly_rate`, `is_active`, `timeline_order`, `created_at`, `updated_at`) VALUES (2, 'Catharine', 'Wisozk', 'Entwicklung', 40, NULL, NULL, NULL, NULL, NULL, 1, 0, '2025-10-30 10:29:18', '2025-10-30 10:29:18');
INSERT INTO `employees` (`id`, `first_name`, `last_name`, `department`, `weekly_capacity`, `email`, `phone`, `role`, `position`, `hourly_rate`, `is_active`, `timeline_order`, `created_at`, `updated_at`) VALUES (3, 'Catherine', 'Mitchell', 'Entwicklung', 40, NULL, NULL, NULL, NULL, NULL, 1, 0, '2025-10-30 10:29:18', '2025-10-30 10:29:18');
INSERT INTO `employees` (`id`, `first_name`, `last_name`, `department`, `weekly_capacity`, `email`, `phone`, `role`, `position`, `hourly_rate`, `is_active`, `timeline_order`, `created_at`, `updated_at`) VALUES (4, 'Idella', 'Altenwerth', 'Design', 40, NULL, NULL, NULL, NULL, NULL, 1, 0, '2025-10-30 10:29:18', '2025-10-30 10:29:18');
INSERT INTO `employees` (`id`, `first_name`, `last_name`, `department`, `weekly_capacity`, `email`, `phone`, `role`, `position`, `hourly_rate`, `is_active`, `timeline_order`, `created_at`, `updated_at`) VALUES (5, 'Javonte', 'Pfeffer', 'Entwicklung', 40, NULL, NULL, NULL, NULL, NULL, 1, 0, '2025-10-30 10:29:18', '2025-10-30 10:29:18');

-- Tabelle: assignments
TRUNCATE TABLE `assignments`;
INSERT INTO `assignments` (`id`, `employee_id`, `project_id`, `task_name`, `role`, `task_description`, `weekly_hours`, `start_date`, `end_date`, `is_active`, `priority_level`, `source`, `display_order`, `created_at`, `updated_at`) VALUES (1, 1, 1, 'Konzeptphase 1', 'team_member', 'Detailplanung für die Konzeptphase 1 für Elisha.', 13, '2025-10-12', '2025-10-29', 1, 5, 'manual', 1, '2025-10-30 10:29:18', '2025-10-30 10:29:18');
INSERT INTO `assignments` (`id`, `employee_id`, `project_id`, `task_name`, `role`, `task_description`, `weekly_hours`, `start_date`, `end_date`, `is_active`, `priority_level`, `source`, `display_order`, `created_at`, `updated_at`) VALUES (2, 1, 1, 'Konzeptphase 2', 'team_member', 'Detailplanung für die Konzeptphase 2 für Elisha.', 34, '2025-11-03', '2025-11-16', 1, 2, 'manual', 2, '2025-10-30 10:29:18', '2025-10-30 10:29:18');
INSERT INTO `assignments` (`id`, `employee_id`, `project_id`, `task_name`, `role`, `task_description`, `weekly_hours`, `start_date`, `end_date`, `is_active`, `priority_level`, `source`, `display_order`, `created_at`, `updated_at`) VALUES (3, 2, 1, 'Konzeptphase 1', 'team_member', 'Detailplanung für die Konzeptphase 1 für Catharine.', 34, '2025-10-08', '2025-10-22', 1, 5, 'manual', 1, '2025-10-30 10:29:18', '2025-10-30 10:29:18');
INSERT INTO `assignments` (`id`, `employee_id`, `project_id`, `task_name`, `role`, `task_description`, `weekly_hours`, `start_date`, `end_date`, `is_active`, `priority_level`, `source`, `display_order`, `created_at`, `updated_at`) VALUES (4, 2, 1, 'Konzeptphase 2', 'team_member', 'Detailplanung für die Konzeptphase 2 für Catharine.', 11, '2025-10-27', '2025-11-13', 1, 5, 'manual', 2, '2025-10-30 10:29:18', '2025-10-30 10:29:18');
INSERT INTO `assignments` (`id`, `employee_id`, `project_id`, `task_name`, `role`, `task_description`, `weekly_hours`, `start_date`, `end_date`, `is_active`, `priority_level`, `source`, `display_order`, `created_at`, `updated_at`) VALUES (5, 3, 1, 'Konzeptphase 1', 'team_member', 'Detailplanung für die Konzeptphase 1 für Catherine.', 16, '2025-10-03', '2025-10-10', 1, 1, 'manual', 1, '2025-10-30 10:29:18', '2025-10-30 10:29:18');
INSERT INTO `assignments` (`id`, `employee_id`, `project_id`, `task_name`, `role`, `task_description`, `weekly_hours`, `start_date`, `end_date`, `is_active`, `priority_level`, `source`, `display_order`, `created_at`, `updated_at`) VALUES (6, 3, 1, 'Konzeptphase 2', 'team_member', 'Detailplanung für die Konzeptphase 2 für Catherine.', 23, '2025-10-12', '2025-10-20', 1, 2, 'manual', 2, '2025-10-30 10:29:18', '2025-10-30 10:29:18');
INSERT INTO `assignments` (`id`, `employee_id`, `project_id`, `task_name`, `role`, `task_description`, `weekly_hours`, `start_date`, `end_date`, `is_active`, `priority_level`, `source`, `display_order`, `created_at`, `updated_at`) VALUES (7, 3, 1, 'Konzeptphase 3', 'team_member', 'Detailplanung für die Konzeptphase 3 für Catherine.', 29, '2025-10-22', '2025-11-08', 1, 4, 'manual', 3, '2025-10-30 10:29:18', '2025-10-30 10:29:18');
INSERT INTO `assignments` (`id`, `employee_id`, `project_id`, `task_name`, `role`, `task_description`, `weekly_hours`, `start_date`, `end_date`, `is_active`, `priority_level`, `source`, `display_order`, `created_at`, `updated_at`) VALUES (8, 4, 1, 'Konzeptphase 1', 'team_member', 'Detailplanung für die Konzeptphase 1 für Idella.', 26, '2025-10-07', '2025-10-27', 1, 4, 'manual', 1, '2025-10-30 10:29:18', '2025-10-30 10:29:18');
INSERT INTO `assignments` (`id`, `employee_id`, `project_id`, `task_name`, `role`, `task_description`, `weekly_hours`, `start_date`, `end_date`, `is_active`, `priority_level`, `source`, `display_order`, `created_at`, `updated_at`) VALUES (9, 4, 1, 'Konzeptphase 2', 'team_member', 'Detailplanung für die Konzeptphase 2 für Idella.', 13, '2025-10-30', '2025-11-18', 1, 2, 'manual', 2, '2025-10-30 10:29:18', '2025-10-30 10:29:18');
INSERT INTO `assignments` (`id`, `employee_id`, `project_id`, `task_name`, `role`, `task_description`, `weekly_hours`, `start_date`, `end_date`, `is_active`, `priority_level`, `source`, `display_order`, `created_at`, `updated_at`) VALUES (10, 4, 1, 'Konzeptphase 3', 'team_member', 'Detailplanung für die Konzeptphase 3 für Idella.', 26, '2025-11-22', '2025-12-04', 1, 3, 'manual', 3, '2025-10-30 10:29:18', '2025-10-30 10:29:18');
INSERT INTO `assignments` (`id`, `employee_id`, `project_id`, `task_name`, `role`, `task_description`, `weekly_hours`, `start_date`, `end_date`, `is_active`, `priority_level`, `source`, `display_order`, `created_at`, `updated_at`) VALUES (11, 5, 1, 'Konzeptphase 1', 'team_member', 'Detailplanung für die Konzeptphase 1 für Javonte.', 30, '2025-10-15', '2025-10-24', 1, 4, 'manual', 1, '2025-10-30 10:29:18', '2025-10-30 10:29:18');
INSERT INTO `assignments` (`id`, `employee_id`, `project_id`, `task_name`, `role`, `task_description`, `weekly_hours`, `start_date`, `end_date`, `is_active`, `priority_level`, `source`, `display_order`, `created_at`, `updated_at`) VALUES (12, 5, 1, 'Konzeptphase 2', 'team_member', 'Detailplanung für die Konzeptphase 2 für Javonte.', 12, '2025-10-25', '2025-11-01', 1, 1, 'manual', 2, '2025-10-30 10:29:18', '2025-10-30 10:29:18');
INSERT INTO `assignments` (`id`, `employee_id`, `project_id`, `task_name`, `role`, `task_description`, `weekly_hours`, `start_date`, `end_date`, `is_active`, `priority_level`, `source`, `display_order`, `created_at`, `updated_at`) VALUES (13, 5, 1, 'Konzeptphase 3', 'team_member', 'Detailplanung für die Konzeptphase 3 für Javonte.', 25, '2025-11-03', '2025-11-13', 1, 1, 'manual', 3, '2025-10-30 10:29:18', '2025-10-30 10:29:18');

-- Tabelle time_entries: Keine Daten

-- Tabelle absences: Keine Daten

-- Tabelle teams: Keine Daten

-- Tabelle team_assignments: Keine Daten

-- Tabelle gantt_filter_sets: Keine Daten

-- Tabelle project_assignment_overrides: Keine Daten

-- Tabelle moco_sync_logs: Keine Daten


SET FOREIGN_KEY_CHECKS = 1;
