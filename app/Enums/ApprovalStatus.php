<?php

namespace App\Enums;

enum ApprovalStatus: string
{
    case Draft = 'draft';
    case PendingSupervisor = 'pending_supervisor';
    case PendingManager = 'pending_manager';
    case PendingDirector = 'pending_director';
    case Approved = 'approved';
    case Rejected = 'rejected';
}
