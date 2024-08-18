<?php

namespace App\Http\Controllers;
use App\Helpers\ResponseHelper;
use App\Jobs\PDFToImageJob;
use App\Jobs\RunOcrOnImage;
use App\Models\OcrFiles;

use http\Client\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;

class OcrController extends Controller
{
    public function index(Request $request) {
        $ocrFiles = OcrFiles::get() ?? [];
        return view('ocr-file-index', compact('ocrFiles'));
    }

    public function upload(Request $request) {
        $file = $request->file('file');
        if(empty($file)) {
            Session::flash('alert-class', 'alert-danger');
            Session::flash('message', 'File not found !');
            return redirect()->back();
        }
        if($file->getSize() > 5000000) {
            Session::flash('alert-class', 'alert-danger');
            Session::flash('message', 'Largest File That Can be uploaded is 5MB !');
            return redirect()->back()->withErrors(['message' => 'Largest File That Can be uploaded is 5MB !']);
        }
        if($file->getClientMimeType() !== 'application/pdf') {
            Session::flash('alert-class', 'alert-danger');
            Session::flash('message', 'Only Pdf\'s are accepted !');
            return redirect()->back()->withErrors(['message' => 'Only Pdf\'s are accepted !']);
        }

        if(!File::isDirectory(Storage::path('temp-files'))) {
            File::makeDirectory(Storage::path('temp-files'), 0777, true, true);
        }

        $filePath = 'public/temp-files/'.pathinfo($file)['filename'].md5(now()).'.pdf';

        Storage::put($filePath, file_get_contents($file));

        $filePathToStore = explode('public/', $filePath)[1];

        $ocrfile = OcrFiles::create([
            'name' => pathinfo($filePath)['filename'],
            'pdf_file_path' => $filePathToStore,
            'status' => 'processing'
        ]);

        PDFToImageJob::dispatch($ocrfile->id);

        Session::flash('message', 'Uploaded Successfully ! Processing In progress !');

        return redirect()->back()->with(['message' => 'Uploaded Successfully !']);
    }

    public function openOcrPopup(Request $request) {
        if($request->type === "pending_drawing") {
            $ocrFile = OcrFiles::find($request->ocrFileId);
            $imgToshow = json_decode($ocrFile->ocr_data, 1)['img_path'];
            $ocrFilesPath = Storage::url($imgToshow);
            return view('pending-ocr-view', compact('ocrFile', 'ocrFilesPath'));
        }
    }

    public function saveCoordinates(Request $request) {
        $ocrFile = OcrFiles::find($request->ocrFileId);
        $ocrCoordinatesIsBlank = !($request->coords['x']) || !($request->coords['y']) || !($request->coords['w']) || !($request->coords['h']);
        if($ocrFile && !$ocrCoordinatesIsBlank) {
            $ocrData = json_decode($ocrFile->ocr_data, 1);
            $ocrData['coords'] = [
                'x' => $request->coords['x'] ?? '',
                'y' => $request->coords['y'] ?? '',
                'h' => $request->coords['h'] ?? '',
                'w' => $request->coords['w'] ?? '',
            ];
            if($ocrData['coordinates_submitted'] === 0) {
                $ocrData['coordinates_submitted'] = 1;
            }
            $ocrFile->ocr_data = json_encode($ocrData);
            $ocrFile->status = "processing";
            $ocrFile->save();
            dispatch(new RunOcrOnImage($ocrFile->id));
            return ResponseHelper::success('Coordinates saved successfully');
        }
        return ResponseHelper::errorWithMessageAndStatus('Something went wrong !', 400);
    }

    public function startOcrProcess(Request $request) {
        $ocrFile = OcrFiles::find($request->ocrFileId);
        if($ocrFile) {
            $ocrData = json_decode($ocrFile->ocr_data, 1);
            if($ocrData['coordinates_submitted'] !== 1) $ocrData['coordinates_submitted'] = 1;
            $ocrFile->ocr_data = json_encode($ocrData);
            $ocrFile->save();
            return ResponseHelper::success('Saved Successfully !');
        }
        return ResponseHelper::errorWithMessageAndStatus('Something went wrong !', 400);
    }
}
