<?php

namespace App\Service;

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\IReadFilter;
use Symfony\Component\HttpKernel\KernelInterface;

class LineFileService
{
    const CHUNK_READER_SIZE = 50;

    const COLUMNS = [
        'A',
        'B',
        'C',
        'D',
        'G'
    ];

    public function __construct(
        private readonly KernelInterface $kernel
    ) {}

    public function getIterator(): \Generator
    {
        $filePath = $this->getFilePath();
        $inputFileType = IOFactory::identify($filePath);
        $reader = IOFactory::createReader($inputFileType);
        $reader->setLoadSheetsOnly('sheet_name');
        $reader->setReadEmptyCells(false);

        $sitesColumns   = self::COLUMNS;

        $chunkReader = $this->getChunkReader($sitesColumns);

        $chunkSize = self::CHUNK_READER_SIZE;
        $reader->setReadFilter($chunkReader);


        for ($startRow = 1; ; $startRow += $chunkSize) {
            $chunkReader->setRows($startRow,$chunkSize - 1);

            $spreadsheet = $reader->load($filePath);
            $spreadsheet->setActiveSheetIndexByName('sheet_name');

            $activeRange = $spreadsheet->getActiveSheet()->calculateWorksheetDataDimension();
            $activeRange = str_replace('A1', 'A' . $startRow, $activeRange);
            $sheetData   = $spreadsheet->getActiveSheet()->rangeToArray($activeRange, null, true, true, true);

            foreach ($sheetData as $line) {
                yield [
                    'code' => $line['A'],
                    'date' => \DateTime::createFromFormat('Ymd', $line['B']),
                    'type' => $line['C'],
                    'value' => $line['D'],
                    'status' => $line['G'],
                ];
            }
            $spreadsheet->disconnectWorksheets();
            unset($spreadsheet);
        }
    }

    private function getChunkReader(array $columns): IReadFilter
    {
        return new class(0, 0, $columns) implements IReadFilter {
            private int $startRow;
            private int $endRow;
            private array $columns;

            /**  Get the list of rows and columns to read  */
            public function __construct($startRow, $endRow, $columns) {
                $this->startRow = $startRow;
                $this->endRow   = $endRow;
                $this->columns  = $columns;
            }

            public function setRows($startRow, $chunkSize) {
                $this->startRow = $startRow;
                $this->endRow   = $startRow + $chunkSize;
            }

            public function readCell($columnAddress, $row, $worksheetName = ''): bool {
                if ($row >= $this->startRow && $row <= $this->endRow) {
                    if (in_array($columnAddress,$this->columns)) {
                        return true;
                    }
                }

                return false;
            }
        };
    }

    private function getFilePath()
    {
        return $this->kernel->getProjectDir() . '/data/large_excel_file2.xlsx';
    }
}
