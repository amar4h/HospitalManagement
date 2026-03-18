<?php
/**
 * LocalStorage Class
 * Handles JSON file-based data storage
 */

class LocalStorage {
    private $dataPath;

    public function __construct() {
        $this->dataPath = dirname(__DIR__) . '/data/';
        $this->initializeStorage();
    }

    /**
     * Initialize storage directories and default data files
     */
    private function initializeStorage() {
        // Create data directory if not exists
        if (!is_dir($this->dataPath)) {
            mkdir($this->dataPath, 0755, true);
        }

        // Initialize default data files
        $this->initializeDefaultData();
    }

    /**
     * Initialize default data files with sample data
     */
    private function initializeDefaultData() {
        // Users
        if (!file_exists($this->dataPath . 'users.json')) {
            $defaultUsers = [
                [
                    'id' => 1,
                    'username' => 'admin',
                    'password' => password_hash('admin123', PASSWORD_DEFAULT),
                    'name' => 'System Administrator',
                    'email' => 'admin@hospital.com',
                    'phone' => '1234567890',
                    'role' => 'admin',
                    'status' => 'active',
                    'created_at' => date('Y-m-d H:i:s')
                ],
                [
                    'id' => 2,
                    'username' => 'doctor',
                    'password' => password_hash('doctor123', PASSWORD_DEFAULT),
                    'name' => 'Dr. John Smith',
                    'email' => 'doctor@hospital.com',
                    'phone' => '1234567891',
                    'role' => 'doctor',
                    'doctor_id' => 1,
                    'status' => 'active',
                    'created_at' => date('Y-m-d H:i:s')
                ],
                [
                    'id' => 3,
                    'username' => 'nurse',
                    'password' => password_hash('nurse123', PASSWORD_DEFAULT),
                    'name' => 'Jane Wilson',
                    'email' => 'nurse@hospital.com',
                    'phone' => '1234567892',
                    'role' => 'nurse',
                    'status' => 'active',
                    'created_at' => date('Y-m-d H:i:s')
                ],
                [
                    'id' => 4,
                    'username' => 'receptionist',
                    'password' => password_hash('reception123', PASSWORD_DEFAULT),
                    'name' => 'Mary Johnson',
                    'email' => 'reception@hospital.com',
                    'phone' => '1234567893',
                    'role' => 'receptionist',
                    'status' => 'active',
                    'created_at' => date('Y-m-d H:i:s')
                ],
                [
                    'id' => 5,
                    'username' => 'pharmacist',
                    'password' => password_hash('pharma123', PASSWORD_DEFAULT),
                    'name' => 'Robert Brown',
                    'email' => 'pharmacy@hospital.com',
                    'phone' => '1234567894',
                    'role' => 'pharmacist',
                    'status' => 'active',
                    'created_at' => date('Y-m-d H:i:s')
                ],
                [
                    'id' => 6,
                    'username' => 'labtech',
                    'password' => password_hash('lab123', PASSWORD_DEFAULT),
                    'name' => 'Sarah Davis',
                    'email' => 'lab@hospital.com',
                    'phone' => '1234567895',
                    'role' => 'lab_technician',
                    'status' => 'active',
                    'created_at' => date('Y-m-d H:i:s')
                ],
                [
                    'id' => 7,
                    'username' => 'accountant',
                    'password' => password_hash('account123', PASSWORD_DEFAULT),
                    'name' => 'Michael Lee',
                    'email' => 'accounts@hospital.com',
                    'phone' => '1234567896',
                    'role' => 'accountant',
                    'status' => 'active',
                    'created_at' => date('Y-m-d H:i:s')
                ]
            ];
            $this->save('users', $defaultUsers);
        }

        // Roles
        if (!file_exists($this->dataPath . 'roles.json')) {
            $roles = [
                ['id' => 1, 'name' => 'admin', 'display_name' => 'Administrator', 'description' => 'Full system access'],
                ['id' => 2, 'name' => 'doctor', 'display_name' => 'Doctor', 'description' => 'Medical staff with patient access'],
                ['id' => 3, 'name' => 'nurse', 'display_name' => 'Nurse', 'description' => 'Patient care and vitals'],
                ['id' => 4, 'name' => 'receptionist', 'display_name' => 'Receptionist', 'description' => 'Front desk operations'],
                ['id' => 5, 'name' => 'pharmacist', 'display_name' => 'Pharmacist', 'description' => 'Pharmacy management'],
                ['id' => 6, 'name' => 'lab_technician', 'display_name' => 'Lab Technician', 'description' => 'Laboratory operations'],
                ['id' => 7, 'name' => 'accountant', 'display_name' => 'Accountant', 'description' => 'Financial management']
            ];
            $this->save('roles', $roles);
        }

        // Departments
        if (!file_exists($this->dataPath . 'departments.json')) {
            $departments = [
                ['id' => 1, 'name' => 'General Medicine', 'description' => 'General health care', 'status' => 'active'],
                ['id' => 2, 'name' => 'Cardiology', 'description' => 'Heart and cardiovascular care', 'status' => 'active'],
                ['id' => 3, 'name' => 'Orthopedics', 'description' => 'Bone and joint care', 'status' => 'active'],
                ['id' => 4, 'name' => 'Pediatrics', 'description' => 'Child health care', 'status' => 'active'],
                ['id' => 5, 'name' => 'Gynecology', 'description' => 'Women health care', 'status' => 'active'],
                ['id' => 6, 'name' => 'Neurology', 'description' => 'Brain and nervous system', 'status' => 'active'],
                ['id' => 7, 'name' => 'Dermatology', 'description' => 'Skin care', 'status' => 'active'],
                ['id' => 8, 'name' => 'ENT', 'description' => 'Ear, Nose and Throat', 'status' => 'active'],
                ['id' => 9, 'name' => 'Ophthalmology', 'description' => 'Eye care', 'status' => 'active'],
                ['id' => 10, 'name' => 'Surgery', 'description' => 'Surgical procedures', 'status' => 'active']
            ];
            $this->save('departments', $departments);
        }

        // Doctors
        if (!file_exists($this->dataPath . 'doctors.json')) {
            $doctors = [
                [
                    'id' => 1,
                    'user_id' => 2,
                    'name' => 'Dr. John Smith',
                    'specialization' => 'Cardiologist',
                    'department_id' => 2,
                    'qualification' => 'MBBS, MD (Cardiology)',
                    'experience' => 15,
                    'phone' => '1234567891',
                    'email' => 'drjohn@hospital.com',
                    'consultation_fee' => 150,
                    'status' => 'active',
                    'created_at' => date('Y-m-d H:i:s')
                ]
            ];
            $this->save('doctors', $doctors);
        }

        // Initialize empty data files
        $emptyFiles = ['patients', 'appointments', 'opd_visits', 'ipd_admissions', 'surgeries',
                       'medicines', 'medicine_stock', 'lab_tests', 'lab_orders', 'invoices',
                       'payments', 'beds', 'prescriptions', 'vital_signs', 'notifications', 'activity_logs'];

        foreach ($emptyFiles as $file) {
            if (!file_exists($this->dataPath . $file . '.json')) {
                $this->save($file, []);
            }
        }

        // Initialize beds
        if (!file_exists($this->dataPath . 'beds.json') || empty($this->load('beds'))) {
            $beds = [];
            $wards = ['General Ward', 'Private Room', 'Semi-Private', 'ICU', 'NICU', 'Emergency'];
            $bedId = 1;
            foreach ($wards as $index => $ward) {
                $count = ($ward === 'ICU' || $ward === 'NICU') ? 5 : 10;
                for ($i = 1; $i <= $count; $i++) {
                    $beds[] = [
                        'id' => $bedId,
                        'bed_number' => $ward[0] . '-' . str_pad($i, 3, '0', STR_PAD_LEFT),
                        'ward' => $ward,
                        'status' => 'available',
                        'patient_id' => null,
                        'admission_id' => null
                    ];
                    $bedId++;
                }
            }
            $this->save('beds', $beds);
        }

        // Initialize lab tests master
        if (!file_exists($this->dataPath . 'lab_tests.json') || empty($this->load('lab_tests'))) {
            $labTests = [
                ['id' => 1, 'name' => 'Complete Blood Count (CBC)', 'category' => 'Hematology', 'price' => 25, 'normal_range' => 'Varies', 'status' => 'active'],
                ['id' => 2, 'name' => 'Blood Sugar Fasting', 'category' => 'Biochemistry', 'price' => 15, 'normal_range' => '70-100 mg/dL', 'status' => 'active'],
                ['id' => 3, 'name' => 'Blood Sugar PP', 'category' => 'Biochemistry', 'price' => 15, 'normal_range' => '<140 mg/dL', 'status' => 'active'],
                ['id' => 4, 'name' => 'Lipid Profile', 'category' => 'Biochemistry', 'price' => 45, 'normal_range' => 'Varies', 'status' => 'active'],
                ['id' => 5, 'name' => 'Liver Function Test (LFT)', 'category' => 'Biochemistry', 'price' => 55, 'normal_range' => 'Varies', 'status' => 'active'],
                ['id' => 6, 'name' => 'Kidney Function Test (KFT)', 'category' => 'Biochemistry', 'price' => 50, 'normal_range' => 'Varies', 'status' => 'active'],
                ['id' => 7, 'name' => 'Thyroid Profile (T3, T4, TSH)', 'category' => 'Hormone', 'price' => 60, 'normal_range' => 'Varies', 'status' => 'active'],
                ['id' => 8, 'name' => 'Urine Routine', 'category' => 'Clinical Pathology', 'price' => 20, 'normal_range' => 'Normal', 'status' => 'active'],
                ['id' => 9, 'name' => 'X-Ray Chest', 'category' => 'Radiology', 'price' => 40, 'normal_range' => 'Normal', 'status' => 'active'],
                ['id' => 10, 'name' => 'ECG', 'category' => 'Cardiology', 'price' => 30, 'normal_range' => 'Normal Sinus Rhythm', 'status' => 'active'],
                ['id' => 11, 'name' => 'Ultrasound Abdomen', 'category' => 'Radiology', 'price' => 80, 'normal_range' => 'Normal', 'status' => 'active'],
                ['id' => 12, 'name' => 'CT Scan', 'category' => 'Radiology', 'price' => 200, 'normal_range' => 'Normal', 'status' => 'active'],
                ['id' => 13, 'name' => 'MRI', 'category' => 'Radiology', 'price' => 350, 'normal_range' => 'Normal', 'status' => 'active'],
                ['id' => 14, 'name' => 'HbA1c', 'category' => 'Biochemistry', 'price' => 35, 'normal_range' => '<5.7%', 'status' => 'active'],
                ['id' => 15, 'name' => 'Vitamin D', 'category' => 'Biochemistry', 'price' => 45, 'normal_range' => '30-100 ng/mL', 'status' => 'active']
            ];
            $this->save('lab_tests', $labTests);
        }

        // Initialize medicines
        if (!file_exists($this->dataPath . 'medicines.json') || empty($this->load('medicines'))) {
            $medicines = [
                ['id' => 1, 'name' => 'Paracetamol 500mg', 'category' => 'Analgesic', 'manufacturer' => 'Generic', 'unit' => 'Tablet', 'price' => 0.50, 'stock' => 1000, 'reorder_level' => 100, 'status' => 'active'],
                ['id' => 2, 'name' => 'Amoxicillin 500mg', 'category' => 'Antibiotic', 'manufacturer' => 'Generic', 'unit' => 'Capsule', 'price' => 1.50, 'stock' => 500, 'reorder_level' => 50, 'status' => 'active'],
                ['id' => 3, 'name' => 'Omeprazole 20mg', 'category' => 'Antacid', 'manufacturer' => 'Generic', 'unit' => 'Capsule', 'price' => 0.80, 'stock' => 800, 'reorder_level' => 100, 'status' => 'active'],
                ['id' => 4, 'name' => 'Metformin 500mg', 'category' => 'Antidiabetic', 'manufacturer' => 'Generic', 'unit' => 'Tablet', 'price' => 0.30, 'stock' => 1000, 'reorder_level' => 100, 'status' => 'active'],
                ['id' => 5, 'name' => 'Atorvastatin 10mg', 'category' => 'Lipid Lowering', 'manufacturer' => 'Generic', 'unit' => 'Tablet', 'price' => 1.00, 'stock' => 600, 'reorder_level' => 60, 'status' => 'active'],
                ['id' => 6, 'name' => 'Amlodipine 5mg', 'category' => 'Antihypertensive', 'manufacturer' => 'Generic', 'unit' => 'Tablet', 'price' => 0.60, 'stock' => 700, 'reorder_level' => 70, 'status' => 'active'],
                ['id' => 7, 'name' => 'Cetirizine 10mg', 'category' => 'Antihistamine', 'manufacturer' => 'Generic', 'unit' => 'Tablet', 'price' => 0.40, 'stock' => 800, 'reorder_level' => 80, 'status' => 'active'],
                ['id' => 8, 'name' => 'Ibuprofen 400mg', 'category' => 'NSAID', 'manufacturer' => 'Generic', 'unit' => 'Tablet', 'price' => 0.45, 'stock' => 900, 'reorder_level' => 90, 'status' => 'active'],
                ['id' => 9, 'name' => 'Azithromycin 500mg', 'category' => 'Antibiotic', 'manufacturer' => 'Generic', 'unit' => 'Tablet', 'price' => 2.50, 'stock' => 300, 'reorder_level' => 30, 'status' => 'active'],
                ['id' => 10, 'name' => 'Pantoprazole 40mg', 'category' => 'Antacid', 'manufacturer' => 'Generic', 'unit' => 'Tablet', 'price' => 1.20, 'stock' => 600, 'reorder_level' => 60, 'status' => 'active']
            ];
            $this->save('medicines', $medicines);
        }
    }

