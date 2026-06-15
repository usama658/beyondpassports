<?php

namespace App\Enums;

/**
 * Who uploaded a document.
 * from: journey agent on upload.
 */
enum DocumentUploadedBy: string
{
    case Customer = 'customer';
    case Agent = 'agent';
}
