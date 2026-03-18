<?php
/**
 * New OPD Visit
 */
requireAuth();
requireRole(['admin', 'doctor', 'receptionist']);

$storage = getStorage();
$patients = $storage->getAll('patients');
$doctors = $storage->getAll('doctors', ['status' => 'active']);
$medicines = $storage->getAll('medicines', ['status' => 'active']);

// Pre-fill from appointment or patient
$selectedPatient = $_GET['patient_id'] ?? '';
$selectedDoctor = $_GET['doctor_id'] ?? '';
$appointmentId = $_GET['appointment_id'] ?? '';

if ($appointmentId) {
    $appointment = $storage->getById('appointments', $appointmentId);
    if ($appointment) {
        $selectedPatient = $appointment['patient_id'];
        $selectedDoctor = $appointment['doctor_id'];
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'patient_id' => (int)($_POST['patient_id'] ?? 0),
        'doctor_id' => (int)($_POST['doctor_id'] ?? 0),
        'appointment_id' => (int)($_POST['appointment_id'] ?? 0),
        'date' => date('Y-m-d'),
        'symptoms' => sanitize($_POST['symptoms'] ?? ''),
        'diagnosis' => sanitize($_POST['diagnosis'] ?? ''),
        'treatment' => sanitize($_POST['treatment'] ?? ''),
        'notes' => sanitize($_POST['notes'] ?? ''),
        'bp' => sanitize($_POST['bp'] ?? ''),
        'pulse' => sanitize($_POST['pulse'] ?? ''),
        'temperature' => sanitize($_POST['temperature'] ?? ''),
        'weight' => sanitize($_POST['weight'] ?? ''),
        'height' => sanitize($_POST['height'] ?? ''),
        'spo2' => sanitize($_POST['spo2'] ?? ''),
        'follow_up_date' => sanitize($_POST['follow_up_date'] ?? '')
    ];

    if (empty($data['patient_id']) || empty($data['doctor_id'])) {
        setFlashMessage('error', 'Patient and Doctor are required');
    } else {
        $visitId = $storage->insert('opd_visits', $data);

        // Save prescriptions
        if (!empty($_POST['medicines'])) {
            foreach ($_POST['medicines'] as $index => $medicineId) {
                if (!empty($medicineId)) {
                    $prescription = [
                        'opd_visit_id' => $visitId,
                        'patient_id' => $data['patient_id'],
                        'doctor_id' => $data['doctor_id'],
                        'medicine_id' => (int)$medicineId,
                        'dosage' => sanitize($_POST['dosages'][$index] ?? ''),
                        'frequency' => sanitize($_POST['frequencies'][$index] ?? ''),
                        'duration' => sanitize($_POST['durations'][$index] ?? ''),
                        'instructions' => sanitize($_POST['instructions'][$index] ?? ''),
                        'date' => date('Y-m-d')
                    ];
                    $storage->insert('prescriptions', $prescription);
                }
            }
        }

        // Update appointment status if linked
        if ($appointmentId) {
            $storage->update('appointments', $appointmentId, ['status' => 'completed']);
        }

        $patient = $storage->getById('patients', $data['patient_id']);
        logActivity('opd_visit', 'Created OPD visit for ' . $patient['name']);
        setFlashMessage('success', 'OPD visit recorded successfully');
        redirect('index.php?page=opd-visit&id=' . $visitId);
    }
}
?>

<div class="page-header">
    <h1><i class="bi bi-clipboard2-plus me-2"></i>New OPD Visit</h1>
    <div>
        <a href="index.php?page=opd" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-2"></i>Back
        </a>
    </div>
</div>

