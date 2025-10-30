<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Toda;
use App\Models\Driver;
use App\Models\Motorcycle;
use App\Models\Operator;
use Carbon\Carbon;

class OperatorManagementSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear existing data
        Operator::query()->delete();
        Driver::query()->delete();
        Motorcycle::query()->delete();
        Toda::query()->delete();

        // Seed TODA
        $toda = [
            [
                'toda_name' => 'San Pedro TODA',
                'toda_president' => 'Juan Dela Cruz',
                'date_registered' => '2020-01-15',
                'toda_status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'toda_name' => 'Pacita TODA',
                'toda_president' => 'Maria Santos',
                'date_registered' => '2019-03-20',
                'toda_status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'toda_name' => 'United Bayanihan TODA',
                'toda_president' => 'Roberto Garcia',
                'date_registered' => '2021-06-10',
                'toda_status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'toda_name' => 'San Vicente TODA',
                'toda_president' => 'Lorna Reyes',
                'date_registered' => '2018-11-05',
                'toda_status' => 'inactive',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'toda_name' => 'Magsaysay TODA',
                'toda_president' => 'Antonio Mendoza',
                'date_registered' => '2022-02-28',
                'toda_status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        $todaIds = [];
        foreach ($toda as $todaData) {
            $todaModel = Toda::create($todaData);
            $todaIds[] = $todaModel->id;
        }

        // Seed Drivers
        $drivers = [
            [
                'driver_fullname' => 'Michael Johnson',
                'driver_license_number' => 'DL-0012345',
                'expiration_date' => '2025-12-31',
                'driver_contact_number' => '09171234567',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'driver_fullname' => 'Sarah Smith',
                'driver_license_number' => 'DL-0012346',
                'expiration_date' => '2024-11-30',
                'driver_contact_number' => '09172345678',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'driver_fullname' => 'David Brown',
                'driver_license_number' => 'DL-0012347',
                'expiration_date' => '2026-03-15',
                'driver_contact_number' => '09173456789',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'driver_fullname' => 'Jennifer Wilson',
                'driver_license_number' => 'DL-0012348',
                'expiration_date' => '2024-09-20',
                'driver_contact_number' => '09174567890',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'driver_fullname' => 'Christopher Lee',
                'driver_license_number' => 'DL-0012349',
                'expiration_date' => '2025-07-10',
                'driver_contact_number' => '09175678901',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'driver_fullname' => 'Amanda Garcia',
                'driver_license_number' => 'DL-0012350',
                'expiration_date' => '2024-12-25',
                'driver_contact_number' => '09176789012',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'driver_fullname' => 'James Martinez',
                'driver_license_number' => 'DL-0012351',
                'expiration_date' => '2025-08-14',
                'driver_contact_number' => '09177890123',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'driver_fullname' => 'Lisa Anderson',
                'driver_license_number' => 'DL-0012352',
                'expiration_date' => '2026-01-30',
                'driver_contact_number' => '09178901234',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'driver_fullname' => 'Robert Taylor',
                'driver_license_number' => 'DL-0012353',
                'expiration_date' => '2024-10-05',
                'driver_contact_number' => '09179012345',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'driver_fullname' => 'Michelle White',
                'driver_license_number' => 'DL-0012354',
                'expiration_date' => '2025-05-20',
                'driver_contact_number' => '09170123456',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        $driverIds = [];
        foreach ($drivers as $driverData) {
            $driverModel = Driver::create($driverData);
            $driverIds[] = $driverModel->id;
        }

        // Seed Motorcycles
        $motorcycles = [
            [
                'plate_number' => 'ABC-1234',
                'motor_number' => 'MN123456789',
                'chassis_number' => 'CH123456789',
                'make' => 'Honda',
                'year_model' => '2022',
                'registered_date' => '2022-01-10',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'plate_number' => 'DEF-5678',
                'motor_number' => 'MN987654321',
                'chassis_number' => 'CH987654321',
                'make' => 'Yamaha',
                'year_model' => '2021',
                'registered_date' => '2021-03-15',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'plate_number' => 'GHI-9012',
                'motor_number' => 'MN456789123',
                'chassis_number' => 'CH456789123',
                'make' => 'Suzuki',
                'year_model' => '2023',
                'registered_date' => '2023-02-20',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'plate_number' => 'JKL-3456',
                'motor_number' => 'MN789123456',
                'chassis_number' => 'CH789123456',
                'make' => 'Kawasaki',
                'year_model' => '2020',
                'registered_date' => '2020-11-05',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'plate_number' => 'MNO-7890',
                'motor_number' => 'MN321654987',
                'chassis_number' => 'CH321654987',
                'make' => 'Honda',
                'year_model' => '2022',
                'registered_date' => '2022-04-12',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'plate_number' => 'PQR-2345',
                'motor_number' => 'MN654987321',
                'chassis_number' => 'CH654987321',
                'make' => 'Yamaha',
                'year_model' => '2021',
                'registered_date' => '2021-07-25',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'plate_number' => 'STU-6789',
                'motor_number' => 'MN987321654',
                'chassis_number' => 'CH987321654',
                'make' => 'Suzuki',
                'year_model' => '2023',
                'registered_date' => '2023-01-08',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'plate_number' => 'VWX-0123',
                'motor_number' => 'MN147258369',
                'chassis_number' => 'CH147258369',
                'make' => 'Kawasaki',
                'year_model' => '2020',
                'registered_date' => '2020-09-14',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'plate_number' => 'YZA-4567',
                'motor_number' => 'MN258369147',
                'chassis_number' => 'CH258369147',
                'make' => 'Honda',
                'year_model' => '2022',
                'registered_date' => '2022-06-30',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'plate_number' => 'BCD-8901',
                'motor_number' => 'MN369147258',
                'chassis_number' => 'CH369147258',
                'make' => 'Yamaha',
                'year_model' => '2021',
                'registered_date' => '2021-12-18',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        $motorcycleIds = [];
        foreach ($motorcycles as $motorcycleData) {
            $motorcycleModel = Motorcycle::create($motorcycleData);
            $motorcycleIds[] = $motorcycleModel->id;
        }

        // Seed Operators
        $operators = [
            [
                'fullname' => 'Carlos Rodriguez',
                'address' => '123 Main Street, San Pedro, Laguna',
                'email_address' => 'carlos.rodriguez@email.com',
                'contact_number' => '09171234567',
                'driver_id' => $driverIds[0],
                'motorcycle_id' => $motorcycleIds[0],
                'mtop_number' => 'MTOP-001',
                'toda_id' => $todaIds[0],
                'date_registered' => '2023-01-15',
                'date_last_paid' => '2024-01-10',
                'permit_status' => 'renew',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'fullname' => 'Elena Santos',
                'address' => '456 Oak Avenue, Pacita, San Pedro, Laguna',
                'email_address' => 'elena.santos@email.com',
                'contact_number' => '09172345678',
                'driver_id' => $driverIds[1],
                'motorcycle_id' => $motorcycleIds[1],
                'mtop_number' => 'MTOP-002',
                'toda_id' => $todaIds[1],
                'date_registered' => '2023-02-20',
                'date_last_paid' => '2024-02-15',
                'permit_status' => 'renew',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'fullname' => 'Miguel Torres',
                'address' => '789 Pine Road, United Bayanihan, San Pedro, Laguna',
                'email_address' => 'miguel.torres@email.com',
                'contact_number' => '09173456789',
                'driver_id' => $driverIds[2],
                'motorcycle_id' => $motorcycleIds[2],
                'mtop_number' => 'MTOP-003',
                'toda_id' => $todaIds[2],
                'date_registered' => '2023-03-10',
                'date_last_paid' => '2024-03-05',
                'permit_status' => 'renew',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'fullname' => 'Isabel Cruz',
                'address' => '321 Elm Street, San Vicente, San Pedro, Laguna',
                'email_address' => 'isabel.cruz@email.com',
                'contact_number' => '09174567890',
                'driver_id' => $driverIds[3],
                'motorcycle_id' => $motorcycleIds[3],
                'mtop_number' => 'MTOP-004',
                'toda_id' => $todaIds[3],
                'date_registered' => '2022-11-05',
                'date_last_paid' => '2023-11-01',
                'permit_status' => 'retire',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'fullname' => 'Ricardo Lim',
                'address' => '654 Maple Lane, Magsaysay, San Pedro, Laguna',
                'email_address' => 'ricardo.lim@email.com',
                'contact_number' => '09175678901',
                'driver_id' => $driverIds[4],
                'motorcycle_id' => $motorcycleIds[4],
                'mtop_number' => 'MTOP-005',
                'toda_id' => $todaIds[4],
                'date_registered' => '2024-01-08',
                'date_last_paid' => null,
                'permit_status' => 'new',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'fullname' => 'Andrea Reyes',
                'address' => '987 Cedar Court, San Pedro, Laguna',
                'email_address' => 'andrea.reyes@email.com',
                'contact_number' => '09176789012',
                'driver_id' => $driverIds[5],
                'motorcycle_id' => $motorcycleIds[5],
                'mtop_number' => 'MTOP-006',
                'toda_id' => $todaIds[0],
                'date_registered' => '2023-12-15',
                'date_last_paid' => null,
                'permit_status' => 'new',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'fullname' => 'Fernando Gomez',
                'address' => '147 Birch Street, Pacita, San Pedro, Laguna',
                'email_address' => 'fernando.gomez@email.com',
                'contact_number' => '09177890123',
                'driver_id' => $driverIds[6],
                'motorcycle_id' => $motorcycleIds[6],
                'mtop_number' => 'MTOP-007',
                'toda_id' => $todaIds[1],
                'date_registered' => '2023-09-20',
                'date_last_paid' => '2024-09-15',
                'permit_status' => 'renew',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'fullname' => 'Sofia Tan',
                'address' => '258 Walnut Avenue, United Bayanihan, San Pedro, Laguna',
                'email_address' => 'sofia.tan@email.com',
                'contact_number' => '09178901234',
                'driver_id' => $driverIds[7],
                'motorcycle_id' => $motorcycleIds[7],
                'mtop_number' => 'MTOP-008',
                'toda_id' => $todaIds[2],
                'date_registered' => '2023-07-12',
                'date_last_paid' => '2024-07-08',
                'permit_status' => 'renew',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'fullname' => 'Alberto Chen',
                'address' => '369 Spruce Road, Magsaysay, San Pedro, Laguna',
                'email_address' => 'alberto.chen@email.com',
                'contact_number' => '09179012345',
                'driver_id' => $driverIds[8],
                'motorcycle_id' => $motorcycleIds[8],
                'mtop_number' => 'MTOP-009',
                'toda_id' => $todaIds[4],
                'date_registered' => '2024-02-28',
                'date_last_paid' => null,
                'permit_status' => 'new',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'fullname' => 'Carmen Wong',
                'address' => '741 Oakwood Drive, San Pedro, Laguna',
                'email_address' => 'carmen.wong@email.com',
                'contact_number' => '09170123456',
                'driver_id' => $driverIds[9],
                'motorcycle_id' => $motorcycleIds[9],
                'mtop_number' => 'MTOP-010',
                'toda_id' => $todaIds[0],
                'date_registered' => '2023-11-30',
                'date_last_paid' => '2024-11-25',
                'permit_status' => 'renew',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($operators as $operatorData) {
            Operator::create($operatorData);
        }

        $this->command->info('Operator management data seeded successfully!');
        $this->command->info('Created:');
        $this->command->info('- ' . count($toda) . ' TODA records');
        $this->command->info('- ' . count($drivers) . ' Driver records');
        $this->command->info('- ' . count($motorcycles) . ' Motorcycle records');
        $this->command->info('- ' . count($operators) . ' Operator records');
    }
}
