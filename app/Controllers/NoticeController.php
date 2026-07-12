<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Helpers\Auth;
use App\Helpers\Csrf;
use App\Helpers\Flash;
use App\Models\Event;
use App\Models\Member;
use App\Models\Notice;
use App\Models\Poll;
use App\Models\Society;

final class NoticeController
{
    public function index(): void
    {
        $pageTitle = 'Notice Board';
        $notices = Notice::allForSociety(Society::currentId());
        require __DIR__ . '/../Views/notices/index.php';
    }

    public function store(): void
    {
        $this->verifyCsrf();

        $title = trim((string) ($_POST['title'] ?? ''));
        $body = trim((string) ($_POST['body'] ?? ''));
        $type = ($_POST['notice_type'] ?? '') === 'circular' ? 'circular' : 'notice';

        if ($title === '' || $body === '') {
            Flash::set('error', 'Title and body are required.');
            header('Location: /notices');
            exit;
        }

        Notice::create(Society::currentId(), [
            'title' => $title,
            'body' => $body,
            'notice_type' => $type,
            'published_by' => Auth::id(),
            'expires_at' => $_POST['expires_at'] ?? '',
        ]);

        Flash::set('success', "\"{$title}\" published.");
        header('Location: /notices');
        exit;
    }

    public function destroy(string $id): void
    {
        $this->verifyCsrf();
        Notice::delete((int) $id);
        Flash::set('success', 'Notice removed.');
        header('Location: /notices');
        exit;
    }

    public function events(): void
    {
        $pageTitle = 'Events';
        $events = Event::allForSociety(Society::currentId());
        require __DIR__ . '/../Views/notices/events.php';
    }

    public function storeEvent(): void
    {
        $this->verifyCsrf();

        $title = trim((string) ($_POST['title'] ?? ''));
        $startsAt = $_POST['starts_at'] ?? '';

        if ($title === '' || !$startsAt) {
            Flash::set('error', 'Title and start date/time are required.');
            header('Location: /notices/events');
            exit;
        }

        Event::create(Society::currentId(), [
            'title' => $title,
            'description' => trim((string) ($_POST['description'] ?? '')),
            'venue' => trim((string) ($_POST['venue'] ?? '')),
            'starts_at' => $startsAt,
            'ends_at' => $_POST['ends_at'] ?? '',
            'created_by' => Auth::id(),
        ]);

        Flash::set('success', "Event \"{$title}\" created.");
        header('Location: /notices/events');
        exit;
    }

    public function deleteEvent(string $id): void
    {
        $this->verifyCsrf();
        Event::delete((int) $id);
        Flash::set('success', 'Event removed.');
        header('Location: /notices/events');
        exit;
    }

    public function polls(): void
    {
        $pageTitle = 'Polls';
        $polls = Poll::allForSociety(Society::currentId());
        require __DIR__ . '/../Views/notices/polls.php';
    }

    public function storePoll(): void
    {
        $this->verifyCsrf();

        $question = trim((string) ($_POST['question'] ?? ''));
        $options = array_filter(array_map('trim', $_POST['options'] ?? []), fn ($o) => $o !== '');

        if ($question === '' || count($options) < 2) {
            Flash::set('error', 'A question and at least 2 options are required.');
            header('Location: /notices/polls');
            exit;
        }

        Poll::create(Society::currentId(), $question, $_POST['closes_at'] ?: null, Auth::id(), $options);

        Flash::set('success', 'Poll created.');
        header('Location: /notices/polls');
        exit;
    }

    public function showPoll(string $id): void
    {
        $pageTitle = 'Poll Detail';
        $poll = Poll::find((int) $id);
        if (!$poll) {
            http_response_code(404);
            require __DIR__ . '/../Views/errors/404.php';
            return;
        }
        $options = Poll::optionsWithVoteCounts((int) $id);
        $members = Member::allForSociety(Society::currentId());
        require __DIR__ . '/../Views/notices/poll_detail.php';
    }

    public function vote(string $id): void
    {
        $this->verifyCsrf();

        $optionId = (int) ($_POST['poll_option_id'] ?? 0);
        $memberId = (int) ($_POST['member_id'] ?? 0);

        if ($optionId <= 0 || $memberId <= 0) {
            Flash::set('error', 'A resident and an option are required to vote.');
            header("Location: /notices/polls/{$id}");
            exit;
        }

        Poll::vote((int) $id, $optionId, $memberId);

        Flash::set('success', 'Vote recorded.');
        header("Location: /notices/polls/{$id}");
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
