import { test, expect, type Page } from "@playwright/test";
async function login(p: Page, email: string) {
    await p.goto("/masuk");
    await p.getByLabel("Email", { exact: true }).fill(email);
    await p
        .getByLabel("Kata sandi", { exact: true })
        .fill(process.env.E2E_PASSWORD!);
    await p.getByRole("button", { name: "Masuk", exact: true }).click();
    await expect(p).toHaveURL(/reservasi/);
}
async function noOverflow(p: Page) {
    expect(
        await p.evaluate(
            () => document.documentElement.scrollWidth <= innerWidth + 1,
        ),
    ).toBe(true);
}
test("Profil, kalender, pencegahan booking ganda, konfirmasi, pembatalan dan reservasi ulang", async ({
    page,
    browser,
}) => {
    const errors: string[] = [];
    page.on("pageerror", (e) => errors.push(e.message));
    await login(page, "petugas@example.test");
    await page
        .getByRole("link", { name: "Kelola tutor & jadwal", exact: true })
        .click();
    await page
        .getByRole("link", { name: "Tambah tutor", exact: false })
        .first()
        .click();
    await page.getByLabel("Nama tutor").fill("Tutor Pengujian");
    await page
        .getByLabel("Ringkasan pendekatan")
        .fill("Pendekatan bertahap untuk memahami konsep");
    await page.getByLabel("Mata pelajaran").selectOption("Matematika");
    await page.getByLabel("Jenjang", { exact: false }).selectOption("SMP");
    await page.getByLabel("Tarif per jam (Rp)").fill("100000");
    await page.getByLabel("Metode", { exact: false }).selectOption("Online");
    await page.getByLabel("Kota umum").fill("Bandung");
    await page
        .getByLabel("Profil dan pendekatan belajar")
        .fill(
            "Profil tutor untuk pengujian pemesanan jadwal, tanpa klaim kualifikasi nyata.",
        );
    await page
        .getByLabel("Foto tutor (opsional)")
        .setInputFiles("public/images/study.jpg");
    await page.getByRole("button", { name: "Simpan profil tutor" }).click();
    const day = new Date(Date.now() + 7 * 86400000).toISOString().slice(0, 10);
    await page.getByLabel("Mulai sesi (WIB)").fill(day + "T10:00");
    await page.getByLabel("Durasi", { exact: false }).selectOption("60");
    await page
        .getByLabel("Petunjuk pertemuan privat")
        .fill("Tautan sesi: KELAS-RAHASIA");
    await page.getByRole("button", { name: "Tambah jadwal" }).click();
    await expect(page.locator(".schedule-list")).toContainText("10:00");
    const firstContext = await browser.newContext();
    const first = await firstContext.newPage();
    first.on("pageerror", (e) => errors.push(e.message));
    await first.goto("/masuk?mode=daftar");
    await first.getByLabel("Nama lengkap").fill("Pelajar Pertama");
    await first
        .getByLabel("Email", { exact: true })
        .fill("pertama@example.test");
    await first
        .getByLabel("Kata sandi", { exact: true })
        .fill(process.env.E2E_PASSWORD!);
    await first.getByLabel("Ulangi kata sandi").fill(process.env.E2E_PASSWORD!);
    await first.getByRole("button", { name: "Daftar akun belajar" }).click();
    await expect(first).toHaveURL(/reservasi/);
    const secondContext = await browser.newContext();
    const second = await secondContext.newPage();
    await login(second, "kedua@example.test");
    const calendar = "/tutor/1?week=" + day;
    await first.goto(calendar);
    await second.goto(calendar);
    await expect(first.locator("body")).not.toContainText("KELAS-RAHASIA");
    await first
        .getByLabel("Topik / kebutuhan belajar")
        .fill("Latihan persamaan linear menjelang ujian.");
    await first.getByRole("button", { name: "Reservasi jadwal ini" }).click();
    await expect(first).toHaveURL(/reservasi\/\d+$/);
    const reservation = new URL(first.url()).pathname;
    await expect(first.locator(".fee")).toContainText("100.000");
    await second
        .getByLabel("Topik / kebutuhan belajar")
        .fill("Topik pelajaran untuk pemesan kedua.");
    await second.getByRole("button", { name: "Reservasi jadwal ini" }).click();
    await expect(second.getByRole("alert")).toContainText("sudah dipesan");
    await page.goto(reservation);
    await page
        .getByLabel("Catatan perubahan")
        .fill("Tutor telah mengonfirmasi sesi belajar.");
    await page.getByRole("button", { name: "Konfirmasi reservasi" }).click();
    await first.reload();
    await expect(first.locator(".meeting")).toContainText("KELAS-RAHASIA");
    const denied = await second.goto(reservation);
    expect(denied?.status()).toBe(403);
    await first
        .getByLabel("Catatan perubahan")
        .fill("Perlu mengatur ulang waktu belajar.");
    await first.getByRole("button", { name: "Batalkan reservasi" }).click();
    await expect(first.locator(".section-heading .badge")).toHaveText(
        "Dibatalkan",
    );
    await second.goto(calendar);
    await second
        .getByLabel("Topik / kebutuhan belajar")
        .fill("Belajar persamaan pada slot yang tersedia kembali.");
    await second.getByRole("button", { name: "Reservasi jadwal ini" }).click();
    await expect(second).toHaveURL(/reservasi\/\d+$/);
    expect(new URL(second.url()).pathname).not.toBe(reservation);
    for (const width of [1440, 768, 390]) {
        await first.setViewportSize({ width, height: 900 });
        await first.goto("/");
        await first
            .locator(".hero img")
            .evaluate(async (img: HTMLImageElement) => img.decode());
        await noOverflow(first);
        await first.screenshot({
            path: "test-results/home-" + width + ".png",
            fullPage: true,
        });
        await first.goto(calendar);
        await noOverflow(first);
        await second.setViewportSize({ width, height: 900 });
        await noOverflow(second);
        await second.screenshot({
            path: "test-results/booking-" + width + ".png",
            fullPage: true,
        });
    }
    expect(errors).toEqual([]);
    await firstContext.close();
    await secondContext.close();
});
