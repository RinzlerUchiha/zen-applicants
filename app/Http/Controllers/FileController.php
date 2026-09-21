<?php

namespace App\Http\Controllers;

use App\Services\FileService;

class FileController extends Controller
{
    // Serve an asset (logo, background, etc.)
    public function serve($src, $filename)
    {
        // abort(404, "Not found.");
        $source = [
            'app-img' => 'applicant/images',
            'license' => 'applicant/licenses',
            'certificate' => 'applicant/certificates',
            'contract' => 'applicant/contracts',
            // The assessment question images are served only during a running
            // attempt, by AssessmentController::image().
        ];

        // Path inside storage
        $path = ($source[$src] ?? '') . '/' . $filename;
        
        // use serveAsset($filename, 's3') for production
        return FileService::serveFile($path);
    }
}
