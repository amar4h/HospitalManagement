<?php
/**
 * Add New Patient
 */
requireAuth();
requireRole(['admin', 'receptionist']);

$storage = getStorage();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'patient_id' => $storage->generateUniqueId('PAT', 'patients', 'patient_id'),
        'name' => sanitize($_POST['name'] ?? ''),
        'dob' => sanitize($_POST['dob'] ?? ''),
        'gender' => sanitize($_POST['gender'] ?? ''),
        'blood_group' => sanitize($_POST['blood_group'] ?? ''),
        'marital_status' => sanitize($_POST['marital_status'] ?? ''),
        'phone' => sanitize($_POST['phone'] ?? ''),
        'email' => sanitize($_POST['email'] ?? ''),
        'address' => sanitize($_POST['address'] ?? ''),
        'city' => sanitize($_POST['city'] ?? ''),
        'state' => sanitize($_POST['state'] ?? ''),
        'zip_code' => sanitize($_POST['zip_code'] ?? ''),
        'emergency_contact_name' => sanitize($_POST['emergency_contact_name'] ?? ''),
        'emergency_contact_phone' => sanitize($_POST['emergency_contact_phone'] ?? ''),
        'emergency_contact_relation' => sanitize($_POST['emergency_contact_relation'] ?? ''),
        'allergies' => sanitize($_POST['allergies'] ?? ''),
        'chronic_conditions' => sanitize($_POST['chronic_conditions'] ?? ''),
        'current_medications' => sanitize($_POST['current_medications'] ?? ''),
        'insurance_provider' => sanitize($_POST['insurance_provider'] ?? ''),
        'insurance_id' => sanitize($_POST['insurance_id'] ?? ''),
        'notes' => sanitize($_POST['notes'] ?? ''),
        'status' => 'active'
    ];

    if (empty($data['name']) || empty($data['phone']) || empty($data['gender'])) {
        setFlashMessage('error', 'Please fill in all required fields');
    } else {
        $id = $storage->insert('patients', $data);
        logActivity('patient_add', 'Added new patient: ' . $data['name'] . ' (' . $data['patient_id'] . ')');
        setFlashMessage('success', 'Patient registered successfully. Patient ID: ' . $data['patient_id']);
        redirect('index.php?page=patient-view&id=' . $id);
    }
}
?>

<div class="page-header">
    <h1><i class="bi bi-person-plus me-2"></i>Add New Patient</h1>
    <div>
        <a href="index.php?page=patients" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-2"></i>Back to Patients
        </a>
    </div>
</div>

<form method="POST" action="" class="needs-validation" novalidate>
    <?= csrfField() ?>

    <div class="row">
        <!-- Personal Information -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <i class="bi bi-person me-2"></i>Personal Information
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Full Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Date of Birth <span class="text-danger">*</span></label>
                            <input type="date" name="dob" id="dob" class="form-control" max="<?= date('Y-m-d') ?>" required>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Age</label>
                            <input type="text" id="age" class="form-control" readonly>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Gender <span class="text-danger">*</span></label>
                            <select name="gender" class="form-select" required>
                                <option value="">Select Gender</option>
                                <?php foreach (getGenders() as $gender): ?>
                                <option value="<?= $gender ?>"><?= $gender ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Blood Group</label>
                            <select name="blood_group" class="form-select">
                                <option value="">Select Blood Group</option>
                                <?php foreach (getBloodGroups() as $bg): ?>
                                <option value="<?= $bg ?>"><?= $bg ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Marital Status</label>
                            <select name="marital_status" class="form-select">
                                <option value="">Select Status</option>
                                <?php foreach (getMaritalStatuses() as $status): ?>
                                <option value="<?= $status ?>"><?= $status ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Phone <span class="text-danger">*</span></label>
                            <input type="tel" name="phone" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Address</label>
                        <textarea name="address" class="form-control" rows="2"></textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">City</label>
                            <input type="text" name="city" class="form-control">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">State</label>
                            <input type="text" name="state" class="form-control">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">ZIP Code</label>
                            <input type="text" name="zip_code" class="form-control">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Emergency Contact -->
            <div class="card">
                <div class="card-header">
                    <i class="bi bi-telephone me-2"></i>Emergency Contact
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Contact Name</label>
                            <input type="text" name="emergency_contact_name" class="form-control">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Contact Phone</label>
                            <input type="tel" name="emergency_contact_phone" class="form-control">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Relationship</label>
                            <input type="text" name="emergency_contact_relation" class="form-control" placeholder="e.g., Spouse, Parent">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Medical Information -->
            <div class="card">
                <div class="card-header">
                    <i class="bi bi-heart-pulse me-2"></i>Medical Information
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Known Allergies</label>
                        <textarea name="allergies" class="form-control" rows="2" placeholder="List any known allergies"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Chronic Conditions</label>
                        <textarea name="chronic_conditions" class="form-control" rows="2" placeholder="List any chronic conditions (diabetes, hypertension, etc.)"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Current Medications</label>
                        <textarea name="current_medications" class="form-control" rows="2" placeholder="List current medications"></textarea>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-md-4">
            <!-- Insurance Information -->
            <div class="card">
                <div class="card-header">
                    <i class="bi bi-shield-check me-2"></i>Insurance Information
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Insurance Provider</label>
                        <input type="text" name="insurance_provider" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Insurance ID / Policy Number</label>
                        <input type="text" name="insurance_id" class="form-control">
                    </div>
                </div>
            </div>

            <!-- Additional Notes -->
            <div class="card">
                <div class="card-header">
                    <i class="bi bi-journal-text me-2"></i>Additional Notes
                </div>
                <div class="card-body">
                    <textarea name="notes" class="form-control" rows="4" placeholder="Any additional notes..."></textarea>
                </div>
            </div>

            <!-- Actions -->
            <div class="card">
                <div class="card-body">
                    <button type="submit" class="btn btn-primary w-100 mb-2">
                        <i class="bi bi-check-lg me-2"></i>Register Patient
                    </button>
                    <a href="index.php?page=patients" class="btn btn-outline-secondary w-100">
                        <i class="bi bi-x me-2"></i>Cancel
                    </a>
                </div>
            </div>
        </div>
    </div>
</form>
