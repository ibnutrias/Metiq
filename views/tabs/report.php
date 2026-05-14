<?php 
defined('INDEX_AUTH') OR die('Direct access not allowed!'); 

$jsonPath = dirname(__DIR__, 2) . '/database/latest_anomalies.json';
$anomalies = file_exists($jsonPath) ? json_decode(file_get_contents($jsonPath) ?: '[]', true) : [];

$errorLabels = [
    'req_title' => '<span class="badge badge-danger p-2 mr-1 mb-1 shadow-sm"><i class="fa-solid fa-heading mr-1"></i> Judul Wajib</span>',
    'req_author' => '<span class="badge badge-danger p-2 mr-1 mb-1 shadow-sm"><i class="fa-solid fa-users mr-1"></i> Pengarang Wajib</span>',
    'req_isbn' => '<span class="badge badge-danger p-2 mr-1 mb-1 shadow-sm"><i class="fa-solid fa-barcode mr-1"></i> ISBN Wajib</span>',
    'req_publisher' => '<span class="badge badge-danger p-2 mr-1 mb-1 shadow-sm"><i class="fa-solid fa-building mr-1"></i> Penerbit Wajib</span>',
    'req_year' => '<span class="badge badge-danger p-2 mr-1 mb-1 shadow-sm"><i class="fa-solid fa-calendar mr-1"></i> Tahun Wajib</span>',
    'req_place' => '<span class="badge badge-danger p-2 mr-1 mb-1 shadow-sm"><i class="fa-solid fa-map-marker-alt mr-1"></i> Kota Terbit Wajib</span>',
    'req_callnumber' => '<span class="badge badge-danger p-2 mr-1 mb-1 shadow-sm"><i class="fa-solid fa-phone-square mr-1"></i> Call Number Wajib</span>',
    'req_notes' => '<span class="badge badge-danger p-2 mr-1 mb-1 shadow-sm"><i class="fa-solid fa-sticky-note mr-1"></i> Abstrak Wajib</span>',
    'req_image' => '<span class="badge badge-danger p-2 mr-1 mb-1 shadow-sm"><i class="fa-solid fa-image mr-1"></i> Cover Wajib</span>',
    'req_class' => '<span class="badge badge-danger p-2 mr-1 mb-1 shadow-sm"><i class="fa-solid fa-tags mr-1"></i> Klasifikasi Wajib</span>',

    'fmt_isbn' => '<span class="badge badge-warning p-2 mr-1 mb-1 shadow-sm text-dark"><i class="fa-solid fa-triangle-exclamation mr-1"></i> Format ISBN Salah</span>',
    'fmt_class' => '<span class="badge badge-warning p-2 mr-1 mb-1 shadow-sm text-dark"><i class="fa-solid fa-triangle-exclamation mr-1"></i> Format DDC Salah</span>',

    'rec_sor' => '<span class="badge badge-light border p-1 mr-1 mb-1 text-muted"><i class="fa-solid fa-pen-nib mr-1"></i> SOR</span>',
    'rec_edition' => '<span class="badge badge-light border p-1 mr-1 mb-1 text-muted"><i class="fa-solid fa-copy mr-1"></i> Edisi</span>',
    'rec_gmd' => '<span class="badge badge-light border p-1 mr-1 mb-1 text-muted"><i class="fa-solid fa-compact-disc mr-1"></i> GMD</span>',
    'rec_content' => '<span class="badge badge-light border p-1 mr-1 mb-1 text-muted"><i class="fa-solid fa-file-alt mr-1"></i> Tipe Isi</span>',
    'rec_media' => '<span class="badge badge-light border p-1 mr-1 mb-1 text-muted"><i class="fa-solid fa-play-circle mr-1"></i> Tipe Media</span>',
    'rec_carrier' => '<span class="badge badge-light border p-1 mr-1 mb-1 text-muted"><i class="fa-solid fa-box mr-1"></i> Pembawa</span>',
    'rec_frequency' => '<span class="badge badge-light border p-1 mr-1 mb-1 text-muted"><i class="fa-solid fa-clock-rotate-left mr-1"></i> Frekuensi</span>',
    'rec_collation' => '<span class="badge badge-light border p-1 mr-1 mb-1 text-muted"><i class="fa-solid fa-layer-group mr-1"></i> Kolasi</span>',
    'rec_series' => '<span class="badge badge-light border p-1 mr-1 mb-1 text-muted"><i class="fa-solid fa-layer-group mr-1"></i> Seri</span>',
    'rec_subject' => '<span class="badge badge-light border p-1 mr-1 mb-1 text-muted"><i class="fa-solid fa-bookmark mr-1"></i> Subjek</span>',
    'rec_language' => '<span class="badge badge-light border p-1 mr-1 mb-1 text-muted"><i class="fa-solid fa-language mr-1"></i> Bahasa</span>',
    'rec_labels' => '<span class="badge badge-light border p-1 mr-1 mb-1 text-muted"><i class="fa-solid fa-tag mr-1"></i> Label</span>'
];
?>

