<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Helpers\Auth;
use App\Helpers\Csrf;
use App\Helpers\Flash;
use App\Models\Delivery;
use App\Models\Flat;
use App\Models\Society;
use App\Models\Visitor;
use App\Models\VisitorPass;

final class VisitorController
{
    public function index(): void
    {
        $pageTitle = 'Visitor Register';
        $date = $_GET['date'] ?? date('Y-m-d');
        $visitors = Visitor::allForSociety(Society::currentId(), $date ?: null);
        $flats = Flat::allForSociety(Society::currentId());
        require __DIR__ . '/../Views/visitors/index.php';
    }

    public function store(): void
    {
        $this->verifyCsrf();

        $flatId = (int) ($_POST['flat_id'] ?? 0);
        $name = trim((string) ($_POST['name'] ?? ''));

        if ($flatId <= 0 || $name === '') {
            Flash::set('error', 'Flat and visitor name are required.');
            header('Location: /visitors');
            exit;
        }

        Visitor::create(Society::currentId(), [
            'flat_id' => $flatId,
            'name' => $name,
            'phone' => trim((string) ($_POST['phone'] ?? '')),
            'purpose' => trim((string) ($_POST['purpose'] ?? '')),
            'approval_status' => 'pending',
            'logged_by' => Auth::id(),
        ]);

        Flash::set('success', "Visitor \"{$name}\" logged, awaiting approval.");
        header('Location: /visitors');
        exit;
    }

    public function approve(string $id): void
    {
        $this->verifyCsrf();
        Visitor::setApprovalStatus((int) $id, 'approved', null);
        Flash::set('success', 'Visitor approved.');
        header('Location: /visitors');
        exit;
    }

    public function reject(string $id): void
    {
        $this->verifyCsrf();
        Visitor::setApprovalStatus((int) $id, 'rejected', null);
        Flash::set('success', 'Visitor rejected.');
        header('Location: /visitors');
        exit;
    }

    public function checkout(string $id): void
    {
        $this->verifyCsrf();
        Visitor::checkOut((int) $id);
        Flash::set('success', 'Visitor checked out.');
        header('Location: /visitors');
        exit;
    }

    public function passes(): void
    {
        $pageTitle = 'Visitor Passes';
        $passes = VisitorPass::allForSociety(Society::currentId());
        $flats = Flat::allForSociety(Society::currentId());
        require __DIR__ . '/../Views/visitors/passes.php';
    }

    public function storePass(): void
    {
        $this->verifyCsrf();

        $flatId = (int) ($_POST['flat_id'] ?? 0);
        $visitorName = trim((string) ($_POST['visitor_name'] ?? ''));
        $validFrom = $_POST['valid_from'] ?? '';
        $validUntil = $_POST['valid_until'] ?? '';

        if ($flatId <= 0 || $visitorName === '' || !$validFrom || !$validUntil) {
            Flash::set('error', 'Flat, visitor name, and a valid date range are required.');
            header('Location: /visitors/passes');
            exit;
        }

        if (strtotime($validUntil) < strtotime($validFrom)) {
            Flash::set('error', 'Valid-until must be after valid-from.');
            header('Location: /visitors/passes');
            exit;
        }

        VisitorPass::create([
            'flat_id' => $flatId,
            'visitor_name' => $visitorName,
            'valid_from' => $validFrom,
            'valid_until' => $validUntil,
            'created_by' => Auth::id(),
        ]);

        Flash::set('success', "Pass created for \"{$visitorName}\".");
        header('Location: /visitors/passes');
        exit;
    }

    public function verifyPass(): void
    {
        $this->verifyCsrf();

        $token = strtoupper(trim((string) ($_POST['token'] ?? '')));
        $pass = $token !== '' ? VisitorPass::findByToken($token) : null;

        if (!$pass) {
            Flash::set('error', "No pass found for token \"{$token}\".");
            header('Location: /visitors/passes');
            exit;
        }

        if ($pass['used_at'] !== null) {
            Flash::set('error', "Pass for \"{$pass['visitor_name']}\" was already used on {$pass['used_at']}.");
            header('Location: /visitors/passes');
            exit;
        }

        $now = time();
        if ($now < strtotime((string) $pass['valid_from']) || $now > strtotime((string) $pass['valid_until'])) {
            Flash::set('error', "Pass for \"{$pass['visitor_name']}\" is outside its valid window.");
            header('Location: /visitors/passes');
            exit;
        }

        VisitorPass::markUsed((int) $pass['id']);
        Visitor::create(Society::currentId(), [
            'flat_id' => $pass['flat_id'],
            'name' => $pass['visitor_name'],
            'phone' => '',
            'purpose' => 'Pre-authorized visitor pass',
            'approval_status' => 'approved',
            'logged_by' => Auth::id(),
        ]);

        Flash::set('success', "Pass verified. \"{$pass['visitor_name']}\" checked in for {$pass['wing_name']}-{$pass['flat_number']}.");
        header('Location: /visitors');
        exit;
    }

    public function deliveries(): void
    {
        $pageTitle = 'Delivery Register';
        $deliveries = Delivery::allForSociety(Society::currentId());
        $flats = Flat::allForSociety(Society::currentId());
        require __DIR__ . '/../Views/visitors/deliveries.php';
    }

    public function storeDelivery(): void
    {
        $this->verifyCsrf();

        $flatId = (int) ($_POST['flat_id'] ?? 0);
        if ($flatId <= 0) {
            Flash::set('error', 'Flat is required.');
            header('Location: /visitors/deliveries');
            exit;
        }

        Delivery::create(Society::currentId(), [
            'flat_id' => $flatId,
            'courier_company' => trim((string) ($_POST['courier_company'] ?? '')),
            'recipient_name' => trim((string) ($_POST['recipient_name'] ?? '')),
            'logged_by' => Auth::id(),
        ]);

        Flash::set('success', 'Delivery logged.');
        header('Location: /visitors/deliveries');
        exit;
    }

    public function collectDelivery(string $id): void
    {
        $this->verifyCsrf();
        Delivery::markCollected((int) $id);
        Flash::set('success', 'Delivery marked as collected.');
        header('Location: /visitors/deliveries');
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
