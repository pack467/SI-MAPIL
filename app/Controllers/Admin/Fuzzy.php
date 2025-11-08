<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use Config\Database;

class Fuzzy extends BaseController
{
    /**
     * POST /admin/fuzzy/hitung
     * Menghitung kecocokan mata kuliah peminatan menggunakan Fuzzy Tsukamoto
     * dengan 27 rules untuk semester 5-6 dan 27 rules untuk semester 7+
     * OUTPUT: Hanya 3 kategori (Kurang Cocok, Cukup Cocok, Sangat Cocok)
     */
    public function hitung()
    {
        $db   = Database::connect();
        $json = $this->request->getJSON(true) ?? [];
        $mhsId = (int)($json['mahasiswa_id'] ?? 0);

        if ($mhsId <= 0) {
            return $this->response->setStatusCode(400)
                ->setJSON(['error' => true, 'message' => 'mahasiswa_id wajib.']);
        }

        // ---- Ambil identitas mahasiswa ----
        $mhs = $db->table('mahasiswa')
            ->select('id, nama, nim, semester')
            ->where('id', $mhsId)
            ->get()->getRowArray();

        if (!$mhs) {
            return $this->response->setStatusCode(404)
                ->setJSON(['error' => true, 'message' => 'Mahasiswa tidak ditemukan.']);
        }

        // ---- Ambil detail nilai per kriteria ----
        $nilaiDetail = $db->table('nilai_mahasiswa nm')
            ->select('nm.kriteria, nm.nama_mk, nm.nilai_huruf, nm.nilai_angka, mk.semester_mk')
            ->join('mata_kuliah mk', 'mk.kode_mk = nm.kode_mk', 'left')
            ->where('nm.mahasiswa_id', $mhsId)
            ->whereIn('LOWER(nm.kriteria)', ['robotika', 'matematika', 'pemrograman', 'analisis'])
            ->orderBy('nm.kriteria')
            ->orderBy('mk.semester_mk')
            ->get()->getResultArray();

        // Kelompokkan per kriteria untuk detail perhitungan
        $detailPerKriteria = [
            'robotika' => [],
            'matematika' => [],
            'pemrograman' => [],
            'analisis' => []
        ];
        
        foreach ($nilaiDetail as $row) {
            $krit = strtolower($row['kriteria']);
            if (isset($detailPerKriteria[$krit])) {
                $angka = $row['nilai_angka'] ?? $this->hurufToAngka($row['nilai_huruf']);
                $detailPerKriteria[$krit][] = [
                    'matakuliah' => $row['nama_mk'],
                    'semester' => $row['semester_mk'] ?? '-',
                    'huruf' => $row['nilai_huruf'],
                    'angka' => $angka
                ];
            }
        }

        // ---- Hitung rata-rata nilai per kriteria (skala 0-4) ----
        $nilai = $db->table('nilai_mahasiswa')
            ->select("LOWER(kriteria) AS krit, AVG(COALESCE(nilai_angka, 
                        CASE UPPER(nilai_huruf)
                            WHEN 'A' THEN 4 WHEN 'B' THEN 3
                            WHEN 'C' THEN 2 WHEN 'D' THEN 1
                            WHEN 'E' THEN 0 ELSE NULL
                        END)) AS rata4", false)
            ->where('mahasiswa_id', $mhsId)
            ->where('kriteria IS NOT NULL')
            ->groupBy('krit')
            ->get()->getResultArray();

        $avg4 = ['robotika'=>0.0,'matematika'=>0.0,'pemrograman'=>0.0,'analisis'=>0.0];
        
        foreach ($nilai as $r) {
            $k = $r['krit'];
            if (isset($avg4[$k]) && $r['rata4'] !== null) {
                $avg4[$k] = (float)$r['rata4'];
            }
        }
        
        // Konversi ke skala 0-100
        $S = array_map(fn($v) => max(0, min(100, $v * 25.0)), $avg4);

        // ============================================
        // STEP 1: FUZZIFIKASI
        // ============================================
        $mu = [
            'robotika'    => $this->membershipAll($S['robotika']),
            'matematika'  => $this->membershipAll($S['matematika']),
            'pemrograman' => $this->membershipAll($S['pemrograman']),
            'analisis'    => $this->membershipAll($S['analisis']),
        ];

