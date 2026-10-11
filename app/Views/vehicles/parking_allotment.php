<?php
$pageTitle = 'Parking Allotment';
ob_start();
?>
<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <div>
        <h4 class="mb-1">Allot Resident Vehicle to Parking</h4>
        <p class="text-muted mb-0">Choose a resident's flat and registered vehicle, then assign a compatible free parking slot.</p>
    </div>
    <a href="/vehicles/parking" class="btn btn-outline-secondary"><i class="fa-solid fa-square-parking me-1"></i>Manage Parking Slots</a>
</div>

<div class="row g-3">
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <h5 class="mb-3">New Parking Allotment</h5>
                <form method="post" action="/vehicles/parking-allotment" id="parking-allotment-form">
                    <?= \App\Helpers\Csrf::field() ?>
                    <div class="mb-3">
                        <label class="form-label" for="allot-flat">Resident Flat *</label>
                        <select id="allot-flat" name="flat_id" class="form-select" required>
                            <option value="">Select resident flat</option>
                            <?php foreach ($flats as $flat): ?>
                                <option value="<?= (int) $flat['id'] ?>"><?= htmlspecialchars($flat['wing_name'] . '-' . $flat['flat_number']) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <div class="form-text">Select the flat linked to the resident's registered vehicle.</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="allot-vehicle">Resident Vehicle *</label>
                        <select id="allot-vehicle" name="vehicle_id" class="form-select" required disabled>
                            <option value="">First select a flat</option>
                            <?php foreach ($vehicles as $vehicle): ?>
                                <option value="<?= (int) $vehicle['id'] ?>"
                                    data-flat-id="<?= (int) $vehicle['flat_id'] ?>"
                                    data-vehicle-type="<?= htmlspecialchars($vehicle['vehicle_type']) ?>">
                                    <?= htmlspecialchars($vehicle['registration_number'] . ' — ' . ($vehicle['make'] ?: 'Vehicle') . ' ' . ($vehicle['model'] ?? '') . ' (' . $vehicle['wing_name'] . '-' . $vehicle['flat_number'] . ')') ?>
                                    <?= $vehicle['vehicle_type'] === 'four_wheeler' ? '· 4-Wheeler' : '· 2-Wheeler' ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="form-text" id="vehicle-help">Only vehicles registered to the selected flat will be shown.</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="allot-slot">Available Parking Slot *</label>
                        <select id="allot-slot" name="parking_slot_id" class="form-select" required disabled>
                            <option value="">First select a vehicle</option>
                            <?php foreach ($slots as $slot): ?>
                                <option value="<?= (int) $slot['id'] ?>" data-slot-type="<?= htmlspecialchars($slot['slot_type']) ?>">
                                    <?= htmlspecialchars($slot['slot_number']) ?> — <?= $slot['slot_type'] === 'four_wheeler' ? '4-Wheeler' : '2-Wheeler' ?>
                                    <?= $slot['current_rate'] !== null ? ' · ₹' . number_format((float) $slot['current_rate'], 2) . '/month' : ' · Rate not set' ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="form-text" id="slot-help">Only free slots matching the vehicle type will be shown.</div>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label" for="allot-from">Allotment Start Date *</label>
                            <input type="date" id="allot-from" name="allocated_from" class="form-control" value="<?= date('Y-m-d') ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="allot-billing">Parking Billing *</label>
                            <select id="allot-billing" name="billing_status" class="form-select" required>
                                <option value="paid">Paid — charge applicable rate</option>
                                <option value="free">Free — no parking charge</option>
                            </select>
                        </div>
                    </div>
                    <div class="alert alert-info mt-3 mb-3 small">
                        <i class="fa-solid fa-circle-info me-1"></i>
                        The allotment stays active until staff releases it. The release date is recorded when the slot is released, preserving allocation history.
                    </div>
                    <button type="submit" class="btn btn-primary w-100"><i class="fa-solid fa-check me-1"></i>Confirm Parking Allotment</button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body">
                <h6><i class="fa-solid fa-list-check me-2"></i>Before confirming</h6>
                <ul class="small text-muted mb-0 ps-3">
                    <li class="mb-2">The vehicle must already be registered under the selected flat.</li>
                    <li class="mb-2">The parking slot type must match the vehicle type.</li>
                    <li class="mb-2">A 4-wheeler with an active parking allotment cannot be assigned a second 4-wheeler slot.</li>
                    <li>For common two-wheeler parking, choose one of the available Common Area spaces.</li>
                </ul>
            </div>
        </div>
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h6>Need to register a vehicle first?</h6>
                <p class="small text-muted">Add the resident's vehicle before allotting parking.</p>
                <a href="/vehicles/create" class="btn btn-outline-primary btn-sm">Register Vehicle</a>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm mt-4">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="mb-0">Current Parking Allotments</h5>
            <span class="badge bg-primary"><?= count($activeAllocations) ?> active</span>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead><tr><th>Flat / Resident</th><th>Vehicle</th><th>Parking Slot</th><th>Allotted From</th><th>Billing</th><th>Manage</th></tr></thead>
                <tbody>
                <?php foreach ($activeAllocations as $allocation): ?>
                    <tr>
                        <td><?= htmlspecialchars($allocation['wing_name'] . '-' . $allocation['flat_number']) ?><?php if ($allocation['resident_name']): ?><div class="small text-muted"><?= htmlspecialchars($allocation['resident_name']) ?></div><?php endif; ?></td>
                        <td><?= htmlspecialchars($allocation['registration_number'] ?? 'Vehicle not linked') ?></td>
                        <td><a href="/vehicles/parking/<?= (int) $allocation['parking_slot_id'] ?>"><?= htmlspecialchars($allocation['slot_number']) ?></a></td>
                        <td><?= htmlspecialchars($allocation['allocated_from']) ?></td>
                        <td><span class="badge bg-<?= $allocation['is_chargeable'] ? 'warning text-dark' : 'secondary' ?>"><?= $allocation['is_chargeable'] ? 'Paid' : 'Free' ?></span></td>
                        <td><a class="btn btn-sm btn-outline-primary" href="/vehicles/parking/<?= (int) $allocation['parking_slot_id'] ?>">View / Release</a></td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($activeAllocations)): ?>
                    <tr><td colspan="6" class="text-center text-muted py-4">No active parking allotments yet.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
