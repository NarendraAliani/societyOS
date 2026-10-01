<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Helpers\Auth;
use App\Helpers\Csrf;
use App\Helpers\FileUpload;
use App\Helpers\Flash;
use App\Models\ActivityLog;
use App\Models\Document;
use App\Models\EmergencyContact;
use App\Models\FamilyMember;
use App\Models\Flat;
use App\Models\Member;
use App\Models\Society;
use App\Models\Tenant;
use App\Models\Vehicle;

final class MemberController
{
    public function index(): void
    {
        $pageTitle = 'Residents';
        $members = Member::allForSociety(Society::currentId());
        require __DIR__ . '/../Views/members/index.php';
    }

    public function tenants(): void
    {
        $pageTitle = 'Tenants';
        $tenants = Tenant::allForSociety(Society::currentId());
        require __DIR__ . '/../Views/members/tenants.php';
    }

    public function create(): void
    {
        $pageTitle = 'Add Resident';
        $flats = Flat::allForSociety(Society::currentId());
        require __DIR__ . '/../Views/members/create.php';
    }

    public function store(): void
    {
        $this->verifyCsrf();

        $name = trim((string) ($_POST['name'] ?? ''));
        $phone = trim((string) ($_POST['phone'] ?? ''));
        $flatId = (int) ($_POST['flat_id'] ?? 0);
        $memberType = ($_POST['member_type'] ?? '') === 'tenant' ? 'tenant' : 'owner';

        $flat = $flatId > 0 ? Flat::find($flatId) : null;
        if ($name === '' || $phone === '' || !$flat || (int) $flat['society_id'] !== Society::currentId()) {
            Flash::set('error', 'Name, phone, and a valid society flat are required.');
            header('Location: /members/create');
            exit;
        }

        $id = Member::create(Society::currentId(), [
            'flat_id' => $flatId,
            'member_type' => $memberType,
            'name' => $name,
            'email' => trim((string) ($_POST['email'] ?? '')),
            'phone' => $phone,
            'alternate_phone' => trim((string) ($_POST['alternate_phone'] ?? '')),
            'move_in_date' => $_POST['move_in_date'] ?? '',
        ]);

        ActivityLog::log('members', 'create', "Added resident \"{$name}\"");
        Flash::set('success', "Resident \"{$name}\" added.");
        header("Location: /members/{$id}");
        exit;
    }

    public function show(string $id): void
    {
        $pageTitle = 'Resident Detail';
        $member = Member::find((int) $id);
        if (!$member || (int) $member['society_id'] !== Society::currentId()) {
            http_response_code(404);
            require __DIR__ . '/../Views/errors/404.php';
            return;
        }
        $familyMembers = FamilyMember::forMember((int) $id);
        $emergencyContacts = EmergencyContact::forMember((int) $id);
        $vehicles = Vehicle::forMember((int) $id);
        $documents = Document::forMember((int) $id);

        $tenant = null;
        $ownerCandidates = [];
        if ($member['member_type'] === 'tenant') {
            $tenant = Tenant::forMember((int) $id);
            $ownerCandidates = Tenant::ownerCandidatesForFlat((int) $member['flat_id']);
        }

        require __DIR__ . '/../Views/members/show.php';
    }

    public function update(string $id): void
    {
        $this->verifyCsrf();

        $member = Member::find((int) $id);
        if (!$member || (int) $member['society_id'] !== Society::currentId()) {
            http_response_code(404);
            require __DIR__ . '/../Views/errors/404.php';
            return;
        }

        $name = trim((string) ($_POST['name'] ?? ''));
        $phone = trim((string) ($_POST['phone'] ?? ''));

        if ($name === '' || $phone === '') {
            Flash::set('error', 'Name and phone are required.');
            header("Location: /members/{$id}");
            exit;
        }

        Member::update((int) $id, [
            'name' => $name,
            'email' => trim((string) ($_POST['email'] ?? '')),
            'phone' => $phone,
            'alternate_phone' => trim((string) ($_POST['alternate_phone'] ?? '')),
            'member_type' => ($_POST['member_type'] ?? '') === 'tenant' ? 'tenant' : 'owner',
            'status' => ($_POST['status'] ?? '') === 'inactive' ? 'inactive' : 'active',
        ]);

        ActivityLog::log('members', 'update', "Updated resident \"{$name}\" (id {$id})");
        Flash::set('success', 'Resident updated.');
        header("Location: /members/{$id}");
        exit;
    }

