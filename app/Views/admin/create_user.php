<?php
$pageTitle = 'Add User';
ob_start();
?>
<p><a href="/admin/users">&laquo; Back to Users</a></p>
<?php $needsResident = isset($_GET['needs_resident']); ?>
<div class="card border-0 shadow-sm" style="max-width: 760px;">
    <div class="card-body">
        <?php if ($needsResident): ?>
            <div class="alert alert-warning d-flex align-items-start gap-3" role="alert">
                <i class="fa-solid fa-house-user mt-1"></i>
                <div>
                    <div class="fw-semibold">A resident is required for this account.</div>
                    <div class="small mb-2">Resident/Tenant users must be linked to an active resident record. Create the resident first, then return here and select them.</div>
                    <a class="btn btn-sm btn-warning" href="/members/create"><i class="fa-solid fa-plus me-1"></i>Add Resident</a>
                </div>
            </div>
        <?php endif; ?>
        <?php if (empty($residentCandidates)): ?>
            <div class="alert alert-info d-flex align-items-start gap-3" role="alert">
                <i class="fa-solid fa-circle-info mt-1"></i>
                <div>
                    <div class="fw-semibold">No active residents available to link.</div>
                    <div class="small mb-2">If you are creating a Resident or Tenant account, add the resident record first.</div>
                    <a class="btn btn-sm btn-outline-primary" href="/members/create"><i class="fa-solid fa-user-plus me-1"></i>Add Resident</a>
                </div>
            </div>
        <?php endif; ?>
        <form method="post" action="/admin/users">
            <?= \App\Helpers\Csrf::field() ?>
            <div class="row g-3">
                <div class="col-12">
                    <label class="form-label">Name *</label>
                    <input type="text" name="name" class="form-control" required>
                </div>
                <div class="col-6">
                    <label class="form-label">Email *</label>
                    <input type="email" name="email" class="form-control" required>
                </div>
                <div class="col-6">
                    <label class="form-label">Phone</label>
                    <input type="text" name="phone" class="form-control">
                </div>
                <div class="col-6">
                    <label class="form-label">Role *</label>
                    <select name="role_id" class="form-select" required>
                        <option value="">Select</option>
                        <?php foreach ($roles as $role): ?>
                            <option value="<?= (int) $role['id'] ?>" data-role-name="<?= htmlspecialchars($role['name']) ?>"><?= htmlspecialchars($role['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-6" id="linked-resident-group">
                    <label class="form-label">Linked Resident <span class="text-danger" id="resident-required">*</span></label>
                    <select name="member_id" class="form-select" disabled>
                        <option value="0">Select a role first</option>
                    </select>
                </div>
                <div class="col-6">
                    <label class="form-label">Password *</label>
                    <input type="password" name="password" class="form-control" minlength="8" required>
                </div>
            </div>
            <div id="resident-link-help" class="form-text mt-2">For Resident/Tenant accounts, select the person who occupies the home. <a href="/members/create">Add a resident</a> if they do not exist yet.</div>
            <p class="text-muted small mt-2">The user will be required to change this password on first login.</p>
            <button type="submit" class="btn btn-primary mt-2">Create User</button>
        </form>
    </div>
</div>
<script>
(function () {
    const role = document.querySelector('select[name="role_id"]');
    const resident = document.querySelector('select[name="member_id"]');
    const required = document.getElementById('resident-required');
    const group = document.getElementById('linked-resident-group');

    async function loadEligibleHomes() {
        const selected = role.options[role.selectedIndex];
        const roleName = selected ? (selected.dataset.roleName || '') : '';
        resident.innerHTML = '';
        resident.disabled = true;

        if (!['resident', 'tenant'].includes(roleName)) {
            resident.required = false;
            required.classList.add('d-none');
            resident.append(new Option('Not linked', '0'));
            return;
        }

        resident.append(new Option('Loading available homes…', '0'));
        try {
            const response = await fetch('/admin/users/resident-candidates?role_id=' + encodeURIComponent(role.value), {
                headers: { 'Accept': 'application/json' }
            });
            if (!response.ok) throw new Error('Unable to load available homes.');
            const data = await response.json();
            resident.innerHTML = '';

            if (!data.items || data.items.length === 0) {
                resident.append(new Option('No available homes for this role', '0'));
                resident.required = false;
                return;
            }

            resident.append(new Option('Select home', '0'));
            data.items.forEach(function (item) {
                const label = item.wing_name + '-' + item.flat_number + ' — ' + item.name + ' (' + item.member_type + ')';
                resident.append(new Option(label, item.id));
            });
            resident.disabled = false;
            resident.required = true;
            required.classList.remove('d-none');
        } catch (error) {
            resident.innerHTML = '';
            resident.append(new Option('Could not load available homes', '0'));
            resident.required = false;
        }
    }

    role.addEventListener('change', loadEligibleHomes);
    loadEligibleHomes();
})();
</script>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/app.php';
