<?php

namespace App\Services;

use Exception;
use ZipArchive;
use Smalot\PdfParser\Parser as PdfParser;

class ResumeParserService
{
    /**
     * Extract raw text from a PDF or DOCX file.
     */
    public function extractText(string $filePath, string $extension): string
    {
        $extension = strtolower($extension);

        if ($extension === 'pdf') {
            return $this->extractFromPdf($filePath);
        } elseif ($extension === 'docx') {
            return $this->extractFromDocx($filePath);
        }

        throw new Exception("Unsupported file type: .{$extension}. Only PDF and DOCX are supported.");
    }

    /**
     * Extract text from a PDF file.
     */
    protected function extractFromPdf(string $filePath): string
    {
        try {
            $parser = new PdfParser();
            $pdf = $parser->parseFile($filePath);
            return $pdf->getText();
        } catch (Exception $e) {
            throw new Exception("Failed to parse PDF file: " . $e->getMessage());
        }
    }

    /**
     * Extract text from a DOCX file.
     */
    protected function extractFromDocx(string $filePath): string
    {
        try {
            $zip = new ZipArchive();
            if ($zip->open($filePath) !== true) {
                throw new Exception("Unable to open DOCX file archive.");
            }

            $xmlContent = '';
            // DOCX text is stored in word/document.xml
            if (($index = $zip->locateName('word/document.xml')) !== false) {
                $xmlContent = $zip->getFromIndex($index);
            }

            $zip->close();

            if (empty($xmlContent)) {
                throw new Exception("Could not find document content in DOCX.");
            }

            // Simple XML to Text parsing by stripping tags
            // Replace word XML paragraph/break tags with newlines for formatting
            $cleanXml = str_replace(['<w:p>', '<w:p ', '</w:p>', '<w:br/>', '<w:br ', '<w:tr>', '</w:tr>'], ["\n", "\n", "\n", "\n", "\n", "\n", "\n"], $xmlContent);
            $text = strip_tags($cleanXml);
            $text = html_entity_decode($text, ENT_QUOTES, 'UTF-8');
            
            // Clean up multiple consecutive newlines or spacing
            $text = preg_replace("/\n+/", "\n", $text);
            $text = preg_replace("/[ \t]+/", " ", $text);

            return trim($text);
        } catch (Exception $e) {
            throw new Exception("Failed to parse DOCX file: " . $e->getMessage());
        }
    }
}
