import fs from 'fs';
import path from 'path';
import puppeteer from 'puppeteer';

(async ()=>{
  try{
    const previewPath = path.resolve('./resources/views/cobrancas/preview_toolbar.html');
    if(!fs.existsSync(previewPath)){
      console.error('Preview file not found:', previewPath);
      process.exit(2);
    }
    const browser = await puppeteer.launch({args: ['--no-sandbox','--disable-setuid-sandbox']});
    const page = await browser.newPage();
    await page.setViewport({width:1200, height:540, deviceScaleFactor:1});
    await page.goto('file://' + previewPath, {waitUntil: 'networkidle0'});
    const outPath = path.resolve('./resources/views/cobrancas/preview_toolbar.png');
    await page.screenshot({path: outPath, fullPage: false});
    console.log('Screenshot saved to', outPath);
    await browser.close();
  }catch(err){
    console.error('Error capturing screenshot:', err);
    process.exit(1);
  }
})();
