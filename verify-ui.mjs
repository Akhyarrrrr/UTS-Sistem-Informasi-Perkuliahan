import assert from 'node:assert/strict';
import fs from 'node:fs';
import vm from 'node:vm';
import path from 'node:path';
import {fileURLToPath} from 'node:url';

const root = path.dirname(fileURLToPath(import.meta.url));
const source = fs.readFileSync(path.join(root, 'Aplikasi/resources/js/app.js'), 'utf8');
const init = fs.readFileSync(path.join(root, 'Aplikasi/resources/views/partials/theme-init.blade.php'), 'utf8').replace(/<\/?script>/g, '');
const checks = [];
for (const settings of [
    {saved: 'dark', reduced: false, observer: true},
    {saved: 'invalid', reduced: true, observer: true},
    {blocked: true, reduced: true, observer: false},
]) {
    const listeners = {};
    const element = () => ({classList: {add() {}, remove() {}, toggle() {}}, setAttribute() {}, addEventListener() {}, focus() {}});
    const button = {...element(), querySelector: () => ({textContent: ""}), addEventListener: (name, handler) => { listeners[name] = handler; }};
    let revealed = 0;
    let observed = 0;
    const reveals = [{classList: {add: () => { revealed++; }}}];
    const document = {
        documentElement: {dataset: {}},
        querySelector: selector => selector === '.theme-button' ? button : selector === '.home-body' ? element() : selector === '.home-menu-button' ? element() : null,
        querySelectorAll: selector => selector === '[data-reveal]' ? reveals : [],
        getElementById: id => id === 'home-navigation' ? element() : null,
        addEventListener() {},
    };
    const storage = {getItem: () => { if (settings.blocked) throw Error('blocked'); return settings.saved; }, setItem: () => { if (settings.blocked) throw Error('blocked'); }};
    const Observer = class { observe() { observed++; } unobserve() {} };
    const window = {matchMedia: query => ({matches: query.includes('reduced-motion') && settings.reduced, addEventListener() {}}), addEventListener() {}};
    if (settings.observer) window.IntersectionObserver = Observer;
    const context = vm.createContext({document, window, localStorage: storage, IntersectionObserver: Observer});
    vm.runInContext(init, context);
    assert.equal(document.documentElement.dataset.theme, settings.saved === 'dark' ? 'dark' : 'light');
    vm.runInContext(source, context);
    const previous = document.documentElement.dataset.theme;
    listeners.click();
    assert.equal(document.documentElement.dataset.theme, previous === 'light' ? 'dark' : 'light');
    assert.equal(revealed, settings.reduced || !settings.observer ? 1 : 0);
    assert.equal(observed, settings.reduced || !settings.observer ? 0 : 1);
    checks.push({...settings, themeAndRevealFallback: 'PASS'});
}
for (const name of ['app', 'home']) {
    const css = fs.readFileSync(path.join(root, `Aplikasi/resources/css/${name}.css`), 'utf8');
    assert.match(css, /prefers-reduced-motion:\s*reduce/);
    assert.match(css, /transition:\s*none\s*!important/);
    assert.match(css, /animation:\s*none\s*!important/);
}
const handlers = {}, attributes = {}, status = {textContent: ''}, message = {textContent: ''};
let submittedCount = 0, focusCount = 0, dialogCount = 0;
const submitter = {focus() { focusCount++; }};
const event = () => ({submitter, defaultPrevented:false, preventDefault(){this.defaultPrevented=true;}});
const form = {dataset: {confirm: 'Hapus catatan?'}, classList: {add() {}, remove() {}}, setAttribute: (k,v) => {attributes[k]=v;}, removeAttribute: k => {delete attributes[k];}, querySelector: () => status, addEventListener: (name,fn) => {(handlers[name] ??= []).push(fn);}, requestSubmit(button) {assert.equal(button, submitter); const e=event(); handlers.submit.forEach(fn=>fn(e)); if (!e.defaultPrevented) submittedCount++;}};
const cancelControl = {focus() {isolated.document.activeElement=this;}}, confirmControl = {focus() {isolated.document.activeElement=this;}};
const dialog = {returnValue:'', showModal() {dialogCount++;}, querySelectorAll: () => [cancelControl,confirmControl], addEventListener(name, fn) {handlers['dialog-'+name]=[fn];}};
const isolated = vm.createContext({document: {documentElement: {dataset: {}}, querySelector: () => null, querySelectorAll: s => s === 'form[data-confirm]' || s === 'form[data-busy]' ? [form] : [], getElementById: id => id === 'delete-confirmation' ? dialog : id === 'confirm-message' ? message : null, addEventListener() {}}, window: {matchMedia: () => ({matches:false, addEventListener(){}}), addEventListener: (name, fn) => {handlers[name]=[fn];}}});
vm.runInContext(source, isolated);
let cancelled=event();handlers.submit.forEach(fn=>fn(cancelled));assert.equal(cancelled.defaultPrevented,true);assert.equal(form.dataset.submitting,undefined);
assert.equal(message.textContent,form.dataset.confirm); dialog.returnValue='cancel';handlers['dialog-close'].forEach(fn=>fn());assert.equal(submittedCount,0);assert.equal(focusCount,1);
let submitted=event();handlers.submit.forEach(fn=>fn(submitted));assert.equal(submitted.defaultPrevented,true);dialog.returnValue='delete';handlers['dialog-close'].forEach(fn=>fn());assert.equal(submittedCount,1);assert.equal(attributes['aria-busy'],'true');assert.ok(status.textContent.includes('Menyimpan'));
let repeated=event();handlers.submit.forEach(fn=>fn(repeated));assert.equal(repeated.defaultPrevented,true);assert.equal(dialogCount,2);assert.equal(submittedCount,1);
handlers.pageshow.forEach(fn=>fn());assert.equal(form.dataset.submitting,undefined);assert.equal(attributes['aria-busy'],undefined);assert.equal(status.textContent,'');
let escaped=event();handlers.submit.forEach(fn=>fn(escaped));assert.equal(dialog.returnValue,'');handlers['dialog-close'].forEach(fn=>fn());assert.equal(submittedCount,1);
isolated.document.activeElement=cancelControl;let shift={...event(),key:'Tab',shiftKey:true};handlers['dialog-keydown'].forEach(fn=>fn(shift));assert.equal(shift.defaultPrevented,true);assert.equal(isolated.document.activeElement,confirmControl);
let tab={...event(),key:'Tab',shiftKey:false};handlers['dialog-keydown'].forEach(fn=>fn(tab));assert.equal(tab.defaultPrevented,true);assert.equal(isolated.document.activeElement,cancelControl);
assert.match(fs.readFileSync(path.join(root,'Aplikasi/resources/views/master/index.blade.php'),'utf8'),/data-busy data-confirm=/);
checks.push({confirmationCancel:'PASS', confirmationEscapeReset:'PASS', confirmedSubmitOnce:'PASS', firstSubmitBusy:'PASS', repeatedSubmitBlocked:'PASS', pageshowReset:'PASS', dialogKeyboardWrap:'PASS', actualDeleteFormsUseBusyGuard:'PASS'});
fs.writeFileSync(path.join(root, 'Bukti/ui-resilience.json'), JSON.stringify({method: 'Node VM executes actual theme initializer and app.js with controlled storage, motion preference, dialog return value and form events; CSS reduction rules checked. This supplements actual browser dialog and keyboard tests.', checks, reducedMotionCss: 'PASS'}, null, 2));
console.log('PASS: theme storage fallback, theme toggle, reduced-motion content visibility, observer fallback, CSS reduction.');