<form method="POST" action="" class="needs-validation" novalidate>
    <?= csrfField() ?>
    <input type="hidden" name="appointment_id" value="<?= $appointmentId ?>">

    <div class="row">
        <div class="col-lg-8">
            <!-- Patient & Doctor Selection -->
            <div class="card">
                <div class="card-header">
                    <i class="bi bi-person me-2"></i>Patient & Doctor
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Patient <span class="text-danger">*</span></label>
                            <select name="patient_id" class="form-select" required>
                                <option value="">Select Patient</option>
                                <?php foreach ($patients as $patient): ?>
                                <option value="<?= $patient['id'] ?>" <?= $selectedPatient == $patient['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($patient['name']) ?> (<?= $patient['patient_id'] ?>)
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Doctor <span class="text-danger">*</span></label>
                            <select name="doctor_id" class="form-select" required>
                                <option value="">Select Doctor</option>
                                <?php foreach ($doctors as $doctor): ?>
                                <option value="<?= $doctor['id'] ?>" <?= $selectedDoctor == $doctor['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($doctor['name']) ?> - <?= $doctor['specialization'] ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Vitals -->
            <div class="card">
                <div class="card-header">
                    <i class="bi bi-heart-pulse me-2"></i>Vital Signs
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 col-6 mb-3">
                            <label class="form-label">Blood Pressure</label>
                            <input type="text" name="bp" class="form-control" placeholder="120/80">
                        </div>
                        <div class="col-md-4 col-6 mb-3">
                            <label class="form-label">Pulse (bpm)</label>
                            <input type="number" name="pulse" class="form-control" placeholder="72">
                        </div>
                        <div class="col-md-4 col-6 mb-3">
                            <label class="form-label">Temperature (°F)</label>
                            <input type="text" name="temperature" class="form-control" placeholder="98.6">
                        </div>
                        <div class="col-md-4 col-6 mb-3">
                            <label class="form-label">Weight (kg)</label>
                            <input type="text" name="weight" class="form-control" placeholder="70">
                        </div>
                        <div class="col-md-4 col-6 mb-3">
                            <label class="form-label">Height (cm)</label>
                            <input type="text" name="height" class="form-control" placeholder="170">
                        </div>
                        <div class="col-md-4 col-6 mb-3">
                            <label class="form-label">SpO2 (%)</label>
                            <input type="text" name="spo2" class="form-control" placeholder="98">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Clinical Notes -->
            <div class="card">
                <div class="card-header">
                    <i class="bi bi-journal-medical me-2"></i>Clinical Notes
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Symptoms / Chief Complaints</label>
                        <textarea name="symptoms" class="form-control" rows="2" placeholder="Patient's complaints..."></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Diagnosis</label>
                        <textarea name="diagnosis" class="form-control" rows="2" placeholder="Clinical diagnosis..."></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Treatment / Advice</label>
                        <textarea name="treatment" class="form-control" rows="2" placeholder="Treatment plan..."></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Additional Notes</label>
                        <textarea name="notes" class="form-control" rows="2"></textarea>
                    </div>
                </div>
            </div>

            <!-- Prescription -->
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span><i class="bi bi-capsule me-2"></i>Prescription</span>
                    <button type="button" class="btn btn-sm btn-primary" onclick="addPrescriptionItem()">
                        <i class="bi bi-plus me-1"></i>Add Medicine
                    </button>
                </div>
                <div class="card-body">
                    <div id="prescriptionItems">
                        <div class="prescription-item row mb-3">
                            <div class="col-md-3 mb-2">
                                <select name="medicines[]" class="form-select">
                                    <option value="">Select Medicine</option>
                                    <?php foreach ($medicines as $med): ?>
                                    <option value="<?= $med['id'] ?>"><?= htmlspecialchars($med['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-2 mb-2">
                                <input type="text" name="dosages[]" class="form-control" placeholder="Dosage">
                            </div>
                            <div class="col-md-2 mb-2">
                                <select name="frequencies[]" class="form-select">
                                    <option value="">Frequency</option>
                                    <option value="Once daily">Once daily</option>
                                    <option value="Twice daily">Twice daily</option>
                                    <option value="Thrice daily">Thrice daily</option>
                                    <option value="Four times">Four times</option>
                                    <option value="As needed">As needed</option>
                                </select>
                            </div>
                            <div class="col-md-2 mb-2">
                                <input type="text" name="durations[]" class="form-control" placeholder="Duration">
                            </div>
                            <div class="col-md-2 mb-2">
                                <input type="text" name="instructions[]" class="form-control" placeholder="Instructions">
                            </div>
                            <div class="col-md-1 mb-2">
                                <button type="button" class="btn btn-outline-danger btn-sm" onclick="removePrescriptionItem(this)">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- Follow-up -->
            <div class="card">
                <div class="card-header">
                    <i class="bi bi-calendar-event me-2"></i>Follow-up
                </div>
                <div class="card-body">
                    <label class="form-label">Follow-up Date</label>
                    <input type="date" name="follow_up_date" class="form-control" min="<?= date('Y-m-d', strtotime('+1 day')) ?>">
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card">
                <div class="card-header">Quick Actions</div>
                <div class="card-body">
                    <a href="index.php?page=lab-order-add&patient_id=<?= $selectedPatient ?>" class="btn btn-outline-info w-100 mb-2">
                        <i class="bi bi-droplet me-2"></i>Order Lab Test
                    </a>
                </div>
            </div>

            <!-- Submit -->
            <div class="card">
                <div class="card-body">
                    <button type="submit" class="btn btn-primary w-100 mb-2">
                        <i class="bi bi-check-lg me-2"></i>Save OPD Visit
                    </button>
                    <a href="index.php?page=opd" class="btn btn-outline-secondary w-100">Cancel</a>
                </div>
            </div>
        </div>
    </div>
</form>

<!-- Prescription Item Template -->
<template id="prescriptionItemTemplate">
    <div class="prescription-item row mb-3">
        <div class="col-md-3 mb-2">
            <select name="medicines[]" class="form-select">
                <option value="">Select Medicine</option>
                <?php foreach ($medicines as $med): ?>
                <option value="<?= $med['id'] ?>"><?= htmlspecialchars($med['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-2 mb-2">
            <input type="text" name="dosages[]" class="form-control" placeholder="Dosage">
        </div>
        <div class="col-md-2 mb-2">
            <select name="frequencies[]" class="form-select">
                <option value="">Frequency</option>
                <option value="Once daily">Once daily</option>
                <option value="Twice daily">Twice daily</option>
                <option value="Thrice daily">Thrice daily</option>
                <option value="Four times">Four times</option>
                <option value="As needed">As needed</option>
            </select>
        </div>
        <div class="col-md-2 mb-2">
            <input type="text" name="durations[]" class="form-control" placeholder="Duration">
        </div>
        <div class="col-md-2 mb-2">
            <input type="text" name="instructions[]" class="form-control" placeholder="Instructions">
        </div>
        <div class="col-md-1 mb-2">
            <button type="button" class="btn btn-outline-danger btn-sm" onclick="removePrescriptionItem(this)">
                <i class="bi bi-trash"></i>
            </button>
        </div>
    </div>
</template>
