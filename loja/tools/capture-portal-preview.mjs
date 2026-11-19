import { spawn } from 'node:child_process';
import { mkdtemp, readFile, writeFile } from 'node:fs/promises';
import { tmpdir } from 'node:os';
import { join, resolve } from 'node:path';
import { pathToFileURL } from 'node:url';

const edge = 'C:\\Program Files (x86)\\Microsoft\\Edge\\Application\\msedge.exe';
const profile = await mkdtemp(join(tmpdir(), 'portal-campaign-preview-'));
const browser = spawn(edge, [
  '--headless=new', '--disable-gpu', '--no-first-run', '--hide-scrollbars',
  '--remote-debugging-port=0', `--user-data-dir=${profile}`, 'about:blank',
], { windowsHide: true, stdio: 'ignore' });

const pause = (ms) => new Promise((done) => setTimeout(done, ms));
let socket;
try {
  let port;
  for (let attempt = 0; attempt < 60; attempt++) {
    try {
      port = Number((await readFile(join(profile, 'DevToolsActivePort'), 'utf8')).split('\n')[0]);
      break;
    } catch {
      await pause(100);
    }
  }
  if (!port) throw new Error('Edge não iniciou a depuração local.');

  const targets = await (await fetch(`http://127.0.0.1:${port}/json`)).json();
  const target = targets.find((item) => item.type === 'page');
  if (!target) throw new Error('Página do Edge não encontrada.');
  socket = new WebSocket(target.webSocketDebuggerUrl);
  await new Promise((resolveOpen, rejectOpen) => {
    socket.addEventListener('open', resolveOpen, { once: true });
    socket.addEventListener('error', rejectOpen, { once: true });
  });

  let nextId = 0;
  const pending = new Map();
  socket.addEventListener('message', (event) => {
    const message = JSON.parse(event.data);
    const waiter = pending.get(message.id);
    if (!waiter) return;
    pending.delete(message.id);
    if (message.error) waiter.reject(new Error(message.error.message));
    else waiter.resolve(message.result);
  });
  const command = (method, params = {}) => new Promise((resolveCommand, rejectCommand) => {
    const id = ++nextId;
    pending.set(id, { resolve: resolveCommand, reject: rejectCommand });
    socket.send(JSON.stringify({ id, method, params }));
  });

  const preview = pathToFileURL(resolve('screenshots/portal-campaign-preview.html')).href;
  await command('Page.enable');
  for (const [name, width, height, mobile] of [
    ['desktop', 1366, 900, false],
    ['android-small', 320, 640, true],
    ['android', 360, 800, true],
    ['android-wide', 412, 915, true],
  ]) {
    await command('Emulation.setDeviceMetricsOverride', { width, height, deviceScaleFactor: 1, mobile });
    await command('Page.navigate', { url: preview });
    await pause(700);
    const screenshot = await command('Page.captureScreenshot', { format: 'png', captureBeyondViewport: false });
    await writeFile(resolve(`screenshots/portal-campaign-${name}.png`), Buffer.from(screenshot.data, 'base64'));
    const dimensions = await command('Runtime.evaluate', {
      expression: '({ viewport: innerWidth, document: document.documentElement.scrollWidth })',
      returnByValue: true,
    });
    if (dimensions.result.value.document > width) throw new Error(`Conteúdo ultrapassa ${width} px em ${name}.`);
    console.log(`${name}: ${width} × ${height}, sem deslocamento horizontal`);
  }
} finally {
  socket?.close();
  browser.kill();
}
