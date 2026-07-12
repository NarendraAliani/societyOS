<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Helpers\Csrf;
use App\Helpers\Flash;
use App\Models\ActivityLog;
use App\Models\EmergencyContact;
use App\Models\FamilyMember;
use App\Models\Flat;
use App\Models\Member;
use App\Models\Society;
use App\Models\Vehicle;

final class MemberController
{
    public function index(): void
    {
        $pageTitle = 'Residents';
        $members = Member::allForSociety(Society::currentId());
        require __DIR__ . '/../Views/members/index.php';
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

        if ($name === '' || $phone === '' || $flatId <= 0) {
            Flash::set('error', 'Name, phone, and flat are required.');
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
        if (!$member) {
            http_response_code(404);
            require __DIR__ . '/../Views/errors/404.php';
            return;
        }
        $familyMembers = FamilyMember::forMember((int) $id);
        $emergencyContacts = EmergencyContact::forMember((int) $id);
        $vehicles = Vehicle::forMember((int) $id);
        require __DIR__ . '/../Views/members/show.php';
    }

    public function update(string $id): void
    {
        $this->verifyCsrf();

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
        Member::delete((int) $id);
        ActivityLog::log('members', 'delete', "Removed resident \"" . ($member['name'] ?? $id) . "\"");
        Flash::set('success', 'Resident removed.');
        header('Location: /members');
        exit;
    }

    public function storeFamilyMember(string $memberId): void
    {
        $this->verifyCsrf();

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
        FamilyMember::create(
            (int) $memberId,
            $name,
            trim((string) ($_POST['relation'] ?? '')) ?: null,
            $dob,
            $age,
            trim((string) ($_POST['phone'] ?? '')) ?: null
        );
        Flash::set('success', 'Family member added.');
        header("Location: /members/{$memberId}");
        exit;
    }

    public function deleteFamilyMember(string $id): void
    {
        $this->verifyCsrf();
        $familyMember = FamilyMember::find((int) $id);
        FamilyMember::delete((int) $id);
        Flash::set('success', 'Family member removed.');
        header('Location: /members/' . ($familyMember['member_id'] ?? ''));
        exit;
    }

    public function storeEmergencyContact(string $memberId): void
    {
        $this->verifyCsrf();

        $name = trim((string) ($_POST['name'] ?? ''));
        $phone = trim((string) ($_POST['phone'] ?? ''));
        if ($name === '' || $phone === '') {
            Flash::set('error', 'Name and phone are required.');
        } else {
            EmergencyContact::create((int) $memberId, $name, trim((string) ($_POST['relation'] ?? '')) ?: null, $phone);
            Flash::set('success', 'Emergency contact added.');
        }
        header("Location: /members/{$memberId}");
        exit;
    }

    public function deleteEmergencyContact(string $id): void
    {
        $this->verifyCsrf();
        $contact = EmergencyContact::find((int) $id);
        EmergencyContact::delete((int) $id);
        Flash::set('success', 'Emergency contact removed.');
        header('Location: /members/' . ($contact['member_id'] ?? ''));
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