(function () {
    const flat = document.getElementById('allot-flat');
    const vehicle = document.getElementById('allot-vehicle');
    const slot = document.getElementById('allot-slot');
    const vehicleHelp = document.getElementById('vehicle-help');
    const slotHelp = document.getElementById('slot-help');
    const vehicleOptions = Array.from(vehicle.querySelectorAll('option[data-flat-id]'));
    const slotOptions = Array.from(slot.querySelectorAll('option[data-slot-type]'));

    function filterVehicles() {
        const flatId = flat.value;
        let count = 0;
        vehicleOptions.forEach(option => {
            option.hidden = !flatId || option.dataset.flatId !== flatId;
            if (!option.hidden) count++;
        });
        vehicle.value = '';
        vehicle.disabled = !flatId || count === 0;
        vehicleHelp.textContent = !flatId ? 'Only vehicles registered to the selected flat will be shown.'
            : (count ? 'Choose the resident vehicle to allot.' : 'No registered vehicles found for this flat. Register a vehicle first.');
        filterSlots();
    }

    function filterSlots() {
        const selectedVehicle = vehicle.selectedOptions[0];
        const type = selectedVehicle && selectedVehicle.dataset.vehicleType;
        let count = 0;
        slotOptions.forEach(option => {
            option.hidden = !type || option.dataset.slotType !== type;
            if (!option.hidden) count++;
        });
        slot.value = '';
        slot.disabled = !type || count === 0;
        slotHelp.textContent = !type ? 'Only free slots matching the vehicle type will be shown.'
            : (count ? 'Select an available compatible slot.' : 'No free slots match this vehicle type.');
    }

    flat.addEventListener('change', filterVehicles);
    vehicle.addEventListener('change', filterSlots);
    filterVehicles();
})();
</script>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/app.php';
