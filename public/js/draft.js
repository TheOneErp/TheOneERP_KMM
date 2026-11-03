/**
 * 表單草稿 / 登入逾時保活
 * - Draft      : 草稿儲存 (localStorage) 與選擇視窗
 * - KeepAlive  : 定期向伺服器報到,避免 session 在畫面還開著時就過期 (HTTP 419)
 * 需要的全域變數由 resources/views/assets/js/main.blade.js 提供:
 *   window.draftScope / window.draftUser / window.draftEnabledPages / window.urls.keepalive
 */
(function () {
    'use strict';

    var MAX_DRAFTS = 5;                      // 每個頁面最多保留幾份草稿
    var TTL = 7 * 24 * 60 * 60 * 1000;       // 草稿保存 7 天
    var PING_INTERVAL = 5 * 60 * 1000;       // 保活間隔 5 分鐘 (期間有操作才送)

    // ---------- 小工具 ----------
    function el(tag, style, text) {
        var e = document.createElement(tag);
        if (style) e.style.cssText = style;
        if (text != null) e.textContent = text;
        return e;
    }
    function btn(text, bg, color) {
        var b = el('button', 'cursor:pointer;border:0;border-radius:4px;padding:8px 16px;font-size:14px;margin-left:8px;background:' + (bg || '#e8e8e8') + ';color:' + (color || '#333'), text);
        b.type = 'button';
        return b;
    }
    function pad(n) { return n < 10 ? '0' + n : '' + n; }
    function fmtTime(ts) {
        var d = new Date(ts);
        return d.getFullYear() + '/' + pad(d.getMonth() + 1) + '/' + pad(d.getDate()) + ' ' + pad(d.getHours()) + ':' + pad(d.getMinutes());
    }
    function openModal(width) {
        var overlay = el('div', 'position:fixed;left:0;top:0;right:0;bottom:0;background:rgba(0,0,0,.45);z-index:2147483000;display:flex;align-items:center;justify-content:center');
        var box = el('div', 'background:#fff;border-radius:8px;padding:20px 24px;width:' + (width || 460) + 'px;max-width:92vw;max-height:86vh;overflow:auto;box-shadow:0 8px 30px rgba(0,0,0,.35);font-size:14px;color:#333');
        overlay.appendChild(box);
        document.body.appendChild(overlay);
        return { overlay: overlay, box: box, close: function () { if (overlay.parentNode) overlay.parentNode.removeChild(overlay); } };
    }

    // ---------- 草稿儲存 ----------
    function storageKey(pageId) {
        return 'erpDraft:' + (window.draftScope || 'x') + ':' + pageId;
    }
    function readAll(pageId) {
        try {
            var raw = localStorage.getItem(storageKey(pageId));
            if (!raw) return [];
            var obj = JSON.parse(raw);
            var items = obj && Array.isArray(obj.items) ? obj.items : [];
            var now = Date.now();
            return items.filter(function (i) { return i && i.id && i.snap && now - i.savedAt < TTL; });
        } catch (e) { return []; }
    }
    function writeAll(pageId, items) {
        try {
            if (!items.length) localStorage.removeItem(storageKey(pageId));
            else localStorage.setItem(storageKey(pageId), JSON.stringify({ v: 1, items: items }));
            return true;
        } catch (e) { return false; }
    }

    // 把值轉成可存進 JSON 的純資料 (檔案、參考頁 dataset 等不存)
    function cleanValue(v) {
        if (v === null) return null;
        var t = typeof v;
        if (t === 'string' || t === 'number' || t === 'boolean') return v;
        if (t !== 'object') return undefined;
        if ((typeof Blob !== 'undefined' && v instanceof Blob) || v.schema || v.form_id) return undefined;
        try { return JSON.parse(JSON.stringify(v)); } catch (e) { return undefined; }
    }
    function snapshot(ds) {
        var out = { form_id: ds.form_id, data: {}, subData: {} };
        Object.keys(ds.data || {}).forEach(function (k) {
            if (k === 'data_options') return;
            var c = cleanValue(ds.data[k]);
            if (c !== undefined) out.data[k] = c;
        });
        Object.keys(ds.subData || {}).forEach(function (fid) {
            out.subData[fid] = (ds.subData[fid] || []).map(snapshot);
        });
        return out;
    }
    // 顯示在草稿清單上的簡短說明: 優先挑名稱類欄位
    function summary(snap) {
        var keys = Object.keys(snap.data || {});
        var pri = keys.filter(function (k) { return /name|client|customer|supplier|vendor/i.test(k); });
        var rest = keys.filter(function (k) { return pri.indexOf(k) < 0; });
        var parts = [];
        pri.concat(rest).forEach(function (k) {
            if (parts.length >= 3) return;
            var v = snap.data[k];
            if ((typeof v === 'string' || typeof v === 'number') && String(v).trim() !== '' && String(v).length <= 30) parts.push(String(v));
        });
        return parts.join(' / ') || '(尚無內容)';
    }

    var Draft = {
        enabled: function (pageCode) {
            var list = window.draftEnabledPages || [];
            return list.indexOf('*') >= 0 || list.indexOf(pageCode) >= 0;
        },
        list: function (pageId) {
            return readAll(pageId).sort(function (a, b) { return b.savedAt - a.savedAt; });
        },
        newId: function () { return 'd' + Date.now().toString(36) + Math.random().toString(36).slice(2, 6); },
        save: function (pageId, item) {
            var items = readAll(pageId).filter(function (i) { return i.id !== item.id; });
            items.push(item);
            items.sort(function (a, b) { return b.savedAt - a.savedAt; });
            items = items.slice(0, MAX_DRAFTS);
            if (!writeAll(pageId, items)) {            // 空間不足就丟掉較舊的再試一次
                items = items.slice(0, Math.max(1, items.length - 2));
                writeAll(pageId, items);
            }
        },
        remove: function (pageId, id) {
            writeAll(pageId, readAll(pageId).filter(function (i) { return i.id !== id; }));
        },
        snapshot: snapshot,
        snapshotString: function (ds) { return JSON.stringify(snapshot(ds)); },
        summary: summary,

        // 按「新增」時有草稿 -> 跳出選擇視窗。resolve: null(取消) | {type:'new'} | {type:'draft', draft}
        choose: function (pageId) {
            return new Promise(function (resolve) {
                var m = openModal(520);
                function done(v) { m.close(); resolve(v); }
                function render() {
                    m.box.innerHTML = '';
                    var drafts = Draft.list(pageId);
                    m.box.appendChild(el('div', 'font-size:18px;font-weight:bold;margin-bottom:6px', '發現尚未儲存的草稿'));
                    m.box.appendChild(el('div', 'color:#777;margin-bottom:12px', '這些是上次輸入到一半的資料,可以繼續編輯,或直接開新單。'));
                    if (!drafts.length) m.box.appendChild(el('div', 'color:#999;padding:10px 0', '目前沒有草稿。'));
                    drafts.forEach(function (d) {
                        var row = el('div', 'display:flex;align-items:center;justify-content:space-between;border:1px solid #ddd;border-radius:6px;padding:8px 12px;margin-bottom:8px');
                        var info = el('div', 'flex:1;min-width:0');
                        info.appendChild(el('div', 'font-weight:bold;overflow:hidden;text-overflow:ellipsis;white-space:nowrap', d.summary || '(尚無內容)'));
                        info.appendChild(el('div', 'color:#888;font-size:12px', '最後儲存: ' + fmtTime(d.savedAt)));
                        var go = btn('繼續編輯', '#2185d0', '#fff');
                        go.onclick = function () { done({ type: 'draft', draft: d }); };
                        var del = btn('刪除', '#fff', '#c0392b');
                        del.style.border = '1px solid #c0392b';
                        del.onclick = function () {
                            if (confirm('確定刪除這份草稿?')) { Draft.remove(pageId, d.id); render(); }
                        };
                        row.appendChild(info); row.appendChild(go); row.appendChild(del);
                        m.box.appendChild(row);
                    });
                    var foot = el('div', 'text-align:right;margin-top:14px');
                    var cancel = btn('取消');
                    cancel.onclick = function () { done(null); };
                    var fresh = btn('開新單', '#21ba45', '#fff');
                    fresh.onclick = function () { done({ type: 'new' }); };
                    foot.appendChild(cancel); foot.appendChild(fresh);
                    m.box.appendChild(foot);
                }
                render();
            });
        },

        // 登入逾時視窗。retry: 重新登入後要重做的動作 (例如再按一次儲存),可為 null
        sessionExpired: function (retry, draftKept) {
            if (Draft._expiredOpen) return;
            Draft._expiredOpen = true;
            var m = openModal(480);
            function close() { Draft._expiredOpen = false; m.close(); }
            m.box.appendChild(el('div', 'font-size:18px;font-weight:bold;margin-bottom:8px;color:#c0392b', '登入已逾時'));
            m.box.appendChild(el('div', 'line-height:1.7', '伺服器已將您登出,所以剛才的操作沒有送出。' + (draftKept ? '您輸入的資料已存成草稿,不會遺失。' : '畫面上的資料仍保留著,請不要關閉或重新整理本頁。')));
            m.box.appendChild(el('div', 'line-height:1.7;margin-top:6px', '請先按「開啟登入頁」在新分頁重新登入,登入完成後回到這裡按「我已登入」' + (retry ? ',系統會自動重新送出。' : '。')));
            var msg = el('div', 'color:#c0392b;margin-top:8px;min-height:20px');
            m.box.appendChild(msg);
            var foot = el('div', 'text-align:right;margin-top:10px');
            var later = btn('稍後再說');
            later.onclick = close;
            var login = btn('開啟登入頁', '#2185d0', '#fff');
            login.onclick = function () { window.open(window.baseURL + 'login', '_blank'); };
            var ok = btn('我已登入', '#21ba45', '#fff');
            ok.onclick = function () {
                msg.textContent = '確認中…';
                KeepAlive.refreshSession().then(function (r) {
                    if (!r.ok) { msg.textContent = '還沒有登入成功,請先在新分頁完成登入。'; return; }
                    if (String(r.user) !== String(window.draftUser)) { msg.textContent = '目前登入的帳號與原本不同,請用原本的帳號登入。'; return; }
                    close();
                    if (retry) retry();
                });
            };
            foot.appendChild(later); foot.appendChild(login); foot.appendChild(ok);
            m.box.appendChild(foot);
        }
    };

    // ---------- 保活 ----------
    var KeepAlive = {
        activity: false,
        timer: null,
        // 向伺服器要一個新的 CSRF token 並順便延長 session。回傳 {ok, expired, user}
        refreshSession: function () {
            if (!window.urls || !window.urls.keepalive) return Promise.resolve({ ok: false, expired: false });
            return fetch(window.urls.keepalive, {
                method: 'GET',
                credentials: 'same-origin',
                cache: 'no-store',
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            }).then(function (res) {
                var path = '';
                try { path = new URL(res.url).pathname; } catch (e) { }
                if (res.status === 401 || res.status === 419 || (res.redirected && /\/login\/?$/.test(path))) return { ok: false, expired: true };
                if (!res.ok) return { ok: false, expired: false };
                return res.json().then(function (j) {
                    if (j && j.token) window.csrfToken = j.token;
                    return { ok: true, user: j ? j.user : null };
                });
            }).catch(function () { return { ok: false, expired: false }; });
        },
        tick: function (force) {
            var self = this;
            if (!force && !self.activity) return;
            self.activity = false;
            self.refreshSession().then(function (r) {
                if (!r.ok && r.expired) Draft.sessionExpired(null, false);
            });
        },
        start: function () {
            var self = this;
            if (self.timer) return;
            if (!window.urls || !window.urls.keepalive || !window.draftUser || String(window.draftUser) === '0') return;
            ['click', 'keydown', 'input', 'change'].forEach(function (ev) {
                document.addEventListener(ev, function () { self.activity = true; }, true);
            });
            self.timer = setInterval(function () { self.tick(false); }, PING_INTERVAL);
            document.addEventListener('visibilitychange', function () {
                if (!document.hidden) self.tick(true);   // 切回分頁時立刻確認登入狀態
            });
        }
    };

    window.Draft = Draft;
    window.KeepAlive = KeepAlive;
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', function () { KeepAlive.start(); });
    else KeepAlive.start();
})();