        // ============================================
        // STEP 2: EVALUASI 27 RULES SECARA OTOMATIS
        // ============================================
        $semester = (int)($mhs['semester'] ?? 0);
        $allRules = [];

        if ($semester <= 6) {
            // ===== SEMESTER 5-6: 27 RULES OTOMATIS =====
            $allRules = $this->generateAllRulesSemester56($mu);
        } else {
            // ===== SEMESTER 7+: 27 RULES OTOMATIS =====
            $allRules = $this->generateAllRulesSemester7Plus($mu);
        }

        // ============================================
        // STEP 3: PROSES RULES UNTUK OUTPUT & INFERENSI
        // ============================================
        $rulesOut = [];
        $inferensi = [];
        $outputsByMatkul = [];
        
        foreach ($allRules as $rule) {
            // Tambahkan ke rulesOut untuk ditampilkan (tanpa nilai Z)
            $rulesOut[] = [
                'id' => $rule['id'],
                'kondisi_html' => $rule['kondisi'],
                'output_html' => $rule['output_simple'],
                'alpha' => $rule['alpha']
            ];
            
            // Tambahkan inferensi detail (dengan perhitungan Z)
            $inferSteps = [];
            $inferSteps[] = "<span class='math-symbol'>α</span> = min({$rule['alpha_calculation']}) = <span class='result-highlight'>{$this->fmt($rule['alpha'])}</span>";
            
            foreach ($rule['outputs'] as $matkul => $data) {
                $inferSteps[] = "Z<sub>{$matkul}</sub> = {$data['z_formula']} = <span class='result-highlight'>{$this->fmt($data['z'])}</span>";
            }
            
            $inferensi[] = [
                'rule' => $rule['id'],
                'label' => "{$rule['id']}: {$rule['output_label']}",
                'steps' => $inferSteps
            ];
            
            // Kumpulkan output per mata kuliah
            foreach ($rule['outputs'] as $matkul => $data) {
                if (!isset($outputsByMatkul[$matkul])) {
                    $outputsByMatkul[$matkul] = [];
                }
                $outputsByMatkul[$matkul][] = [
                    'rule_id' => $rule['id'],
                    'alpha' => $rule['alpha'],
                    'z' => $data['z'],
                    'label' => $data['label']
                ];
            }
        }

        // ============================================
        // STEP 4: DEFUZZIFIKASI (Weighted Average)
        // ============================================
        $defuzzCards = [];
        $resultDetail = [];
        
        foreach ($outputsByMatkul as $matkul => $outputs) {
            if (empty($outputs)) {
                $resultDetail[] = ['nama' => $matkul, 'nilai' => 0.0];
                continue;
            }

            $sumAz = 0.0;
            $sumA  = 0.0;
            $tableRows = [];
            $alphaValues = [];
            
            foreach ($outputs as $out) {
                $az = $out['alpha'] * $out['z'];
                $sumAz += $az;
                $sumA  += $out['alpha'];
                
                $tableRows[] = [
                    'rule_id' => $out['rule_id'],
                    'alpha' => $this->fmt($out['alpha']),
                    'z' => $this->fmt($out['z']),
                    'result' => $this->fmt($az)
                ];
                
                $alphaValues[] = $this->fmt($out['alpha']);
            }
            
            $score = ($sumA > 0) ? $sumAz / $sumA : 0.0;

            // Icon berdasarkan mata kuliah
            $icon = match($matkul) {
                'Mikrokontroler' => 'fas fa-microchip',
                'JST' => 'fas fa-project-diagram',
                'Machine Learning' => 'fas fa-brain',
                'Logika Fuzzy' => 'fas fa-code-branch',
                default => 'fas fa-book'
            };

            $defuzzCards[] = [
                'icon'  => $icon,
                'title' => $matkul,
                'table_rows' => $tableRows,
                'sum_az' => $this->fmt($sumAz),
                'sum_alpha' => $this->fmt($sumA),
                'alpha_values' => $alphaValues,
                'score' => $score,
                'label' => "Nilai Kecocokan {$matkul}",
            ];
            
            $resultDetail[] = ['nama' => $matkul, 'nilai' => $score];
        }

