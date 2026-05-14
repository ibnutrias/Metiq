/**
 * Metiq Core Engine
 * Menangani fungsi reusable untuk UI dan interaksi SLiMS
 */

// Gunakan window.Metiq agar bisa diakses secara global meskipun di-load via AJAX jQuery
window.Metiq = {
    /**
     * Memuat ulang kontainer utama SLiMS tanpa hard-refresh halaman
     * @param {string} url - URL plugin saat ini (biasanya $_SERVER["REQUEST_URI"])
     */
    reloadView: function(url) {
        if ($('#main-content').length) {
            $('#main-content').load(url);
        } else if ($('#metiq-app-container').length) {
            $('#metiq-app-container').parent().load(url);
        } else {
            window.location.reload(); // Absolute fallback
        }
    },

    /**
     * Menangani form submit via AJAX dengan indikator loading otomatis
     * @param {string} url - URL endpoint (router)
     * @param {object} payload - Data POST (contoh: { action: 'nama_aksi', data: '...' })
     * @param {jQuery} $btn - Elemen tombol submit untuk diubah state-nya
     * @param {function} onSuccess - Callback ketika request sukses
     */
    postAction: function(url, payload, $btn, onSuccess) {
        const originalHtml = $btn.html();
        
        // Disable button & ubah ke loading state
        $btn.html('<i class="fa-solid fa-circle-notch fa-spin mr-2"></i> Memproses...').addClass('disabled').prop('disabled', true);

        $.post(url, payload, function(res) {
            if (res.status === 'success') {
                if (typeof onSuccess === 'function') onSuccess(res);
            } else {
                alert('Metiq Error: ' + (res.message || 'Terjadi kesalahan sistem.'));
                // Kembalikan tombol jika error
                $btn.html(originalHtml).removeClass('disabled').prop('disabled', false);
            }
        }, 'json').fail(function() {
            alert('Metiq Error: Gagal menghubungi server SLiMS.');
            // Kembalikan tombol jika gagal koneksi
            $btn.html(originalHtml).removeClass('disabled').prop('disabled', false);
        });
    }
};