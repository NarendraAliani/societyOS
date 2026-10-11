<?php
declare(strict_types=1);

$root = dirname(__DIR__, 2);
$flat = file_get_contents($root . '/app/Models/Flat.php');
$controller = file_get_contents($root . '/app/Controllers/MemberController.php');
$view = file_get_contents($root . '/app/Views/members/create.php');

$checks = [
    'resident form requests only unassigned flats' => str_contains($controller, 'Flat::availableForResidents(Society::currentId())') && !str_contains($controller, '$flats = Flat::allForSociety'),
    'available flats exclude flats already linked to residents' => str_contains($flat, 'public static function availableForResidents') && str_contains($flat, 'NOT EXISTS (') && str_contains($flat, 'FROM members m') && str_contains($flat, 'm.flat_id = f.id'),
    'resident creation rejects an already assigned flat server-side' => str_contains($controller, '$availableFlatIds') && str_contains($controller, 'This flat is already assigned to a resident.'),
    'flat dropdown is populated from the filtered flat list' => str_contains($view, 'foreach ($flats as $flat)'),
];

$failed = false;
foreach ($checks as $name => $passed) {
    printf("[%s] %s\\n", $passed ? 'PASS' : 'FAIL', $name);
    $failed = $failed || !$passed;
}
if ($failed) {
    fwrite(STDERR, "Resident flat assignment regression detected.\\n");
    exit(1);
}
echo "Resident flat assignment regression contract passed.\\n";
