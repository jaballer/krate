<?php

namespace App\Enums;

enum RecordSpeed: string
{
    case Rpm33 = '33 1/3 RPM';
    case Rpm45 = '45 RPM';
    case Rpm78 = '78 RPM';
}
