<?php

namespace sqless\Http\Controllers;

class DownloadsController extends Controller {

    public function action($platform = '') {
        switch ($platform) {
            case 'mobile':
                return redirect()->away('https://play.google.com/store/apps/details?id=com.sqless.sqlessmobile');
            case 'desktop':
                $headers = array(
                    'Content-type: application/java-archive',
                    'Content-Disposition: attachment; filename="sqless_stable.jar"'
                );
                $file = storage_path('download/sqless_stable.jar');
                return response()->download($file, 'sqless_stable.jar', $headers);
            default:
                return abort(404);
        }
    }
}