<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Department;
use App\Models\Speciality;
use App\Models\Floor;
use App\Models\Room;
use App\Models\DoctorType;
use App\Models\Doctor;
use App\Models\Procedure;

class HospitalMasterSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Departments
        $departments = [
            ['name' => 'Cardiology', 'number_of_beds' => 50, 'is_active' => true],
            ['name' => 'Neurology', 'number_of_beds' => 30, 'is_active' => true],
            ['name' => 'Orthopedics', 'number_of_beds' => 40, 'is_active' => true],
            ['name' => 'Pediatrics', 'number_of_beds' => 20, 'is_active' => true],
            ['name' => 'Oncology', 'number_of_beds' => 25, 'is_active' => true],
        ];
        
        $deptIds = [];
        foreach ($departments as $dept) {
            $department = Department::firstOrCreate(['name' => $dept['name']], $dept);
            $deptIds[$dept['name']] = $department->id;
        }

        // 2. Specialities
        $specialities = [
            ['name' => 'Interventional Cardiology', 'department_id' => $deptIds['Cardiology']],
            ['name' => 'Neurosurgery', 'department_id' => $deptIds['Neurology']],
            ['name' => 'Joint Replacement', 'department_id' => $deptIds['Orthopedics']],
            ['name' => 'Neonatology', 'department_id' => $deptIds['Pediatrics']],
            ['name' => 'Medical Oncology', 'department_id' => $deptIds['Oncology']],
        ];

        $specIds = [];
        foreach ($specialities as $spec) {
            $speciality = Speciality::firstOrCreate(['name' => $spec['name']], $spec);
            $specIds[$spec['name']] = $speciality->id;
        }

        // 3. Floors
        $floors = [
            ['name' => 'Ground Floor'],
            ['name' => 'First Floor'],
            ['name' => 'Second Floor'],
            ['name' => 'Third Floor'],
            ['name' => 'Fourth Floor'],
        ];

        $floorIds = [];
        foreach ($floors as $flr) {
            $floor = Floor::firstOrCreate(['name' => $flr['name']], $flr);
            $floorIds[$flr['name']] = $floor->id;
        }

        // 4. Rooms (Wards and regular rooms)
        $rooms = [
            ['floor_id' => $floorIds['Ground Floor'], 'name' => 'Emergency Ward A', 'is_ward' => true, 'number_of_beds' => 10, 'per_day_rent' => 500],
            ['floor_id' => $floorIds['First Floor'], 'name' => 'ICU', 'is_ward' => true, 'number_of_beds' => 5, 'per_day_rent' => 2000],
            ['floor_id' => $floorIds['Second Floor'], 'name' => 'Room 201 (Private)', 'is_ward' => false, 'number_of_beds' => 1, 'per_day_rent' => 1500],
            ['floor_id' => $floorIds['Third Floor'], 'name' => 'General Ward B', 'is_ward' => true, 'number_of_beds' => 20, 'per_day_rent' => 300],
            ['floor_id' => $floorIds['Fourth Floor'], 'name' => 'Room 405 (Semi-Private)', 'is_ward' => false, 'number_of_beds' => 2, 'per_day_rent' => 800],
        ];

        foreach ($rooms as $rm) {
            Room::firstOrCreate(['name' => $rm['name']], $rm);
        }

        // 5. Doctor Types
        $docTypes = [
            ['name' => 'Consultant'],
            ['name' => 'Surgeon'],
            ['name' => 'Resident'],
            ['name' => 'Visiting Doctor'],
            ['name' => 'General Physician'],
        ];

        foreach ($docTypes as $dt) {
            DoctorType::firstOrCreate(['name' => $dt['name']], $dt);
        }

        // 6. Doctors
        $doctors = [
            [
                'code' => 'DOC001',
                'name' => 'Dr. John Smith',
                'type' => 'Consultant',
                'department_id' => $deptIds['Cardiology'],
                'speciality_id' => $specIds['Interventional Cardiology'],
                'room_location' => 'Room 101',
                'employee_group' => 'Permanent',
                'working_days' => ['Monday', 'Wednesday', 'Friday'],
                'address' => '123 Medical St, City',
                'mobile_number' => '1234567890',
                'office_phone' => '0987654321',
                'reception_phone' => '1112223334',
                'accounts_of' => 'Hospital',
                'fee' => 1000,
                'is_active' => true,
                'general_normal_fee' => 800,
                'general_emergency_fee' => 1200,
                'welfare_normal_fee' => 500,
                'welfare_emergency_fee' => 700,
                'general_normal_percentage' => 50,
                'general_emergency_percentage' => 60,
                'welfare_normal_percentage' => 40,
                'welfare_emergency_percentage' => 50,
            ],
            [
                'code' => 'DOC002',
                'name' => 'Dr. Sarah Connor',
                'type' => 'Surgeon',
                'department_id' => $deptIds['Neurology'],
                'speciality_id' => $specIds['Neurosurgery'],
                'room_location' => 'OT 1',
                'employee_group' => 'Permanent',
                'working_days' => ['Tuesday', 'Thursday', 'Saturday'],
                'address' => '456 Tech Ave, City',
                'mobile_number' => '2234567890',
                'office_phone' => '0987654322',
                'reception_phone' => '1112223335',
                'accounts_of' => 'Hospital',
                'fee' => 1500,
                'is_active' => true,
                'general_normal_fee' => 1200,
                'general_emergency_fee' => 2000,
                'welfare_normal_fee' => 800,
                'welfare_emergency_fee' => 1200,
                'general_normal_percentage' => 60,
                'general_emergency_percentage' => 70,
                'welfare_normal_percentage' => 50,
                'welfare_emergency_percentage' => 60,
            ],
            [
                'code' => 'DOC003',
                'name' => 'Dr. Michael Johnson',
                'type' => 'Resident',
                'department_id' => $deptIds['Pediatrics'],
                'speciality_id' => $specIds['Neonatology'],
                'room_location' => 'Ward A',
                'employee_group' => 'Contract',
                'working_days' => ['Monday', 'Tuesday', 'Wednesday'],
                'address' => '789 Baby Lane, City',
                'mobile_number' => '3234567890',
                'office_phone' => '0987654323',
                'reception_phone' => '1112223336',
                'accounts_of' => 'Self',
                'fee' => 500,
                'is_active' => true,
                'general_normal_fee' => 400,
                'general_emergency_fee' => 800,
                'welfare_normal_fee' => 200,
                'welfare_emergency_fee' => 400,
                'general_normal_percentage' => 40,
                'general_emergency_percentage' => 50,
                'welfare_normal_percentage' => 30,
                'welfare_emergency_percentage' => 40,
            ],
            [
                'code' => 'DOC004',
                'name' => 'Dr. Emily Rose',
                'type' => 'Consultant',
                'department_id' => $deptIds['Oncology'],
                'speciality_id' => $specIds['Medical Oncology'],
                'room_location' => 'Room 305',
                'employee_group' => 'Permanent',
                'working_days' => ['Monday', 'Thursday', 'Friday'],
                'address' => '321 Care Blvd, City',
                'mobile_number' => '4234567890',
                'office_phone' => '0987654324',
                'reception_phone' => '1112223337',
                'accounts_of' => 'Hospital',
                'fee' => 1200,
                'is_active' => true,
                'general_normal_fee' => 1000,
                'general_emergency_fee' => 1500,
                'welfare_normal_fee' => 600,
                'welfare_emergency_fee' => 900,
                'general_normal_percentage' => 55,
                'general_emergency_percentage' => 65,
                'welfare_normal_percentage' => 45,
                'welfare_emergency_percentage' => 55,
            ],
            [
                'code' => 'DOC005',
                'name' => 'Dr. William Blake',
                'type' => 'Visiting Doctor',
                'department_id' => $deptIds['Orthopedics'],
                'speciality_id' => $specIds['Joint Replacement'],
                'room_location' => 'Room 202',
                'employee_group' => 'Visiting',
                'working_days' => ['Saturday', 'Sunday'],
                'address' => '555 Bone St, City',
                'mobile_number' => '5234567890',
                'office_phone' => '0987654325',
                'reception_phone' => '1112223338',
                'accounts_of' => 'Self',
                'fee' => 2000,
                'is_active' => true,
                'general_normal_fee' => 1800,
                'general_emergency_fee' => 2500,
                'welfare_normal_fee' => 1000,
                'welfare_emergency_fee' => 1500,
                'general_normal_percentage' => 70,
                'general_emergency_percentage' => 80,
                'welfare_normal_percentage' => 60,
                'welfare_emergency_percentage' => 70,
            ],
        ];

        foreach ($doctors as $doc) {
            Doctor::firstOrCreate(['code' => $doc['code']], $doc);
        }

        // 7. Procedures
        $procedures = [
            [
                'type' => 'Surgical',
                'department_id' => $deptIds['Cardiology'],
                'speciality_id' => $specIds['Interventional Cardiology'],
                'room_location' => 'Cath Lab',
                'name' => 'Angioplasty',
                'performed_for' => 'Inpatient',
                'general_normal_fee' => 50000,
                'general_emergency_fee' => 75000,
                'welfare_normal_fee' => 30000,
                'welfare_emergency_fee' => 45000,
                'general_normal_doctor_percentage' => 30,
                'general_emergency_doctor_percentage' => 40,
                'welfare_normal_doctor_percentage' => 20,
                'welfare_emergency_doctor_percentage' => 30,
            ],
            [
                'type' => 'Diagnostic',
                'department_id' => $deptIds['Neurology'],
                'speciality_id' => $specIds['Neurosurgery'],
                'room_location' => 'MRI Room',
                'name' => 'Brain MRI',
                'performed_for' => 'Outpatient',
                'general_normal_fee' => 10000,
                'general_emergency_fee' => 15000,
                'welfare_normal_fee' => 5000,
                'welfare_emergency_fee' => 8000,
                'general_normal_doctor_percentage' => 10,
                'general_emergency_doctor_percentage' => 15,
                'welfare_normal_doctor_percentage' => 5,
                'welfare_emergency_doctor_percentage' => 10,
            ],
            [
                'type' => 'Therapeutic',
                'department_id' => $deptIds['Orthopedics'],
                'speciality_id' => $specIds['Joint Replacement'],
                'room_location' => 'OT 2',
                'name' => 'Knee Replacement',
                'performed_for' => 'Inpatient',
                'general_normal_fee' => 150000,
                'general_emergency_fee' => 200000,
                'welfare_normal_fee' => 100000,
                'welfare_emergency_fee' => 120000,
                'general_normal_doctor_percentage' => 40,
                'general_emergency_doctor_percentage' => 50,
                'welfare_normal_doctor_percentage' => 30,
                'welfare_emergency_doctor_percentage' => 40,
            ],
            [
                'type' => 'Diagnostic',
                'department_id' => $deptIds['Pediatrics'],
                'speciality_id' => $specIds['Neonatology'],
                'room_location' => 'NICU',
                'name' => 'Newborn Screening',
                'performed_for' => 'Both',
                'general_normal_fee' => 2000,
                'general_emergency_fee' => 3000,
                'welfare_normal_fee' => 1000,
                'welfare_emergency_fee' => 1500,
                'general_normal_doctor_percentage' => 20,
                'general_emergency_doctor_percentage' => 25,
                'welfare_normal_doctor_percentage' => 10,
                'welfare_emergency_doctor_percentage' => 15,
            ],
            [
                'type' => 'Therapeutic',
                'department_id' => $deptIds['Oncology'],
                'speciality_id' => $specIds['Medical Oncology'],
                'room_location' => 'Chemo Ward',
                'name' => 'Chemotherapy Session',
                'performed_for' => 'Daycare',
                'general_normal_fee' => 20000,
                'general_emergency_fee' => 25000,
                'welfare_normal_fee' => 10000,
                'welfare_emergency_fee' => 15000,
                'general_normal_doctor_percentage' => 15,
                'general_emergency_doctor_percentage' => 20,
                'welfare_normal_doctor_percentage' => 10,
                'welfare_emergency_doctor_percentage' => 15,
            ],
        ];

        foreach ($procedures as $proc) {
            Procedure::firstOrCreate(['name' => $proc['name']], $proc);
        }
    }
}
