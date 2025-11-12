<?php

namespace App\Enums;

enum GameStatus: string {
    case DRAFT = 'draft';
    case ACTIVE = 'active';
    case FINALIZING = 'finalizing';
    case FINISHED = 'finished';
}
?>