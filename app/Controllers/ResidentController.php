<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Helpers\Auth;
use App\Helpers\Csrf;
use App\Helpers\Flash;
use App\Models\Complaint;
use App\Models\EmergencyContact;
use App\Models\FamilyMember;
use App\Models\MaintenanceBill;
use App\Models\Member;
use App\Models\Notice;
use App\Models\VisitorPass;
use App\Models\Society;

final class ResidentController
{
    public function home(): void
    {
        $member = $this->member();
        $pageTitle = 'My Home';
        $bills = MaintenanceBill::forMember((int) $member['id']);
        $outstanding = MaintenanceBill::outstandingForMember((int) $member['id']);
        $notices = Notice::activeForSociety(Society::currentId(), 5);
        $complaints = Complaint::forMember((int) $member['id']);
        $passes = VisitorPass::forFlat((int) $member['flat_id']);

        require __DIR__ . '/../Views/resident/home.php';
    }

    public function family(): void
    {
        $member = $this->member();
        $pageTitle = 'My Family';
        $familyMembers = FamilyMember::forMember((int) $member['id']);
        $emergencyContacts = EmergencyContact::forMember((int) $member['id']);
        require __DIR__ . '/../Views/resident/family.php';
    }

    public function storeFamilyMember(): void
    {
        $this->verifyCsrf();
        $member = $this->member();
        $name = trim((string) ($_POST['name'] ?? ''));
        $relation = trim((string) ($_POST['relation'] ?? '')) ?: null;
        $dob = trim((string) ($_POST['date_of_birth'] ?? '')) ?: null;
        $age = is_numeric($_POST['age'] ?? '') ? (int) $_POST['age'] : null;
        if ($name === '') {
            Flash::set('error', 'Family member name is required.');
            header('Location: /resident/family');
            exit;
        }
        if ($dob !== null && strtotime($dob) > time()) {
            Flash::set('error', 'Date of birth cannot be in the future.');
            header('Location: /resident/family');
            exit;
        }
        FamilyMember::create((int) $member['id'], $name, $relation, $dob, $age, trim((string) ($_POST['phone'] ?? '')) ?: null);
        Flash::set('success', 'Family member added.');
        header('Location: /resident/family');
        exit;
    }

    public function updateFamilyMember(string $id): void
    {
        $this->verifyCsrf();
        $this->member();
        $familyMember = FamilyMember::find((int) $id);
        if (!$familyMember || (int) $familyMember['member_id'] !== (int) Auth::memberId()) {
            http_response_code(404);
            require __DIR__ . '/../Views/errors/404.php';
            return;
        }

        $name = trim((string) ($_POST['name'] ?? ''));
        $relation = trim((string) ($_POST['relation'] ?? '')) ?: null;
        $dob = trim((string) ($_POST['date_of_birth'] ?? '')) ?: null;
        if ($name === '') {
            Flash::set('error', 'Family member name is required.');
            header('Location: /resident/family');
            exit;
        }
        if ($dob !== null && strtotime($dob) > time()) {
            Flash::set('error', 'Date of birth cannot be in the future.');
            header('Location: /resident/family');
            exit;
        }

        FamilyMember::update(
            (int) $id,
            $name,
            $relation,
            $dob,
            is_numeric($_POST['age'] ?? '') ? (int) $_POST['age'] : null,
            trim((string) ($_POST['phone'] ?? '')) ?: null
        );
        Flash::set('success', 'Family member updated.');
        header('Location: /resident/family');
        exit;
    }

    public function deleteFamilyMember(string $id): void
    {
        $this->verifyCsrf();
        $this->member();
        $familyMember = FamilyMember::find((int) $id);
        if (!$familyMember || (int) $familyMember['member_id'] !== (int) Auth::memberId()) {
            http_response_code(404);
            require __DIR__ . '/../Views/errors/404.php';
            return;
        }
        FamilyMember::delete((int) $id);
        Flash::set('success', 'Family member removed.');
        header('Location: /resident/family');
        exit;
    }

    public function storeEmergencyContact(): void
    {
        $this->verifyCsrf();
        $member = $this->member();
        $name = trim((string) ($_POST['name'] ?? ''));
        $phone = trim((string) ($_POST['phone'] ?? ''));
        if ($name === '' || $phone === '') {
            Flash::set('error', 'Emergency contact name and phone are required.');
            header('Location: /resident/family');
            exit;
        }
        EmergencyContact::create((int) $member['id'], $name, trim((string) ($_POST['relation'] ?? '')) ?: null, $phone);
        Flash::set('success', 'Emergency contact added.');
        header('Location: /resident/family');
        exit;
    }

    public function updateEmergencyContact(string $id): void
    {
        $this->verifyCsrf();
        $this->member();
        $contact = EmergencyContact::find((int) $id);
        if (!$contact || (int) $contact['member_id'] !== (int) Auth::memberId()) {
            http_response_code(404);
            require __DIR__ . '/../Views/errors/404.php';
            return;
        }

        $name = trim((string) ($_POST['name'] ?? ''));
        $phone = trim((string) ($_POST['phone'] ?? ''));
        if ($name === '' || $phone === '') {
            Flash::set('error', 'Emergency contact name and phone are required.');
            header('Location: /resident/family');
            exit;
        }

        EmergencyContact::update($id = (int) $id, $name, trim((string) ($_POST['relation'] ?? '')) ?: null, $phone);
        Flash::set('success', 'Emergency contact updated.');
        header('Location: /resident/family');
        exit;
    }

