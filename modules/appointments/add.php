<?php
/**
 * Add New Appointment
 */
requireAuth();
requireRole(['admin', 'receptionist', 'doctor']);

$storage = getStorage();
$patients = $storage->getAll('patients');
$doctors = $storage->getAll('doctors', ['status' => 'active']);

// Pre-fill if coming from patient or doctor page
$selectedPatient = $_GET['patient_id'] ?? '';
$selectedDoctor = $_GET['doctor_id'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'patient_id' => (int)($_POST['patient_id'] ?? 0),
        'doctor_id' => (int)($_POST['doctor_id'] ?? 0),
        'date' => sanitize($_POST['date'] ?? ''),
        'time' => sanitize($_POST['time'] ?? ''),
        'reason' => sanitize($_POST['reason'] ?? ''),
        'notes' => sanitize($_POST['notes'] ?? ''),
        'status' => 'pending'
    ];

    if (empty($data['patient_id']) || empty($data['doctor_id']) || empty($data['date']) || empty($data['time'])) {
        setFlashMessage('error', 'Please fill in all required fields');
    } elseif (!isSlotAvailable($data['doctor_id'], $data['date'], $data['time'])) {
        setFlashMessage('error', 'This time slot is already booked');
    } else {
        $id = $storage->insert('appointments', $data);
        $patient = $storage->getById('patients', $data['patient_id']);
        logActivity('appointment_add', 'Created appointment for ' . $patient['name']);
        setFlashMessage('success', 'Appointment scheduled successfully');
        redirect('index.php?page=appointments&date=' . $data['date']);
    }
}

$timeSlots = getTimeSlots('08:00', '20:00', 30);
?>

<div class="page-header">
    <h1><i class="bi bi-calendar-plus me-2"></i>Schedule Appointment</h1>
    <div>
        <a href="index.php?page=appointments" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-2"></i>Back
        </a>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-body">
                <form method="POST" action="" class="needs-validation" novalidate>
                    <?= csrfField() ?>

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
                            <div class="mt-2">
                                <a href="index.php?page=patient-add" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-plus me-1"></i>New Patient
                                </a>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Doctor <span class="text-danger">*</span></label>
                            <select name="doctor_id" id="doctor_id" class="form-select" required>
                                <option value="">Select Doctor</option>
                                <?php foreach ($doctors as $doctor): ?>
                                <?php $dept = $storage->getById('departments', $doctor['department_id']); ?>
                                <option value="<?= $doctor['id'] ?>" <?= $selectedDoctor == $doctor['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($doctor['name']) ?> - <?= htmlspecialchars($dept['name'] ?? '') ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Date <span class="text-danger">*</span></label>
                            <input type="date" name="date" id="appointment_date" class="form-control"
                                   min="<?= date('Y-m-d') ?>" value="<?= date('Y-m-d') ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Time <span class="text-danger">*</span></label>
                            <select name="time" id="appointment_time" class="form-select" required>
                                <option value="">Select Time</option>
                                <?php foreach ($timeSlots as $slot): ?>
                                <option value="<?= $slot ?>"><?= date('h:i A', strtotime($slot)) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Reason for Visit</label>
                        <input type="text" name="reason" class="form-control" placeholder="e.g., Regular checkup, Follow-up, Consultation">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Additional Notes</label>
                        <textarea name="notes" class="form-control" rows="3" placeholder="Any additional information..."></textarea>
                    </div>

                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <a href="index.php?page=appointments" class="btn btn-outline-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-calendar-check me-2"></i>Schedule Appointment
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
