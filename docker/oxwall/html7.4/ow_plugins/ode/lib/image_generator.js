const puppeteer = require('puppeteer');

(async () => {
    try {
        const browser = await puppeteer.launch();
        const page = await browser.newPage();
        await page.goto(`http://localhost/share_datalet/${process.argv[2]}`);
        setTimeout(async () => {
            await page.screenshot({path: `../datalet_images/datalet_${process.argv[2]}.png`});
            await browser.close();
        }, 5000);
    } catch(e) {}
})();
