<?php
declare(strict_types=1);

namespace Krate\Core\Validation\Rules;

interface RuleInterface
{
    public function validate($value): bool;
    public function getMessage(): string;
} 