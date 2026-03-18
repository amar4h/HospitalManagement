<?php
/**
 * JSON File Storage Class
 */
class Storage {
    private $dataPath;

    public function __construct() {
        $this->dataPath = STORAGE_PATH;
        $this->initializeData();
    }

    /**
     * Initialize default data if not exists
     */
    private function initializeData() {
        // Initialize users
        if (!file_exists($this->dataPath . 'users.json')) {
            $this->saveFile('users', [
                [
                    'id' => 1,
                    'username' => 'admin',
                    'password' => password_hash('admin123', PASSWORD_DEFAULT),
                    'name' => 'Administrator',
                    'email' => 'admin@hospital.com',
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
                    'role' => 'doctor',
                    'status' => 'active',
                    'created_at' => date('Y-m-d H:i:s')
                ],
                [
                    'id' => 3,
                    'username' => 'receptionist',
                    'password' => password_hash('reception123', PASSWORD_DEFAULT),
                    'name' => 'Reception Staff',
                    'email' => 'reception@hospital.com',
                    'role' => 'receptionist',
                    'status' => 'active',
                    'created_at' => date('Y-m-d H:i:s')
                ]
            ]);
        }

        // Initialize other empty collections
        $collections = ['patients', 'doctors', 'appointments', 'opd_visits', 'ipd_admissions',
                       'surgeries', 'medicines', 'dispenses', 'lab_tests', 'lab_orders',
                       'invoices', 'payments', 'departments', 'settings', 'beds', 'activity_logs'];

        foreach ($collections as $collection) {
            if (!file_exists($this->dataPath . $collection . '.json')) {
                $defaultData = [];

                // Add default data for specific collections
                if ($collection === 'departments') {
                    $defaultData = [
                        ['id' => 1, 'name' => 'General Medicine', 'status' => 'active'],
                        ['id' => 2, 'name' => 'Cardiology', 'status' => 'active'],
                        ['id' => 3, 'name' => 'Orthopedics', 'status' => 'active'],
                        ['id' => 4, 'name' => 'Pediatrics', 'status' => 'active'],
                        ['id' => 5, 'name' => 'Gynecology', 'status' => 'active']
                    ];
                } elseif ($collection === 'lab_tests') {
                    $defaultData = [
                        ['id' => 1, 'name' => 'Complete Blood Count', 'price' => 500, 'category' => 'Hematology'],
                        ['id' => 2, 'name' => 'Blood Sugar (Fasting)', 'price' => 150, 'category' => 'Biochemistry'],
                        ['id' => 3, 'name' => 'Lipid Profile', 'price' => 800, 'category' => 'Biochemistry'],
                        ['id' => 4, 'name' => 'Liver Function Test', 'price' => 600, 'category' => 'Biochemistry'],
                        ['id' => 5, 'name' => 'Kidney Function Test', 'price' => 550, 'category' => 'Biochemistry'],
                        ['id' => 6, 'name' => 'Urine Routine', 'price' => 200, 'category' => 'Pathology'],
                        ['id' => 7, 'name' => 'X-Ray Chest', 'price' => 400, 'category' => 'Radiology'],
                        ['id' => 8, 'name' => 'ECG', 'price' => 300, 'category' => 'Cardiology']
                    ];
                } elseif ($collection === 'beds') {
                    $defaultData = [];
                    $wards = ['General', 'Semi-Private', 'Private', 'ICU'];
                    $id = 1;
                    foreach ($wards as $ward) {
                        for ($i = 1; $i <= 5; $i++) {
                            $defaultData[] = [
                                'id' => $id++,
                                'bed_number' => substr($ward, 0, 1) . '-' . str_pad($i, 2, '0', STR_PAD_LEFT),
                                'ward' => $ward,
                                'status' => 'available'
                            ];
                        }
                    }
                } elseif ($collection === 'settings') {
                    $defaultData = [
                        'hospital_name' => 'City Hospital',
                        'address' => '123 Medical Center Road',
                        'phone' => '+91 1234567890',
                        'email' => 'info@cityhospital.com'
                    ];
                } elseif ($collection === 'doctors') {
                    $defaultData = [
                        ['id' => 1, 'name' => 'Dr. Rajesh Kumar', 'specialization' => 'General Medicine', 'qualification' => 'MBBS, MD', 'phone' => '9876543210', 'email' => 'rajesh@hospital.com', 'consultation_fee' => 500, 'status' => 'active', 'created_at' => date('Y-m-d H:i:s')],
                        ['id' => 2, 'name' => 'Dr. Priya Sharma', 'specialization' => 'Cardiology', 'qualification' => 'MBBS, DM Cardiology', 'phone' => '9876543211', 'email' => 'priya@hospital.com', 'consultation_fee' => 800, 'status' => 'active', 'created_at' => date('Y-m-d H:i:s')],
                        ['id' => 3, 'name' => 'Dr. Amit Patel', 'specialization' => 'Orthopedics', 'qualification' => 'MBBS, MS Ortho', 'phone' => '9876543212', 'email' => 'amit@hospital.com', 'consultation_fee' => 700, 'status' => 'active', 'created_at' => date('Y-m-d H:i:s')],
                        ['id' => 4, 'name' => 'Dr. Sneha Reddy', 'specialization' => 'Pediatrics', 'qualification' => 'MBBS, DCH', 'phone' => '9876543213', 'email' => 'sneha@hospital.com', 'consultation_fee' => 600, 'status' => 'active', 'created_at' => date('Y-m-d H:i:s')],
                        ['id' => 5, 'name' => 'Dr. Vikram Singh', 'specialization' => 'Surgery', 'qualification' => 'MBBS, MS Surgery', 'phone' => '9876543214', 'email' => 'vikram@hospital.com', 'consultation_fee' => 1000, 'status' => 'active', 'created_at' => date('Y-m-d H:i:s')]
                    ];
                } elseif ($collection === 'patients') {
                    $defaultData = [
                        ['id' => 1, 'patient_id' => 'PT2024001', 'name' => 'Rahul Verma', 'dob' => '1985-03-15', 'gender' => 'Male', 'phone' => '9988776655', 'email' => 'rahul@email.com', 'blood_group' => 'O+', 'address' => '45 Gandhi Nagar, Mumbai', 'emergency_contact_name' => 'Meena Verma', 'emergency_contact_phone' => '9988776656', 'allergies' => 'Penicillin', 'medical_history' => 'Diabetes Type 2', 'created_at' => date('Y-m-d H:i:s')],
                        ['id' => 2, 'patient_id' => 'PT2024002', 'name' => 'Anita Desai', 'dob' => '1990-07-22', 'gender' => 'Female', 'phone' => '9988776644', 'email' => 'anita@email.com', 'blood_group' => 'A+', 'address' => '78 MG Road, Delhi', 'emergency_contact_name' => 'Suresh Desai', 'emergency_contact_phone' => '9988776645', 'allergies' => 'None', 'medical_history' => 'None', 'created_at' => date('Y-m-d H:i:s')],
                        ['id' => 3, 'patient_id' => 'PT2024003', 'name' => 'Sunil Mehta', 'dob' => '1978-11-30', 'gender' => 'Male', 'phone' => '9988776633', 'email' => 'sunil@email.com', 'blood_group' => 'B+', 'address' => '23 Park Street, Kolkata', 'emergency_contact_name' => 'Kavita Mehta', 'emergency_contact_phone' => '9988776634', 'allergies' => 'Sulfa drugs', 'medical_history' => 'Hypertension, High Cholesterol', 'created_at' => date('Y-m-d H:i:s')],
                        ['id' => 4, 'patient_id' => 'PT2024004', 'name' => 'Pooja Gupta', 'dob' => '1995-05-10', 'gender' => 'Female', 'phone' => '9988776622', 'email' => 'pooja@email.com', 'blood_group' => 'AB+', 'address' => '56 Jubilee Hills, Hyderabad', 'emergency_contact_name' => 'Ramesh Gupta', 'emergency_contact_phone' => '9988776623', 'allergies' => 'None', 'medical_history' => 'Asthma', 'created_at' => date('Y-m-d H:i:s')],
                        ['id' => 5, 'patient_id' => 'PT2024005', 'name' => 'Arun Nair', 'dob' => '1982-09-18', 'gender' => 'Male', 'phone' => '9988776611', 'email' => 'arun@email.com', 'blood_group' => 'O-', 'address' => '89 Marine Drive, Chennai', 'emergency_contact_name' => 'Lakshmi Nair', 'emergency_contact_phone' => '9988776612', 'allergies' => 'Aspirin', 'medical_history' => 'Previous knee surgery', 'created_at' => date('Y-m-d H:i:s')]
                    ];
                } elseif ($collection === 'appointments') {
                    $today = date('Y-m-d');
                    $tomorrow = date('Y-m-d', strtotime('+1 day'));
                    $defaultData = [
                        ['id' => 1, 'patient_id' => 1, 'doctor_id' => 1, 'date' => $today, 'time' => '09:00', 'type' => 'General', 'status' => 'scheduled', 'notes' => 'Regular checkup', 'created_at' => date('Y-m-d H:i:s')],
                        ['id' => 2, 'patient_id' => 2, 'doctor_id' => 2, 'date' => $today, 'time' => '10:30', 'type' => 'Follow-up', 'status' => 'scheduled', 'notes' => 'Heart checkup', 'created_at' => date('Y-m-d H:i:s')],
                        ['id' => 3, 'patient_id' => 3, 'doctor_id' => 3, 'date' => $today, 'time' => '11:00', 'type' => 'Specialist', 'status' => 'scheduled', 'notes' => 'Knee pain consultation', 'created_at' => date('Y-m-d H:i:s')],
                        ['id' => 4, 'patient_id' => 4, 'doctor_id' => 4, 'date' => $tomorrow, 'time' => '09:30', 'type' => 'General', 'status' => 'scheduled', 'notes' => '', 'created_at' => date('Y-m-d H:i:s')],
                        ['id' => 5, 'patient_id' => 5, 'doctor_id' => 5, 'date' => $tomorrow, 'time' => '14:00', 'type' => 'Specialist', 'status' => 'scheduled', 'notes' => 'Pre-surgery consultation', 'created_at' => date('Y-m-d H:i:s')]
                    ];
                } elseif ($collection === 'medicines') {
                    $defaultData = [
                        ['id' => 1, 'name' => 'Paracetamol 500mg', 'generic_name' => 'Acetaminophen', 'category' => 'Tablet', 'unit' => 'tablets', 'price' => 2, 'stock' => 500, 'reorder_level' => 100, 'expiry_date' => '2025-12-31', 'created_at' => date('Y-m-d H:i:s')],
                        ['id' => 2, 'name' => 'Amoxicillin 250mg', 'generic_name' => 'Amoxicillin', 'category' => 'Capsule', 'unit' => 'capsules', 'price' => 8, 'stock' => 200, 'reorder_level' => 50, 'expiry_date' => '2025-06-30', 'created_at' => date('Y-m-d H:i:s')],
                        ['id' => 3, 'name' => 'Omeprazole 20mg', 'generic_name' => 'Omeprazole', 'category' => 'Capsule', 'unit' => 'capsules', 'price' => 5, 'stock' => 300, 'reorder_level' => 75, 'expiry_date' => '2025-09-30', 'created_at' => date('Y-m-d H:i:s')],
                        ['id' => 4, 'name' => 'Metformin 500mg', 'generic_name' => 'Metformin', 'category' => 'Tablet', 'unit' => 'tablets', 'price' => 3, 'stock' => 400, 'reorder_level' => 100, 'expiry_date' => '2025-08-31', 'created_at' => date('Y-m-d H:i:s')],
                        ['id' => 5, 'name' => 'Amlodipine 5mg', 'generic_name' => 'Amlodipine', 'category' => 'Tablet', 'unit' => 'tablets', 'price' => 4, 'stock' => 250, 'reorder_level' => 60, 'expiry_date' => '2025-10-31', 'created_at' => date('Y-m-d H:i:s')],
                        ['id' => 6, 'name' => 'Cetirizine 10mg', 'generic_name' => 'Cetirizine', 'category' => 'Tablet', 'unit' => 'tablets', 'price' => 2, 'stock' => 350, 'reorder_level' => 80, 'expiry_date' => '2025-11-30', 'created_at' => date('Y-m-d H:i:s')],
                        ['id' => 7, 'name' => 'Cough Syrup', 'generic_name' => 'Dextromethorphan', 'category' => 'Syrup', 'unit' => 'bottles', 'price' => 65, 'stock' => 50, 'reorder_level' => 15, 'expiry_date' => '2025-07-31', 'created_at' => date('Y-m-d H:i:s')],
                        ['id' => 8, 'name' => 'Insulin Injection', 'generic_name' => 'Insulin', 'category' => 'Injection', 'unit' => 'vials', 'price' => 350, 'stock' => 30, 'reorder_level' => 10, 'expiry_date' => '2025-05-31', 'created_at' => date('Y-m-d H:i:s')]
                    ];
                } elseif ($collection === 'opd_visits') {
                    $defaultData = [
                        ['id' => 1, 'patient_id' => 1, 'doctor_id' => 1, 'date' => date('Y-m-d', strtotime('-2 days')), 'consultation_fee' => 500, 'bp' => '130/85', 'temperature' => '98.6', 'pulse' => '78', 'weight' => '72', 'complaints' => 'Fever, body ache', 'diagnosis' => 'Viral fever', 'prescription' => 'Paracetamol 500mg - 1 tablet 3 times daily\nRest for 3 days', 'notes' => 'Follow up after 3 days', 'created_at' => date('Y-m-d H:i:s')],
                        ['id' => 2, 'patient_id' => 3, 'doctor_id' => 2, 'date' => date('Y-m-d', strtotime('-1 day')), 'consultation_fee' => 800, 'bp' => '145/95', 'temperature' => '98.4', 'pulse' => '82', 'weight' => '85', 'complaints' => 'Chest discomfort', 'diagnosis' => 'Mild hypertension', 'prescription' => 'Amlodipine 5mg - 1 tablet daily\nLow salt diet', 'notes' => 'ECG done, normal', 'created_at' => date('Y-m-d H:i:s')]
                    ];
                } elseif ($collection === 'invoices') {
                    $defaultData = [
                        ['id' => 1, 'invoice_number' => 'INV' . date('Ymd') . '001', 'patient_id' => 1, 'date' => date('Y-m-d', strtotime('-2 days')), 'items' => [['description' => 'Consultation - Dr. Rajesh Kumar', 'quantity' => 1, 'price' => 500], ['description' => 'Paracetamol 500mg x 10', 'quantity' => 10, 'price' => 2]], 'discount' => 0, 'total' => 520, 'paid' => 520, 'status' => 'paid', 'created_at' => date('Y-m-d H:i:s')],
                        ['id' => 2, 'invoice_number' => 'INV' . date('Ymd') . '002', 'patient_id' => 3, 'date' => date('Y-m-d', strtotime('-1 day')), 'items' => [['description' => 'Consultation - Dr. Priya Sharma', 'quantity' => 1, 'price' => 800], ['description' => 'ECG', 'quantity' => 1, 'price' => 300], ['description' => 'Amlodipine 5mg x 30', 'quantity' => 30, 'price' => 4]], 'discount' => 50, 'total' => 1170, 'paid' => 500, 'status' => 'partial', 'created_at' => date('Y-m-d H:i:s')],
                        ['id' => 3, 'invoice_number' => 'INV' . date('Ymd') . '003', 'patient_id' => 2, 'date' => date('Y-m-d'), 'items' => [['description' => 'Consultation - Dr. Sneha Reddy', 'quantity' => 1, 'price' => 600], ['description' => 'Blood Test - CBC', 'quantity' => 1, 'price' => 500]], 'discount' => 0, 'total' => 1100, 'paid' => 0, 'status' => 'pending', 'created_at' => date('Y-m-d H:i:s')]
                    ];
                } elseif ($collection === 'surgeries') {
                    $tomorrow = date('Y-m-d', strtotime('+1 day'));
                    $nextWeek = date('Y-m-d', strtotime('+7 days'));
                    $defaultData = [
                        ['id' => 1, 'patient_id' => 5, 'doctor_id' => 5, 'surgery_name' => 'Knee Arthroscopy', 'date' => $tomorrow, 'time' => '10:00', 'operation_theatre' => 'OT-1', 'anesthesia_type' => 'Spinal', 'pre_op_notes' => 'Patient fasting from midnight. All pre-op tests done.', 'post_op_notes' => '', 'status' => 'scheduled', 'created_at' => date('Y-m-d H:i:s')],
                        ['id' => 2, 'patient_id' => 3, 'doctor_id' => 5, 'surgery_name' => 'Appendectomy', 'date' => $nextWeek, 'time' => '09:00', 'operation_theatre' => 'OT-2', 'anesthesia_type' => 'General', 'pre_op_notes' => 'Laparoscopic procedure planned', 'post_op_notes' => '', 'status' => 'scheduled', 'created_at' => date('Y-m-d H:i:s')],
                        ['id' => 3, 'patient_id' => 1, 'doctor_id' => 3, 'surgery_name' => 'Carpal Tunnel Release', 'date' => date('Y-m-d', strtotime('-5 days')), 'time' => '14:00', 'operation_theatre' => 'OT-3', 'anesthesia_type' => 'Local', 'pre_op_notes' => 'Minor procedure', 'post_op_notes' => 'Surgery successful. Patient discharged same day.', 'status' => 'completed', 'created_at' => date('Y-m-d H:i:s')]
                    ];
                } elseif ($collection === 'ipd_admissions') {
                    $defaultData = [
                        ['id' => 1, 'patient_id' => 5, 'doctor_id' => 5, 'admission_date' => date('Y-m-d'), 'ward' => 'Private', 'bed_number' => 'P-01', 'admission_reason' => 'Pre-surgery admission for knee arthroscopy', 'diagnosis' => 'Meniscus tear - Right knee', 'status' => 'admitted', 'created_at' => date('Y-m-d H:i:s')],
                        ['id' => 2, 'patient_id' => 3, 'doctor_id' => 2, 'admission_date' => date('Y-m-d', strtotime('-3 days')), 'ward' => 'Semi-Private', 'bed_number' => 'S-02', 'admission_reason' => 'Chest pain observation', 'diagnosis' => 'Angina - Under observation', 'status' => 'admitted', 'created_at' => date('Y-m-d H:i:s')],
                        ['id' => 3, 'patient_id' => 4, 'doctor_id' => 4, 'admission_date' => date('Y-m-d', strtotime('-7 days')), 'discharge_date' => date('Y-m-d', strtotime('-4 days')), 'ward' => 'General', 'bed_number' => 'G-03', 'admission_reason' => 'Severe asthma attack', 'diagnosis' => 'Acute asthma exacerbation', 'discharge_notes' => 'Recovered well. Continue nebulization at home.', 'status' => 'discharged', 'created_at' => date('Y-m-d H:i:s')]
                    ];
                } elseif ($collection === 'lab_orders') {
                    $defaultData = [
                        ['id' => 1, 'patient_id' => 1, 'doctor_id' => 1, 'test_id' => 1, 'order_date' => date('Y-m-d'), 'priority' => 'normal', 'status' => 'pending', 'notes' => 'Routine checkup', 'created_at' => date('Y-m-d H:i:s')],
                        ['id' => 2, 'patient_id' => 3, 'doctor_id' => 2, 'test_id' => 3, 'order_date' => date('Y-m-d'), 'priority' => 'urgent', 'status' => 'pending', 'notes' => 'Check cholesterol levels', 'created_at' => date('Y-m-d H:i:s')],
                        ['id' => 3, 'patient_id' => 2, 'doctor_id' => 1, 'test_id' => 2, 'order_date' => date('Y-m-d', strtotime('-1 day')), 'priority' => 'normal', 'result' => '92 mg/dL', 'normal_range' => '70-100 mg/dL', 'remarks' => 'Normal fasting blood sugar', 'result_date' => date('Y-m-d'), 'status' => 'completed', 'notes' => '', 'created_at' => date('Y-m-d H:i:s')],
                        ['id' => 4, 'patient_id' => 5, 'doctor_id' => 5, 'test_id' => 1, 'order_date' => date('Y-m-d', strtotime('-2 days')), 'priority' => 'normal', 'result' => 'Hb: 14.2 g/dL, WBC: 7500/cumm, Platelets: 250000/cumm', 'normal_range' => 'Hb: 13-17, WBC: 4000-11000, Plt: 150000-400000', 'remarks' => 'All values within normal limits. Fit for surgery.', 'result_date' => date('Y-m-d', strtotime('-1 day')), 'status' => 'completed', 'notes' => 'Pre-operative workup', 'created_at' => date('Y-m-d H:i:s')]
                    ];
                } elseif ($collection === 'dispenses') {
                    $defaultData = [
                        ['id' => 1, 'patient_id' => 1, 'medicine_id' => 1, 'quantity' => 10, 'unit_price' => 2, 'total' => 20, 'notes' => 'For fever', 'dispensed_by' => 1, 'dispense_date' => date('Y-m-d H:i:s', strtotime('-2 days')), 'created_at' => date('Y-m-d H:i:s')],
                        ['id' => 2, 'patient_id' => 3, 'medicine_id' => 5, 'quantity' => 30, 'unit_price' => 4, 'total' => 120, 'notes' => 'For blood pressure', 'dispensed_by' => 1, 'dispense_date' => date('Y-m-d H:i:s', strtotime('-1 day')), 'created_at' => date('Y-m-d H:i:s')],
                        ['id' => 3, 'patient_id' => 4, 'medicine_id' => 6, 'quantity' => 10, 'unit_price' => 2, 'total' => 20, 'notes' => 'For allergies', 'dispensed_by' => 1, 'dispense_date' => date('Y-m-d H:i:s'), 'created_at' => date('Y-m-d H:i:s')]
                    ];
                } elseif ($collection === 'payments') {
                    $defaultData = [
                        ['id' => 1, 'invoice_id' => 1, 'amount' => 520, 'payment_method' => 'Cash', 'reference' => '', 'date' => date('Y-m-d', strtotime('-2 days')), 'received_by' => 1, 'created_at' => date('Y-m-d H:i:s')],
                        ['id' => 2, 'invoice_id' => 2, 'amount' => 500, 'payment_method' => 'UPI', 'reference' => 'TXN123456789', 'date' => date('Y-m-d', strtotime('-1 day')), 'received_by' => 1, 'created_at' => date('Y-m-d H:i:s')]
                    ];
                } elseif ($collection === 'activity_logs') {
                    $defaultData = [
                        ['id' => 1, 'action' => 'login', 'description' => 'User admin logged in', 'user_id' => 1, 'ip_address' => '127.0.0.1', 'created_at' => date('Y-m-d H:i:s', strtotime('-2 hours'))],
                        ['id' => 2, 'action' => 'patient_create', 'description' => 'Created patient: Rahul Verma', 'user_id' => 1, 'ip_address' => '127.0.0.1', 'created_at' => date('Y-m-d H:i:s', strtotime('-1 hour'))],
                        ['id' => 3, 'action' => 'appointment_create', 'description' => 'Created appointment for patient ID: 1', 'user_id' => 1, 'ip_address' => '127.0.0.1', 'created_at' => date('Y-m-d H:i:s', strtotime('-45 minutes'))],
                        ['id' => 4, 'action' => 'opd_create', 'description' => 'Created OPD visit for patient ID: 1', 'user_id' => 2, 'ip_address' => '127.0.0.1', 'created_at' => date('Y-m-d H:i:s', strtotime('-30 minutes'))],
                        ['id' => 5, 'action' => 'invoice_create', 'description' => 'Created invoice: INV' . date('Ymd') . '001', 'user_id' => 1, 'ip_address' => '127.0.0.1', 'created_at' => date('Y-m-d H:i:s', strtotime('-15 minutes'))]
                    ];
                }

                $this->saveFile($collection, $defaultData);
            }
        }
    }