    public function destroy(string $id): void
    {
        $this->verifyCsrf();
        $member = Member::find((int) $id);
        if (!$member || (int) $member['society_id'] !== Society::currentId()) {
            Flash::set('error', 'Resident not found.');
            header('Location: /members');
            exit;
        }
        Member::delete((int) $id);
        ActivityLog::log('members', 'delete', "Removed resident \"" . ($member['name'] ?? $id) . "\"");
        Flash::set('success', 'Resident removed.');
        header('Location: /members');
        exit;
    }

    public function storeFamilyMember(string $memberId): void
    {
        $this->verifyCsrf();

        $member = Member::find((int) $memberId);
        if (!$member || (int) $member['society_id'] !== Society::currentId()) {
            Flash::set('error', 'Resident not found.');
            header('Location: /members');
            exit;
        }

        $name = trim((string) ($_POST['name'] ?? ''));
        $dob = trim((string) ($_POST['date_of_birth'] ?? '')) ?: null;

        if ($name === '') {
            Flash::set('error', 'Family member name is required.');
            header("Location: /members/{$memberId}");
            exit;
        }

        if ($dob !== null && strtotime($dob) > time()) {
            Flash::set('error', 'Date of birth cannot be in the future.');
            header("Location: /members/{$memberId}");
            exit;
        }

        $age = is_numeric($_POST['age'] ?? '') ? (int) $_POST['age'] : null;
        $relation = trim((string) ($_POST['relation'] ?? '')) ?: null;
        $phone = trim((string) ($_POST['phone'] ?? '')) ?: null;
        if (!empty($_POST['id'])) {
            $existing = FamilyMember::find((int) $_POST['id']);
            if (!$existing || (int) $existing['member_id'] !== (int) $memberId) {
                Flash::set('error', 'Family member not found.');
                header("Location: /members/{$memberId}");
                exit;
            }
            FamilyMember::update((int) $_POST['id'], $name, $relation, $dob, $age, $phone);
            Flash::set('success', 'Family member updated.');
        } else {
            FamilyMember::create((int) $memberId, $name, $relation, $dob, $age, $phone);
            Flash::set('success', 'Family member added.');
        }
        header("Location: /members/{$memberId}");
        exit;
    }

    public function deleteFamilyMember(string $id): void
    {
        $this->verifyCsrf();
        $familyMember = FamilyMember::find((int) $id);
        $familyOwner = $familyMember ? Member::find((int) $familyMember['member_id']) : null;
        if (!$familyMember || !$familyOwner || (int) $familyOwner['society_id'] !== Society::currentId()) {
            Flash::set('error', 'Family member not found.');
            header('Location: /members');
            exit;
        }
        FamilyMember::delete((int) $id);
        Flash::set('success', 'Family member removed.');
        header('Location: /members/' . ($familyMember['member_id'] ?? ''));
        exit;
    }

    public function storeEmergencyContact(string $memberId): void
    {
        $this->verifyCsrf();

        $member = Member::find((int) $memberId);
        if (!$member || (int) $member['society_id'] !== Society::currentId()) {
            Flash::set('error', 'Resident not found.');
            header('Location: /members');
            exit;
        }

        $name = trim((string) ($_POST['name'] ?? ''));
        $phone = trim((string) ($_POST['phone'] ?? ''));
        $relation = trim((string) ($_POST['relation'] ?? '')) ?: null;
        if ($name === '' || $phone === '') {
            Flash::set('error', 'Name and phone are required.');
        } elseif (!empty($_POST['id'])) {
            $existing = EmergencyContact::find((int) $_POST['id']);
            if (!$existing || (int) $existing['member_id'] !== (int) $memberId) {
                Flash::set('error', 'Emergency contact not found.');
            } else {
                EmergencyContact::update((int) $_POST['id'], $name, $relation, $phone);
                Flash::set('success', 'Emergency contact updated.');
            }
        } else {
            EmergencyContact::create((int) $memberId, $name, $relation, $phone);
            Flash::set('success', 'Emergency contact added.');
        }
        header("Location: /members/{$memberId}");
        exit;
    }

