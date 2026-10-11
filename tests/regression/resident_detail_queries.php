<?php
declare(strict_types=1);

$root = dirname(__DIR__, 2);
$family = file_get_contents($root . '/app/Models/FamilyMember.php');
$document = file_get_contents($root . '/app/Models/Document.php');
$controller = file_get_contents($root . '/app/Controllers/MemberController.php');

$checks = [
    'family members query qualifies the name sort column' => str_contains($family, 'ORDER BY fm.name'),
    'resident documents query joins the member for society scoping' => str_contains($document, 'JOIN members m ON m.id = d.member_id') && str_contains($document, 'm.society_id = :sid'),
    'resident detail loads family and documents through the fixed queries' => str_contains($controller, 'FamilyMember::forMember((int) $id)') && str_contains($controller, 'Document::forMember((int) $id)'),
];

$failed = false;
foreach ($checks as $name => $passed) {
    printf("[%s] %s\\n", $passed ? 'PASS' : 'FAIL', $name);
    $failed = $failed || !$passed;
}
if ($failed) {
    fwrite(STDERR, "Resident detail query regression detected.\\n");
    exit(1);
}
echo "Resident detail query regression contract passed.\\n";
