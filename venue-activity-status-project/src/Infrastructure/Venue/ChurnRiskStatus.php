<?php

declare(strict_types=1);

namespace Infrastructure\Venue;

enum ChurnRiskStatus: string
{
    case IGNORED = 'ignored';
    case LOW = 'low';
    case MEDIUM = 'medium';
    case HIGH = 'high';

    public function shouldNotBeNotified(): bool
    {
        return $this === self::IGNORED || $this === self::LOW;
    }

    private function getTextIcon(): string
    {
        return $this === self::HIGH ? ':red_circle:' : ':large_yellow_circle:';
    }
    
    public function getText(): string
    {
        return sprintf('%s Anti churn alert %s', $this->getTextIcon(), $this->getTextIcon());
    }

    public function getColor(): string
    {
        return $this === self::HIGH ? '#FF0000' : '#FFFF00';
    }
}
