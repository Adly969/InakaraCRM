<?php

namespace App\Enums;

enum OpportunityStatus: string
{
    case Qualification = 'qualification';
    case Discovery = 'discovery';
    case Proposal = 'proposal';
    case Negotiation = 'negotiation';
    case VerbalCommit = 'verbal_commit';
    case Won = 'won';
    case Lost = 'lost';

    /**
     * Get the human-readable display name.
     */
    public function label(): string
    {
        return match ($this) {
            self::Qualification => 'Qualification',
            self::Discovery => 'Discovery',
            self::Proposal => 'Proposal',
            self::Negotiation => 'Negotiation',
            self::VerbalCommit => 'Verbal Commit',
            self::Won => 'Won',
            self::Lost => 'Lost',
        };
    }

    /**
     * Get the display color for UI badges.
     */
    public function color(): string
    {
        return match ($this) {
            self::Qualification => 'gray',
            self::Discovery => 'blue',
            self::Proposal => 'indigo',
            self::Negotiation => 'amber',
            self::VerbalCommit => 'emerald',
            self::Won => 'green',
            self::Lost => 'red',
        };
    }

    /**
     * Get the default win probability for this stage.
     */
    public function defaultProbability(): int
    {
        return match ($this) {
            self::Qualification => 10,
            self::Discovery => 20,
            self::Proposal => 50,
            self::Negotiation => 80,
            self::VerbalCommit => 90,
            self::Won => 100,
            self::Lost => 0,
        };
    }

    /**
     * Whether this status represents a closed state.
     */
    public function isClosed(): bool
    {
        return in_array($this, [self::Won, self::Lost]);
    }
}
