<?php
// modules/appointments/slot_generator.php

require_once "../../config/database.php";

/**
 * Generate available appointment slots for a doctor on a given date
 *
 * @param int $doctor_id
 * @param string $date (YYYY-MM-DD)
 * @return array
 */
function generateAvailableSlots($doctor_id, $date)
{
    global $conn;

    $slotDuration = 30; // minutes
    $availableSlots = [];

    /* ================================
       1. GET DOCTOR AVAILABILITY
    ================================ */
    $stmt = $conn->prepare("
        SELECT start_time, end_time
        FROM doctor_availability
        WHERE doctor_id = ?
          AND available_date = ?
          AND status = 'available'
    ");
    $stmt->bind_param("is", $doctor_id, $date);
    $stmt->execute();
    $availability = $stmt->get_result();

    if ($availability->num_rows === 0) {
        return []; // No availability
    }

    /* ================================
       2. GET EXISTING APPROVED APPOINTMENTS
    ================================ */
    $stmt2 = $conn->prepare("
        SELECT appointment_time
        FROM appointments
        WHERE doctor_id = ?
          AND appointment_date = ?
          AND status = 'approved'
    ");
    $stmt2->bind_param("is", $doctor_id, $date);
    $stmt2->execute();
    $bookedResult = $stmt2->get_result();

    $bookedTimes = [];
    while ($row = $bookedResult->fetch_assoc()) {
        $bookedTimes[] = $row['appointment_time'];
    }

    /* ================================
       3. GENERATE SLOTS
    ================================ */
    while ($row = $availability->fetch_assoc()) {
        $start = strtotime($row['start_time']);
        $end   = strtotime($row['end_time']);

        while ($start < $end) {
            $slot = date("H:i:s", $start);

            if (!in_array($slot, $bookedTimes)) {
                $availableSlots[] = $slot;
            }

            $start = strtotime("+{$slotDuration} minutes", $start);
        }
    }

    return $availableSlots;
}

/**
 * Check if a specific slot is still available
 */
function isSlotAvailable($doctor_id, $date, $time)
{
    global $conn;

    $stmt = $conn->prepare("
        SELECT appointment_id
        FROM appointments
        WHERE doctor_id = ?
          AND appointment_date = ?
          AND appointment_time = ?
          AND status = 'approved'
    ");
    $stmt->bind_param("iss", $doctor_id, $date, $time);
    $stmt->execute();
    $stmt->store_result();

    return $stmt->num_rows === 0;
}

/**
 * Suggest next available slots (used for conflict resolution)
 */
function suggestAlternativeSlots($doctor_id, $date, $limit = 3)
{
    $slots = generateAvailableSlots($doctor_id, $date);
    return array_slice($slots, 0, $limit);
}
