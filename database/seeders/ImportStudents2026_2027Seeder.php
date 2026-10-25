<?php

namespace Database\Seeders;

use DOMDocument;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use ZipArchive;

class ImportStudents2026_2027Seeder extends Seeder
{
    private const FILE = 'storage_data/Absen jadi 2026-2027.xlsx';
    private const SCHOOL_YEAR = '2026/2027';
    private const SHEETS = [
        'KELAS 7' => 7,
        'KELAS 8' => 8,
        'KELAS 9' => 9,
    ];

    public function run(): void
    {
        $path = base_path(self::FILE);
        if (!is_file($path)) {
            throw new RuntimeException("File roster siswa tidak ditemukan: {$path}");
        }
        if (!class_exists(ZipArchive::class)) {
            throw new RuntimeException('Ekstensi PHP ZipArchive wajib tersedia untuk membaca file XLSX.');
        }

        $workbook = new ZipArchive();
        if ($workbook->open($path) !== true) {
            throw new RuntimeException("File XLSX tidak dapat dibuka: {$path}");
        }

        try {
            $sharedStrings = $this->readSharedStrings($workbook);
            $worksheets = $this->worksheetPaths($workbook);
            $rosters = $this->readRosters($workbook, $sharedStrings, $worksheets);
        } finally {
            $workbook->close();
        }

        $schoolId = DB::table('sekolah')->orderBy('id')->value('id');
        if (!$schoolId) {
            throw new RuntimeException('Data sekolah belum tersedia. Jalankan seeder sekolah terlebih dahulu.');
        }

        DB::transaction(function () use ($rosters, $schoolId): void {
            foreach ($rosters as $grade => $students) {
                $classId = DB::table('kelas')->where([
                    'sekolah_id' => $schoolId,
                    'nama_kelas' => "Kelas {$grade}",
                    'tahun_ajaran' => self::SCHOOL_YEAR,
                ])->value('id');

                if (!$classId) {
                    $classId = DB::table('kelas')->insertGetId([
                        'sekolah_id' => $schoolId,
                        'nama_kelas' => "Kelas {$grade}",
                        'tingkat' => $grade,
                        'tahun_ajaran' => self::SCHOOL_YEAR,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }

                foreach ($students as $student) {
                    DB::table('siswa')->updateOrInsert(
                        ['nis' => $student['nis']],
                        [
                            'kelas_id' => $classId,
                            'nama' => $student['nama'],
                            'jenis_kelamin' => $student['jenis_kelamin'],
                            'updated_at' => now(),
                        ] + (DB::table('siswa')->where('nis', $student['nis'])->exists()
                            ? []
                            : ['status' => 'aktif', 'created_at' => now()])
                    );
                }
            }
        });

        $total = 0;
        $temporaryNis = 0;
        foreach ($rosters as $students) {
            $total += count($students);
            $temporaryNis += count(array_filter(
                $students,
                fn (array $student): bool => str_starts_with($student['nis'], 'TMP-')
            ));
        }

        $this->command?->info("Import selesai: {$total} siswa untuk tahun ajaran " . self::SCHOOL_YEAR . '.');
        if ($temporaryNis > 0) {
            $this->command?->warn(
                "{$temporaryNis} siswa tidak memiliki NIS pada file sumber. NIS sementara berawalan TMP-; ganti saat NIS resmi tersedia."
            );
        }
    }

    private function readSharedStrings(ZipArchive $workbook): array
    {
        $xml = $workbook->getFromName('xl/sharedStrings.xml');
        if ($xml === false) {
            return [];
        }

        $document = $this->loadXml($xml, 'shared strings');
        $strings = [];
        foreach ($document->getElementsByTagName('si') as $item) {
            $value = '';
            foreach ($item->getElementsByTagName('t') as $text) {
                $value .= $text->textContent;
            }
            $strings[] = $value;
        }

        return $strings;
    }

    private function worksheetPaths(ZipArchive $workbook): array
    {
        $workbookXml = $workbook->getFromName('xl/workbook.xml');
        $relationshipsXml = $workbook->getFromName('xl/_rels/workbook.xml.rels');
        if ($workbookXml === false || $relationshipsXml === false) {
            throw new RuntimeException('File XLSX tidak memiliki struktur workbook yang valid.');
        }

        $workbookDocument = $this->loadXml($workbookXml, 'workbook');
        $relationshipsDocument = $this->loadXml($relationshipsXml, 'workbook relationships');
        $relationships = [];
        foreach ($relationshipsDocument->getElementsByTagName('Relationship') as $relationship) {
            $relationships[$relationship->getAttribute('Id')] = $relationship->getAttribute('Target');
        }

        $paths = [];
        foreach ($workbookDocument->getElementsByTagName('sheet') as $sheet) {
            $name = $sheet->getAttribute('name');
            if (!isset(self::SHEETS[$name])) {
                continue;
            }
            $relationshipId = $sheet->getAttributeNS(
                'http://schemas.openxmlformats.org/officeDocument/2006/relationships',
                'id'
            );
            $target = $relationships[$relationshipId] ?? null;
            if (!$target) {
                throw new RuntimeException("Worksheet {$name} tidak memiliki relasi file yang valid.");
            }

            $paths[$name] = str_starts_with($target, '/')
                ? ltrim($target, '/')
                : 'xl/' . ltrim($target, '/');
        }

        foreach (self::SHEETS as $name => $_) {
            if (!isset($paths[$name])) {
                throw new RuntimeException("Worksheet wajib tidak ditemukan: {$name}");
            }
        }

        return $paths;
    }

    private function readRosters(ZipArchive $workbook, array $sharedStrings, array $worksheetPaths): array
    {
        $rosters = [];
        $allStudentNumbers = [];

        foreach (self::SHEETS as $sheetName => $grade) {
            $xml = $workbook->getFromName($worksheetPaths[$sheetName]);
            if ($xml === false) {
                throw new RuntimeException("Data worksheet tidak dapat dibaca: {$sheetName}");
            }

            $document = $this->loadXml($xml, $sheetName);
            $students = [];
            $temporaryNisSequence = 0;
            foreach ($document->getElementsByTagName('row') as $row) {
                $cells = $this->rowValues($row, $sharedStrings);
                $ordinal = $cells['A'] ?? '';
                $name = trim($cells['C'] ?? '');
                if ($name === '' || !ctype_digit($ordinal)) {
                    continue;
                }

                $gender = strtoupper(trim($cells['D'] ?? ''));
                if (!in_array($gender, ['L', 'P'], true)) {
                    throw new RuntimeException(
                        "Jenis kelamin tidak valid pada worksheet {$sheetName}, baris {$row->getAttribute('r')}."
                    );
                }
                if (mb_strlen($name) > 150) {
                    throw new RuntimeException(
                        "Nama siswa melebihi batas 150 karakter pada worksheet {$sheetName}, baris {$row->getAttribute('r')}."
                    );
                }

                $nis = trim($cells['B'] ?? '');
                if ($nis === '') {
                    $nis = sprintf('TMP-2026-2027-%d-%03d', $grade, ++$temporaryNisSequence);
                }
                if (mb_strlen($nis) > 20) {
                    throw new RuntimeException(
                        "Nomor induk melebihi batas 20 karakter pada worksheet {$sheetName}, baris {$row->getAttribute('r')}."
                    );
                }
                if (isset($allStudentNumbers[$nis])) {
                    throw new RuntimeException(
                        "Nomor induk duplikat '{$nis}' ditemukan pada worksheet {$sheetName}, baris {$row->getAttribute('r')}."
                    );
                }
                $allStudentNumbers[$nis] = true;

                $students[] = [
                    'nis' => $nis,
                    'nama' => $name,
                    'jenis_kelamin' => $gender,
                ];
            }

            if ($students === []) {
                throw new RuntimeException("Tidak ada data siswa valid pada worksheet {$sheetName}.");
            }

            $rosters[$grade] = $students;
        }

        return $rosters;
    }

    private function rowValues(\DOMElement $row, array $sharedStrings): array
    {
        $values = [];
        foreach ($row->getElementsByTagName('c') as $cell) {
            if (!preg_match('/^([A-Z]+)/', $cell->getAttribute('r'), $matches)) {
                continue;
            }

            $type = $cell->getAttribute('t');
            if ($type === 'inlineStr') {
                $value = '';
                foreach ($cell->getElementsByTagName('t') as $text) {
                    $value .= $text->textContent;
                }
            } else {
                $value = $cell->getElementsByTagName('v')->item(0)?->textContent ?? '';
                if ($type === 's') {
                    $value = $sharedStrings[(int) $value] ?? '';
                }
            }

            $values[$matches[1]] = trim($value);
        }

        return $values;
    }

    private function loadXml(string $xml, string $description): DOMDocument
    {
        $document = new DOMDocument();
        if (!$document->loadXML($xml, LIBXML_NONET)) {
            throw new RuntimeException("Format XML tidak valid pada {$description}.");
        }

        return $document;
    }
}