    /**
     * Load data from JSON file
     */
    public function load($collection) {
        $file = $this->dataPath . $collection . '.json';
        if (!file_exists($file)) {
            return [];
        }
        $content = file_get_contents($file);
        return json_decode($content, true) ?? [];
    }

    /**
     * Save data to JSON file
     */
    public function save($collection, $data) {
        $file = $this->dataPath . $collection . '.json';
        return file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT));
    }

    /**
     * Get all records from a collection
     */
    public function getAll($collection, $conditions = []) {
        $data = $this->load($collection);

        if (empty($conditions)) {
            return $data;
        }

        return array_filter($data, function($item) use ($conditions) {
            foreach ($conditions as $key => $value) {
                if (!isset($item[$key]) || $item[$key] != $value) {
                    return false;
                }
            }
            return true;
        });
    }

    /**
     * Get single record by ID
     */
    public function getById($collection, $id) {
        $data = $this->load($collection);
        foreach ($data as $item) {
            if (isset($item['id']) && $item['id'] == $id) {
                return $item;
            }
        }
        return null;
    }

    /**
     * Get single record by field value
     */
    public function getByField($collection, $field, $value) {
        $data = $this->load($collection);
        foreach ($data as $item) {
            if (isset($item[$field]) && $item[$field] == $value) {
                return $item;
            }
        }
        return null;
    }

    /**
     * Insert new record
     */
    public function insert($collection, $record) {
        $data = $this->load($collection);

        // Generate new ID
        $maxId = 0;
        foreach ($data as $item) {
            if (isset($item['id']) && $item['id'] > $maxId) {
                $maxId = $item['id'];
            }
        }
        $record['id'] = $maxId + 1;
        $record['created_at'] = date('Y-m-d H:i:s');

        $data[] = $record;
        $this->save($collection, $data);

        return $record['id'];
    }

    /**
     * Update record by ID
     */
    public function update($collection, $id, $updates) {
        $data = $this->load($collection);
        $updated = false;

        foreach ($data as &$item) {
            if (isset($item['id']) && $item['id'] == $id) {
                foreach ($updates as $key => $value) {
                    $item[$key] = $value;
                }
                $item['updated_at'] = date('Y-m-d H:i:s');
                $updated = true;
                break;
            }
        }

        if ($updated) {
            $this->save($collection, $data);
        }

        return $updated;
    }

    /**
     * Delete record by ID
     */
    public function delete($collection, $id) {
        $data = $this->load($collection);
        $initialCount = count($data);

        $data = array_filter($data, function($item) use ($id) {
            return !isset($item['id']) || $item['id'] != $id;
        });

        if (count($data) < $initialCount) {
            $this->save($collection, array_values($data));
            return true;
        }

        return false;
    }

    /**
     * Count records
     */
    public function count($collection, $conditions = []) {
        return count($this->getAll($collection, $conditions));
    }

    /**
     * Search records
     */
    public function search($collection, $searchFields, $searchTerm) {
        $data = $this->load($collection);
        $searchTerm = strtolower($searchTerm);

        return array_filter($data, function($item) use ($searchFields, $searchTerm) {
            foreach ($searchFields as $field) {
                if (isset($item[$field]) && strpos(strtolower($item[$field]), $searchTerm) !== false) {
                    return true;
                }
            }
            return false;
        });
    }

    /**
     * Generate unique ID (for patient ID, invoice number, etc.)
     */
    public function generateUniqueId($prefix, $collection, $field = 'id') {
        $data = $this->load($collection);
        $maxNum = 0;

        foreach ($data as $item) {
            if (isset($item[$field])) {
                $num = (int) preg_replace('/[^0-9]/', '', $item[$field]);
                if ($num > $maxNum) {
                    $maxNum = $num;
                }
            }
        }

        return $prefix . str_pad($maxNum + 1, 6, '0', STR_PAD_LEFT);
    }
}