    public function deleteEmergencyContact(string $id): void
    {
        $this->verifyCsrf();
        $contact = EmergencyContact::find((int) $id);
        $contactOwner = $contact ? Member::find((int) $contact['member_id']) : null;
        if (!$contact || !$contactOwner || (int) $contactOwner['society_id'] !== Society::currentId()) {
            Flash::set('error', 'Emergency contact not found.');
            header('Location: /members');
            exit;
        }
        EmergencyContact::delete((int) $id);
        Flash::set('success', 'Emergency contact removed.');
        header('Location: /members/' . ($contact['member_id'] ?? ''));
        exit;
    }

    public function storeDocument(string $memberId): void
    {
        $this->verifyCsrf();

        $member = Member::find((int) $memberId);
        if (!$member || (int) $member['society_id'] !== Society::currentId()) {
            Flash::set('error', 'Resident not found.');
            header('Location: /members');
            exit;
        }

        $title = trim((string) ($_POST['title'] ?? ''));
        if ($title === '') {
            Flash::set('error', 'A title is required.');
            header("Location: /members/{$memberId}");
            exit;
        }

        try {
            $path = FileUpload::storeDocument($_FILES['document'] ?? [], 'documents');
        } catch (\RuntimeException $e) {
            Flash::set('error', $e->getMessage());
            header("Location: /members/{$memberId}");
            exit;
        }

        if ($path === null) {
            Flash::set('error', 'A file is required.');
            header("Location: /members/{$memberId}");
            exit;
        }

        $fileType = strtolower((string) pathinfo($path, PATHINFO_EXTENSION));
        Document::create(Society::currentId(), (int) $memberId, $title, $path, $fileType, Auth::id());

        ActivityLog::log('members', 'document_upload', "Uploaded document \"{$title}\" for member id {$memberId}");
        Flash::set('success', 'Document uploaded.');
        header("Location: /members/{$memberId}");
        exit;
    }

    public function deleteDocument(string $id): void
    {
        $this->verifyCsrf();

        $document = Document::find((int) $id);
        if (!$document || (int) $document['member_society_id'] !== Society::currentId()) {
            Flash::set('error', 'Document not found.');
            header('Location: /members');
            exit;
        }

        $fullPath = dirname(__DIR__, 2) . '/uploads/' . $document['file_path'];
        Document::delete((int) $id);
        if (is_file($fullPath)) {
            unlink($fullPath);
        }

        ActivityLog::log('members', 'document_delete', "Deleted document \"{$document['title']}\" for member id {$document['member_id']}");
        Flash::set('success', 'Document removed.');
        header('Location: /members/' . $document['member_id']);
        exit;
    }

    /**
     * Streams an uploaded resident document after the route's own auth+permission
     * middleware has already run — same "never linked directly" pattern as
     * StaffController::serveFile().
     */
    public function serveDocument(string $id): void
    {
        $document = Document::find((int) $id);
        if (!$document || (int) $document['member_society_id'] !== Society::currentId()) {
            http_response_code(404);
            exit('Not found.');
        }

        $fullPath = dirname(__DIR__, 2) . '/uploads/' . $document['file_path'];
        if (!is_file($fullPath)) {
            http_response_code(404);
            exit('Not found.');
        }

        $contentTypes = ['jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png', 'pdf' => 'application/pdf'];
        $extension = strtolower((string) pathinfo($fullPath, PATHINFO_EXTENSION));

        header('Content-Type: ' . ($contentTypes[$extension] ?? 'application/octet-stream'));
        header('Content-Length: ' . filesize($fullPath));
        header('X-Content-Type-Options: nosniff');
        readfile($fullPath);
        exit;
    }

