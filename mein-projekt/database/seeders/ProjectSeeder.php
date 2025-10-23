<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (!config('app.enable_demo_seeders', false)) {
            return;
        }

        DB::table('projects')->insert([
            // Bestehende Projekte
            [
                'name' => 'Projektmanagement enodia',
                'description' => 'IHK-Abschlussprojekt: Webapplikation zur Ressourcen- und Kapazitätsverwaltung',
                'status' => 'active',
                'start_date' => '2025-09-01',
                'end_date' => '2025-11-07',
                'estimated_hours' => 120,
                'hourly_rate' => 75.00,
                'progress' => 85,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'CRM-System Modernisierung',
                'description' => 'Aktualisierung des Kundenverwaltungssystems mit neuer Benutzeroberfläche',
                'status' => 'active',
                'start_date' => '2025-08-15',
                'end_date' => '2025-12-20',
                'estimated_hours' => 200,
                'hourly_rate' => 95.00,
                'progress' => 45,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Mobile App Kunde XYZ',
                'description' => 'Cross-Platform App für iOS und Android mit React Native',
                'status' => 'planning',
                'start_date' => '2025-10-01',
                'end_date' => '2026-02-28',
                'estimated_hours' => 300,
                'hourly_rate' => 110.00,
                'progress' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'API-Integration Projekt',
                'description' => 'REST-API für externe Systemanbindung und Datenabgleich',
                'status' => 'completed',
                'start_date' => '2025-07-01',
                'end_date' => '2025-09-15',
                'estimated_hours' => 80,
                'hourly_rate' => 85.00,
                'progress' => 100,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // Neue Projekte für mehr Vielfalt
            [
                'name' => 'E-Commerce Plattform',
                'description' => 'Entwicklung einer modernen Online-Shop-Lösung mit Laravel und Vue.js',
                'status' => 'active',
                'start_date' => '2025-09-15',
                'end_date' => '2026-01-31',
                'estimated_hours' => 450,
                'hourly_rate' => 120.00,
                'progress' => 25,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Datenbank-Migration',
                'description' => 'Migration von MySQL zu PostgreSQL mit Performance-Optimierung',
                'status' => 'active',
                'start_date' => '2025-10-01',
                'end_date' => '2025-12-15',
                'estimated_hours' => 150,
                'hourly_rate' => 90.00,
                'progress' => 60,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Security Audit',
                'description' => 'Umfassende Sicherheitsprüfung aller Webanwendungen',
                'status' => 'planning',
                'start_date' => '2025-11-01',
                'end_date' => '2025-12-31',
                'estimated_hours' => 100,
                'hourly_rate' => 150.00,
                'progress' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Cloud-Infrastruktur Setup',
                'description' => 'Aufbau einer skalierbaren AWS-Infrastruktur mit Docker',
                'status' => 'active',
                'start_date' => '2025-08-01',
                'end_date' => '2025-11-30',
                'estimated_hours' => 180,
                'hourly_rate' => 110.00,
                'progress' => 70,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Legacy System Wartung',
                'description' => 'Wartung und Updates für veraltete Systeme',
                'status' => 'active',
                'start_date' => '2025-01-01',
                'end_date' => '2025-12-31',
                'estimated_hours' => 200,
                'hourly_rate' => 65.00,
                'progress' => 40,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'KI-Chatbot Integration',
                'description' => 'Integration eines intelligenten Chatbots für Kundensupport',
                'status' => 'planning',
                'start_date' => '2026-01-15',
                'end_date' => '2026-04-30',
                'estimated_hours' => 250,
                'hourly_rate' => 130.00,
                'progress' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Performance Monitoring',
                'description' => 'Implementierung eines umfassenden Monitoring-Systems',
                'status' => 'completed',
                'start_date' => '2025-06-01',
                'end_date' => '2025-08-31',
                'estimated_hours' => 90,
                'hourly_rate' => 80.00,
                'progress' => 100,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Microservices Architektur',
                'description' => 'Umstellung auf Microservices-Architektur für bessere Skalierbarkeit',
                'status' => 'on_hold',
                'start_date' => '2025-12-01',
                'end_date' => '2026-06-30',
                'estimated_hours' => 500,
                'hourly_rate' => 140.00,
                'progress' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
