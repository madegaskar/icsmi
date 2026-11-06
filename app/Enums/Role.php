<?php

namespace App\Enums;

enum Role: string
{
    case Admin = 'admin';
    case Editor = 'editor';
    case Reviewer = 'reviewer';
    case Author = 'author';

    public function label(): string
    {
        return ucfirst($this->value);
    }

    public function workspaceRoute(): string
    {
        return $this->value.'.dashboard';
    }
}
