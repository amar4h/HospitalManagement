/**
 * Hospital Management System - Main JavaScript
 */

$(document).ready(function() {
    // Initialize DataTables
    if ($.fn.DataTable) {
        $('.datatable').DataTable({
            responsive: true,
            pageLength: 20,
            order: [[0, 'desc']],
            language: {
                search: '',
                searchPlaceholder: 'Search...',
                lengthMenu: 'Show _MENU_ entries',
                info: 'Showing _START_ to _END_ of _TOTAL_ entries',
                paginate: {
                    first: '<i class="bi bi-chevron-double-left"></i>',
                    last: '<i class="bi bi-chevron-double-right"></i>',
                    next: '<i class="bi bi-chevron-right"></i>',
                    previous: '<i class="bi bi-chevron-left"></i>'
                }
            }
        });
    }

    // Sidebar Toggle
    $('#sidebarToggle').on('click', function() {
        $('body').toggleClass('sidebar-collapsed');
        localStorage.setItem('sidebarCollapsed', $('body').hasClass('sidebar-collapsed'));
    });

    // Restore sidebar state
    if (localStorage.getItem('sidebarCollapsed') === 'true') {
        $('body').addClass('sidebar-collapsed');
    }

    // Mobile sidebar
    if ($(window).width() < 768) {
        $('#sidebarToggle').on('click', function() {
            $('#sidebar').toggleClass('show');
        });

        // Close sidebar on outside click
        $(document).on('click', function(e) {
            if (!$(e.target).closest('#sidebar, #sidebarToggle').length) {
                $('#sidebar').removeClass('show');
            }
        });
    }

    // Delete confirmation
    $(document).on('click', '.btn-delete', function(e) {
        if (!confirm('Are you sure you want to delete this record?')) {
            e.preventDefault();
        }
    });

    // Form validation
    $('form.needs-validation').on('submit', function(e) {
        if (!this.checkValidity()) {
            e.preventDefault();
            e.stopPropagation();
        }
        $(this).addClass('was-validated');
    });

    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function(tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Auto-dismiss alerts after 5 seconds
    setTimeout(function() {
        $('.alert').fadeOut('slow');
    }, 5000);

    // Print functionality
    $(document).on('click', '.btn-print', function() {
        window.print();
    });

    // Date picker initialization (if needed)
    if ($.fn.datepicker) {
        $('.datepicker').datepicker({
            format: 'yyyy-mm-dd',
            autoclose: true,
            todayHighlight: true
        });
    }

    // Patient search autocomplete
    $('#patientSearch').on('keyup', function() {
        var query = $(this).val();
        if (query.length >= 2) {
            $.ajax({
                url: 'api/search.php',
                method: 'GET',
                data: { type: 'patients', q: query },
                success: function(response) {
                    var results = JSON.parse(response);
                    displaySearchResults(results);
                }
            });
        }
    });

    // Calculate age from DOB
    $('#dob').on('change', function() {
        var dob = new Date($(this).val());
        var today = new Date();
        var age = Math.floor((today - dob) / (365.25 * 24 * 60 * 60 * 1000));
        $('#age').val(age);
    });

    // Dynamic total calculation
    $(document).on('change', '.item-qty, .item-price', function() {
        calculateTotal();
    });

    // Medicine stock warning
    $(document).on('change', '#medicine_id', function() {
        var selected = $(this).find(':selected');
        var stock = selected.data('stock');
        var reorderLevel = selected.data('reorder');

        if (stock <= reorderLevel) {
            $('#stockWarning').show().text('Low stock warning: Only ' + stock + ' units available');
        } else {
            $('#stockWarning').hide();
        }
    });

    // Load doctor schedule
    $(document).on('change', '#doctor_id, #appointment_date', function() {
        var doctorId = $('#doctor_id').val();
        var date = $('#appointment_date').val();

        if (doctorId && date) {
            loadDoctorTimeSlots(doctorId, date);
        }
    });

    // Toggle password visibility
    $(document).on('click', '.toggle-password', function() {
        var input = $($(this).data('target'));
        var icon = $(this).find('i');

        if (input.attr('type') === 'password') {
            input.attr('type', 'text');
            icon.removeClass('bi-eye').addClass('bi-eye-slash');
        } else {
            input.attr('type', 'password');
            icon.removeClass('bi-eye-slash').addClass('bi-eye');
        }
    });

    // AJAX form submission
    $(document).on('submit', '.ajax-form', function(e) {
        e.preventDefault();
        var form = $(this);
        var submitBtn = form.find('[type="submit"]');
        var originalText = submitBtn.html();

        submitBtn.html('<span class="spinner-border spinner-border-sm"></span> Processing...').prop('disabled', true);

        $.ajax({
            url: form.attr('action'),
            method: form.attr('method') || 'POST',
            data: form.serialize(),
            success: function(response) {
                var result = JSON.parse(response);
                if (result.success) {
                    showNotification('success', result.message);
                    if (result.redirect) {
                        window.location.href = result.redirect;
                    }
                } else {
                    showNotification('error', result.message);
                }
            },
            error: function() {
                showNotification('error', 'An error occurred. Please try again.');
            },
            complete: function() {
                submitBtn.html(originalText).prop('disabled', false);
            }
        });
    });
});

/**
 * Calculate total for billing
 */
function calculateTotal() {
    var total = 0;
    $('.invoice-item').each(function() {
        var qty = parseFloat($(this).find('.item-qty').val()) || 0;
        var price = parseFloat($(this).find('.item-price').val()) || 0;
        var itemTotal = qty * price;
        $(this).find('.item-total').val(itemTotal.toFixed(2));
        total += itemTotal;
    });

    var discount = parseFloat($('#discount').val()) || 0;
    var tax = parseFloat($('#tax').val()) || 0;

    var subtotal = total;
    var discountAmount = (subtotal * discount) / 100;
    var taxableAmount = subtotal - discountAmount;
    var taxAmount = (taxableAmount * tax) / 100;
    var grandTotal = taxableAmount + taxAmount;

    $('#subtotal').val(subtotal.toFixed(2));
    $('#discountAmount').val(discountAmount.toFixed(2));
    $('#taxAmount').val(taxAmount.toFixed(2));
    $('#grandTotal').val(grandTotal.toFixed(2));
}

/**
 * Load doctor time slots
 */
function loadDoctorTimeSlots(doctorId, date) {
    $.ajax({
        url: 'api/appointments.php',
        method: 'GET',
        data: { action: 'getSlots', doctor_id: doctorId, date: date },
        success: function(response) {
            var slots = JSON.parse(response);
            var select = $('#appointment_time');
            select.empty().append('<option value="">Select Time</option>');

            slots.forEach(function(slot) {
                var disabled = slot.booked ? 'disabled' : '';
                var text = slot.time + (slot.booked ? ' (Booked)' : '');
                select.append('<option value="' + slot.time + '" ' + disabled + '>' + text + '</option>');
            });
        }
    });
}

/**
 * Show notification
 */
function showNotification(type, message) {
    var alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
    var icon = type === 'success' ? 'bi-check-circle' : 'bi-exclamation-circle';

    var html = '<div class="alert ' + alertClass + ' alert-dismissible fade show" role="alert">' +
               '<i class="bi ' + icon + ' me-2"></i>' + message +
               '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>' +
               '</div>';

    $('.content-wrapper').prepend(html);

    setTimeout(function() {
        $('.alert').fadeOut('slow', function() {
            $(this).remove();
        });
    }, 5000);
}

/**
 * Add prescription item
 */
function addPrescriptionItem() {
    var template = $('#prescriptionItemTemplate').html();
    $('#prescriptionItems').append(template);
}

/**
 * Remove prescription item
 */
function removePrescriptionItem(btn) {
    $(btn).closest('.prescription-item').remove();
}

/**
 * Add invoice item
 */
function addInvoiceItem() {
    var template = $('#invoiceItemTemplate').html();
    $('#invoiceItems').append(template);
    calculateTotal();
}

/**
 * Remove invoice item
 */
function removeInvoiceItem(btn) {
    $(btn).closest('.invoice-item').remove();
    calculateTotal();
}

/**
 * Format currency
 */
function formatCurrency(amount) {
    return '$' + parseFloat(amount).toFixed(2);
}

/**
 * Confirm action
 */
function confirmAction(message) {
    return confirm(message || 'Are you sure you want to proceed?');
}

/**
 * Print element
 */
function printElement(elementId) {
    var content = document.getElementById(elementId).innerHTML;
    var printWindow = window.open('', '_blank');
    printWindow.document.write('<html><head><title>Print</title>');
    printWindow.document.write('<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">');
    printWindow.document.write('<link href="assets/css/style.css" rel="stylesheet">');
    printWindow.document.write('</head><body>');
    printWindow.document.write(content);
    printWindow.document.write('</body></html>');
    printWindow.document.close();
    printWindow.onload = function() {
        printWindow.print();
        printWindow.close();
    };
}
