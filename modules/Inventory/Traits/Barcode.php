<?php

namespace Modules\Inventory\Traits;

use App\Models\Common\Media;
use App\Traits\Uploads;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Com\Tecnick\Barcode\Barcode as TecnickBarcode;

trait Barcode
{
    use Uploads;

    public function getBarcodeTypes()
    {
        $barcode = new TecnickBarcode;

        $barcode_type = $barcode->getTypes();

        return array_combine($barcode_type, $barcode_type);
    }

    public function getBarcodeHtml($type, $value)
    {
        $barcode_types = $this->getBarcodeTypes();

        if (! in_array($type, $barcode_types)) {
            $type = 'C128';
        }

        $barcode_view = $this->getBarcodeView($type);

        $barcode = new TecnickBarcode;

        try {
            switch ($barcode_view) {
                case 'square':
                    $barcode = $barcode->getBarcodeObj($type, $value, -4, -4);
                    break;
                case 'linear':
                    $barcode = $barcode->getBarcodeObj($type, $value, -1, -30);
                    break;
            }
        } catch (\Throwable | \Exception $e ) {
            return;
        }
        
        $barcode_html = $barcode->getHtmlDiv();

        return $barcode_html;
    }

    public function getExampleBarcode($type)
    {
        $barcode_types = $this->getBarcodeTypes();

        if (! in_array($type, $barcode_types)) {
            $type = 'C128';
        }

        $barcode_view = $this->getBarcodeView($type);

        $barcode = new TecnickBarcode;

        switch ($barcode_view) {
            case 'square':
                $barcode = $barcode->getBarcodeObj($type, $this->getExampleBarcodeValues($type), -4, -4);
                break;
            case 'linear':
                $barcode = $barcode->getBarcodeObj($type, $this->getExampleBarcodeValues($type), -1, -30);
                break;
        }

        $barcode_html = $barcode->getHtmlDiv();

        return $barcode_html;
    }

    public function getExampleBarcodeValues($type = null)
    {   
        $values = [
            'C128A'             => '0123456789',
            'C128B'             => '0123456789',
            'C128C'             => '0123456789',
            'C128'              => '0123456789',
            'C39E+'             => '0123456789',
            'C39E'              => '0123456789',
            'C39+'              => '0123456789',
            'C39'               => '0123456789',
            'C93'               => '0123456789',
            'CODABAR'           => '0123456789',
            'CODE11'            => '0123456789',
            'EAN13'             => '0123456789',
            'EAN2'              => '12',
            'EAN5'              => '12345',
            'EAN8'              => '1234567',
            'I25+'              => '0123456789',
            'I25'               => '0123456789',
            'IMB'               => '01234567094987654321-01234567891',
            'IMBPRE'            => 'AADTFFDFTDADTAADAATFDTDDAAADDTDTTDAFADADDDTFFFDDTTTADFAAADFTDAADA',
            'KIX'               => '0123456789',
            'MSI+'              => '0123456789',
            'MSI'               => '0123456789',
            'PHARMA2T'          => '0123456789',
            'PHARMA'            => '0123456789',
            'PLANET'            => '0123456789',
            'POSTNET'           => '0123456789',
            'RMS4CC'            => '0123456789',
            'S25+'              => '0123456789',
            'S25'               => '0123456789',
            'UPCA'              => '72527273070',
            'UPCE'              => '725277',
            'LRAW'              => '0101010101',
            'SRAW'              => '0101,1010',
            'PDF417'            => '0123456789',
            'QRCODE'            => '0123456789',
            'QRCODE,H,ST,0,0'   => 'abcdefghijklmnopqrstuvwxy0123456789',
            'DATAMATRIX'        => '0123456789',
            'DATAMATRIX,R'      => '0123456789012345678901234567890123456789',
            'DATAMATRIX,S,GS1'  => chr(232).'01095011010209171719050810ABCD1234'.chr(232).'2110',
            'DATAMATRIX,R,GS1'  => chr(232).'01095011010209171719050810ABCD1234'.chr(232).'2110',
        ];
        
        if (isset($values[$type])) {
            return $values[$type];
        }

        return $values['C128'];
    }

    public function getBarcodeView($type)
    {
        $square = ['LRAW', 'SRAW', 'PDF417', 'QRCODE', 'QRCODE,H,ST,0,0', 'DATAMATRIX', 'DATAMATRIX,R', 'DATAMATRIX,S,GS1', 'DATAMATRIX,R,GS1'];

        if (in_array($type, $square)) {
            return 'square';
        } else {
            return 'linear';
        }
    }

    public function setBarcode($item, $value)
    {
        if ($value == false) {
            return;
        }

        $type = setting('inventory.barcode_type');

        $barcode_types = $this->getBarcodeTypes();

        if (! in_array($type, $barcode_types)) {
            $type = 'C128';
        }

        $barcode_view = $this->getBarcodeView($type);

        $barcode = new TecnickBarcode;

        switch ($barcode_view) {
            case 'square':
                $barcode = $barcode->getBarcodeObj($type, $value, -4, -4);
                break;
            case 'linear':
                $barcode = $barcode->getBarcodeObj($type, $value, -1, -30);
                break;
        }

        $content = $barcode->getPngData();

        $filename = $item->name . '.png';
        $media = Media::where('directory', $this->getMediaFolder('items'))
                      ->where('filename', 'like', $item->name . '%')
                      ->get()
                      ->last();

        if ($media) {
            if (str_ends_with($media->filename, $item->name)) {
                $filename = $media->filename . '_1.png';
            } else {
                $update_count = str_replace($item->name . '_', '', $media->filename);
                $update_count++;

                $filename = $item->name . '_' . $update_count . '.png';
            }
        }

        $path = $this->getMediaFolder('items') . '/' . $filename;

        Storage::disk(config('mediable.default_disk'))->put($path, $content);

        $import_media = $this->importMedia($filename, 'items');

        $item->attachMedia($import_media, Str::snake('inventory.barcode'));

        return $filename;
    }

    public function getBarcodeMedia($item)
    {
        if (! $item->hasMedia('inventory.barcode')) {
            return false;
        }

        return $item->getMedia('inventory.barcode')->last();
    }

    public function createBarcodeValue()
    {
        $type = setting('inventory.barcode_type');

        $barcode_types = $this->getBarcodeTypes();

        if (! in_array($type, $barcode_types)) {
            $type = 'C128';
        }

        switch ($type) {
            case 'EAN2':
                return random_int(1, 99);
                break;
            case 'EAN5':
                return random_int(1, 99999);
                break;
            case 'EAN8':
                return random_int(1, 9999999);
                break;
            case 'EAN13':
                return random_int(1, 9999999999999);
                break;
            case 'UPCA':
                return random_int(1, 999999999999);
                break;
            case 'UPCE':
                return random_int(1, 999999);
                break;
            default:
                return random_int(1, 9999999999999);
                break;     
        }
    }
}
