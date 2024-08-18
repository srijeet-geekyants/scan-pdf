<?php

namespace App\Jobs;

use App\Models\OcrFiles;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Imagick;

class PDFToImageJob implements ShouldQueue
{
    use Queueable;

    private int $ocrFileId;
    /**
     * Create a new job instance.
     */
    public function __construct($ocrFileId)
    {
        $this->ocrFileId = $ocrFileId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $imagick = new Imagick();
            $ocrFile = OcrFiles::find($this->ocrFileId);
            if(!$ocrFile) {
                return;
            }
            $pdfPathFromDB = $ocrFile->getAttributes()['pdf_file_path'];
            $pdfPath = Storage::path($pdfPathFromDB);
            $pdfPathToOpen = Storage::path('public/'.$pdfPathFromDB);
            $pathInfo = pathinfo($pdfPath);


            $imagick->setResolution(200, 200);
            $imagick->readImage($pdfPathToOpen.'[0]');
            $imagick->setImageCompressionQuality(100);
            $imagick->setImageBackgroundColor('white');
            $imagick->setImageAlphaChannel(Imagick::ALPHACHANNEL_REMOVE);
            $imagick->mergeImageLayers(Imagick::LAYERMETHOD_FLATTEN);
            $imagick->sharpenImage(0, 1.0);

            //        $width = $imagick->getImageWidth();
            //        $height = $imagick->getImageHeight();

            // autorotate image
            //$this->autoRotateImage($imagick);

            /*$width = $imagick->getImageWidth();
            $height = $imagick->getImageHeight();
            \Log::info('image width');
            \Log::info($width);
            \Log::info('image height');
            \Log::info($height);*/


            // crop image to Q4 quandrant
            // job3
            //job2 -> job3
            /*$cropStartX = floor($width / 2);
            $cropStartY = floor($height / 2);
            $cropWidth = $cropStartX;
            $cropHeight = $cropStartY;*/

            //$imagick->cropImage($cropWidth, $cropHeight, $cropStartX, $cropStartY);

            $imagick->setImageFormat('jpeg');
            $outputPath = $pathInfo['dirname'].'/'.$pathInfo['filename'].'.jpg';
            $imagick->writeImage($outputPath);
            $ocrFile->img_file_path = $outputPath;
            //$ocrFile->status = "pending_drawing";
            $ocrFile->save();

            dispatch(new UpdateOCRProjectFileDataJob($ocrFile->id));
        } catch(\Exception $ex) {
            \Log::info($ex->getMessage());
            \Log::info($ex->getTraceAsString());
            $ocrFile->status = 'error';
            $ocrFile->save();
        }
        $imagick->clear();
        $imagick->destroy();
    }

    private function autoRotateImage($image) {
        $orientation = $image->getImageOrientation();
        switch($orientation) {
            case imagick::ORIENTATION_UNDEFINED:
                if($image->getImageWidth() < $image->getImageHeight()) {
                    $image->rotateimage("#000", 90);
                }
                break;
            case imagick::ORIENTATION_BOTTOMRIGHT:
                $image->rotateimage("#000", 180); // rotate 180 degrees
                break;
            case imagick::ORIENTATION_RIGHTTOP:
                $image->rotateimage("#000", 90); // rotate 90 degrees CW
                break;
            case imagick::ORIENTATION_LEFTBOTTOM:
                $image->rotateimage("#000", -90); // rotate 90 degrees CCW
                break;
        }
        // Now that it's auto-rotated, make sure the EXIF data is correct in case the EXIF gets saved with the image!
        $image->setImageOrientation(imagick::ORIENTATION_TOPLEFT);
    }
}
