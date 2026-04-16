<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class UraianTugas extends CI_Controller {
    public function __construct() {
        parent::__construct();
        $this->load->library('session');
        $this->load->model(['UraianTugas_model', 'Organisasi_model', 'Pegawai_model', 'BebanKerja_model']);
        if (!$this->session->userdata('logged_in') || $this->session->userdata('user_role') !== 'admin') redirect('auth');
    }

    public function index() {
        $data['cabang'] = $this->Organisasi_model->get_cabang();
        $data['unit'] = $this->Organisasi_model->get_unit();
        $data['jabatan'] = $this->Organisasi_model->get_jabatan();
        $data['user_name'] = $this->session->userdata('user_name');
        
        // Auto detect Periode Sekarang
        $data['filter_tahun'] = $this->input->get('tahun') ?: date('Y');
        $data['filter_bulan'] = $this->input->get('bulan') ?: date('m');
        $data['filter_cabang'] = $this->input->get('id_cabang');
        $data['filter_unit'] = $this->input->get('id_unit');
        $data['filter_jabatan'] = $this->input->get('id_jabatan');

        if ($data['filter_jabatan']) {
            $data['tugas'] = $this->UraianTugas_model->get_by_filter($data['filter_tahun'], $data['filter_bulan'], $data['filter_cabang'], $data['filter_unit'], $data['filter_jabatan']);
            $data['pegawai_list'] = $this->Pegawai_model->get_by_jabatan($data['filter_jabatan']);
            $data['all_volumes'] = $this->BebanKerja_model->get_all_volumes($data['filter_tahun'], $data['filter_bulan']);
        } else {
            $data['tugas'] = [];
            $data['pegawai_list'] = [];
            $data['all_volumes'] = [];
        }

        $this->load->view('admin/layout/header', $data);
        $this->load->view('admin/layout/sidebar', $data);
        $this->load->view('admin/master_uraian_tugas', $data);
        $this->load->view('admin/layout/footer', $data);
    }

    public function store() {
        $id_cabang = $this->input->post('id_cabang');
        $id_unit = $this->input->post('id_unit');
        $id_jabatan = $this->input->post('id_jabatan');
        $tahun = $this->input->post('tahun');
        $bulan_input = $this->input->post('bulan');
        $id_parent_input = empty($this->input->post('id_parent')) ? NULL : $this->input->post('id_parent');
        $nama_tugas = $this->input->post('nama_tugas');
        $output_pekerjaan = empty($this->input->post('output_pekerjaan')) ? NULL : $this->input->post('output_pekerjaan');
        $standar_waktu = $this->input->post('standar_waktu') !== '' ? $this->input->post('standar_waktu') : NULL;
        $keterangan = empty($this->input->post('keterangan')) ? NULL : $this->input->post('keterangan');
        $tindakan_petugas = empty($this->input->post('tindakan_petugas')) ? NULL : $this->input->post('tindakan_petugas');
        $is_active = $this->input->post('is_active');

        // Jika ada parent, cari namanya
        $nama_parent = '';
        if ($id_parent_input) {
            $p_row = $this->db->where('id_tugas', $id_parent_input)->get('wla_uraian_tugas')->row();
            if ($p_row) $nama_parent = strtolower(trim($p_row->nama_tugas));
        }

        $this->UraianTugas_model->insert([
            'id_cabang' => $id_cabang, 'id_unit' => $id_unit, 'id_jabatan' => $id_jabatan,
            'tahun' => $tahun, 'bulan' => $bulan_input, 'id_parent' => $id_parent_input,
            'nama_tugas' => $nama_tugas, 'output_pekerjaan' => $output_pekerjaan,
            'standar_waktu' => $standar_waktu, 'keterangan' => $keterangan,
            'tindakan_petugas' => $tindakan_petugas, 'is_active' => $is_active
        ]);

        $this->session->set_flashdata('success', 'Master Uraian Tugas berhasil ditambahkan untuk bulan ini.');
        redirect("admin/uraiantugas?tahun={$tahun}&bulan={$bulan_input}&id_cabang={$id_cabang}&id_unit={$id_unit}&id_jabatan={$id_jabatan}");
    }

    public function update() {
        $id = $this->input->post('id_tugas');
        $data = $this->input->post();
        unset($data['id_tugas']); // Hapus ID dari array update
        
        // Format nullable fields
        if(empty($data['id_parent'])) $data['id_parent'] = NULL;
        if($data['standar_waktu'] === '') $data['standar_waktu'] = NULL;
        if(empty($data['tindakan_petugas'])) $data['tindakan_petugas'] = NULL;

        $old_task = $this->db->where('id_tugas', $id)->get('wla_uraian_tugas')->row();

        if ($old_task) {
            // Hanya update untuk tugas di bulan terkait
            $this->db->where('id_tugas', $id)->update('wla_uraian_tugas', [
                'id_parent' => $data['id_parent'],
                'nama_tugas' => $data['nama_tugas'],
                'output_pekerjaan' => $data['output_pekerjaan'],
                'standar_waktu' => $data['standar_waktu'],
                'keterangan' => $data['keterangan'],
                'tindakan_petugas' => $data['tindakan_petugas'],
                'is_active' => $data['is_active']
            ]);
        }

        $this->session->set_flashdata('success', 'Master Uraian Tugas berhasil diperbarui.');
        redirect($_SERVER['HTTP_REFERER']);
    }

    public function delete($id) {
        $task = $this->db->where('id_tugas', $id)->get('wla_uraian_tugas')->row();
        
        if ($task) {
            // Jika yang dihapus adalah sub-tugas (memiliki parent), maka target hapus dialihkan ke parentnya
            $target_id = !empty($task->id_parent) ? $task->id_parent : $id;

            // Hapus semua sub-tugas (anak) dari target terlebih dahulu untuk mencegah orphaned data
            $this->db->where('id_parent', $target_id)->delete('wla_uraian_tugas');
            
            // Hapus tugas induknya (parent)
            $this->db->where('id_tugas', $target_id)->delete('wla_uraian_tugas');
        }

        $this->session->set_flashdata('success', 'Uraian Tugas berhasil dihapus permanen.');
        redirect($_SERVER['HTTP_REFERER']);
    }

    public function sync_months() {
        $tahun = $this->input->post('tahun');
        $bulan = $this->input->post('bulan');
        $id_cabang = $this->input->post('id_cabang');
        $id_unit = $this->input->post('id_unit');
        $id_jabatan = $this->input->post('id_jabatan');

        $source_tasks = $this->db->where([
            'tahun' => $tahun, 'bulan' => $bulan,
            'id_cabang' => $id_cabang, 'id_unit' => $id_unit, 'id_jabatan' => $id_jabatan
        ])->get('wla_uraian_tugas')->result();

        if (empty($source_tasks)) {
            $this->session->set_flashdata('error', 'Tidak ada tugas di bulan ini untuk disalin.');
            redirect($_SERVER['HTTP_REFERER']);
        }

        $parents = array_filter($source_tasks, function($t) { return empty($t->id_parent); });
        $children = array_filter($source_tasks, function($t) { return !empty($t->id_parent); });

        $count = 0;

        for ($i = 1; $i <= 12; $i++) {
            $m = str_pad($i, 2, '0', STR_PAD_LEFT);
            if ($m === $bulan) continue;

            $parent_map = [];
            foreach ($parents as $p) {
                $cek = $this->db->where(['tahun' => $tahun, 'bulan' => $m, 'id_cabang' => $id_cabang, 'id_unit' => $id_unit, 'id_jabatan' => $id_jabatan, 'nama_tugas' => $p->nama_tugas])->get('wla_uraian_tugas')->row();
                if (!$cek) {
                    $this->db->insert('wla_uraian_tugas', [
                        'id_cabang' => $id_cabang, 'id_unit' => $id_unit, 'id_jabatan' => $id_jabatan, 'tahun' => $tahun, 'bulan' => $m, 'id_parent' => NULL,
                        'nama_tugas' => $p->nama_tugas, 'output_pekerjaan' => $p->output_pekerjaan, 'standar_waktu' => $p->standar_waktu, 'keterangan' => $p->keterangan,
                        'tindakan_petugas' => $p->tindakan_petugas, 'is_active' => $p->is_active
                    ]);
                    $parent_map[$p->id_tugas] = $this->db->insert_id();
                    $count++;
                } else {
                    $parent_map[$p->id_tugas] = $cek->id_tugas;
                }
            }

            foreach ($children as $c) {
                // Dapatkan ID parent yang benar untuk bulan target dari map yang sudah dibuat
                $new_parent_id = isset($parent_map[$c->id_parent]) ? $parent_map[$c->id_parent] : NULL;

                // Jika parent tidak ditemukan di map, lewati proses untuk anak ini.
                if ($new_parent_id === NULL) continue;

                // Cek apakah anak sudah ada DI BAWAH PARENT YANG BENAR
                $cek = $this->db->where([
                    'tahun' => $tahun, 'bulan' => $m, 'id_cabang' => $id_cabang, 'id_unit' => $id_unit, 'id_jabatan' => $id_jabatan, 
                    'nama_tugas' => $c->nama_tugas,
                    'id_parent' => $new_parent_id
                ])->get('wla_uraian_tugas')->row();

                if (!$cek) {
                    $this->db->insert('wla_uraian_tugas', [
                        'id_cabang' => $id_cabang, 'id_unit' => $id_unit, 'id_jabatan' => $id_jabatan, 'tahun' => $tahun, 'bulan' => $m, 'id_parent' => $new_parent_id,
                        'nama_tugas' => $c->nama_tugas, 'output_pekerjaan' => $c->output_pekerjaan, 'standar_waktu' => $c->standar_waktu, 'keterangan' => $c->keterangan,
                        'tindakan_petugas' => $c->tindakan_petugas, 'is_active' => $c->is_active
                    ]);
                    $count++;
                }
            }
        }

        $this->session->set_flashdata('success', "Berhasil menerapkan data Uraian Tugas ke seluruh bulan di tahun {$tahun}.");
        redirect($_SERVER['HTTP_REFERER']);
    }

    public function import_excel() {
        require FCPATH . 'vendor/autoload.php';
        $file_mimes = ['application/octet-stream', 'application/vnd.ms-excel', 'application/x-csv', 'text/x-csv', 'text/csv', 'application/csv', 'application/excel', 'application/vnd.msexcel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'];
        
        $tahun = $this->input->post('tahun');
        $bulan = $this->input->post('bulan');
        $id_cabang = $this->input->post('id_cabang');
        $id_unit = $this->input->post('id_unit');
        $id_jabatan = $this->input->post('id_jabatan');

        if(isset($_FILES['file_excel']['name']) && in_array($_FILES['file_excel']['type'], $file_mimes)) {
            $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReaderForFile($_FILES['file_excel']['tmp_name']);
            $spreadsheet = $reader->load($_FILES['file_excel']['tmp_name']);
            $sheetData = $spreadsheet->getActiveSheet()->toArray(null, true, true, false); // Read raw values, not formatted.

            // --- REFINED HEADER AND DATA AREA DETECTION ---
            $header = ['uraian' => -1, 'output' => -1, 'standar' => -1, 'keterangan' => -1];
            $header_row_index = -1;
            $start_row = -1;

            // 1. Find the most likely header row (contains at least 2 keywords)
            for ($i = 0; $i < 25 && $i < count($sheetData); $i++) {
                $row = $sheetData[$i];
                $found_count = 0;
                if (is_array($row)) {
                    foreach ($row as $cell_val) {
                        $v = strtolower(trim((string)$cell_val));
                        if (strpos($v, 'uraian') !== false && strpos($v, 'tugas') !== false) $found_count++;
                        if (strpos($v, 'standar') !== false && strpos($v, 'waktu') !== false) $found_count++;
                        if (strpos($v, 'hasil') !== false && (strpos($v, 'kerja') !== false || strpos($v, 'output') !== false)) $found_count++;
                    }
                }
                if ($found_count >= 2) {
                    $header_row_index = $i;
                }
            }

            if ($header_row_index === -1) {
                $this->session->set_flashdata('error', 'Gagal Import: Tidak dapat menemukan baris header yang valid (cth: Uraian Tugas, Standar Waktu) di dalam file Excel.');
                redirect($_SERVER['HTTP_REFERER']);
            }

            // 2. Map column indices from the found header row
            foreach ($sheetData[$header_row_index] as $col_index => $cell_val) {
                $v = strtolower(trim((string)$cell_val));
                if ($header['uraian'] == -1 && strpos($v, 'uraian') !== false && strpos($v, 'tugas') !== false) $header['uraian'] = $col_index;
                if ($header['output'] == -1 && (strpos($v, 'hasil') !== false || strpos($v, 'output') !== false)) $header['output'] = $col_index;
                if ($header['standar'] == -1 && (strpos($v, 'standar') !== false && strpos($v, 'waktu') !== false)) $header['standar'] = $col_index;
                if ($header['keterangan'] == -1 && strpos($v, 'keterangan') !== false) $header['keterangan'] = $col_index;
            }

            // 3. Find the actual start of data (first row with a number in the first column)
            for ($i = $header_row_index + 1; $i < count($sheetData); $i++) {
                $first_cell_val = isset($sheetData[$i][0]) ? trim((string)$sheetData[$i][0]) : '';
                if (is_numeric($first_cell_val) && (int)$first_cell_val > 0) {
                    $start_row = $i;
                    break;
                }
            }

            if ($start_row === -1) {
                $this->session->set_flashdata('error', 'Gagal Import: Header ditemukan, namun tidak ada baris data yang valid (baris dimulai dengan angka).');
                redirect($_SERVER['HTTP_REFERER']);
            }

            $success_count = 0;
            $current_parent_id = NULL; // ID parent untuk bulan ini
            $prefix_stack = []; // Stack untuk menyimpan konteks hirarki teks

            // 4. Process rows from the data start row
            for ($key = $start_row; $key < count($sheetData); $key++) {
                $row = $sheetData[$key];

                // Gabungkan kolom-kolom sebelum Uraian sebagai NO
                $no_parts = [];
                for ($col = 0; $col < $header['uraian']; $col++) {
                    $val = trim((string)($row[$col] ?? ''));
                    if ($val !== '') $no_parts[] = $val;
                }
                $no_col = implode(' ', $no_parts);

                // Gabungkan kolom Uraian hingga sebelum Output (mengatasi Uraian yang di-indent multi-kolom)
                $uraian_parts = [];
                $indent_level = 0;
                $found_text = false;
                $end_col = ($header['output'] > $header['uraian']) ? $header['output'] : $header['uraian'] + 1;
                for ($col = $header['uraian']; $col < $end_col; $col++) {
                    $raw_val = (string)($row[$col] ?? '');
                    $val = trim($raw_val);
                    if ($val !== '') {
                        $uraian_parts[] = $val;
                        if (!$found_text) {
                            // Hitung level indentasi dari spasi di awal kata atau posisi kolom Excel
                            preg_match('/^\s*/', $raw_val, $spaces);
                            $space_count = strlen(str_replace("\t", "    ", $spaces[0] ?? ''));
                            $indent_level = ($col - $header['uraian']) * 10 + $space_count;
                            $found_text = true;
                        }
                    }
                }
                $uraian = implode(' ', $uraian_parts);
                
                $output = ($header['output'] > -1) ? trim((string)($row[$header['output']] ?? '')) : '';
                
                $uraian_lower = strtolower(trim($uraian));
                $no_col_lower = strtolower($no_col);
                
                // Break loop jika sudah mencapai akhir tabel (Kebutuhan Pegawai / Total)
                if (strpos($uraian_lower, 'kebutuhan pegawai') !== false || strpos($no_col_lower, 'kebutuhan pegawai') !== false) {
                    break;
                }
                
                // Skip baris kosong atau header tersisa
                if (empty($uraian_lower) || strlen($uraian_lower) <= 1 || strpos($uraian_lower, 'uraian tugas') !== false) {
                    continue;
                }

                // Extract angka standar waktu dengan regex agar aman
                $standar_waktu = ($header['standar'] > -1) ? trim((string)($row[$header['standar']] ?? '')) : '';
                $sw_val = null;
                if ($standar_waktu !== '') {
                    if (preg_match('/[0-9]+(?:\.[0-9]+)?/', str_replace(',', '.', $standar_waktu), $matches)) {
                        $sw_val = $matches[0];
                    }
                }
                // Jika standar waktu kosong, coba geser kolom +/- 1 (seringkali format excel merger kolom meleset)
                if ($sw_val === null && $header['standar'] > -1) {
                    $sw_min1 = trim((string)($row[$header['standar'] - 1] ?? ''));
                    $sw_plus1 = trim((string)($row[$header['standar'] + 1] ?? ''));
                    if (is_numeric($sw_min1) && preg_match('/[0-9]+(?:\.[0-9]+)?/', str_replace(',', '.', $sw_min1), $matches)) {
                        $sw_val = $matches[0];
                    } elseif (is_numeric($sw_plus1) && preg_match('/[0-9]+(?:\.[0-9]+)?/', str_replace(',', '.', $sw_plus1), $matches)) {
                        $sw_val = $matches[0];
                    }
                }
                
                $keterangan = ($header['keterangan'] > -1) ? trim((string)($row[$header['keterangan']] ?? '')) : '';

                // Deteksi Parent/Child dari Penomoran di kolom pertama (NO)
                $is_parent = false;
                if (preg_match('/^[0-9]+$/', $no_col)) {
                    $is_parent = true;
                    $prefix_stack = []; // Reset hirarki jika masuk ke Parent baru
                } else if (empty($current_parent_id)) {
                    $is_parent = true; // Fallback parent pertama
                    $prefix_stack = [];
                }

                // Bersihkan abjad/poin di awal uraian tugas
                $uraian_bersih = preg_replace('/^[a-zA-Z][\.\)]?\s+/', '', $uraian);
                $uraian_bersih = preg_replace('/^[\-\*]\s+/', '', $uraian_bersih);

                // Cek apakah baris ini adalah header grup/kategori (TIDAK ADA standar waktu dan BUKAN parent utama)
                $is_group_header = ($sw_val === null || $sw_val === '') && !$is_parent;

                if ($is_group_header && !empty(trim($uraian_bersih))) {
                    // Simpan sebagai prefix di level kedalamannya, hapus konteks level yang lebih dalam
                    $prefix_stack[$indent_level] = trim($uraian_bersih);
                    foreach (array_keys($prefix_stack) as $k) {
                        if ($k > $indent_level) unset($prefix_stack[$k]);
                    }
                    continue; // Skip insert ke DB, karena baris ini hanya sebagai judul penyambung konteks
                }

                // Jika ini adalah Sub-Tugas (Child), gabungkan prefix hirarki yang ada agar konteks tugasnya jelas
                if (!$is_parent && !empty(trim($uraian_bersih))) {
                    ksort($prefix_stack); // Pastikan urut dari level teratas
                    $prefix_str = implode(' - ', $prefix_stack);
                    $uraian = !empty($prefix_str) ? $prefix_str . ' - ' . trim($uraian_bersih) : trim($uraian_bersih);
                } else {
                    $uraian = trim($uraian);
                }

                // Hanya proses jika Uraian Tugas valid
                if (!empty($uraian)) {
                    // Tambahkan id_parent ke dalam pengecekan duplikat
                    $cek = $this->db->where([
                        'tahun' => $tahun, 'bulan' => $bulan, 
                        'id_cabang' => $id_cabang, 'id_unit' => $id_unit, 'id_jabatan' => $id_jabatan, 
                        'nama_tugas' => $uraian, 'id_parent' => $is_parent ? NULL : $current_parent_id
                    ])->get('wla_uraian_tugas')->row();

                    if (!$cek) {
                        $this->db->insert('wla_uraian_tugas', [
                            'id_cabang' => $id_cabang, 'id_unit' => $id_unit, 'id_jabatan' => $id_jabatan, 'tahun' => $tahun, 'bulan' => $bulan, 
                            'id_parent' => $is_parent ? NULL : $current_parent_id,
                            'nama_tugas' => $uraian, 'output_pekerjaan' => empty($output) ? NULL : $output,
                            'standar_waktu' => $sw_val, 'keterangan' => empty($keterangan) ? NULL : $keterangan,
                            'is_active' => 1
                        ]);
                        if ($is_parent) $current_parent_id = $this->db->insert_id();
                    } else {
                        if ($is_parent) $current_parent_id = $cek->id_tugas;
                    }
                    $success_count++;
                }
            }
            $this->session->set_flashdata('success', "Berhasil mengimport {$success_count} baris Uraian Tugas untuk bulan ini.");
        } else {
            $this->session->set_flashdata('error', 'Gagal Import: File tidak valid atau format tidak sesuai (.xlsx)');
        }
        redirect($_SERVER['HTTP_REFERER']);
    }
}