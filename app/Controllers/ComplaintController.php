<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Helpers\Auth;
use App\Helpers\Csrf;
use App\Helpers\Flash;
use App\Models\ActivityLog;
use App\Models\Complaint;
use App\Models\ComplaintCategory;
use App\Models\Member;
use App\Models\Society;

final class ComplaintController
{
    public function index(): void
    {
        $pageTitle = 'Complaints';
        $status = $_GET['status'] ?? '';
        $complaints = Complaint::allForSociety(Society::currentId(), $status ?: null);
        require __DIR__ . '/../Views/complaints/index.php';
    }

    public function create(): void
    {
        $pageTitle = 'Register Complaint';
        $members = Member::allForSociety(Society::currentId());
        $categories = ComplaintCategory::allForSociety(Society::currentId());
        require __DIR__ . '/../Views/complaints/create.php';
    }

    public function store(): void
    {
        $this->verifyCsrf();

        $memberId = (int) ($_POST['member_id'] ?? 0);
        $categoryId = (int) ($_POST['category_id'] ?? 0);
        $subject = trim((string) ($_POST['subject'] ?? ''));
        $priority = in_array($_POST['priority'] ?? '', ['low', 'medium', 'high'], true) ? $_POST['priority'] : 'medium';

        // The flat is never chosen independently — it's always the selected resident's own
        // flat, so a complaint can never end up tied to a flat the resident doesn't live in.
        $member = $memberId > 0 ? Member::find($memberId) : null;

        if (!$member || $categoryId <= 0 || $subject === '') {
            Flash::set('error', 'Resident, category, and subject are required.');
            header('Location: /complaints/create');
            exit;
        }

        $id = Complaint::create(Society::currentId(), [
            'flat_id' => $member['flat_id'],
            'member_id' => $memberId,
            'category_id' => $categoryId,
            'subject' => $subject,
            'description' => trim((string) ($_POST['description'] ?? '')),
            'priority' => $priority,
        ]);

        ActivityLog::log('complaints', 'create', "Registered complaint \"{$subject}\"");
        Flash::set('success', 'Complaint registered.');
        header("Location: /complaints/{$id}");
        exit;
    }

    public function show(string $id): void
    {
        $pageTitle = 'Complaint Detail';
        $complaint = Complaint::find((int) $id);
        if (!$complaint) {
            http_response_code(404);
            require __DIR__ . '/../Views/errors/404.php';
            return;
        }
        $updates = Complaint::updates((int) $id);
        require __DIR__ . '/../Views/complaints/show.php';
    }

    public function addUpdate(string $id): void
    {
        $this->verifyCsrf();

        $status = $_POST['status'] ?? '';
        if (!in_array($status, ['open', 'in_progress', 'resolved', 'closed'], true)) {
            Flash::set('error', 'Invalid status.');
            header("Location: /complaints/{$id}");
            exit;
        }

        Complaint::addUpdate(
            (int) $id,
            $status,
            trim((string) ($_POST['remarks'] ?? '')) ?: null,
            Auth::id(),
            null
        );

        ActivityLog::log('complaints', 'status_update', "Complaint id {$id} status set to \"{$status}\"");
        Flash::set('success', 'Complaint updated.');
        header("Location: /complaints/{$id}");
        exit;
    }

    public function categories(): void
    {
        $pageTitle = 'Complaint Categories';
        $categories = ComplaintCategory::allForSociety(Society::currentId());
        require __DIR__ . '/../Views/complaints/categories.php';
    }

    public function storeCategory(): void
    {
        $this->verifyCsrf();

        $name = trim((string) ($_POST['name'] ?? ''));
        if ($name === '') {
            Flash::set('error', 'Category name is required.');
        } else {
            ComplaintCategory::create(Society::currentId(), $name);
            Flash::set('success', "Category \"{$name}\" added.");
        }
        header('Location: /complaints/categories');
        exit;
    }

    public function updateCategory(string $id): void
    {
        $this->verifyCsrf();

        $name = trim((string) ($_POST['name'] ?? ''));
        if ($name === '') {
            Flash::set('error', 'Category name is required.');
        } else {
            ComplaintCategory::update((int) $id, $name);
            Flash::set('success', 'Category updated.');
        }
        header('Location: /complaints/categories');
        exit;
    }

    public function deleteCategory(string $id): void
    {
        $this->verifyCsrf();

        try {
            ComplaintCategory::delete((int) $id);
            Flash::set('success', 'Category deleted.');
        } catch (\PDOException $e) {
            Flash::set('error', 'This category has complaints logged against it and cannot be deleted.');
        }

        header('Location: /complaints/categories');
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
