<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Guarantor;

class GuarantorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $guarantors = [
            // Predefined realistic Sri Lankan guarantors with proper details
            [
                'first_name' => 'Mahinda',
                'last_name' => 'Rajapaksa',
                'nic' => '195110234567',
                'phone' => '0771234567',
                'email' => 'mahinda.rajapaksa@gmail.com',
                'address' => 'No. 123, Galle Road, Colombo 03',
                'relationship' => 'Father',
                'is_active' => true
            ],
            [
                'first_name' => 'Sirimavo',
                'last_name' => 'Bandaranaike',
                'nic' => '194512345678',
                'phone' => '0712345678',
                'email' => 'sirimavo.bandaranaike@yahoo.com',
                'address' => 'No. 45, Kandy Road, Peradeniya',
                'relationship' => 'Mother',
                'is_active' => true
            ],
            [
                'first_name' => 'Chamari',
                'last_name' => 'Athapaththu',
                'nic' => '199012345679',
                'phone' => '0773456789',
                'email' => 'chamari.athapaththu@cricket.lk',
                'address' => 'No. 78, Temple Road, Nugegoda',
                'relationship' => 'Sister',
                'is_active' => true
            ],
            [
                'first_name' => 'Lasith',
                'last_name' => 'Malinga',
                'nic' => '198423456780',
                'phone' => '0754567890',
                'email' => 'lasith.malinga@sports.lk',
                'address' => 'No. 56, Main Street, Gampaha',
                'relationship' => 'Brother',
                'is_active' => true
            ],
            [
                'first_name' => 'Arjuna',
                'last_name' => 'Ranatunga',
                'nic' => '196534567891',
                'phone' => '0765678901',
                'email' => 'arjuna.ranatunga@cricket.lk',
                'address' => 'No. 89, Lake Road, Kurunegala',
                'relationship' => 'Uncle',
                'is_active' => true
            ],
            [
                'first_name' => 'Muttiah',
                'last_name' => 'Muralitharan',
                'nic' => '197245678902',
                'phone' => '0776789012',
                'email' => 'murali@cricket.lk',
                'address' => 'No. 234, Hill Street, Kandy',
                'relationship' => 'Father',
                'is_active' => true
            ],
            [
                'first_name' => 'Chandrika',
                'last_name' => 'Kumaratunga',
                'nic' => '194556789013',
                'phone' => '0717890123',
                'email' => 'chandrika.kumaratunga@president.lk',
                'address' => 'No. 567, Independence Avenue, Colombo 07',
                'relationship' => 'Mother',
                'is_active' => true
            ],
            [
                'first_name' => 'Kumar',
                'last_name' => 'Sangakkara',
                'nic' => '197767890124',
                'phone' => '0728901234',
                'email' => 'kumar.sangakkara@cricket.lk',
                'address' => 'No. 890, Peradeniya Road, Kandy',
                'relationship' => 'Spouse',
                'is_active' => false
            ],
            [
                'first_name' => 'Mahela',
                'last_name' => 'Jayawardene',
                'nic' => '197778901235',
                'phone' => '0739012345',
                'email' => 'mahela.jayawardene@cricket.lk',
                'address' => 'No. 123, Baseline Road, Colombo 09',
                'relationship' => 'Friend',
                'is_active' => true
            ],
            [
                'first_name' => 'Rangana',
                'last_name' => 'Herath',
                'nic' => '197889012346',
                'phone' => '0740123456',
                'email' => 'rangana.herath@cricket.lk',
                'address' => 'No. 456, Galle Road, Kurunegala',
                'relationship' => 'Brother',
                'is_active' => true
            ],
            [
                'first_name' => 'Sanath',
                'last_name' => 'Jayasuriya',
                'nic' => '196990123457',
                'phone' => '0751234567',
                'email' => 'sanath.jayasuriya@cricket.lk',
                'address' => 'No. 789, Matara Road, Matara',
                'relationship' => 'Uncle',
                'is_active' => true
            ],
            [
                'first_name' => 'Tillakaratne',
                'last_name' => 'Dilshan',
                'nic' => '197601234568',
                'phone' => '0762345678',
                'email' => 'tillakaratne.dilshan@cricket.lk',
                'address' => 'No. 012, Kalutara Road, Kalutara',
                'relationship' => 'Father',
                'is_active' => true
            ],
            [
                'first_name' => 'Angelo',
                'last_name' => 'Mathews',
                'nic' => '198712345679',
                'phone' => '0773456789',
                'email' => 'angelo.mathews@cricket.lk',
                'address' => 'No. 345, Negombo Road, Negombo',
                'relationship' => 'Son',
                'is_active' => false
            ],
            [
                'first_name' => 'Dinesh',
                'last_name' => 'Chandimal',
                'nic' => '198923456780',
                'phone' => '0784567890',
                'email' => 'dinesh.chandimal@cricket.lk',
                'address' => 'No. 678, Ratnapura Road, Ratnapura',
                'relationship' => 'Spouse',
                'is_active' => true
            ],
            [
                'first_name' => 'Kusal',
                'last_name' => 'Mendis',
                'nic' => '199534567891',
                'phone' => '0795678901',
                'email' => 'kusal.mendis@cricket.lk',
                'address' => 'No. 901, Chilaw Road, Chilaw',
                'relationship' => 'Brother',
                'is_active' => true
            ],
            [
                'first_name' => 'Niroshan',
                'last_name' => 'Dickwella',
                'nic' => '198645678902',
                'phone' => '0706789012',
                'email' => 'niroshan.dickwella@cricket.lk',
                'address' => 'No. 234, Panadura Road, Panadura',
                'relationship' => 'Friend',
                'is_active' => true
            ],
            [
                'first_name' => 'Suranga',
                'last_name' => 'Lakmal',
                'nic' => '198756789013',
                'phone' => '0717890123',
                'email' => 'suranga.lakmal@cricket.lk',
                'address' => 'No. 567, Badulla Road, Badulla',
                'relationship' => 'Uncle',
                'is_active' => true
            ],
            [
                'first_name' => 'Thisara',
                'last_name' => 'Perera',
                'nic' => '198967890124',
                'phone' => '0728901234',
                'email' => 'thisara.perera@cricket.lk',
                'address' => 'No. 890, Polonnaruwa Road, Polonnaruwa',
                'relationship' => 'Daughter',
                'is_active' => false
            ],
            [
                'first_name' => 'Nuwan',
                'last_name' => 'Pradeep',
                'nic' => '199078901235',
                'phone' => '0739012345',
                'email' => 'nuwan.pradeep@cricket.lk',
                'address' => 'No. 123, Anuradhapura Road, Anuradhapura',
                'relationship' => 'Father',
                'is_active' => true
            ],
            [
                'first_name' => 'Dhananjaya',
                'last_name' => 'de Silva',
                'nic' => '199189012346',
                'phone' => '0740123456',
                'email' => 'dhananjaya.desilva@cricket.lk',
                'address' => 'No. 456, Hambantota Road, Hambantota',
                'relationship' => 'Sister',
                'is_active' => true
            ]
        ];

        // Create the guarantors
        foreach ($guarantors as $guarantorData) {
            Guarantor::create($guarantorData);
        }

        // Display summary
        $this->command->info('GuarantorSeeder completed successfully!');
        $this->command->info('Created ' . count($guarantors) . ' guarantors:');
        $this->command->info('- Active guarantors: ' . collect($guarantors)->where('is_active', true)->count());
        $this->command->info('- Inactive guarantors: ' . collect($guarantors)->where('is_active', false)->count());
        $this->command->info('- Relationships: ' . collect($guarantors)->pluck('relationship')->unique()->implode(', '));
    }
}