    public function deleteEmergencyContact(string $id): void
    {
        $this->verifyCsrf();
        $this->member();
        $contact = EmergencyContact::find((int) $id);
        if (!$contact || (int) $contact['member_id'] !== (int) Auth::memberId()) {
            http_response_code(404);
            require __DIR__ . '/../Views/errors/404.php';
            return;
        }
        EmergencyContact::delete((int) $id);
        Flash::set('success', 'Emergency contact removed.');
        header('Location: /resident/family');
        exit;
    }

    public function bills(): void
    {
        $member = $this->member();
        $pageTitle = 'My Maintenance Bills';
        $bills = MaintenanceBill::forMember((int) $member['id']);
        require __DIR__ . '/../Views/resident/bills.php';
    }

    public function notices(): void
    {
        $pageTitle = 'Society Notices';
        $notices = Notice::activeForSociety(Society::currentId(), 50);
        require __DIR__ . '/../Views/resident/notices.php';
    }

    public function complaints(): void
    {
        $member = $this->member();
        $pageTitle = 'My Complaints';
        $complaints = Complaint::forMember((int) $member['id']);
        $categories = Complaint::categoriesForSociety(Society::currentId());
        require __DIR__ . '/../Views/resident/complaints.php';
    }

    public function storeComplaint(): void
    {
        $this->verifyCsrf();
        $member = $this->member();

        $categoryId = (int) ($_POST['category_id'] ?? 0);
        $subject = trim((string) ($_POST['subject'] ?? ''));
        $description = trim((string) ($_POST['description'] ?? ''));
        $priority = in_array($_POST['priority'] ?? '', ['low', 'medium', 'high'], true) ? $_POST['priority'] : 'medium';

        if ($categoryId <= 0 || $subject === '') {
            Flash::set('error', 'Category and subject are required.');
            header('Location: /resident/complaints');
            exit;
        }

        $validCategory = false;
        foreach (Complaint::categoriesForSociety(Society::currentId()) as $category) {
            if ((int) $category['id'] === $categoryId) {
                $validCategory = true;
                break;
            }
        }
        if (!$validCategory) {
            Flash::set('error', 'Invalid complaint category.');
            header('Location: /resident/complaints');
            exit;
        }

        Complaint::create(Society::currentId(), [
            'flat_id' => (int) $member['flat_id'],
            'member_id' => (int) $member['id'],
            'category_id' => $categoryId,
            'subject' => $subject,
            'description' => $description,
            'priority' => $priority,
        ]);

        Flash::set('success', 'Complaint submitted successfully.');
        header('Location: /resident/complaints');
        exit;
    }

    public function visitorPasses(): void
    {
        $member = $this->member();
        $pageTitle = 'My Visitor Passes';
        $passes = VisitorPass::forFlat((int) $member['flat_id']);
        require __DIR__ . '/../Views/resident/visitor_passes.php';
    }

    public function storeVisitorPass(): void
    {
        $this->verifyCsrf();
        $member = $this->member();

        $visitorName = trim((string) ($_POST['visitor_name'] ?? ''));
        $validFrom = trim((string) ($_POST['valid_from'] ?? ''));
        $validUntil = trim((string) ($_POST['valid_until'] ?? ''));

        if ($visitorName === '' || $validFrom === '' || $validUntil === '') {
            Flash::set('error', 'Visitor name and validity window are required.');
            header('Location: /resident/visitor-passes');
            exit;
        }

        if (strtotime($validUntil) < strtotime($validFrom)) {
            Flash::set('error', 'Valid-until must be after valid-from.');
            header('Location: /resident/visitor-passes');
            exit;
        }

        VisitorPass::create([
            'flat_id' => (int) $member['flat_id'],
            'visitor_name' => $visitorName,
            'valid_from' => $validFrom,
            'valid_until' => $validUntil,
            'created_by' => Auth::id(),
        ]);

        Flash::set('success', 'Visitor pass created. Share the token with your visitor.');
        header('Location: /resident/visitor-passes');
        exit;
    }

    private function member(): array
    {
        $memberId = Auth::memberId();
        if ($memberId === null) {
            http_response_code(403);
            require __DIR__ . '/../Views/errors/403.php';
            exit;
        }

        $member = Member::find($memberId);
        if (!$member || (int) $member['society_id'] !== (int) $_SESSION['society_id'] || $member['status'] !== 'active') {
            http_response_code(403);
            require __DIR__ . '/../Views/errors/403.php';
            exit;
        }

        return $member;
    }

    private function verifyCsrf(): void
    {
        if (!Csrf::verify($_POST['_csrf'] ?? null)) {
            http_response_code(419);
            exit('Session expired. Go back and try again.');
        }
    }
}
