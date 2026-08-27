<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\PaymentSetting;
use Dompdf\Dompdf;
use Dompdf\Options;

class InvoicePdfService
{
    public function output(Invoice $invoice, PaymentSetting $settings, bool $isReceipt = false): string
    {
        $this->ensureDompdfAutoload();

        $invoice->loadMissing(['items', 'customer', 'student', 'payments']);

        $html = view('mail.invoice-pdf', [
            'invoice' => $invoice,
            'settings' => $settings,
            'isReceipt' => $isReceipt,
            'brand' => 'PickDrop',
        ])->render();

        $options = new Options();
        $options->set('isRemoteEnabled', false);
        $options->set('isHtml5ParserEnabled', true);
        $options->set('defaultFont', 'Helvetica');

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        $this->addWatermark($dompdf);

        return (string) $dompdf->output();
    }

    private function addWatermark(Dompdf $dompdf): void
    {
        $canvas = $dompdf->getCanvas();
        $font = $dompdf->getFontMetrics()->getFont('Helvetica', 'bold')
            ?: $dompdf->getFontMetrics()->getFont('Helvetica');

        $canvas->page_script(function ($pageNumber, $pageCount, $canvas) use ($font) {
            $text = 'PickDrop';
            $size = 72;
            $angle = -32.0;
            $pageWidth = $canvas->get_width();
            $pageHeight = $canvas->get_height();
            $textWidth = $canvas->get_text_width($text, $font, $size);
            $x = ($pageWidth - $textWidth) / 2;
            $y = $pageHeight * 0.55;

            $canvas->set_opacity(0.09);
            $canvas->text($x, $y, $text, $font, $size, [0.45, 0.45, 0.45], 0.0, 1.4, $angle);
            $canvas->set_opacity(1.0);
        });
    }

    private function ensureDompdfAutoload(): void
    {
        static $ready = false;
        if ($ready || class_exists(Dompdf::class)) {
            $ready = true;

            return;
        }

        $vendor = base_path('vendor');
        $cpdf = $vendor . '/dompdf/dompdf/lib/Cpdf.php';
        if (is_file($cpdf)) {
            require_once $cpdf;
        }

        $safe = $vendor . '/thecodingmachine/safe';
        foreach ([
            $safe . '/lib/special_cases.php',
            $safe . '/generated/pcre.php',
            $safe . '/generated/strings.php',
            $safe . '/generated/mbstring.php',
            $safe . '/generated/filesystem.php',
            $safe . '/generated/iconv.php',
            $safe . '/generated/fileinfo.php',
            $safe . '/generated/json.php',
        ] as $file) {
            if (is_file($file)) {
                require_once $file;
            }
        }

        spl_autoload_register(static function (string $class) use ($vendor): void {
            $prefixes = [
                'Dompdf\\' => $vendor . '/dompdf/dompdf/src/',
                'FontLib\\' => $vendor . '/dompdf/php-font-lib/src/FontLib/',
                'Svg\\' => $vendor . '/dompdf/php-svg-lib/src/Svg/',
                'Masterminds\\' => $vendor . '/masterminds/html5/src/',
                'Sabberworm\\CSS\\' => $vendor . '/sabberworm/php-css-parser/src/',
            ];

            foreach ($prefixes as $prefix => $dir) {
                if (!str_starts_with($class, $prefix)) {
                    continue;
                }

                $path = $dir . str_replace('\\', '/', substr($class, strlen($prefix))) . '.php';
                if (is_file($path)) {
                    require $path;
                }

                return;
            }
        });

        $ready = true;
    }
}