        // Urutkan berdasarkan nilai tertinggi
        usort($resultDetail, fn($a,$b) => $b['nilai'] <=> $a['nilai']);
        
        // Tentukan rekomendasi (nilai tertinggi)
        $rekom = $resultDetail ? $resultDetail[0]['nama'] : '-';

        // ---- Response JSON ----
        return $this->response->setJSON([
            'summary' => [
                'robotika'    => $S['robotika'],
                'matematika'  => $S['matematika'],
                'pemrograman' => $S['pemrograman'],
                'analisis'    => $S['analisis'],
            ],
            'detail_nilai' => $detailPerKriteria,
            'fuzzifikasi' => [
                'robotika'    => $mu['robotika'],
                'matematika'  => $mu['matematika'],
                'pemrograman' => $mu['pemrograman'],
                'analisis'    => $mu['analisis'],
            ],
            'rules'     => $rulesOut,
            'inferensi' => $inferensi,
            'defuzz'    => $defuzzCards,
            'result'    => [
                'detail'      => $resultDetail,
                'rekomendasi' => $rekom,
                'semester'    => $semester,
            ],
        ]);
    }

    /**
     * GENERATE 27 RULES UNTUK SEMESTER 5-6 (JST & MIKROKONTROLER)
     * Hanya 3 output kategori: Kurang Cocok, Cukup Cocok, Sangat Cocok
     */
    private function generateAllRulesSemester56(array $mu): array
    {
        $rules = [];
        $ruleCount = 1;
        $sets = ['lemah', 'lumayan', 'kuat'];
        
        // Generate semua 81 kombinasi
        foreach ($sets as $rob) {
            foreach ($sets as $mat) {
                foreach ($sets as $prog) {
                    foreach ($sets as $anal) {
                        $alpha = min(
                            $mu['robotika'][$rob],
                            $mu['matematika'][$mat],
                            $mu['pemrograman'][$prog],
                            $mu['analisis'][$anal]
                        );
                        
                        // Hanya simpan rule yang aktif (alpha > 0)
                        if ($alpha <= 0) {
                            $ruleCount++;
                            continue;
                        }
                        
                        // Tentukan output berdasarkan prioritas (HANYA 3 KATEGORI)
                        list($jstLabel, $mikoLabel) = $this->determineOutputSem56($rob, $mat, $prog, $anal);
                        
                        // Gunakan metode inversi Tsukamoto dengan 3 kategori
                        $z_jst = $this->inferTsukamoto($jstLabel, $alpha);
                        $z_miko = $this->inferTsukamoto($mikoLabel, $alpha);
                        
                        $rules[] = [
                            'id' => 'R' . $ruleCount,
                            'alpha' => $alpha,
                            'alpha_calculation' => "μ<sub>Rob</sub>({$rob})={$this->fmt($mu['robotika'][$rob])}, μ<sub>Mat</sub>({$mat})={$this->fmt($mu['matematika'][$mat])}, μ<sub>Prog</sub>({$prog})={$this->fmt($mu['pemrograman'][$prog])}, μ<sub>Anal</sub>({$anal})={$this->fmt($mu['analisis'][$anal])}",
                            'kondisi' => "Robotika = <span class='fuzzy-value " . $this->getFuzzyClass($rob) . "'>" . ucfirst($rob) . "</span> (μ={$this->fmt($mu['robotika'][$rob])}) <strong>AND</strong> " .
                                        "Matematika = <span class='fuzzy-value " . $this->getFuzzyClass($mat) . "'>" . ucfirst($mat) . "</span> (μ={$this->fmt($mu['matematika'][$mat])}) <strong>AND</strong> " .
                                        "Pemrograman = <span class='fuzzy-value " . $this->getFuzzyClass($prog) . "'>" . ucfirst($prog) . "</span> (μ={$this->fmt($mu['pemrograman'][$prog])}) <strong>AND</strong> " .
                                        "Analisis = <span class='fuzzy-value " . $this->getFuzzyClass($anal) . "'>" . ucfirst($anal) . "</span> (μ={$this->fmt($mu['analisis'][$anal])})",
                            'output_simple' => "<strong>JST</strong> = <span class='output-value " . $this->getOutputClass($jstLabel) . "'>{$jstLabel}</span> | " .
                                              "<strong>Mikrokontroler</strong> = <span class='output-value " . $this->getOutputClass($mikoLabel) . "'>{$mikoLabel}</span>",
                            'output_label' => "JST={$jstLabel}, Mikrokontroler={$mikoLabel}",
                            'outputs' => [
                                'JST' => [
                                    'z' => $z_jst['z'],
                                    'z_formula' => $z_jst['formula'],
                                    'label' => $jstLabel
                                ],
                                'Mikrokontroler' => [
                                    'z' => $z_miko['z'],
                                    'z_formula' => $z_miko['formula'],
                                    'label' => $mikoLabel
                                ]
                            ]
                        ];
                        
                        $ruleCount++;
                    }
                }
            }
        }
        
        return $rules;
    }

    /**
     * GENERATE 27 RULES UNTUK SEMESTER 7+ (MACHINE LEARNING & LOGIKA FUZZY)
     * Hanya 3 output kategori: Kurang Cocok, Cukup Cocok, Sangat Cocok
     */
    private function generateAllRulesSemester7Plus(array $mu): array
    {
        $rules = [];
        $ruleCount = 1;
        $sets = ['lemah', 'lumayan', 'kuat'];
        
        // Generate semua 81 kombinasi
        foreach ($sets as $rob) {
            foreach ($sets as $mat) {
                foreach ($sets as $prog) {
                    foreach ($sets as $anal) {
                        $alpha = min(
                            $mu['robotika'][$rob],
                            $mu['matematika'][$mat],
                            $mu['pemrograman'][$prog],
                            $mu['analisis'][$anal]
                        );
                        
                        // Hanya simpan rule yang aktif (alpha > 0)
                        if ($alpha <= 0) {
                            $ruleCount++;
                            continue;
                        }
                        
                        // Tentukan output berdasarkan prioritas (HANYA 3 KATEGORI)
                        list($mlLabel, $lfLabel) = $this->determineOutputSem7Plus($rob, $mat, $prog, $anal);
                        
                        // Gunakan metode inversi Tsukamoto dengan 3 kategori
                        $z_ml = $this->inferTsukamoto($mlLabel, $alpha);
                        $z_lf = $this->inferTsukamoto($lfLabel, $alpha);
                        
                        $rules[] = [
                            'id' => 'R' . $ruleCount,
                            'alpha' => $alpha,
                            'alpha_calculation' => "μ<sub>Rob</sub>({$rob})={$this->fmt($mu['robotika'][$rob])}, μ<sub>Mat</sub>({$mat})={$this->fmt($mu['matematika'][$mat])}, μ<sub>Prog</sub>({$prog})={$this->fmt($mu['pemrograman'][$prog])}, μ<sub>Anal</sub>({$anal})={$this->fmt($mu['analisis'][$anal])}",
                            'kondisi' => "Robotika = <span class='fuzzy-value " . $this->getFuzzyClass($rob) . "'>" . ucfirst($rob) . "</span> (μ={$this->fmt($mu['robotika'][$rob])}) <strong>AND</strong> " .
                                        "Matematika = <span class='fuzzy-value " . $this->getFuzzyClass($mat) . "'>" . ucfirst($mat) . "</span> (μ={$this->fmt($mu['matematika'][$mat])}) <strong>AND</strong> " .
                                        "Pemrograman = <span class='fuzzy-value " . $this->getFuzzyClass($prog) . "'>" . ucfirst($prog) . "</span> (μ={$this->fmt($mu['pemrograman'][$prog])}) <strong>AND</strong> " .
                                        "Analisis = <span class='fuzzy-value " . $this->getFuzzyClass($anal) . "'>" . ucfirst($anal) . "</span> (μ={$this->fmt($mu['analisis'][$anal])})",
                            'output_simple' => "<strong>Machine Learning</strong> = <span class='output-value " . $this->getOutputClass($mlLabel) . "'>{$mlLabel}</span> | " .
                                              "<strong>Logika Fuzzy</strong> = <span class='output-value " . $this->getOutputClass($lfLabel) . "'>{$lfLabel}</span>",
                            'output_label' => "ML={$mlLabel}, Logika Fuzzy={$lfLabel}",
                            'outputs' => [
                                'Machine Learning' => [
                                    'z' => $z_ml['z'],
                                    'z_formula' => $z_ml['formula'],
                                    'label' => $mlLabel
                                ],
                                'Logika Fuzzy' => [
                                    'z' => $z_lf['z'],
                                    'z_formula' => $z_lf['formula'],
                                    'label' => $lfLabel
                                ]
                            ]
                        ];
                        
                        $ruleCount++;
                    }
                }
            }
        }
        
        return $rules;
    }

    /**
     * Tentukan output untuk semester 5-6 berdasarkan kombinasi input
     * HANYA 3 KATEGORI OUTPUT: Kurang Cocok, Cukup Cocok, Sangat Cocok
     */
    private function determineOutputSem56($rob, $mat, $prog, $anal): array
    {
        $robScore = $this->setToScore($rob);
        $matScore = $this->setToScore($mat);
        $progScore = $this->setToScore($prog);
        $analScore = $this->setToScore($anal);
        
        // Hitung skor untuk JST (Prioritas: Pemrograman > Matematika > Analisis > Robotika)
        $jstScore = ($progScore * 0.40) + ($matScore * 0.30) + ($analScore * 0.20) + ($robScore * 0.10);
        
        // Hitung skor untuk Mikrokontroler (Prioritas: Robotika > Analisis > Pemrograman > Matematika)
        $mikoScore = ($robScore * 0.40) + ($analScore * 0.30) + ($progScore * 0.20) + ($matScore * 0.10);
        
        return [$this->scoreToLabel($jstScore), $this->scoreToLabel($mikoScore)];
    }

    /**
     * Tentukan output untuk semester 7+ berdasarkan kombinasi input
     * HANYA 3 KATEGORI OUTPUT: Kurang Cocok, Cukup Cocok, Sangat Cocok
     */
    private function determineOutputSem7Plus($rob, $mat, $prog, $anal): array
    {
        $robScore = $this->setToScore($rob);
        $matScore = $this->setToScore($mat);
        $progScore = $this->setToScore($prog);
        $analScore = $this->setToScore($anal);
        
        // Hitung skor untuk ML (Prioritas: Matematika > Analisis > Pemrograman > Robotika)
        $mlScore = ($matScore * 0.40) + ($analScore * 0.30) + ($progScore * 0.20) + ($robScore * 0.10);
        
        // Hitung skor untuk Logika Fuzzy (Prioritas: Analisis > Matematika > Robotika > Pemrograman)
        $lfScore = ($analScore * 0.40) + ($matScore * 0.30) + ($robScore * 0.20) + ($progScore * 0.10);
        
        return [$this->scoreToLabel($mlScore), $this->scoreToLabel($lfScore)];
    }

    /**
     * Convert fuzzy set ke score numerik
     */
    private function setToScore($set): float
    {
        return match($set) {
            'kuat' => 3.0,
            'lumayan' => 2.0,
            'lemah' => 1.0,
            default => 0.0
        };
    }

    /**
     * Convert score ke label output (HANYA 3 KATEGORI)
     * Threshold disesuaikan untuk distribusi yang lebih seimbang
     */
    private function scoreToLabel($score): string
    {
        if ($score >= 2.34) return 'Sangat Cocok';  // Kuat (score 2.34-3.0)
        if ($score >= 1.67) return 'Cukup Cocok';   // Lumayan (score 1.67-2.33)
        return 'Kurang Cocok';                       // Lemah (score 1.0-1.66)
    }

    /**
     * METODE INVERSI TSUKAMOTO - HANYA 3 KATEGORI OUTPUT
     * 
     * Fungsi keanggotaan monoton untuk 3 kategori:
     * 1) Kurang Cocok: fungsi turun [40, 0] → range [0-40]
     * 2) Cukup Cocok: fungsi naik [30, 70] → range [30-70]
     * 3) Sangat Cocok: fungsi naik [60, 100] → range [60-100]
     */
    private function inferTsukamoto($label, $alpha): array
    {
        switch ($label) {
            case 'Kurang Cocok':
                // Fungsi TURUN: Z = y_max - α(y_max - y_min)
                $y_min = 0.0;
                $y_max = 40.0;
                $z = $y_max - ($alpha * ($y_max - $y_min));
                $formula = "{$this->fmt($y_max)} - ({$this->fmt($alpha)} × ({$this->fmt($y_max)} - {$this->fmt($y_min)}))";
                break;
                
            case 'Cukup Cocok':
                // Fungsi NAIK: Z = y_min + α(y_max - y_min)
                $y_min = 30.0;
                $y_max = 70.0;
                $z = $y_min + ($alpha * ($y_max - $y_min));
                $formula = "{$this->fmt($y_min)} + ({$this->fmt($alpha)} × ({$this->fmt($y_max)} - {$this->fmt($y_min)}))";
                break;
                
            case 'Sangat Cocok':
                // Fungsi NAIK: Z = y_min + α(y_max - y_min)
                $y_min = 60.0;
                $y_max = 100.0;
                $z = $y_min + ($alpha * ($y_max - $y_min));
                $formula = "{$this->fmt($y_min)} + ({$this->fmt($alpha)} × ({$this->fmt($y_max)} - {$this->fmt($y_min)}))";
                break;
                
            default:
                // Default ke Kurang Cocok
                $y_min = 0.0;
                $y_max = 40.0;
                $z = $y_max - ($alpha * ($y_max - $y_min));
                $formula = "{$this->fmt($y_max)} - ({$this->fmt($alpha)} × ({$this->fmt($y_max)} - {$this->fmt($y_min)}))";
                break;
        }
        
        return [
            'z' => $z,
            'formula' => $formula,
            'y_min' => $y_min,
            'y_max' => $y_max
        ];
    }

    /**
     * Get CSS class for fuzzy value
     */
    private function getFuzzyClass($set): string
    {
        return match($set) {
            'kuat' => 'strong',
            'lumayan' => 'medium',
            'lemah' => 'weak',
            default => 'weak'
        };
    }

    /**
     * Get CSS class for output label (HANYA 3 KATEGORI)
     */
    private function getOutputClass($label): string
    {
        return match($label) {
            'Sangat Cocok' => 'high',
            'Cukup Cocok' => 'medium',
            'Kurang Cocok' => 'low',
            default => 'low'
        };
    }

    // ============== HELPER FUNCTIONS ==============

    /**
     * Hitung derajat keanggotaan untuk semua himpunan fuzzy
     * Menggunakan fungsi trapesoid untuk 3 kategori: lemah, lumayan, kuat
     */
    private function membershipAll(float $x): array
    {
        return [
            'lemah'   => $this->trap($x, 0, 0, 20, 40),
            'lumayan' => $this->trap($x, 20, 40, 60, 80),
            'kuat'    => $this->trap($x, 60, 80, 100, 100),
        ];
    }

    /**
     * Fungsi keanggotaan trapesium
     */
    private function trap(float $x, float $a, float $b, float $c, float $d): float
    {
        if ($x <= $a || $x >= $d) return 0.0;
        if ($x >= $b && $x <= $c) return 1.0;
        if ($x > $a && $x < $b)   return ($x - $a) / max(1e-9, ($b - $a));
        if ($x > $c && $x < $d)   return ($d - $x) / max(1e-9, ($d - $c));
        return 0.0;
    }

    /**
     * Format angka dengan 2 desimal
     */
    private function fmt($x, int $n = 2): string
    {
        return number_format((float)$x, $n, '.', '');
    }

    /**
     * Konversi nilai huruf ke angka (skala 0-4)
     */
    private function hurufToAngka($huruf): float
    {
        return match(strtoupper($huruf ?? '')) {
            'A' => 4.0,
            'B' => 3.0,
            'C' => 2.0,
            'D' => 1.0,
            'E' => 0.0,
            default => 0.0,
        };
    }
}