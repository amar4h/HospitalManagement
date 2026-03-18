<?php
/**
 * Edit Appointment
 */
requireAuth();
requireRole(['admin', 'receptionist']);

$storage = getStorage();
$id = (int)($_GET['id'] ?? 0);
$appointment = $storage->getById('appointments', $id);

if (!$appointment) {
    setFlashMessage('error', 'Appointment not found');
    redirect('index.php?page=appointments');
}

$patients = $storage->getAll('patients');
$doctors = $storage->getAll('doctors', ['status' => 'active']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'patient_id' => (int)($_POST['patient_id'] ?? 0),
        'doctor_id' => (int)($_POST['doctor_id'] ?? 0),
        'date' => sanitize($_POST['date'] ?? ''),
        'time' => sanitize($_POST['time'] ?? ''),
        'reason' => sanitize($_POST['reason'] ?? ''),
        'notes' => sanitize($_POST['notes'] ?? ''),
        'status' => sanitize($_POST['status'] ?? 'pending')
    ];

    if (empty($data['patient_id']) || empty($data['doctor_id']) || empty($data['date']) || empty($data['time'])) {
        setFlashMessage('error', 'Please fill in all required fields');
    } else {
        $storage->update('appointments', $id, $data);
        logActivity('appointment_update', 'Updated appointment ID: ' . $id);
        setFlashMessage('success', 'Appointment updated successfully');
        redirect('index.php?page=appointments&date=' . $data['date']);
    }
}

$timeSlots = getTimeSlots('08:00', '20:00', 30);
?>

<div class="page-header">
    <h1><i class="bi bi-pencil me-2"></i>Edit Appointment</h1>
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
                                <option value="<?= $patient['id'] ?>" <?= $appointment['patient_id'] == $patient['id'] ? 'selected' : '' ?>>
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
                                <option value="<?= $doctor['id'] ?>" <?= $appointment['doctor_id'] == $doctor['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($doctor['name']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Date <span class="text-danger">*</span></label>
                            <input type="date" name="date" class="form-control" value="<?= $appointment['date'] ?>" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Time <span class="text-danger">*</span></label>
                            <select name="time" class="form-select" required>
                                <?php foreach ($timeSlots as $slot): ?>
                                <option value="<?= $slot ?>" <?= $appointment['time'] == $slot ? 'selected' : '' ?>>
                                    <?= date('h:i A', strtotime($slot)) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                <option value="pending" <?= $appointment['status'] == 'pending' ? 'selected' : '' ?>>Pending</option>
                                <option value="confirmed" <?= $appointment['status'] == 'confirmed' ? 'selected' : '' ?>>Confirmed</option>
                                <option value="completed" <?= $appointment['status'] == 'completed' ? 'selected' : '' ?>>Completed</option>
                                <option value="cancelled" <?= $appointment['status'] == 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Reason for Visit</label>
                        <input type="text" name="reason" class="form-control" value="<?= htmlspecialchars($appointment['reason'] ?? '') ?>">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Additional Notes</label>
                        <textarea name="notes" class="form-control" rows="3"><?= htmlspecialchars($appointment['notes'] ?? '') ?></textarea>
                    </div>

                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <a href="index.php?page=appointments" class="btn btn-outline-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-lg me-2"></i>Update Appointment
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
