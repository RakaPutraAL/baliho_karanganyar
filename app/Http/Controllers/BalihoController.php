<?php

namespace App\Http\Controllers;

use App\Models\Baliho;
use App\Models\Kecamatan;
use App\Models\Opd;
use Illuminate\Http\Request;
use Inertia\Inertia;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;


class BalihoController extends Controller
{
   
    public function index()
    {
        
        $balihos = Baliho::with(['kecamatan', 'opd'])
            ->orderBy('kode', 'asc')
            ->get();

        return Inertia::render('balihos/Index', compact('balihos'));
    }

    
    public function create()
{
    $kecamatans = Kecamatan::orderBy('nama_kecamatan', 'asc')->get();

    $opds = Opd::orderBy('nama_opd', 'asc')->get()->unique('nama_opd')->values();

    return Inertia::render('balihos/Create', [
        'kecamatans' => $kecamatans,
        'opds'       => $opds
    ]);
}

    public function store(Request $request)
    {
        $data = $request->validate([
            'jenis_baliho'    => 'required|string|max:255',
            'pemasangan'      => 'required|string|max:255',
            'view'            => 'required|string|max:255',
            'dimensi'         => 'required|string|max:255',
            'jenis_kontruksi' => 'required|string|max:255',
            'alamat'          => 'required|string|max:255',
            'kode'            => 'required|string|exists:kecamatans,kode',
            'opd_id'          => 'required|exists:opds,id',
            'latitude'        => 'nullable|numeric|between:-90,90',
            'longitude'       => 'nullable|numeric|between:-180,180',
            'foto'            => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

      
        if ($request->hasFile('foto')) {
            $filename = time() . '.' . $request->file('foto')->getClientOriginalExtension();
            $request->file('foto')->move(public_path('uploads'), $filename);
            $data['foto'] = $filename;
        }

        Baliho::create($data);

        return redirect()->route('balihos.index')->with('message', 'Data baliho berhasil ditambahkan');
    }

   
    public function edit(Baliho $baliho)
{
    $kecamatans = Kecamatan::orderBy('nama_kecamatan', 'asc')->get();

    $opds = Opd::orderBy('nama_opd', 'asc')->get()->unique('nama_opd')->values();

    return Inertia::render('balihos/edit', [
        'baliho'     => $baliho->load(['kecamatan', 'opd']),
        'kecamatans' => $kecamatans,
        'opds'       => $opds,
        'baseUrl'    => url(''),
    ]);
}

    
    public function update(Request $request, Baliho $baliho)
    {
        $validated = $request->validate([
            'jenis_baliho'    => 'required|string|max:255',
            'pemasangan'      => 'required|string|max:255',
            'view'            => 'required|string|max:255',
            'dimensi'         => 'required|string|max:255',
            'jenis_kontruksi' => 'required|string|max:255',
            'alamat'          => 'required|string|max:255',
            'kode'            => 'required|string|exists:kecamatans,kode',
            'opd_id'          => 'required|exists:opds,id',
            'latitude'        => 'nullable|numeric|between:-90,90',
            'longitude'       => 'nullable|numeric|between:-180,180',
            'foto'            => 'nullable|image|mimes:jpg,jpeg,png,gif|max:2048',
        ]);

    
        if ($request->hasFile('foto')) {
            if ($baliho->foto && file_exists(public_path('uploads/' . $baliho->foto))) {
                unlink(public_path('uploads/' . $baliho->foto));
            }

            $filename = time() . '.' . $request->file('foto')->getClientOriginalExtension();
            $request->file('foto')->move(public_path('uploads'), $filename);

            $validated['foto'] = $filename;
        } else {
            unset($validated['foto']);
        }

        $baliho->update($validated);

        return redirect()->route('balihos.index')->with('message', 'Data baliho berhasil diperbarui');
    }

  
    public function destroy(Baliho $baliho)
    {
        if ($baliho->foto && file_exists(public_path('uploads/' . $baliho->foto))) {
            unlink(public_path('uploads/' . $baliho->foto));
        }

        $baliho->delete();

        return redirect()->route('balihos.index')->with('message', 'Data baliho berhasil dihapus');
    }

   
    public function maps()
    {
        $balihos = Baliho::with(['kecamatan', 'opd'])->get();

        return Inertia::render('maps/index', [
            'balihos' => $balihos,
        ]);
    }

   public function export()
{
    
    ini_set('memory_limit', '512M');
    
    $balihos = Baliho::with(['opd', 'kecamatan'])->get();

    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    
    $headers = [
        'A1' => 'ID',
        'B1' => 'OPD',
        'C1' => 'Jenis Baliho',
        'D1' => 'Pemasangan',
        'E1' => 'View',
        'F1' => 'Dimensi',
        'G1' => 'Jenis Kontruksi',
        'H1' => 'Jumlah',
        'I1' => 'Alamat',
        'J1' => 'Kecamatan',
        'K1' => 'Latitude',
        'L1' => 'Longitude',
        'M1' => 'Foto',
    ];

    foreach ($headers as $cell => $value) {
        $sheet->setCellValue($cell, $value);
    }

   
    $sheet->getStyle('A1:M1')->applyFromArray([
        'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => '4F81BD']],
    ]);

    $row = 2;
    $photoCount = 0;
    $errorCount = 0;

    foreach ($balihos as $baliho) {
        $sheet->setCellValue("A$row", $baliho->id);
        $sheet->setCellValue("B$row", $baliho->opd->nama_opd ?? '-');
        $sheet->setCellValue("C$row", $baliho->jenis_baliho);
        $sheet->setCellValue("D$row", $baliho->pemasangan);
        $sheet->setCellValue("E$row", $baliho->view);
        $sheet->setCellValue("F$row", $baliho->dimensi);
        $sheet->setCellValue("G$row", $baliho->jenis_kontruksi);
        $sheet->setCellValue("H$row", $baliho->jumlah);
        $sheet->setCellValue("I$row", $baliho->alamat);
        $sheet->setCellValue("J$row", $baliho->kecamatan->nama_kecamatan ?? '-');
        $sheet->setCellValue("K$row", $baliho->latitude);
        $sheet->setCellValue("L$row", $baliho->longitude);

    
        if ($baliho->foto) {
            $filename = basename($baliho->foto);
            
          
            $possiblePaths = [
                storage_path('app/public/uploads/' . $filename),
                storage_path('app/public/' . $filename),
                public_path('uploads/' . $filename),
                public_path('storage/uploads/' . $filename),
            ];

            $validPath = null;
            foreach ($possiblePaths as $testPath) {
                if (file_exists($testPath)) {
                    $validPath = $testPath;
                    break;
                }
            }

            if ($validPath) {
                try {
                    
                    $imageInfo = getimagesize($validPath);
                    if ($imageInfo === false) {
                        \Log::error("Invalid image format: " . $validPath);
                        $sheet->setCellValue("M$row", 'Invalid Image Format');
                        $errorCount++;
                    } else {
                        
                        $fileSize = filesize($validPath);
                        if ($fileSize > 2097152) {
                            \Log::warning("File too large (" . round($fileSize/1024/1024, 2) . "MB): " . $validPath);
                            $sheet->setCellValue("M$row", 'File Too Large');
                            $errorCount++;
                        } else {
                            $drawing = new Drawing();
                            $drawing->setPath($validPath);
                            $drawing->setCoordinates("M$row");
                            $drawing->setOffsetX(5);
                            $drawing->setOffsetY(5);
                            $drawing->setHeight(70);
                            $drawing->setWorksheet($sheet);

                            // Set row height dan column width
                            $sheet->getRowDimension($row)->setRowHeight(70);
                            $sheet->getColumnDimension('M')->setWidth(20);
                            
                            $photoCount++;
                            \Log::info("Photo added successfully: " . $validPath);
                        }
                    }
                } catch (\Exception $e) {
                    \Log::error("Error adding photo: " . $e->getMessage());
                    $sheet->setCellValue("M$row", 'Error Loading Image');
                    $errorCount++;
                }
            } else {
                \Log::error("Photo file not found. Tried paths: " . implode(', ', $possiblePaths));
                $sheet->setCellValue("M$row", 'File Not Found');
                $errorCount++;
            }
        } else {
            $sheet->setCellValue("M$row", 'No Photo');
        }

        $row++;
    }

  
    \Log::info("Export completed. Photos added: $photoCount, Errors: $errorCount");

  
    $sheet->getStyle("A1:M" . ($row - 1))->applyFromArray([
        'borders' => [
            'allBorders' => [
                'borderStyle' => Border::BORDER_THIN,
                'color' => ['rgb' => '000000'],
            ],
        ],
    ]);

    
    foreach (range('A', 'L') as $col) {
        $sheet->getColumnDimension($col)->setAutoSize(true);
    }

   
    $sheet->getStyle("A2:A" . ($row - 1))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    $sheet->getStyle("H2:H" . ($row - 1))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    $sheet->getStyle("K2:L" . ($row - 1))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

    $fileName = "balihos_" . now()->format('Ymd_His') . ".xlsx";
    $writer = new Xlsx($spreadsheet);

    return response()->streamDownload(function () use ($writer) {
        $writer->save('php://output');
    }, $fileName, [
        'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
    ]);
}

}
