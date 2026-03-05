const puppeteer = require('puppeteer');
const fs = require('fs');
(async () => {
  try {
    if (!fs.existsSync('screenshots')) fs.mkdirSync('screenshots');
    const browser = await puppeteer.launch({args: ['--no-sandbox','--disable-setuid-sandbox']});
    const page = await browser.newPage();
    await page.setViewport({width: 1366, height: 900});

    await page.goto('http://127.0.0.1:8001/', {waitUntil: 'networkidle2', timeout: 60000});
    await page.screenshot({path: 'screenshots/home.png', fullPage: true});

    await page.goto('http://127.0.0.1:8001/quem-somos', {waitUntil: 'networkidle2', timeout: 60000});
    await page.screenshot({path: 'screenshots/quem-somos.png', fullPage: true});

    await browser.close();
    console.log('Screenshots saved to ./screenshots/');
  } catch (err) {
    console.error('Error taking screenshots:', err);
    process.exit(1);
  }
})();
