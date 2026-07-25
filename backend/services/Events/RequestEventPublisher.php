<?php

declare(strict_types=1);

namespace App\Services\Events;

class RequestEventPublisher
{
    private array $observers = [];

    public function subscribe(object $observer): void
    {
        $this->observers[] = $observer;
    }

    public function publish(string $event, array $payload): void
    {
        foreach ($this->observers as $observer) {
            if (method_exists($observer, 'handle')) {
                $observer->handle($event, $payload);
            }
        }
    }
}