<style>
    .metiq-link-detail { color: #007bff; cursor: pointer; font-weight: 700; transition: all 0.2s; border-bottom: 1px dashed transparent; }
    .metiq-link-detail:hover { color: #0056b3; border-bottom-color: #0056b3; text-decoration: none; }
    .dataTables_filter input { border: 1px solid #ced4da; border-radius: 8px; padding: 6px 12px; outline: none; }
    .dataTables_paginate, .dataTables_info { display: none !important; }
    .detail-label { font-size: 0.75rem; text-transform: uppercase; color: #6c757d; font-weight: 700; margin-bottom: 2px; }
    .detail-value { font-size: 0.95rem; font-weight: 600; color: #343a40; margin-bottom: 10px; border-bottom: 1px solid #f0f0f0; padding-bottom: 6px;}
    .cover-preview { width: 100%; max-width: 200px; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); object-fit: cover; }
    .filter-btn-active { background-color: #e8f0fe; border-color: #0d6efd; color: #0d6efd; }
</style>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h5 class="font-weight-bold mb-0">Laporan Detail Anomali</h5>
        <p class="text-muted small mb-0">Gunakan filter lanjutan untuk menyeleksi masalah secara spesifik.</p>
    </div>
    <div class="d-flex align-items-center">
        <button class="btn btn-outline-primary font-weight-bold shadow-sm mr-2" data-toggle="modal" data-target="#advancedFilterModal" id="btnOpenFilter">
            <i class="fa-solid fa-filter mr-1"></i> <span id="filterBtnText">Filter Laporan</span>
        </button>

        <?php if (!empty($anomalies)): ?>
        <button class="btn btn-outline-secondary font-weight-bold shadow-sm" id="btnExportCSV">
            <i class="fa-solid fa-file-csv mr-1"></i> Ekspor
        </button>
        <?php endif; ?>
    </div>
</div>

<div class="table-responsive">
    <table class="table table-hover border-bottom" id="tableAnomalies" style="width:100%">
        <thead class="bg-light text-uppercase small">
            <tr>
                <th style="width: 80px;">ID</th>
                <th style="width: 35%;">Judul Bibliografi</th>
                <th>Masalah Metadata</th>
                <th class="text-center" style="width: 100px;">Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($anomalies as $row): ?>
            <tr>
                <td class="align-middle text-muted font-weight-bold">#<?= (int)$row['biblio_id'] ?></td>
                <td class="align-middle">
                    <span class="metiq-link-detail" data-id="<?= (int)$row['biblio_id'] ?>">
                        <?= htmlspecialchars($row['title']) ?>
                    </span>
                </td>
                <td class="align-middle" data-errors="<?= implode(',', $row['errors']) ?>">
                    <div class="d-flex flex-wrap anomaly-container">
                        <?php 
                        foreach ($row['errors'] as $err) {
                            echo $errorLabels[$err] ?? '';
                        }
                        ?>
                    </div>
                </td>
                <td class="align-middle text-center">
                    <button class="btn btn-sm btn-primary btn-quick-fix shadow-sm" data-id="<?= (int)$row['biblio_id'] ?>">
                        <i class="fa-solid fa-bolt"></i> Fix
                    </button>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<div id="loadMoreTrigger" class="text-center py-5">
    <div id="loadingStatus">
        <i class="fa-solid fa-circle-notch fa-spin text-primary fa-lg mr-2"></i>
        <span class="text-muted font-weight-bold small">Memuat data lebih banyak...</span>
    </div>
    <div id="endStatus" style="display:none;">
        <i class="fa-solid fa-check-circle text-success mr-2"></i>
        <span class="text-muted small">Semua data telah ditampilkan.</span>
    </div>
</div>

<div class="modal fade" id="advancedFilterModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 16px;">
            <div class="modal-header bg-light border-0">
                <h6 class="modal-title font-weight-bold text-dark"><i class="fa-solid fa-filter text-primary mr-2"></i> Filter Spesifik Anomali</h6>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body p-4">
                <p class="small text-muted mb-3">Pilih satu atau beberapa jenis anomali untuk ditampilkan. Kosongkan semua untuk melihat seluruh data.</p>
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="small font-weight-bold text-uppercase text-danger">Kekosongan Wajib</h6>
                        <div class="custom-control custom-checkbox mb-2"><input type="checkbox" class="custom-control-input filter-cb" id="f_req_title" value="req_title"><label class="custom-control-label" for="f_req_title">Judul Kosong</label></div>
                        <div class="custom-control custom-checkbox mb-2"><input type="checkbox" class="custom-control-input filter-cb" id="f_req_author" value="req_author"><label class="custom-control-label" for="f_req_author">Pengarang Kosong</label></div>
                        <div class="custom-control custom-checkbox mb-2"><input type="checkbox" class="custom-control-input filter-cb" id="f_req_isbn" value="req_isbn"><label class="custom-control-label" for="f_req_isbn">ISBN Kosong</label></div>
                        <div class="custom-control custom-checkbox mb-2"><input type="checkbox" class="custom-control-input filter-cb" id="f_req_publisher" value="req_publisher"><label class="custom-control-label" for="f_req_publisher">Penerbit Kosong</label></div>
                        <div class="custom-control custom-checkbox mb-2"><input type="checkbox" class="custom-control-input filter-cb" id="f_req_year" value="req_year"><label class="custom-control-label" for="f_req_year">Tahun Kosong</label></div>
                        <div class="custom-control custom-checkbox mb-2"><input type="checkbox" class="custom-control-input filter-cb" id="f_req_place" value="req_place"><label class="custom-control-label" for="f_req_place">Kota Terbit Kosong</label></div>
                    </div>
                    <div class="col-md-6">
                        <div class="custom-control custom-checkbox mb-2"><input type="checkbox" class="custom-control-input filter-cb" id="f_req_callnumber" value="req_callnumber"><label class="custom-control-label" for="f_req_callnumber">Call Number Kosong</label></div>
                        <div class="custom-control custom-checkbox mb-2"><input type="checkbox" class="custom-control-input filter-cb" id="f_req_class" value="req_class"><label class="custom-control-label" for="f_req_class">DDC Kosong</label></div>
                        <div class="custom-control custom-checkbox mb-2"><input type="checkbox" class="custom-control-input filter-cb" id="f_req_image" value="req_image"><label class="custom-control-label" for="f_req_image">Cover Kosong</label></div>
                        <div class="custom-control custom-checkbox mb-4"><input type="checkbox" class="custom-control-input filter-cb" id="f_req_notes" value="req_notes"><label class="custom-control-label" for="f_req_notes">Abstrak Kosong</label></div>
                        
                        <h6 class="small font-weight-bold text-uppercase text-warning mt-3">Format & Pola</h6>
                        <div class="custom-control custom-checkbox mb-2"><input type="checkbox" class="custom-control-input filter-cb" id="f_fmt_isbn" value="fmt_isbn"><label class="custom-control-label" for="f_fmt_isbn">Format ISBN Salah</label></div>
                        <div class="custom-control custom-checkbox mb-2"><input type="checkbox" class="custom-control-input filter-cb" id="f_fmt_class" value="fmt_class"><label class="custom-control-label" for="f_fmt_class">Format DDC Salah</label></div>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light border-0">
                <button type="button" class="btn btn-outline-secondary font-weight-bold mr-auto" id="btnResetFilter">Reset Filter</button>
                <button type="button" class="btn btn-secondary font-weight-bold" data-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary font-weight-bold px-4" id="btnApplyFilter">Terapkan Filter</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="detailModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl" role="document">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 16px;">
            <div class="modal-header bg-light border-0">
                <h6 class="modal-title font-weight-bold text-dark"><i class="fa-solid fa-book-open text-info mr-2"></i> Rincian Bibliografi</h6>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body p-4">
                <div class="row">
                    <div class="col-md-3 text-center border-right">
                        <img id="detail_cover" src="" alt="Cover" class="cover-preview mb-3 bg-light">
                        <div id="detail_problems" class="d-flex flex-wrap justify-content-center mt-3"></div>
                    </div>
                    <div class="col-md-9 pl-4">
                        <h4 id="detail_title" class="font-weight-bold text-dark mb-4 pb-2 border-bottom"></h4>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="detail-label">Pengarang Utama (Authors)</div>
                                <div class="detail-value text-primary" id="detail_author"></div>
                                <div class="detail-label">Statement of Responsibility (SOR)</div>
                                <div class="detail-value" id="detail_sor"></div>
                                <div class="detail-label">Penerbit & Tempat</div>
                                <div class="detail-value" id="detail_pub"></div>
                                <div class="detail-label">Tahun Terbit</div>
                                <div class="detail-value" id="detail_year"></div>
                                <div class="detail-label">ISBN / ISSN</div>
                                <div class="detail-value" id="detail_isbn"></div>
                                <div class="detail-label">Edisi & Seri</div>
                                <div class="detail-value" id="detail_edition_series"></div>
                            </div>
                            <div class="col-md-6">
                                <div class="detail-label">Nomor Panggil (Call Number)</div>
                                <div class="detail-value text-danger" id="detail_callnumber"></div>
                                <div class="detail-label">Klasifikasi (DDC)</div>
                                <div class="detail-value" id="detail_class"></div>
                                <div class="detail-label">Subjek</div>
                                <div class="detail-value" id="detail_subject"></div>
                                <div class="detail-label">GMD & Deskripsi Fisik (Collation)</div>
                                <div class="detail-value" id="detail_gmd_col"></div>
                                <div class="detail-label">Abstrak / Catatan</div>
                                <div class="detail-value" style="font-size: 0.85rem; max-height: 80px; overflow-y: auto;" id="detail_notes"></div>
                                <div class="detail-label">Label</div>
                                <div class="detail-value border-0" id="detail_labels"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light border-0">
                <button type="button" class="btn btn-secondary font-weight-bold" data-dismiss="modal">Tutup</button>
                <button type="button" class="btn btn-primary font-weight-bold px-4" id="btnEditFull">
                    <i class="fa-solid fa-pen-to-square mr-2"></i> Lengkapi di Editor SLiMS
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="quickFixModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 16px;">
            <div class="modal-header bg-primary text-white border-0">
                <h6 class="modal-title font-weight-bold"><i class="fa-solid fa-wand-magic-sparkles mr-2"></i> Quick Fix Metadata Inti</h6>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <form id="quickFixForm">
                <div class="modal-body p-4">
                    <input type="hidden" id="qf_biblio_id">
                    <div class="form-group mb-4">
                        <label class="font-weight-bold text-dark small text-uppercase">Judul Bibliografi</label>
                        <input type="text" class="form-control form-control-lg border-0 bg-light" id="qf_title" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6 form-group"><label class="small text-uppercase font-weight-bold">SOR</label><input type="text" class="form-control border-0 bg-light" id="qf_sor"></div>
                        <div class="col-md-6 form-group"><label class="small text-uppercase font-weight-bold">Tahun Terbit</label><input type="text" class="form-control border-0 bg-light" id="qf_publish_year"></div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-md-6 form-group"><label class="small text-uppercase font-weight-bold">ISBN/ISSN</label><input type="text" class="form-control border-0 bg-light" id="qf_isbn_issn"></div>
                        <div class="col-md-6 form-group"><label class="small text-uppercase font-weight-bold">DDC</label><input type="text" class="form-control border-0 bg-light" id="qf_classification"></div>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4">
                    <button type="button" class="btn btn-light px-4 font-weight-bold" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary px-5 font-weight-bold shadow-sm" id="btnSaveFix">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    const ajaxUrl = '<?= $_SERVER["REQUEST_URI"] ?>';
    const anomaliesData = <?= json_encode($anomalies) ?>;
    const totalData = anomaliesData.length;
    let currentLimit = 10;
    let tableAnomalies = null;
    let activeFilters = [];

    if ($.fn.DataTable) {
        // Hapus custom engine yang sudah ada sebelumnya
        $.fn.dataTable.ext.search.pop();

        tableAnomalies = $('#tableAnomalies').DataTable({
            "pageLength": currentLimit,
            "order": [[ 0, "desc" ]],
            "dom": '<"d-flex justify-content-between align-items-center mb-4"f>rt',
            "columnDefs": [ { "orderable": false, "targets": [2, 3] } ],
            "language": { "search": "", "searchPlaceholder": "Cari judul atau ID..." }
        });

        // Register Custom Search untuk Modal Filter
        $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
            if (activeFilters.length === 0) return true; // Tampilkan semua jika tidak ada filter
            
            // Ambil atribut data-errors dari <td> index ke-2
            const rowNode = tableAnomalies.row(dataIndex).node();
            const rowErrors = $(rowNode).find('td:eq(2)').attr('data-errors');
            
            if (!rowErrors) return false;
            const errorArray = rowErrors.split(',');

            // Jika buku memiliki SATU SAJA masalah yang sedang difilter, tampilkan.
            for (let i = 0; i < activeFilters.length; i++) {
                if (errorArray.includes(activeFilters[i])) return true;
            }
            return false;
        });
    }

    // APLIKASIKAN FILTER
    $('#btnApplyFilter').on('click', function() {
        activeFilters = [];
        $('.filter-cb:checked').each(function() {
            activeFilters.push($(this).val());
        });

        if (activeFilters.length > 0) {
            $('#filterBtnText').html(`Tampil: ${activeFilters.length} Filter`);
            $('#btnOpenFilter').removeClass('btn-outline-primary').addClass('filter-btn-active');
            
            // Nonaktifkan paginasi agar seluruh data filter tampil
            tableAnomalies.page.len(-1).draw();
            $('#loadMoreTrigger').hide();
        } else {
            resetFilterUI();
        }
        $('#advancedFilterModal').modal('hide');
    });

    // RESET FILTER
    $('#btnResetFilter').on('click', function() {
        $('.filter-cb').prop('checked', false);
        activeFilters = [];
        resetFilterUI();
        $('#advancedFilterModal').modal('hide');
    });

    function resetFilterUI() {
        $('#filterBtnText').html('Filter Laporan');
        $('#btnOpenFilter').removeClass('filter-btn-active').addClass('btn-outline-primary');
        tableAnomalies.page.len(currentLimit).draw();
        $('#loadMoreTrigger').show();
        updateEndStatus();
    }

    $('#btnExportCSV').on('click', function() {
        window.location.href = ajaxUrl + '&action=export_csv';
    });

    const updateEndStatus = () => {
        if (currentLimit >= totalData || totalData === 0) {
            $('#loadingStatus').hide();
            $('#endStatus').show();
        } else {
            $('#loadingStatus').show();
            $('#endStatus').hide();
        }
    };
    updateEndStatus();

    if ('IntersectionObserver' in window && tableAnomalies) {
        const observer = new IntersectionObserver((entries) => {
            // Hanya aktif jika filter tidak ada
            if (entries[0].isIntersecting && currentLimit < totalData && activeFilters.length === 0) {
                setTimeout(() => {
                    currentLimit += 10;
                    tableAnomalies.page.len(currentLimit).draw(false);
                    updateEndStatus();
                }, 300);
            }
        }, { threshold: 0.1 });
        observer.observe(document.getElementById('loadMoreTrigger'));
    }

    $('#tableAnomalies').on('click', '.metiq-link-detail', function() {
        const id = $(this).data('id');
        const anomaly = anomaliesData.find(a => a.biblio_id == id);
        
        $('#detail_title').html('<i class="fa-solid fa-circle-notch fa-spin text-primary"></i> Memuat...');
        $('.detail-value').html('<i class="fa-solid fa-ellipsis text-muted"></i>');
        $('#detail_cover').attr('src', '../images/default/image.png');
        $('#detail_problems').empty();
        $('#detailModal').modal('show');

        $.post(ajaxUrl, { action: 'get_biblio', id: id }, function(res) {
            if (res.status === 'success' && res.data) {
                const d = res.data;
                const fmt = (val) => val ? val : '<span class="text-danger font-italic small">Kosong</span>';
                
                $('#detail_title').text(d.title || 'Tanpa Judul');
                if (d.image) $('#detail_cover').attr('src', '../images/docs/' + d.image);

                $('#detail_author').html(fmt(d.author_names));
                $('#detail_sor').html(fmt(d.sor));
                $('#detail_pub').html(fmt(d.publisher_name) + ' / ' + fmt(d.place_name));
                $('#detail_year').html(fmt(d.publish_year));
                $('#detail_isbn').html(fmt(d.isbn_issn));
                $('#detail_edition_series').html(fmt(d.edition) + ' / Seri: ' + fmt(d.series_title));
                $('#detail_callnumber').html(fmt(d.call_number));
                $('#detail_class').html(fmt(d.classification));
                $('#detail_subject').html(fmt(d.topic_names));
                $('#detail_gmd_col').html(fmt(d.gmd_name) + ' | ' + fmt(d.collation));
                $('#detail_notes').html(fmt(d.notes));
                $('#detail_labels').html(fmt(d.labels));
                
                if (anomaly && anomaly.errors) {
                    let badges = '';
                    const labelMapping = <?= json_encode($errorLabels) ?>;
                    anomaly.errors.forEach(err => badges += (labelMapping[err] || ''));
                    $('#detail_problems').html(badges || '<span class="badge badge-success p-2"><i class="fa-solid fa-check mr-1"></i> Sempurna</span>');
                }

                $('#btnEditFull').off('click').on('click', function() {
                    window.location.href = 'index.php?mod=bibliography&itemID=' + id + '&detail=true';
                });
            }
        }, 'json');
    });

    $('#tableAnomalies').on('click', '.btn-quick-fix', function() {
        const id = $(this).data('id');
        const $btn = $(this);
        const originalHtml = $btn.html();
        $btn.html('<i class="fa-solid fa-spinner fa-spin"></i>').prop('disabled', true);

        $.post(ajaxUrl, { action: 'get_biblio', id: id }, function(res) {
            $btn.html(originalHtml).prop('disabled', false);
            if (res.status === 'success' && res.data) {
                const d = res.data;
                $('#qf_biblio_id').val(d.biblio_id);
                $('#qf_title').val(d.title);
                $('#qf_sor').val(d.sor);
                $('#qf_publish_year').val(d.publish_year);
                $('#qf_isbn_issn').val(d.isbn_issn);
                $('#qf_classification').val(d.classification);
                $('#quickFixModal').modal('show');
            }
        }, 'json');
    });

    $('#quickFixForm').on('submit', function(e) {
        e.preventDefault();
        const payload = {
            title: $('#qf_title').val(),
            sor: $('#qf_sor').val(),
            publish_year: $('#qf_publish_year').val(),
            isbn_issn: $('#qf_isbn_issn').val(),
            classification: $('#qf_classification').val()
        };

        if (typeof window.Metiq !== 'undefined') {
            window.Metiq.postAction(ajaxUrl, { action: 'update_biblio', id: $('#qf_biblio_id').val(), data: payload }, $('#btnSaveFix'), function() {
                $('#quickFixModal').modal('hide');
                loadMetiqTab('report'); 
            });
        }
    });
});
</script>