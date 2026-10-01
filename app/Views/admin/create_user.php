<?php
$pageTitle='Add User / Role';ob_start();?>
<p><a href="/admin/users">&laquo; Back to Users</a></p>
<div class="card border-0 shadow-sm" style="max-width:760px;"><div class="card-body">
<div class="alert alert-light border small mb-3"><i class="fa-solid fa-user-group me-1"></i>
One login account can have multiple roles. If the email already exists, this adds the selected role to that account and keeps its existing password. Each role has its own linked home; a home can be linked only once for each role.
</div>
<form method="post" action="/admin/users"><?= \App\Helpers\Csrf::field() ?>
<div class="row g-3">
<div class="col-12"><label class="form-label">Name *</label><input type="text" name="name" class="form-control" required></div>
<div class="col-6"><label class="form-label">Email *</label><input type="email" name="email" class="form-control" required></div>
<div class="col-6"><label class="form-label">Phone</label><input type="text" name="phone" class="form-control"></div>
<div class="col-6"><label class="form-label">Role *</label><select name="role_id" class="form-select" required><option value="">Select</option><?php foreach($roles as $role): ?><option value="<?= (int)$role['id'] ?>"><?= htmlspecialchars($role['name']) ?></option><?php endforeach; ?></select></div>
<div class="col-6"><label class="form-label">Linked Home *</label><select name="member_id" class="form-select" disabled required><option value="0">Select a role first</option></select></div>
<div class="col-12 col-md-6"><label class="form-label">Password</label><input type="password" name="password" class="form-control" minlength="8"><div class="form-text">Required only for a new account. Ignored when adding a role to an existing account.</div></div>
</div>
<div class="form-text mt-2">Select the active resident/member represented by this role. <a href="/members/create">Add a resident</a> if needed.</div>
<button type="submit" class="btn btn-primary mt-3">Create Account / Add Role</button>
</form></div></div>
<script>
(function(){const role=document.querySelector('[name="role_id"]'),home=document.querySelector('[name="member_id"]');
async function load(){home.disabled=true;home.innerHTML='';if(!role.value){home.append(new Option('Select a role first','0'));return;}
home.append(new Option('Loading available homes…','0'));try{const r=await fetch('/admin/users/resident-candidates?role_id='+encodeURIComponent(role.value),{headers:{Accept:'application/json'}});if(!r.ok)throw new Error();const d=await r.json();home.innerHTML='';if(!d.items||!d.items.length){home.append(new Option('No available homes for this role','0'));return;}home.append(new Option('Select home','0'));d.items.forEach(i=>home.append(new Option(i.wing_name+'-'+i.flat_number+' — '+i.name+' ('+i.member_type+')',i.id)));home.disabled=false;}catch(e){home.innerHTML='';home.append(new Option('Could not load available homes','0'));}}
role.addEventListener('change',load);})();
</script>
<?php $content=ob_get_clean();require __DIR__.'/../layouts/app.php';