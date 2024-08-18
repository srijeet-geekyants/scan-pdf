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
use thiagoalessio\TesseractOCR\TesseractOCR;

class RunOcrOnImage implements ShouldQueue
{
    use Queueable;

    private $ocrFileId;
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
            $ocrFile = OcrFiles::find($this->ocrFileId);
            if(!$ocrFile) {
                return;
            }
            $ocrData = json_decode($ocrFile->ocr_data, 1);
            $imgPath = $ocrData['img_path'];
            $jpgToProcess = $imgPath;
            $width = $ocrData['coords']['w'];
            $height = $ocrData['coords']['h'];
            $x = $ocrData['coords']['x'];
            $y = $ocrData['coords']['y'];

            $imagick = new Imagick();
            $imagick->readImage(Storage::path($jpgToProcess));
            $imagick->cropImage($width, $height, $x, $y);
            $imagick->setImageFormat('jpeg');
            $imagick->setImageCompressionQuality(100);
            $imagick->writeImage(Storage::path($jpgToProcess));
            $imagick->clear();
            $imagick->destroy();

            try {
                $ocrOutput = (new TesseractOCR(Storage::path($jpgToProcess)))->run();
            }catch(\Exception $e) {
                \Log::error('-- Tessaract Job Failed -- ');
                \Log::info($e->getMessage());
                //$this->fail($e);
                $this->delete();
            }

            $sanitizeTextOcrOutput = self::sanitizeText($ocrOutput);

            $ocrData['ocr_text'] = $sanitizeTextOcrOutput;
            $ocrFile->ocr_data = json_encode($ocrData);
            $ocrFile->status = "completed";
            $ocrFile->save();
        }
    catch(\Exception $e) {
            $ocrFile->status = "error";
            $ocrFile->save();
    }

}

    private static function sanitizeText($text) {
        /*if($drawingNumber) {
            $exceptSPChars = preg_replace('/[^A-Za-z0-9.\\s]/', '', $text);
            $arrOfChars = str_split($exceptSPChars);
            $outputStr = '';
            $c = 0;
            foreach($arrOfChars as $chr) {
                if(is_numeric($chr) && $c === 0) {
                    $outputStr .= "-";
                    $outputStr .= $chr;
                    $c++;
                }elseif(is_int($chr) || is_string($chr)) {
                    $outputStr .= $chr;
                }elseif($chr === '.') {
                    $outputStr .= $chr;
                }
            }
            return trim($outputStr);
        } */
        return preg_replace("/[\('$%:*^&)@!=#]/", '', str_replace(array("‘",'“','”', "’"), "'", str_replace("—", '-', trim(preg_replace('/\\n/', ' ', $text)))));
    }
}
