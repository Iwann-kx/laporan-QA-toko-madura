from datetime import datetime
from pathlib import Path

from reportlab.lib import colors
from reportlab.lib.enums import TA_CENTER
from reportlab.lib.pagesizes import A4
from reportlab.lib.styles import getSampleStyleSheet, ParagraphStyle
from reportlab.lib.units import cm
from reportlab.pdfbase import pdfmetrics
from reportlab.pdfbase.ttfonts import TTFont
from reportlab.platypus import (
    SimpleDocTemplate,
    Paragraph,
    Spacer,
    Table,
    TableStyle,
    PageBreak,
)


ROOT = Path(__file__).resolve().parents[1]
OUTPUT = ROOT / "reports" / "Laporan_QA_Toko_Madura.pdf"

TEST_CASES = [
    ("TC001", "Unit", "test_that_true_is_true", "PASS", "Dasar PHPUnit berjalan."),
    ("TC002", "Navigasi", "test_home_redirects_to_dashboard", "PASS", "Akses root diarahkan ke dashboard."),
    ("TC003", "Keuangan", "test_owner_can_create_update_and_delete_a_finance_entry", "PASS", "CRUD catatan keuangan owner berhasil."),
    ("TC004", "Otorisasi", "test_cashier_cannot_create_a_finance_entry", "PASS", "Kasir ditolak mengelola keuangan."),
    ("TC005", "Validasi", "test_invalid_finance_entry_amount_is_rejected", "PASS", "Nominal nol ditolak."),
    ("TC006", "Laporan", "test_report_separates_cash_flow_from_profit_and_loss", "PASS", "Arus kas dan laba rugi dipisahkan."),
    ("TC007", "Laporan", "test_report_marks_profit_incomplete_for_sales_without_historical_cost", "PASS", "Laba ditandai tidak lengkap bila harga modal hilang."),
    ("TC008", "Keamanan", "test_report_escapes_manual_finance_descriptions", "PASS", "Konten manual tidak dieksekusi sebagai HTML."),
    ("TC009", "Autentikasi", "test_guest_can_view_login_page", "PASS", "Halaman login dapat dibuka."),
    ("TC010", "Autentikasi", "test_valid_username_and_password_authenticate_the_cashier", "PASS", "Login kasir valid berhasil."),
    ("TC011", "Autentikasi", "test_seeded_owner_account_can_log_in", "PASS", "Login owner valid berhasil."),
    ("TC012", "Otorisasi", "test_owner_can_view_monitoring_and_reports_but_not_cashier_pages", "PASS", "Hak akses role dipisahkan."),
    ("TC013", "Autentikasi", "test_invalid_credentials_return_to_login_with_an_error", "PASS", "Kredensial salah ditolak."),
    ("TC014", "Otorisasi", "test_guest_is_redirected_to_login_from_protected_pages", "PASS", "Tamu dialihkan ke login."),
    ("TC015", "Autentikasi", "test_authenticated_cashier_can_log_out", "PASS", "Logout kasir berhasil."),
    ("TC016", "Data", "test_users_are_stored_in_owner_table", "PASS", "Model user tersimpan pada tabel owner."),
    ("TC017", "Penjualan", "test_sale_uses_database_price_and_decreases_stock", "PASS", "Harga database digunakan dan stok berkurang."),
    ("TC018", "Validasi", "test_sale_with_insufficient_stock_is_not_saved", "PASS", "Penjualan stok kurang ditolak."),
    ("TC019", "Validasi", "test_sale_with_insufficient_payment_is_not_saved", "PASS", "Pembayaran kurang ditolak."),
    ("TC020", "Pembelian", "test_purchase_uses_database_cost_and_increases_stock", "PASS", "Harga modal database digunakan dan stok bertambah."),
    ("TC021", "Keamanan", "test_stock_page_escapes_product_names", "PASS", "Nama barang berpotensi HTML ditampilkan aman."),
]


def header_footer(canvas, doc):
    canvas.saveState()
    canvas.setFont("Helvetica", 8)
    canvas.setFillColor(colors.HexColor("#555555"))
    canvas.drawString(2 * cm, 1.2 * cm, "Laporan QA - Sistem Informasi Toko Madura")
    canvas.drawRightString(A4[0] - 2 * cm, 1.2 * cm, f"Halaman {doc.page}")
    canvas.restoreState()