    public function storeLease(string $memberId): void
    {
        $this->verifyCsrf();

        $member = Member::find((int) $memberId);
        $ownerMemberId = (int) ($_POST['owner_member_id'] ?? 0);
        $owner = $ownerMemberId > 0 ? Member::find($ownerMemberId) : null;

        if (!$member || (int) $member['society_id'] !== Society::currentId() || $member['member_type'] !== 'tenant' || $ownerMemberId <= 0) {
            Flash::set('error', 'A valid flat owner is required to set up lease details.');
            header("Location: /members/{$memberId}");
            exit;
        }

        if (!$owner || (int) $owner['society_id'] !== Society::currentId() || (int) $owner['flat_id'] !== (int) $member['flat_id'] || $owner['member_type'] !== 'owner' || $owner['status'] !== 'active') {
            Flash::set('error', 'Selected flat owner is invalid.');
            header("Location: /members/{$memberId}");
            exit;
        }

        try {
            $docPath = FileUpload::storeDocument($_FILES['agreement_doc'] ?? [], 'documents');
        } catch (\RuntimeException $e) {
            Flash::set('error', $e->getMessage());
            header("Location: /members/{$memberId}");
            exit;
        }

        Tenant::create(
            (int) $member['flat_id'],
            (int) $memberId,
            $ownerMemberId,
            $_POST['lease_start'] ?? null,
            $_POST['lease_end'] ?? null,
            $docPath
        );

        ActivityLog::log('members', 'lease_create', "Set up lease details for tenant id {$memberId}");
        Flash::set('success', 'Lease details saved.');
        header("Location: /members/{$memberId}");
        exit;
    }

    public function updateLease(string $id): void
    {
        $this->verifyCsrf();

        $tenant = Tenant::find((int) $id);
        $tenantMember = $tenant ? Member::find((int) $tenant['member_id']) : null;
        if (!$tenant || !$tenantMember || (int) $tenantMember['society_id'] !== Society::currentId()) {
            Flash::set('error', 'Lease record not found.');
            header('Location: /members');
            exit;
        }

        $ownerMemberId = (int) ($_POST['owner_member_id'] ?? 0);
        $owner = $ownerMemberId > 0 ? Member::find($ownerMemberId) : null;
        if ($ownerMemberId <= 0 || !$owner || (int) $owner['society_id'] !== Society::currentId() || (int) $owner['flat_id'] !== (int) $tenantMember['flat_id'] || $owner['member_type'] !== 'owner' || $owner['status'] !== 'active') {
            Flash::set('error', 'A valid flat owner is required.');
            header("Location: /members/{$tenant['member_id']}");
            exit;
        }

        try {
            $docPath = FileUpload::storeDocument($_FILES['agreement_doc'] ?? [], 'documents');
        } catch (\RuntimeException $e) {
            Flash::set('error', $e->getMessage());
            header("Location: /members/{$tenant['member_id']}");
            exit;
        }

        Tenant::update((int) $id, $ownerMemberId, $_POST['lease_start'] ?? null, $_POST['lease_end'] ?? null);
        if ($docPath !== null) {
            Tenant::updateAgreementDoc((int) $id, $docPath);
        }

        ActivityLog::log('members', 'lease_update', "Updated lease details (tenant record id {$id})");
        Flash::set('success', 'Lease details updated.');
        header("Location: /members/{$tenant['member_id']}");
        exit;
    }

    /**
     * Streams an uploaded lease agreement after the route's own auth+permission
     * middleware has already run — same "never linked directly" pattern as
     * MemberController::serveDocument().
     */
    public function serveLeaseDocument(string $id): void
    {
        $tenant = Tenant::find((int) $id);
        $tenantMember = $tenant ? Member::find((int) $tenant['member_id']) : null;
        if (!$tenant || !$tenantMember || (int) $tenantMember['society_id'] !== Society::currentId() || empty($tenant['agreement_doc_path'])) {
            http_response_code(404);
            exit('Not found.');
        }

        $fullPath = dirname(__DIR__, 2) . '/uploads/' . $tenant['agreement_doc_path'];
        if (!is_file($fullPath)) {
            http_response_code(404);
            exit('Not found.');
        }

        $contentTypes = ['jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png', 'pdf' => 'application/pdf'];
        $extension = strtolower((string) pathinfo($fullPath, PATHINFO_EXTENSION));

        header('Content-Type: ' . ($contentTypes[$extension] ?? 'application/octet-stream'));
        header('Content-Length: ' . filesize($fullPath));
        header('X-Content-Type-Options: nosniff');
        readfile($fullPath);
        exit;
    }

    private function verifyCsrf(): void
    {
        if (!Csrf::verify($_POST['_csrf'] ?? null)) {
            http_response_code(419);
            exit('Session expired. Go back and try again.');
        }
    }
}