    /**
     * Get all records from a collection
     */
    public function getAll($collection, $filters = []) {
        $data = $this->loadFile($collection);

        if (!empty($filters)) {
            $data = array_filter($data, function($item) use ($filters) {
                foreach ($filters as $key => $value) {
                    if (!isset($item[$key]) || $item[$key] !== $value) {
                        return false;
                    }
                }
                return true;
            });
            $data = array_values($data);
        }

        return $data;
    }

    /**
     * Get record by ID
     */
    public function getById($collection, $id) {
        $data = $this->loadFile($collection);
        foreach ($data as $item) {
            if (isset($item['id']) && $item['id'] == $id) {
                return $item;
            }
        }
        return null;
    }

    /**
     * Get record by field value
     */
    public function getBy($collection, $field, $value) {
        $data = $this->loadFile($collection);
        foreach ($data as $item) {
            if (isset($item[$field]) && $item[$field] === $value) {
                return $item;
            }
        }
        return null;
    }

    /**
     * Insert new record
     */
    public function insert($collection, $record) {
        $data = $this->loadFile($collection);

        // Generate ID if not provided
        if (!isset($record['id'])) {
            $maxId = 0;
            foreach ($data as $item) {
                if (isset($item['id']) && $item['id'] > $maxId) {
                    $maxId = $item['id'];
                }
            }
            $record['id'] = $maxId + 1;
        }

        // Add timestamps
        if (!isset($record['created_at'])) {
            $record['created_at'] = date('Y-m-d H:i:s');
        }

        $data[] = $record;
        $this->saveFile($collection, $data);

        return $record['id'];
    }

    /**
     * Update record
     */
    public function update($collection, $id, $updates) {
        $data = $this->loadFile($collection);
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
            $this->saveFile($collection, $data);
        }

        return $updated;
    }

    /**
     * Delete record
     */
    public function delete($collection, $id) {
        $data = $this->loadFile($collection);
        $originalCount = count($data);

        $data = array_filter($data, function($item) use ($id) {
            return !isset($item['id']) || $item['id'] != $id;
        });

        if (count($data) < $originalCount) {
            $this->saveFile($collection, array_values($data));
            return true;
        }

        return false;
    }

    /**
     * Count records
     */
    public function count($collection, $filters = []) {
        return count($this->getAll($collection, $filters));
    }

    /**
     * Load JSON file
     */
    private function loadFile($collection) {
        $file = $this->dataPath . $collection . '.json';
        if (!file_exists($file)) {
            return [];
        }
        $content = file_get_contents($file);
        return json_decode($content, true) ?? [];
    }

    /**
     * Save JSON file
     */
    private function saveFile($collection, $data) {
        $file = $this->dataPath . $collection . '.json';
        file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT));
    }
}