def main():
    OUTPUT.parent.mkdir(parents=True, exist_ok=True)
    font_path = Path("C:/Windows/Fonts/arial.ttf")
    if font_path.exists():
        pdfmetrics.registerFont(TTFont("Arial", str(font_path)))
        font_name = "Arial"
    else:
        font_name = "Helvetica"

    styles = getSampleStyleSheet()
    styles["Title"].fontName = font_name
    styles["Heading1"].fontName = font_name
    styles["Heading2"].fontName = font_name
    styles["BodyText"].fontName = font_name
    styles["BodyText"].fontSize = 10
    styles["BodyText"].leading = 14
    styles.add(ParagraphStyle(name="CenterTitle", parent=styles["Title"], alignment=TA_CENTER))

    doc = SimpleDocTemplate(
        str(OUTPUT),
        pagesize=A4,
        rightMargin=1.8 * cm,
        leftMargin=1.8 * cm,
        topMargin=1.8 * cm,
        bottomMargin=1.8 * cm,
        title="Laporan QA Sistem Informasi Penjualan dan Keuangan Toko Madura",
        author="QA Engineering",
    )

    story = []
    story.append(Spacer(1, 2.5 * cm))
    story.append(Paragraph("LAPORAN QUALITY ASSURANCE", styles["CenterTitle"]))
    story.append(Spacer(1, 0.4 * cm))
    story.append(Paragraph("Sistem Informasi Penjualan dan Keuangan Toko Madura", styles["CenterTitle"]))
    story.append(Spacer(1, 1.5 * cm))
    story.append(Paragraph("<b>Jenis Pengujian:</b> Automated Testing berbasis PHPUnit/Laravel", styles["BodyText"]))
    story.append(Paragraph("<b>Tanggal Pelaksanaan:</b> 25 November 2026", styles["BodyText"]))
    story.append(Paragraph("<b>Lingkungan:</b> PHP 8.4.28, SQLite in-memory, Laravel", styles["BodyText"]))
    story.append(Spacer(1, 4.2 * cm))
    story.append(Paragraph("Dokumentasi hasil pengujian aplikasi web", styles["BodyText"]))
    story.append(PageBreak())

    story.append(Paragraph("1. Ringkasan Eksekutif", styles["Heading1"]))
    summary = [
        ["Parameter", "Hasil"],
        ["Total test case", "21"],
        ["Test dieksekusi", "21"],
        ["Passed", "21"],
        ["Failed", "0"],
        ["Assertion", "91"],
        ["Pass Rate", "100%"],
        ["Status keseluruhan", "PASS"],
    ]
    table = Table(summary, colWidths=[6 * cm, 8 * cm])
    table.setStyle(TableStyle([
        ("BACKGROUND", (0, 0), (-1, 0), colors.HexColor("#1F4E78")),
        ("TEXTCOLOR", (0, 0), (-1, 0), colors.white),
        ("GRID", (0, 0), (-1, -1), 0.5, colors.grey),
        ("VALIGN", (0, 0), (-1, -1), "MIDDLE"),
        ("FONTNAME", (0, 0), (-1, -1), font_name),
        ("FONTSIZE", (0, 0), (-1, -1), 9),
        ("BACKGROUND", (0, 1), (-1, -1), colors.HexColor("#F3F6F8")),
    ]))
    story.append(table)
    story.append(Spacer(1, 0.5 * cm))
    story.append(Paragraph(
        "Berdasarkan hasil eksekusi aktual, seluruh 21 test case yang tersedia dalam suite "
        "pengujian berhasil lulus. Temuan ini mencakup autentikasi, pemisahan role, validasi "
        "transaksi, pengelolaan keuangan, laporan, pengurangan/penambahan stok, serta escaping "
        "konten berpotensi HTML.",
        styles["BodyText"],
    ))

    story.append(Paragraph("2. Identitas dan Lingkup Aplikasi", styles["Heading1"]))
    story.append(Paragraph(
        "<b>Nama aplikasi:</b> Sistem Informasi Penjualan dan Keuangan Toko Madura<br/>"
        "<b>Tujuan:</b> Mengelola barang, supplier, penjualan, pembelian, laporan, dan keuangan.<br/>"
        "<b>Framework:</b> Laravel 13 dengan PHPUnit.<br/>"
        "<b>Database pengujian:</b> SQLite in-memory.<br/>"
        "<b>Role teridentifikasi:</b> owner dan cashier.",
        styles["BodyText"],
    ))
    story.append(Paragraph(
        "Lingkup pengujian mengikuti test yang benar-benar tersedia pada repository: unit testing "
        "dasar, feature testing, autentikasi, otorisasi, transaksi penjualan/pembelian, keuangan, "
        "laporan, dan escaping HTML.",
        styles["BodyText"],
    ))

    story.append(Paragraph("3. Lingkungan dan Metode", styles["Heading1"]))
    story.append(Paragraph(
        "<b>Perintah eksekusi:</b> php artisan test<br/>"
        "<b>Runtime:</b> PHP 8.4.28<br/>"
        "<b>Driver database:</b> SQLite<br/>"
        "<b>Mode database:</b> :memory:<br/>"
        "<b>Tipe pengujian:</b> Automated feature and unit testing.",
        styles["BodyText"],
    ))
    story.append(Paragraph(
        "<b>White-box testing</b> digunakan karena test mengakses model/database, migration, "
        "route, dan hasil render melalui framework Laravel. "
        "<b>Black-box testing</b> digunakan pada aspek HTTP response, redirect, status, validasi "
        "pesan, dan hak akses tanpa mengandalkan tampilan internal.",
        styles["BodyText"],
    ))

    story.append(Paragraph("4. Perbandingan Black-box dan White-box", styles["Heading1"]))
    comparison = [
        ["Aspek", "Black-box", "White-box"],
        ["Pemahaman", "Perilaku eksternal", "Struktur dan implementasi"],
        ["Dasar verifikasi", "Input/output", "Code, data, route, model"],
        ["Contoh di proyek", "Login, redirect, 403", "Harga database, stok, tabel"],
        ["Dampak temuan", "User-facing behavior", "Logika internal/data integrity"],
    ]
    table = Table(comparison, colWidths=[3.2 * cm, 5.1 * cm, 5.1 * cm])
    table.setStyle(TableStyle([
        ("BACKGROUND", (0, 0), (-1, 0), colors.HexColor("#1F4E78")),
        ("TEXTCOLOR", (0, 0), (-1, 0), colors.white),
        ("GRID", (0, 0), (-1, -1), 0.5, colors.grey),
        ("FONTNAME", (0, 0), (-1, -1), font_name),
        ("FONTSIZE", (0, 0), (-1, -1), 8),
        ("VALIGN", (0, 0), (-1, -1), "TOP"),
    ]))
    story.append(table)

    story.append(PageBreak())
    story.append(Paragraph("5. Rincian Test Case", styles["Heading1"]))
    rows = [["ID", "Modul", "Nama test", "Status", "Bukti ringkas"]]
    for tc_id, module, name, status, evidence in TEST_CASES:
        rows.append([tc_id, module, name, status, evidence])
    table = Table(rows, repeatRows=1, colWidths=[1.2 * cm, 1.8 * cm, 5.8 * cm, 1.3 * cm, 3.4 * cm])
    table.setStyle(TableStyle([
        ("BACKGROUND", (0, 0), (-1, 0), colors.HexColor("#1F4E78")),
        ("TEXTCOLOR", (0, 0), (-1, 0), colors.white),
        ("GRID", (0, 0), (-1, -1), 0.35, colors.grey),
        ("FONTNAME", (0, 0), (-1, -1), font_name),
        ("FONTSIZE", (0, 0), (-1, -1), 7),
        ("VALIGN", (0, 0), (-1, -1), "TOP"),
        ("BACKGROUND", (0, 2), (-1, -1), colors.HexColor("#F7F9FA")),
    ]))
    story.append(table)

    story.append(Spacer(1, 0.5 * cm))
    story.append(Paragraph("6. Temuan dan Risiko", styles["Heading1"]))
    story.append(Paragraph(
        "Tidak ada bug atau kegagalan test yang terkonfirmasi pada eksekusi ini. "
        "Halaman login, transaksi, role permission, laporan, dan escaping konten berperilaku "
        "sesuai assertion yang tersedia.",
        styles["BodyText"],
    ))
    story.append(Paragraph(
        "<b>Keterbatasan:</b> Laporan ini belum mencakup pengujian browser Playwright aktual, "
        "screenshoot UI manual, responsive layout di beberapa resolusi, maupun pengujian integrasi "
        "MySQL/Laragon secara end-to-end. Hasil 21 test adalah bukti otomatis PHPUnit/Laravel "
        "dengan SQLite, bukan bukti browser.",
        styles["BodyText"],
    ))

    story.append(Paragraph("7. Rekomendasi", styles["Heading1"]))
    story.append(Paragraph(
        "1. Tambahkan pengujian Playwright untuk navigasi dan screenshot aktual. "
        "2. Tambahkan test regression untuk validasi stok dan pembayaran. "
        "3. Tambahkan test responsif pada desktop/mobile. "
        "4. Uji aplikasi terhadap MySQL menggunakan data terisolasi sebelum rilis. "
        "5. Tetap dokumentasikan hasil Playwright sebagai bukti tambahan.",
        styles["BodyText"],
    ))

    story.append(Paragraph("8. Kesimpulan", styles["Heading1"]))
    story.append(Paragraph(
        "Berdasarkan eksekusi aktual pada 25 November 2026, suite QA berisi 21 test dengan "
        "21 test lulus, 0 test gagal, dan 91 assertion berhasil. Oleh karena itu, status pengujian "
        "yang dapat dipertanggungjawabkan adalah <b>PASS</b> pada ruang lingkup Laravel test yang tersedia.",
        styles["BodyText"],
    ))

    story.append(Paragraph("9. Referensi", styles["Heading1"]))
    story.append(Paragraph(
        "1. Instruksi QA dosen: https://github.com/LurreDev-Official/SKILL-VIBE-CODE.git<br/>"
        "2. Materi pengujian: https://www.geeksforgeeks.org/software-testing/differences-between-black-box-testing-vs-white-box-testing/",
        styles["BodyText"],
    ))

    doc.build(story, onFirstPage=header_footer, onLaterPages=header_footer)
    print(OUTPUT)


if __name__ == "__main__":
    main()
