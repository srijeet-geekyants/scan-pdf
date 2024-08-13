<?php

namespace App\Jobs;

use App\Constants\Projects\ProjectFileConstants;
use App\Models\OcrFiles;
use App\Models\ProjectFile;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class UpdateOCRProjectFileDataJob implements ShouldQueue
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
        $ocrFile = OcrFiles::find($this->ocrFileId);

        $ocrData = !empty($ocrFile['ocr_data']) ? json_decode($ocrFile['ocr_data'], 1) : [];
        //$height = $this->pdfData['height'];
        //$width = $this->pdfData['width'];
        //$pageNo = $this->pdfData['pageNo'];
        $pdfPath = $ocrFile->getAttributes()['pdf_file_path'];
        $filePath = dirname($pdfPath);
        //$originalPdfPath = $this->pdfData['originalPdfPath'];
        $pathInfo = pathinfo($pdfPath);
        //$totalPageCount = $this->pdfData['totalCountOfPages'];

        try {
//            if(empty($ocrData[$height.'X'.$width])) {
                $ocrData['img_path'] = $filePath.'/'.$pathInfo['filename'].'.jpg';
                $ocrData['coordinates_submitted'] = 0;
//            }elseif(empty($ocrData[$height.'X'.$width]['next_pages'])) {
//                $ocrData[$height.'X'.$width]['next_pages'] = [$filePath.'/'.$pathInfo['filename'].'.jpg'];
//            }elseif(!empty($ocrData[$height.'X'.$width]['next_pages'])) {
//                $ocrData[$height.'X'.$width]['next_pages'][] = $filePath.'/'.$pathInfo['filename'].'.jpg';
//            }
//            if(empty($ocrData[$height.'X'.$width]['pages'])) {
//                $ocrData[$height.'X'.$width]['pages'] = [$filePath.'/'.$pathInfo['filename'].'.jpg'];
//            }elseif(!empty($ocrData[$height.'X'.$width]['pages'])) {
//                $ocrData[$height.'X'.$width]['pages'][] = $filePath.'/'.$pathInfo['filename'].'.jpg';
//            }
            /*        if(!isset($ocrData[$height.'X'.$width]['next_pages']) && ($pageNo-1) === 1) {
                        $ocrData[$height.'X'.$width]['next_pages'] = [$filePath.'/page_'.($pageNo-1).'.jpg'];
                    }
                    if(isset($ocrData[$height.'X'.$width]['next_pages']) && ($pageNo-1) > 1) {
                        $ocrData[$height.'X'.$width]['next_pages'][] = $filePath.'/page_'.($pageNo-1).'.jpg';
                    }
            */

            if(!isset($ocrData['drawing_title'])) {
                $ocrData['drawing_title']['x'] = '';
                $ocrData['drawing_title']['y'] = '';
                $ocrData['drawing_title']['h'] = '';
                $ocrData['drawing_title']['w'] = '';
            }
            if(!isset($ocrData['drawing_number'])) {
                $ocrData['drawing_number']['x'] = '';
                $ocrData['drawing_number']['y'] = '';
                $ocrData['drawing_number']['h'] = '';
                $ocrData['drawing_number']['w'] = '';
            }
//            if(!isset($ocrData['page_to_process'])) {
//                $ocrData[$height.'X'.$width]['page_to_process'] = [$pathInfo['filename']];
//            }

//            if(isset($ocrData[$height.'X'.$width]['page_to_process']) && ($pageNo-1) >= 1) {
//                $ocrData[$height . 'X' . $width]['page_to_process'][] = $pathInfo['filename'];
//            }
            $ocrFile->ocr_data = json_encode($ocrData);


            //if($pageNo >= 1 && $totalPageCount >= 1 && $totalPageCount == $pageNo) {
                $ocrFile->status = 'pending_drawing';
            //}
            $ocrFile->save();

        } catch(\Exception $ex) {
            $ocrFile->status = 'error';
            $ocrFile->save();
            \Log::error("UpdateOCRProjectFileDataJob error encountered");
            \Log::error($ex->getMessage());
            \Log::error($ex->getTraceAsString());
        }
        //\Log::info('current Page => '.$pageNo. " total count => ". $totalPageCount);
    }
}
