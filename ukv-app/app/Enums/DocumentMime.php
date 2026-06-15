<?php

namespace App\Enums;

/**
 * Allowed document MIME types.
 * from: wp_check_filetype_and_ext / UKV_DOC_UPLOAD_ALLOWED (jpg/jpeg -> image/jpeg).
 */
enum DocumentMime: string
{
    case Jpeg = 'image/jpeg';
    case Png = 'image/png';
    case Pdf = 'application/pdf';
    case Heic = 'image/heic';
}
