<script>
(function () {
    if (window.__terrainPolygonMapBooted) {
        return;
    }
    window.__terrainPolygonMapBooted = true;

    var canvas = document.getElementById('terrainFieldGlobal');
    if (!canvas) {
        canvas = document.createElement('canvas');
        canvas.id = 'terrainFieldGlobal';
        canvas.setAttribute('aria-hidden', 'true');
        document.body.insertBefore(canvas, document.body.firstChild || null);
    }

    var ctx = canvas.getContext('2d', { alpha: true });
    if (!ctx) {
        return;
    }

    var theme = ((document.body && document.body.getAttribute('data-terrain-theme')) || 'simple').toLowerCase();
    var isEnterprise = theme === 'enterprise';
    var palette = null;

    var w = 0;
    var h = 0;
    var dpr = 1;
    var cols = 0;
    var rows = 0;
    var xStep = 0;
    var zStep = 0;
    var fov = 640;
    var nearZ = 48;
    var farZ = 1460;
    var terrain = [];
    var time = 0;

    var pointer = {
        x: 0,
        y: 0,
        active: false,
        lastMoveTs: 0,
        vx: 0,
        vy: 0,
        smoothX: 0,
        smoothY: 0
    };
    var pointerFlowActive = false;
    var pointerFlowIdleMs = 1600;
    var animationFrameId = 0;
    var animationRunning = false;
    var lastCanvasTransform = '';
    var lastPinHudTransform = '';
    var lastScrollTs = 0;
    var lastFrameTs = 0;
    var targetFrameMs = 20;
    var slowFrameStreak = 0;
    var fastFrameStreak = 0;
    var mapProfile = null;
    var currentPath = ((window.location && window.location.pathname) || '/');
    var currentLang = (document.documentElement.lang || '').toLowerCase();
    var currentHost = ((window.location && window.location.hostname) || '').toLowerCase();
    var isRuHost = /\.ru$/.test(currentHost);
    var isHomePath = (currentPath === '/' || currentPath === '');
    // Flags infrastructure disabled intentionally: unstable on moving terrain field.
    var isPinsEnabled = false;
    var pinHud = null;
    var pinFeed = [];
    var pinNodes = [];
    var pinsBuilt = false;
    var activePinId = '';
    var pinCountPerSide = 10;
    var pinNowTs = 0;
    var pinCloseLagMs = 320;
    var pinTriggerRadiusPx = 12;
    var pinDeactivateRadiusPx = 40; // 5x from trigger radius
    var pinMinLifeMs = 700;
    var pinMaxLifeMs = 1400;
    var pinSwitchCooldownMs = 140;
    var pinNextAllowedAt = 0;
    var pinQueueCursor = 0;
    var pinHoverPaddingPx = 100;
    var canvasParallax = {
        targetY: 0,
        currentY: 0
    };

    function clamp(v, min, max) {
        return Math.max(min, Math.min(max, v));
    }

    function lerp(a, b, t) {
        return a + (b - a) * t;
    }

    function rand(min, max) {
        return min + Math.random() * (max - min);
    }

    function escapeHtml(value) {
        return String(value || '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }

    function normalizeText(value) {
        return String(value || '').replace(/\s+/g, ' ').trim();
    }

    function trimText(value, limit) {
        var src = normalizeText(value);
        if (src.length <= limit) return src;
        return src.slice(0, Math.max(0, limit - 1)).trim() + '…';
    }

    function detectTypeByUrl(url) {
        if (/^\/services\/[^\/?#]+\/?$/i.test(url)) return 'services';
        if (/^\/projects\/[^\/?#]+\/?$/i.test(url)) return 'projects';
        if (/^\/blog\/[^\/?#]+\/?$/i.test(url)) return 'blog';
        return '';
    }

    function getTypeLabel(type) {
        if (type === 'services') return 'Услуга';
        if (type === 'projects') return 'Продукт';
        return 'Статья';
    }

    function shuffle(items) {
        for (var i = items.length - 1; i > 0; i--) {
            var j = Math.floor(Math.random() * (i + 1));
            var t = items[i];
            items[i] = items[j];
            items[j] = t;
        }
        return items;
    }

    function ensurePinStyles() {
        if (document.getElementById('terrainPinStyles')) return;
        var style = document.createElement('style');
        style.id = 'terrainPinStyles';
        style.textContent = '' +
            '.terrain-pin-layer{position:fixed;inset:0;pointer-events:none;z-index:3;perspective:1200px;transform-style:preserve-3d;' +
                '--pin-marker: rgba(255,92,92,.92);--pin-marker-bg: rgba(255,92,92,.12);--pin-beam-a: rgba(124,196,255,.85);--pin-beam-b: rgba(187,232,255,.16);' +
                '--pin-card-a: rgba(11,34,62,.96);--pin-card-b: rgba(20,66,112,.89);--pin-line: rgba(146,184,237,.38);--pin-text:#edf6ff;}' +
            '.terrain-pin-layer.theme-simple{position:fixed !important;--pin-marker: rgba(255,191,86,.96);--pin-marker-bg: rgba(255,191,86,.18);--pin-beam-a: rgba(255,210,120,.94);--pin-beam-b: rgba(255,132,76,.26);' +
                '--pin-card-a: rgba(93,43,16,.96);--pin-card-b: rgba(173,85,28,.90);--pin-line: rgba(255,198,132,.50);--pin-text:#fff7ee;}' +
            '.terrain-pin-layer.theme-enterprise{--pin-marker: rgba(255,110,170,.96);--pin-marker-bg: rgba(255,110,170,.16);--pin-beam-a: rgba(255,126,219,.90);--pin-beam-b: rgba(255,167,86,.24);' +
                '--pin-card-a: rgba(54,16,44,.96);--pin-card-b: rgba(108,33,80,.90);--pin-line: rgba(255,142,215,.48);--pin-text:#fff1fb;}' +
            '.terrain-pin{position:absolute;left:0;top:0;transform:translate(var(--px), var(--py));pointer-events:auto;width:44px;height:44px;}' +
            '.terrain-pin::before{content:"";position:absolute;left:16px;top:16px;width:12px;height:12px;border-radius:999px;background:var(--pin-marker-bg);' +
                'opacity:.28;filter:none;}' +
            '.terrain-pin::after{content:"";position:absolute;left:0;top:0;width:44px;height:44px;border-radius:50%;background:transparent;}' +
            '.terrain-pin .t-pole{position:absolute;left:21px;top:calc(-130vh + 22px);width:3px;height:130vh;background:linear-gradient(180deg,color-mix(in srgb,var(--pin-marker) 36%, #ffffff 64%),var(--pin-marker));' +
                'box-shadow:0 0 10px color-mix(in srgb,var(--pin-marker) 45%, transparent);opacity:0;display:none;transform-origin:bottom center;transform:scaleY(.04);transition:opacity .2s ease,filter .2s ease;}' +
            '.terrain-pin .t-pole::after{content:"";position:absolute;left:-3px;bottom:-2px;width:9px;height:9px;border-radius:50%;background:var(--pin-marker);' +
                'box-shadow:0 0 12px color-mix(in srgb,var(--pin-marker) 68%, transparent);opacity:0;}' +
            '.terrain-pin .t-beam{position:absolute;height:3px;width:0;top:20px;transform-origin:left center;' +
                'background:linear-gradient(90deg,var(--pin-beam-a),var(--pin-beam-b));filter:drop-shadow(0 0 8px color-mix(in srgb, var(--pin-beam-a) 55%, transparent));' +
                'transition:width .2s cubic-bezier(.2,.8,.2,1);}' +
            '.terrain-pin .t-card{position:absolute;display:block;max-width:240px;min-width:180px;pointer-events:auto;text-decoration:none;color:var(--pin-text);' +
                'border:1px solid var(--pin-line);background:linear-gradient(135deg,var(--pin-card-a),var(--pin-card-b));' +
                'padding:10px 12px;box-shadow:0 14px 28px rgba(5,20,38,.42);opacity:0;transform:translate3d(0,0,0) scale(.94);' +
                'transition:opacity .18s ease,transform .24s cubic-bezier(.2,.8,.2,1);' +
                'text-rendering:optimizeLegibility;-webkit-font-smoothing:antialiased;-moz-osx-font-smoothing:grayscale;' +
                'backface-visibility:hidden;transform-style:preserve-3d;will-change:transform,opacity;}' +
            '.terrain-pin.is-left .t-beam{left:22px;top:22px;transform:rotate(-10deg);}' +
            '.terrain-pin.is-right .t-beam{right:22px;left:auto;top:22px;transform-origin:right center;transform:rotate(10deg);}' +
            '.terrain-pin.is-left .t-card{left:0;top:0;transform:translate3d(var(--card-x,-26px),var(--card-y,-30px),0) rotateY(-7deg) rotateX(2deg) scale(.95);}' +
            '.terrain-pin.is-right .t-card{right:0;top:0;transform:translate3d(var(--card-x,-34px),var(--card-y,-30px),0) rotateY(7deg) rotateX(2deg) scale(.95);}' +
            '.terrain-pin.is-active .t-beam{width:var(--beam-w,116px);}' +
            '.terrain-pin.is-active .t-card{opacity:1;}' +
            '.terrain-pin.is-left.is-active .t-card{transform:translate3d(var(--card-x-active,40px),var(--card-y-active,-38px),12px) rotateY(-7deg) rotateX(2deg) scale(1);}' +
            '.terrain-pin.is-right.is-active .t-card{transform:translate3d(var(--card-x-active,-150px),var(--card-y-active,-38px),12px) rotateY(7deg) rotateX(2deg) scale(1);}' +
            '.terrain-pin.is-active::before{opacity:.95;background:var(--pin-marker);box-shadow:0 0 16px color-mix(in srgb, var(--pin-marker) 72%, transparent),0 0 30px color-mix(in srgb, var(--pin-marker) 44%, transparent);}' +
            '.terrain-pin.is-active .t-card{box-shadow:0 16px 34px rgba(5,20,38,.48),0 0 16px color-mix(in srgb, var(--pin-beam-a) 35%, transparent);}' +
            '.terrain-pin.is-active .t-pole{display:block;}' +
            '.terrain-pin.is-active .t-pole{opacity:1;filter:brightness(1.16);animation: pinPoleBeam 1.05s ease-in-out infinite;}' +
            '.terrain-pin.is-active .t-pole::after{opacity:1;animation: pinPoleDot 1.05s linear infinite;}' +
            '.terrain-pin.is-active .t-beam{animation: pinBeamPulse .95s linear infinite;}' +
            '.terrain-pin.is-active .t-card{animation: pinCardGlow .95s ease-in-out infinite alternate;}' +
            '.terrain-pin.is-active::before{animation: pinNodePulse .95s ease-in-out infinite alternate;}' +
            '.terrain-pin .t-tag{display:inline-flex;font-size:10px;text-transform:uppercase;letter-spacing:.1em;' +
                'padding:2px 6px;border:1px solid var(--pin-line);margin-bottom:6px;}' +
            '.terrain-pin .t-title{display:block;font-size:12px;line-height:1.3;font-weight:700;margin:0 0 4px;text-shadow:0 0 1px rgba(0,0,0,.2);}' +
            '.terrain-pin .t-desc{display:block;font-size:11px;line-height:1.32;opacity:.92;text-shadow:0 0 1px rgba(0,0,0,.18);}' +
            '@keyframes pinPoleBeam{0%{transform:scaleY(.03);opacity:.18;}45%{transform:scaleY(1);opacity:1;}100%{transform:scaleY(.03);opacity:.2;}}' +
            '@keyframes pinPoleDot{0%{transform:translateY(0);opacity:.95;}100%{transform:translateY(-140px);opacity:.14;}}' +
            '@keyframes pinNodePulse{0%{transform:scale(.92);}100%{transform:scale(1.16);}}' +
            '@keyframes pinBeamPulse{0%{filter:drop-shadow(0 0 5px color-mix(in srgb, var(--pin-beam-a) 35%, transparent));background-position:0 0;}100%{filter:drop-shadow(0 0 12px color-mix(in srgb, var(--pin-beam-a) 75%, transparent));background-position:120px 0;}}' +
            '@keyframes pinCardGlow{0%{box-shadow:0 14px 28px rgba(5,20,38,.42),0 0 8px color-mix(in srgb, var(--pin-beam-a) 22%, transparent);}100%{box-shadow:0 18px 34px rgba(5,20,38,.54),0 0 18px color-mix(in srgb, var(--pin-beam-a) 54%, transparent);}}';
        document.head.appendChild(style);
    }

    function collectPinFeed() {
        var anchors = document.querySelectorAll('a[href^="/blog/"],a[href^="/services/"],a[href^="/projects/"]');
        var seen = {};
        var out = [];
        for (var i = 0; i < anchors.length; i++) {
            var a = anchors[i];
            var href = (a.getAttribute('href') || '').trim();
            if (!href || href === '/blog/' || href === '/services/' || href === '/projects/') continue;
            href = href.replace(/\/{2,}/g, '/');
            var type = detectTypeByUrl(href);
            if (!type) continue;
            if (seen[href]) continue;
            seen[href] = true;
            var title = normalizeText(a.textContent || '');
            if (!title) continue;
            var root = a.closest('article,section,li,div') || a.parentElement;
            var desc = '';
            if (root) {
                var textNode = root.querySelector('p, .pc-blog-excerpt, .pc-proof-meta');
                if (textNode) desc = normalizeText(textNode.textContent || '');
            }
            out.push({
                href: href,
                title: trimText(title, 74),
                desc: trimText(desc, 92),
                type: type
            });
        }
        if (out.length === 0) {
            out = [
                { href: '/services/', title: 'Архитектура сайтов и платформ', desc: 'Системный запуск с упором на лиды и SEO.', type: 'services' },
                { href: '/projects/', title: 'Продукты с бизнес-результатом', desc: 'Готовые решения: интеграции, метрики и коммерческий эффект.', type: 'projects' },
                { href: '/blog/', title: 'Практика SEO и B2B-маркетинга', desc: 'Материалы по архитектуре и росту.', type: 'blog' }
            ];
        }
        var doubled = out.slice();
        var shuffled = shuffle(out.slice());
        for (var x = 0; x < shuffled.length; x++) {
            doubled.push({
                href: shuffled[x].href,
                title: shuffled[x].title,
                desc: shuffled[x].desc,
                type: shuffled[x].type
            });
        }
        pinFeed = doubled;
    }

    function ensurePinRoot() {
        if (!isPinsEnabled) return;
        if (pinHud) return;
        ensurePinStyles();
        pinHud = document.createElement('div');
        pinHud.className = 'terrain-pin-layer';
        pinHud.classList.add(isEnterprise ? 'theme-enterprise' : 'theme-simple');
        document.body.appendChild(pinHud);
    }

    function applyOppositePinPalette() {
        if (!pinHud || !palette) return;
        var base = (palette.hueBase + (palette.hueSpan * 0.35) + (palette.hueShift * 0.5)) % 360;
        var opp = (base + 180) % 360;
        var hueCardB = (opp + 30) % 360;
        var hueLine = (opp + 58) % 360;
        var hueMarker = (opp + 82) % 360;
        var hueBeam = (opp + 108) % 360;

        pinHud.style.setProperty('--pin-card-a', 'hsla(' + opp.toFixed(1) + ', 66%, 12%, 0.98)');
        pinHud.style.setProperty('--pin-card-b', 'hsla(' + hueCardB.toFixed(1) + ', 82%, 28%, 0.94)');
        pinHud.style.setProperty('--pin-line', 'hsla(' + hueLine.toFixed(1) + ', 92%, 76%, 0.62)');
        pinHud.style.setProperty('--pin-text', '#fbfdff');
        pinHud.style.setProperty('--pin-marker', 'hsla(' + hueMarker.toFixed(1) + ', 98%, 68%, 0.98)');
        pinHud.style.setProperty('--pin-marker-bg', 'hsla(' + hueMarker.toFixed(1) + ', 98%, 68%, 0.22)');
        pinHud.style.setProperty('--pin-beam-a', 'hsla(' + hueBeam.toFixed(1) + ', 100%, 78%, 0.96)');
        pinHud.style.setProperty('--pin-beam-b', 'hsla(' + opp.toFixed(1) + ', 100%, 66%, 0.26)');
    }

    function pickPinItem(side) {
        if (!pinFeed || pinFeed.length === 0) return null;
        var pools = [];
        for (var i = 0; i < pinFeed.length; i++) {
            if (pinFeed[i].type === 'blog') pools.push(pinFeed[i]);
            if (side === 'left' && pinFeed[i].type === 'services') pools.push(pinFeed[i]);
            if (side === 'right' && pinFeed[i].type === 'projects') pools.push(pinFeed[i]);
        }
        if (pools.length === 0) pools = pinFeed.slice();
        return pools[Math.floor(Math.random() * pools.length)];
    }

    function createPinNode(side, pointRef, idx) {
        if (!pinHud) return null;
        var item = pickPinItem(side);
        if (!item) return;
        var wrap = document.createElement('div');
        wrap.className = 'terrain-pin is-' + side;
        wrap.setAttribute('data-pin-id', side + '-' + (idx + 1));
        wrap.setAttribute('data-pin-group', side);
        var card = document.createElement('a');
        card.className = 't-card';
        card.href = item.href;
        card.innerHTML =
            '<span class="t-tag">' + escapeHtml(getTypeLabel(item.type)) + '</span>' +
            '<span class="t-title">' + escapeHtml(item.title) + '</span>' +
            (item.desc ? ('<span class="t-desc">' + escapeHtml(item.desc) + '</span>') : '');
        wrap.innerHTML = '<span class="t-pole"></span><span class="t-beam"></span>';
        wrap.appendChild(card);
        pinHud.appendChild(wrap);

        // Keep card visually attached to the same active polygon/pole.
        var oy = Math.floor(rand(-20, 20));
        var oxIdle = side === 'left'
            ? Math.floor(rand(16, 36))
            : Math.floor(rand(-36, -16));
        var oxActive = side === 'left'
            ? Math.floor(rand(34, 86))
            : Math.floor(rand(-86, -34));
        var oyActive = Math.floor(oy * 0.68);
        var beamActive = Math.max(44, Math.abs(oxActive) - 10);
        wrap.style.setProperty('--card-x', oxIdle + 'px');
        wrap.style.setProperty('--card-y', oy + 'px');
        wrap.style.setProperty('--card-x-active', oxActive + 'px');
        wrap.style.setProperty('--card-y-active', oyActive + 'px');
        wrap.style.setProperty('--beam-w', beamActive + 'px');

        var pin = {
            side: side,
            row: pointRef.row,
            col: pointRef.col,
            el: wrap,
            id: side + '-' + (idx + 1),
            activationRadius: 5,
            active: false,
            hoverHold: false,
            minVisibleUntil: 0,
            lockX: null,
            lockY: null,
            bornAt: 0,
            queueOrder: (side === 'left' ? idx * 2 : (idx * 2 + 1))
        };
        return pin;
    }

    function scoreCandidate(c, tx, ty) {
        var dx = Math.abs(c.x - tx);
        var dy = Math.abs(c.y - ty);
        var topBias = c.y / Math.max(1, h); // lower y => closer to top => smaller score
        var heightBias = (typeof c.h === 'number') ? (-c.h * 0.22) : 0; // higher terrain peak => better
        return dx * 1.15 + dy * 0.95 + (1 - c.seed) * 24 + topBias * 70 + heightBias + Math.random() * 10;
    }

    function pickAnchors(candidates, tx, count, side) {
        var out = [];
        var used = {};
        if (!candidates || candidates.length === 0) return out;

        var yMin = Math.floor(h * 0.10);
        var yMax = Math.floor(h * 0.88);
        var span = Math.max(1, yMax - yMin);
        var xMin = 36;
        var xMax = Math.max(40, w - 36);
        if (side === 'left') {
            xMax = Math.max(100, Math.floor(w * 0.34));
        } else if (side === 'right') {
            xMin = Math.max(40, Math.floor(w * 0.66));
        }
        var minDistance = Math.max(96, Math.floor(Math.min(w, h) * 0.12));

        for (var i = 0; i < count; i++) {
            var ty = yMin + ((i + 0.5) / count) * span + rand(-22, 22);
            var txLocal = clamp(tx + rand(-180, 180), xMin, xMax);
            var best = null;
            var bestScore = Infinity;

            for (var j = 0; j < candidates.length; j++) {
                var c = candidates[j];
                var key = c.row + ':' + c.col;
                if (used[key]) continue;
                var tooClose = false;
                for (var q = 0; q < out.length; q++) {
                    var ex = out[q];
                    var dxq = c.x - ex.x;
                    var dyq = c.y - ex.y;
                    if ((dxq * dxq + dyq * dyq) < (minDistance * minDistance)) {
                        tooClose = true;
                        break;
                    }
                }
                if (tooClose) continue;
                var sc = scoreCandidate(c, txLocal, ty);
                if (sc < bestScore) {
                    bestScore = sc;
                    best = c;
                }
            }

            if (!best) {
                // relaxed pass if strict spacing filtered out too much
                for (var k = 0; k < candidates.length; k++) {
                    var c2 = candidates[k];
                    var key2 = c2.row + ':' + c2.col;
                    if (used[key2]) continue;
                    var sc2 = scoreCandidate(c2, txLocal, ty);
                    if (sc2 < bestScore) {
                        bestScore = sc2;
                        best = c2;
                    }
                }
            }
            if (!best) break;
            used[best.row + ':' + best.col] = true;
            out.push({ row: best.row, col: best.col, x: best.x, y: best.y });
        }

        return out;
    }

    function collectAllVisiblePoints() {
        var points = [];
        for (var r = 1; r < rows - 1; r++) {
            var row = terrain[r];
            if (!row) continue;
            for (var c = 1; c < cols - 1; c++) {
                var p = row[c];
                if (!p || !p.visible) continue;
                if (p.sy < 64 || p.sy > h - 56) continue;
                points.push({ row: r, col: c, x: p.sx, y: p.sy, h: p.h || 0, seed: p.seed || 0.5 });
            }
        }
        return points;
    }

    function selectEdgePoints() {
        var leftCandidates = [];
        var rightCandidates = [];
        var widthBoost = clamp((w - 1024) / 1200, 0, 1);
        var edgeBandPct = 0.15 + widthBoost * 0.13; // wider screens => wider capture area
        var leftBand = Math.floor(w * edgeBandPct);
        var rightBand = w - leftBand;

        for (var r = 1; r < rows - 1; r++) {
            var row = terrain[r];
            if (!row) continue;
            for (var c = 1; c < cols - 1; c++) {
                var p = row[c];
                if (!p || !p.visible) continue;
                if (p.sy < 70 || p.sy > h - 70) continue;
                var rowRef = { row: r, col: c, x: p.sx, y: p.sy, h: p.h || 0, seed: p.seed || 0.5 };
                if (p.sx <= leftBand) leftCandidates.push(rowRef);
                if (p.sx >= rightBand) rightCandidates.push(rowRef);
            }
        }

        // fallback #1: widen side bands
        if (leftCandidates.length < (pinCountPerSide * 2) || rightCandidates.length < (pinCountPerSide * 2)) {
            var widePct = 0.34 + widthBoost * 0.16;
            for (var rr = 1; rr < rows - 1; rr++) {
                var rrRow = terrain[rr];
                if (!rrRow) continue;
                for (var cc = 1; cc < cols - 1; cc++) {
                    var pp = rrRow[cc];
                    if (!pp || !pp.visible) continue;
                    if (pp.sy < 70 || pp.sy > h - 70) continue;
                    var ref = { row: rr, col: cc, x: pp.sx, y: pp.sy, h: pp.h || 0, seed: pp.seed || 0.5 };
                    if (pp.sx <= w * widePct) leftCandidates.push(ref);
                    if (pp.sx >= w * (1 - widePct)) rightCandidates.push(ref);
                }
            }
        }

        // fallback #2: if still low, use all visible points and pull nearest to side targets
        if (leftCandidates.length < 10 || rightCandidates.length < 10) {
            var all = collectAllVisiblePoints();
            if (leftCandidates.length < 10) leftCandidates = all.slice();
            if (rightCandidates.length < 10) rightCandidates = all.slice();
        }

        var edgeTargetPct = 0.08 + widthBoost * 0.06;
        var left = pickAnchors(leftCandidates, Math.floor(w * edgeTargetPct), pinCountPerSide, 'left');
        var right = pickAnchors(rightCandidates, Math.floor(w * (1 - edgeTargetPct)), pinCountPerSide, 'right');

        return { left: left, right: right };
    }

    function clearPins() {
        for (var i = 0; i < pinNodes.length; i++) {
            if (pinNodes[i].el && pinNodes[i].el.parentNode) {
                pinNodes[i].el.parentNode.removeChild(pinNodes[i].el);
            }
        }
        pinNodes = [];
        activePinId = '';
        pinQueueCursor = 0;
        pinNextAllowedAt = 0;
    }

    function buildPinsFromMap() {
        if (!isPinsEnabled) return;
        ensurePinRoot();
        clearPins();
        var picked = selectEdgePoints();
        for (var i = 0; i < picked.left.length; i++) {
            var lp = createPinNode('left', picked.left[i], i);
            if (lp) pinNodes.push(lp);
        }
        for (var j = 0; j < picked.right.length; j++) {
            var rp = createPinNode('right', picked.right[j], j);
            if (rp) pinNodes.push(rp);
        }
        // hard fallback: if selection failed, create synthetic anchors so effect is always visible
        if (pinNodes.length === 0) {
            var widthBoost = clamp((w - 1024) / 1200, 0, 1);
            var leftBase = 0.09 + widthBoost * 0.06;
            var rightBase = 0.91 - widthBoost * 0.06;
            for (var li = 0; li < pinCountPerSide; li++) {
                var leftSyntheticX = clamp(Math.floor(w * leftBase + rand(-100 - 120 * widthBoost, 100 + 120 * widthBoost)), 36, Math.max(110, Math.floor(w * (0.34 + widthBoost * 0.16))));
                var leftSynthetic = { row: 0, col: 0, sx: leftSyntheticX, sy: Math.floor(h * (0.11 + li * (0.78 / Math.max(1, pinCountPerSide)))), synthetic: true };
                var lsn = createPinNode('left', leftSynthetic, li);
                if (lsn) {
                    lsn.synthetic = true;
                    lsn.sx = leftSynthetic.sx;
                    lsn.sy = leftSynthetic.sy;
                    pinNodes.push(lsn);
                }
            }
            for (var ri = 0; ri < pinCountPerSide; ri++) {
                var rightSyntheticX = clamp(Math.floor(w * rightBase + rand(-100 - 120 * widthBoost, 100 + 120 * widthBoost)), Math.max(40, Math.floor(w * (0.66 - widthBoost * 0.16))), w - 36);
                var rightSynthetic = { row: 0, col: 0, sx: rightSyntheticX, sy: Math.floor(h * (0.11 + ri * (0.78 / Math.max(1, pinCountPerSide)))), synthetic: true };
                var rsn = createPinNode('right', rightSynthetic, ri);
                if (rsn) {
                    rsn.synthetic = true;
                    rsn.sx = rightSynthetic.sx;
                    rsn.sy = rightSynthetic.sy;
                    pinNodes.push(rsn);
                }
            }
        }
        pinsBuilt = true;
        window.__terrainPinsDebug = {
            enabled: isPinsEnabled,
            host: currentHost,
            lang: currentLang,
            path: currentPath,
            count: pinNodes.length
        };
    }

    function updatePins() {
        if (!isPinsEnabled || pinNodes.length === 0) return;
        var nowTs = pinNowTs || Date.now();
        var nearestPin = null;
        var nearestDist2 = Infinity;
        var activePin = null;
        var activePinDist2 = Infinity;
        var livePosById = {};
        var triggerRadius2 = pinTriggerRadiusPx * pinTriggerRadiusPx;
        var deactivateRadius2 = pinDeactivateRadiusPx * pinDeactivateRadiusPx;
        var inTriggerPins = [];
        var pinHitDist2 = {};

        function isPointerInsideFlagZone(pin, pad) {
            if (!pin || !pin.el || !pointer.active) return false;
            var card = pin.el.querySelector('.t-card');
            if (!card) return false;
            var rect = card.getBoundingClientRect();
            if (!rect || !isFinite(rect.left) || !isFinite(rect.top)) return false;
            var x = pointer.x;
            var y = pointer.y;
            return x >= (rect.left - pad) &&
                x <= (rect.right + pad) &&
                y >= (rect.top - pad) &&
                y <= (rect.bottom + pad);
        }

        function distPointToSeg2(px, py, ax, ay, bx, by) {
            var vx = bx - ax;
            var vy = by - ay;
            var wx = px - ax;
            var wy = py - ay;
            var vv = vx * vx + vy * vy;
            var t = vv > 0 ? ((wx * vx + wy * vy) / vv) : 0;
            if (t < 0) t = 0;
            if (t > 1) t = 1;
            var cx = ax + vx * t;
            var cy = ay + vy * t;
            var dx = px - cx;
            var dy = py - cy;
            return dx * dx + dy * dy;
        }

        function pinEdgeDistance2(pin, p) {
            // Synthetic points fallback to point distance.
            if (pin.synthetic) {
                var sdx = pointer.smoothX - p.sx;
                var sdy = pointer.smoothY - p.sy;
                return sdx * sdx + sdy * sdy;
            }
            var rr = pin.row;
            var cc = pin.col;
            if (rr < 0 || cc < 0 || rr >= rows - 1 || cc >= cols - 1) {
                var fdx = pointer.smoothX - p.sx;
                var fdy = pointer.smoothY - p.sy;
                return fdx * fdx + fdy * fdy;
            }
            var r0 = terrain[rr];
            var r1 = terrain[rr + 1];
            if (!r0 || !r1 || !r0[cc] || !r0[cc + 1] || !r1[cc] || !r1[cc + 1]) {
                var gdx = pointer.smoothX - p.sx;
                var gdy = pointer.smoothY - p.sy;
                return gdx * gdx + gdy * gdy;
            }
            var a = r0[cc];
            var b = r0[cc + 1];
            var e = r1[cc + 1];
            var d = r1[cc];
            // Distance to polygon boundaries (four segments).
            var d1 = distPointToSeg2(pointer.smoothX, pointer.smoothY, a.sx, a.sy, b.sx, b.sy);
            var d2 = distPointToSeg2(pointer.smoothX, pointer.smoothY, b.sx, b.sy, e.sx, e.sy);
            var d3 = distPointToSeg2(pointer.smoothX, pointer.smoothY, e.sx, e.sy, d.sx, d.sy);
            var d4 = distPointToSeg2(pointer.smoothX, pointer.smoothY, d.sx, d.sy, a.sx, a.sy);
            return Math.min(d1, d2, d3, d4);
        }

        for (var i = 0; i < pinNodes.length; i++) {
            var pin = pinNodes[i];
            var p = null;
            if (pin.synthetic) {
                p = { sx: pin.sx, sy: pin.sy, visible: true };
            } else {
                var row = terrain[pin.row];
                p = row ? row[pin.col] : null;
                if (!p || !p.visible) {
                    pin.el.style.display = 'none';
                    continue;
                }
            }
            livePosById[pin.id] = { x: p.sx, y: p.sy };

            var renderX = p.sx;
            var renderY = p.sy;
            if ((pin.active || pin.hoverHold) && pin.lockX !== null && pin.lockY !== null) {
                renderX = pin.lockX;
                renderY = pin.lockY;
            }
            pin.el.style.display = '';
            pin.el.style.setProperty('--px', renderX.toFixed(1) + 'px');
            pin.el.style.setProperty('--py', renderY.toFixed(1) + 'px');

            var d2 = pinEdgeDistance2(pin, p);
            pinHitDist2[pin.id] = d2;
            if (pointer.active && d2 <= triggerRadius2) {
                inTriggerPins.push(pin);
            }
            if (d2 < nearestDist2) {
                nearestDist2 = d2;
                nearestPin = pin;
            }
            if (pin.id === activePinId) {
                activePin = pin;
                activePinDist2 = d2;
            }
            pin.el.classList.remove('is-active');
        }

        var nextActiveId = '';

        function pickNextFromQueue(candidates) {
            if (!candidates || candidates.length === 0) return null;
            var sorted = candidates.slice().sort(function (a, b) {
                return a.queueOrder - b.queueOrder;
            });
            for (var si = 0; si < sorted.length; si++) {
                if (sorted[si].queueOrder >= pinQueueCursor) {
                    return sorted[si];
                }
            }
            return sorted[0];
        }

        // Strict single-flag mode with queue: while active flag lives, no next flag can open.
        if (activePin) {
            var keepByMinLife = nowTs < activePin.minVisibleUntil;
            var keepByMaxLife = nowTs < (activePin.bornAt + pinMaxLifeMs);
            var currentActiveDist2 = (typeof pinHitDist2[activePin.id] === 'number') ? pinHitDist2[activePin.id] : activePinDist2;
            var keepByDistance = pointer.active && currentActiveDist2 <= deactivateRadius2;
            var keepByFlagZone = isPointerInsideFlagZone(activePin, pinHoverPaddingPx);
            activePin.hoverHold = keepByFlagZone;
            if (keepByDistance || keepByFlagZone || (keepByMinLife && keepByMaxLife)) {
                nextActiveId = activePin.id;
                activePin.minVisibleUntil = nowTs + pinCloseLagMs;
            }
        }
        if (!nextActiveId && !activePin && pointer.active && nowTs >= pinNextAllowedAt) {
            var nextPin = pickNextFromQueue(inTriggerPins);
            if (!nextPin && nearestPin && nearestDist2 <= deactivateRadius2) {
                nextPin = nearestPin;
            }
            if (nextPin) {
                nextActiveId = nextPin.id;
                pinQueueCursor = (nextPin.queueOrder + 1) % Math.max(1, pinNodes.length);
            }
        }

        for (var j = 0; j < pinNodes.length; j++) {
            var curr = pinNodes[j];
            if (curr.id === nextActiveId) {
                curr.el.classList.add('is-active');
                if (!curr.active) {
                    curr.active = true;
                    if (curr.lockX === null || curr.lockY === null) {
                        var lp = livePosById[curr.id];
                        if (lp) {
                            curr.lockX = lp.x;
                            curr.lockY = lp.y;
                        }
                    }
                    curr.bornAt = nowTs;
                    curr.minVisibleUntil = nowTs + pinMinLifeMs;
                    console.log('[terrain-pin] show', curr.id, {
                        group: curr.side
                    });
                } else if (curr.id === nextActiveId) {
                    // refresh visible window while cursor is near
                    curr.minVisibleUntil = Math.max(curr.minVisibleUntil, nowTs + 180);
                }
            } else {
                if (curr.active) {
                    curr.active = false;
                    curr.hoverHold = false;
                    curr.lockX = null;
                    curr.lockY = null;
                    curr.bornAt = 0;
                    pinNextAllowedAt = nowTs + pinSwitchCooldownMs;
                    console.log('[terrain-pin] hide', curr.id, {
                        group: curr.side
                    });
                }
            }
        }
        activePinId = nextActiveId;
    }

    function onScroll() {
        var y = window.scrollY || window.pageYOffset || 0;
        lastScrollTs = Date.now();
        var doc = document.documentElement;
        var body = document.body;
        var fullHeight = Math.max(
            (doc && doc.scrollHeight) || 0,
            (body && body.scrollHeight) || 0,
            (window.innerHeight || h || 1)
        );
        var viewportH = Math.max(1, window.innerHeight || h || 1);
        var maxScrollable = Math.max(1, fullHeight - viewportH);
        var progress = clamp(y / maxScrollable, 0, 1);
        var travelRange = clamp(Math.round(viewportH * 0.20), 64, 220);
        canvasParallax.targetY = clamp(progress * travelRange, 0, travelRange);
    }

    function makeMapProfile() {
        var macroCount = Math.floor(rand(2, 6));
        var peaks = [];
        for (var i = 0; i < macroCount; i++) {
            peaks.push({
                cx: rand(-0.9, 0.9),
                cz: rand(0.05, 1.1),
                radius: rand(0.18, 0.62),
                height: rand(-34, 58)
            });
        }
        return {
            horizon: rand(0.12, 0.23),
            depthScale: rand(0.53, 0.71),
            perspectiveBias: rand(115, 178),
            noiseScaleX: rand(0.0019, 0.0037),
            noiseScaleZ: rand(0.0020, 0.0038),
            flowX: rand(-15, 15),
            flowZ: rand(-18, 18),
            ridgePower: rand(1.8, 3.8),
            mountainAmp: rand(28, 70),
            valleyAmp: rand(16, 46),
            undulateAmp: rand(14, 36),
            macroSlopeX: rand(-20, 20),
            macroSlopeZ: rand(-14, 12),
            macroPeaks: peaks
        };
    }

    function makePalette() {
        var ruPresets = [
            {
                hueBase: [196, 228], hueSpan: [40, 84], hueShift: [10, 24],
                satBase: [46, 74], satSpan: [10, 20], lightBase: [36, 56], lightSpan: [20, 34],
                lineAlpha: [0.24, 0.42], pointAlpha: [0.58, 0.78],
                bgHueA: [202, 230], bgHueB: [236, 272], bgSatA: [34, 58], bgSatB: [26, 44],
                bgLightA: [82, 94], bgLightB: [72, 88], bgAlphaA: [0.26, 0.44], bgAlphaB: [0.05, 0.14]
            },
            {
                hueBase: [142, 176], hueSpan: [34, 70], hueShift: [8, 20],   // green-teal
                satBase: [40, 70], satSpan: [8, 20], lightBase: [34, 54], lightSpan: [18, 32],
                lineAlpha: [0.24, 0.42], pointAlpha: [0.56, 0.76],
                bgHueA: [154, 182], bgHueB: [190, 220], bgSatA: [24, 44], bgSatB: [18, 34],
                bgLightA: [84, 95], bgLightB: [74, 90], bgAlphaA: [0.24, 0.40], bgAlphaB: [0.04, 0.12]
            },
            {
                hueBase: [34, 58], hueSpan: [26, 56], hueShift: [6, 16],      // gold-amber
                satBase: [34, 58], satSpan: [8, 16], lightBase: [36, 58], lightSpan: [18, 30],
                lineAlpha: [0.22, 0.40], pointAlpha: [0.54, 0.74],
                bgHueA: [28, 54], bgHueB: [66, 94], bgSatA: [22, 42], bgSatB: [16, 30],
                bgLightA: [84, 95], bgLightB: [74, 90], bgAlphaA: [0.24, 0.40], bgAlphaB: [0.04, 0.12]
            },
            {
                hueBase: [206, 236], hueSpan: [20, 44], hueShift: [6, 14],    // steel-gray/cool neutral
                satBase: [14, 36], satSpan: [4, 12], lightBase: [40, 58], lightSpan: [12, 24],
                lineAlpha: [0.22, 0.40], pointAlpha: [0.54, 0.72],
                bgHueA: [210, 232], bgHueB: [226, 248], bgSatA: [14, 28], bgSatB: [10, 22],
                bgLightA: [85, 95], bgLightB: [76, 90], bgAlphaA: [0.22, 0.36], bgAlphaB: [0.04, 0.10]
            }
        ];

        var enDarkPresets = [
            {
                hueBase: [192, 224], hueSpan: [34, 72], hueShift: [8, 20],
                satBase: [36, 62], satSpan: [8, 18], lightBase: [22, 36], lightSpan: [16, 28],
                lineAlpha: [0.30, 0.50], pointAlpha: [0.64, 0.86],
                bgHueA: [198, 224], bgHueB: [228, 256], bgSatA: [22, 42], bgSatB: [16, 32],
                bgLightA: [10, 18], bgLightB: [5, 12], bgAlphaA: [0.20, 0.34], bgAlphaB: [0.04, 0.11]
            },
            {
                hueBase: [144, 172], hueSpan: [28, 56], hueShift: [6, 16],    // dark green
                satBase: [28, 52], satSpan: [6, 16], lightBase: [20, 34], lightSpan: [14, 24],
                lineAlpha: [0.30, 0.50], pointAlpha: [0.62, 0.84],
                bgHueA: [150, 176], bgHueB: [182, 210], bgSatA: [18, 32], bgSatB: [12, 26],
                bgLightA: [9, 16], bgLightB: [4, 10], bgAlphaA: [0.20, 0.32], bgAlphaB: [0.04, 0.10]
            },
            {
                hueBase: [38, 58], hueSpan: [18, 40], hueShift: [4, 12],      // dark gold
                satBase: [24, 44], satSpan: [4, 12], lightBase: [21, 34], lightSpan: [10, 20],
                lineAlpha: [0.28, 0.48], pointAlpha: [0.60, 0.82],
                bgHueA: [32, 52], bgHueB: [56, 78], bgSatA: [14, 26], bgSatB: [10, 20],
                bgLightA: [10, 16], bgLightB: [4, 9], bgAlphaA: [0.20, 0.30], bgAlphaB: [0.03, 0.09]
            },
            {
                hueBase: [206, 228], hueSpan: [16, 34], hueShift: [4, 10],    // dark steel-gray
                satBase: [10, 24], satSpan: [2, 8], lightBase: [20, 32], lightSpan: [10, 20],
                lineAlpha: [0.26, 0.44], pointAlpha: [0.56, 0.78],
                bgHueA: [208, 224], bgHueB: [224, 242], bgSatA: [8, 20], bgSatB: [6, 16],
                bgLightA: [9, 15], bgLightB: [4, 9], bgAlphaA: [0.18, 0.28], bgAlphaB: [0.03, 0.08]
            }
        ];

        function wrapHue(v) {
            var x = v % 360;
            return x < 0 ? x + 360 : x;
        }

        function clampRange(min, max) {
            if (max < min) {
                var t = min;
                min = max;
                max = t;
            }
            return [min, max];
        }

        function premiumPreset(profile, tone) {
            var isDark = tone === 'dark';
            var hueCenter = wrapHue(profile.h || 210);
            var hueShift = profile.hs || 10;
            var hueSpan = profile.hsp || 52;
            var satCenter = profile.s || (isDark ? 34 : 56);
            var lightCenter = profile.l || (isDark ? 30 : 48);
            var bgAHue = wrapHue(profile.bga || hueCenter + (isDark ? -8 : 6));
            var bgBHue = wrapHue(profile.bgb || hueCenter + (isDark ? 28 : 34));
            var bgASat = profile.bgas || (isDark ? 24 : 42);
            var bgBSat = profile.bgbs || (isDark ? 18 : 34);
            var bgALight = profile.bgal || (isDark ? 14 : 90);
            var bgBLight = profile.bgbl || (isDark ? 8 : 82);
            var lineA = profile.la || (isDark ? 0.32 : 0.26);
            var lineB = profile.lb || (isDark ? 0.48 : 0.40);
            var pointA = profile.pa || (isDark ? 0.66 : 0.58);
            var pointB = profile.pb || (isDark ? 0.84 : 0.76);

            return {
                hueBase: clampRange(hueCenter - hueShift, hueCenter + hueShift),
                hueSpan: clampRange(Math.max(16, hueSpan - 12), Math.max(24, hueSpan + 14)),
                hueShift: clampRange(Math.max(4, hueShift - 3), Math.max(8, hueShift + 4)),
                satBase: clampRange(Math.max(10, satCenter - 12), Math.min(84, satCenter + 10)),
                satSpan: clampRange(isDark ? 4 : 6, isDark ? 16 : 20),
                lightBase: clampRange(Math.max(14, lightCenter - 10), Math.min(72, lightCenter + 8)),
                lightSpan: clampRange(isDark ? 12 : 16, isDark ? 26 : 34),
                lineAlpha: clampRange(Math.max(0.20, lineA), Math.min(0.54, lineB)),
                pointAlpha: clampRange(Math.max(0.50, pointA), Math.min(0.90, pointB)),
                bgHueA: clampRange(bgAHue - 8, bgAHue + 8),
                bgHueB: clampRange(bgBHue - 9, bgBHue + 9),
                bgSatA: clampRange(Math.max(8, bgASat - 8), Math.min(64, bgASat + 8)),
                bgSatB: clampRange(Math.max(6, bgBSat - 7), Math.min(56, bgBSat + 7)),
                bgLightA: clampRange(Math.max(6, bgALight - 5), Math.min(96, bgALight + 5)),
                bgLightB: clampRange(Math.max(4, bgBLight - 5), Math.min(94, bgBLight + 5)),
                bgAlphaA: clampRange(isDark ? 0.18 : 0.24, isDark ? 0.34 : 0.44),
                bgAlphaB: clampRange(isDark ? 0.03 : 0.04, isDark ? 0.11 : 0.14)
            };
        }

        function buildPremiumPresets(profiles, tone) {
            var out = [];
            var hueOffsets = [-10, -4, 3, 9];
            for (var i = 0; i < profiles.length; i++) {
                var p = profiles[i];
                for (var j = 0; j < hueOffsets.length; j++) {
                    out.push(premiumPreset({
                        h: wrapHue((p.h || 210) + hueOffsets[j]),
                        hs: Math.max(8, (p.hs || 10) + (j % 2 === 0 ? 0 : 1)),
                        hsp: Math.max(28, (p.hsp || 52) + (j === 0 ? -4 : (j === 3 ? 6 : 0))),
                        s: Math.max(18, (p.s || 50) + (j === 2 ? 2 : 0)),
                        l: Math.max(18, (p.l || 46) + (j === 1 ? 1 : 0)),
                        bga: wrapHue((p.bga || p.h || 210) + (j - 1)),
                        bgb: wrapHue((p.bgb || (p.h || 210) + 28) + (j - 1)),
                        bgas: p.bgas,
                        bgbs: p.bgbs,
                        bgal: p.bgal,
                        bgbl: p.bgbl,
                        la: p.la,
                        lb: p.lb,
                        pa: p.pa,
                        pb: p.pb
                    }, tone));
                }
            }
            return out;
        }

        function getPresetHueCenter(preset) {
            var baseMid = ((preset.hueBase[0] + preset.hueBase[1]) * 0.5);
            var spanMid = ((preset.hueSpan[0] + preset.hueSpan[1]) * 0.5) * 0.22;
            return wrapHue(baseMid + spanMid);
        }

        function getPresetSatCenter(preset) {
            return (preset.satBase[0] + preset.satBase[1]) * 0.5;
        }

        function classifyLightBucket(preset) {
            var sat = getPresetSatCenter(preset);
            if (sat < 24) return 'neutral';

            var hue = getPresetHueCenter(preset);
            if (hue >= 345 || hue < 20) return 'red';
            if (hue < 45) return 'orange';
            if (hue < 70) return 'yellow';
            if (hue < 155) return 'green';
            if (hue < 190) return 'teal';
            if (hue < 245) return 'blue';
            if (hue < 295) return 'violet';
            return 'magenta';
        }

        function buildLightPresetBuckets(presets) {
            var order = ['red', 'orange', 'yellow', 'green', 'teal', 'blue', 'violet', 'magenta', 'neutral'];
            var grouped = {};
            var i;

            for (i = 0; i < order.length; i++) {
                grouped[order[i]] = [];
            }
            for (i = 0; i < presets.length; i++) {
                grouped[classifyLightBucket(presets[i])].push(presets[i]);
            }

            var buckets = [];
            for (i = 0; i < order.length; i++) {
                if (grouped[order[i]].length) {
                    buckets.push(grouped[order[i]]);
                }
            }
            return buckets.length ? buckets : [presets];
        }

        var ruPremiumProfiles = [
            { h: 216, hs: 7, hsp: 26, s: 20, l: 52, bga: 214, bgb: 236, bgas: 16, bgbs: 12, bgal: 92, bgbl: 86, la: 0.24, lb: 0.36, pa: 0.55, pb: 0.72 },  // sterling silver
            { h: 210, hs: 7, hsp: 24, s: 18, l: 51, bga: 208, bgb: 230, bgas: 14, bgbs: 10, bgal: 92, bgbl: 85, la: 0.23, lb: 0.35, pa: 0.54, pb: 0.71 },  // platinum mist
            { h: 46, hs: 8, hsp: 30, s: 40, l: 52, bga: 40, bgb: 74, bgas: 30, bgbs: 22, bgal: 92, bgbl: 85, la: 0.24, lb: 0.37, pa: 0.55, pb: 0.73 },    // brushed gold
            { h: 38, hs: 8, hsp: 28, s: 38, l: 51, bga: 34, bgb: 68, bgas: 28, bgbs: 20, bgal: 92, bgbl: 85, la: 0.24, lb: 0.36, pa: 0.55, pb: 0.72 },    // champagne gold
            { h: 208, hs: 11, hsp: 50, s: 58, l: 50, bga: 214, bgb: 246, bgas: 44, bgbs: 36, bgal: 90, bgbl: 83, la: 0.26, lb: 0.40, pa: 0.58, pb: 0.76 }, // arctic silk
            { h: 192, hs: 10, hsp: 46, s: 54, l: 49, bga: 196, bgb: 232, bgas: 42, bgbs: 32, bgal: 90, bgbl: 82, la: 0.25, lb: 0.38, pa: 0.56, pb: 0.74 }, // lagoon glass
            { h: 224, hs: 12, hsp: 56, s: 52, l: 48, bga: 226, bgb: 262, bgas: 40, bgbs: 30, bgal: 91, bgbl: 83, la: 0.25, lb: 0.39, pa: 0.57, pb: 0.75 }, // cobalt haze
            { h: 170, hs: 10, hsp: 44, s: 50, l: 47, bga: 174, bgb: 210, bgas: 38, bgbs: 30, bgal: 90, bgbl: 82, la: 0.24, lb: 0.38, pa: 0.56, pb: 0.74 }, // mint signal
            { h: 154, hs: 10, hsp: 42, s: 48, l: 46, bga: 158, bgb: 194, bgas: 36, bgbs: 28, bgal: 90, bgbl: 81, la: 0.24, lb: 0.38, pa: 0.55, pb: 0.73 }, // jade mesh
            { h: 138, hs: 10, hsp: 40, s: 46, l: 45, bga: 142, bgb: 178, bgas: 34, bgbs: 26, bgal: 90, bgbl: 81, la: 0.24, lb: 0.37, pa: 0.55, pb: 0.72 }, // verdant ledger
            { h: 34, hs: 9, hsp: 36, s: 44, l: 50, bga: 30, bgb: 70, bgas: 34, bgbs: 26, bgal: 91, bgbl: 83, la: 0.23, lb: 0.36, pa: 0.54, pb: 0.72 },  // champagne glow
            { h: 26, hs: 8, hsp: 34, s: 42, l: 49, bga: 24, bgb: 62, bgas: 32, bgbs: 24, bgal: 91, bgbl: 83, la: 0.23, lb: 0.36, pa: 0.54, pb: 0.71 },  // amber satin
            { h: 16, hs: 8, hsp: 32, s: 40, l: 48, bga: 18, bgb: 52, bgas: 30, bgbs: 22, bgal: 91, bgbl: 83, la: 0.23, lb: 0.35, pa: 0.53, pb: 0.70 },  // rose copper
            { h: 284, hs: 10, hsp: 42, s: 44, l: 49, bga: 278, bgb: 314, bgas: 32, bgbs: 24, bgal: 91, bgbl: 83, la: 0.24, lb: 0.37, pa: 0.55, pb: 0.72 }, // orchid mist
            { h: 262, hs: 10, hsp: 44, s: 46, l: 48, bga: 258, bgb: 294, bgas: 34, bgbs: 26, bgal: 91, bgbl: 83, la: 0.24, lb: 0.37, pa: 0.55, pb: 0.73 }, // violet harbor
            { h: 244, hs: 9, hsp: 40, s: 42, l: 47, bga: 242, bgb: 278, bgas: 30, bgbs: 24, bgal: 90, bgbl: 82, la: 0.24, lb: 0.37, pa: 0.54, pb: 0.72 },  // indigo silk
            { h: 210, hs: 8, hsp: 34, s: 32, l: 47, bga: 212, bgb: 242, bgas: 22, bgbs: 18, bgal: 90, bgbl: 82, la: 0.22, lb: 0.34, pa: 0.52, pb: 0.70 }, // steel whisper
            { h: 198, hs: 8, hsp: 32, s: 30, l: 48, bga: 200, bgb: 232, bgas: 20, bgbs: 16, bgal: 90, bgbl: 82, la: 0.22, lb: 0.34, pa: 0.52, pb: 0.69 }, // cloud chrome
            { h: 184, hs: 8, hsp: 30, s: 30, l: 49, bga: 186, bgb: 220, bgas: 20, bgbs: 16, bgal: 90, bgbl: 82, la: 0.22, lb: 0.34, pa: 0.52, pb: 0.69 }, // polar slate
            { h: 120, hs: 9, hsp: 34, s: 34, l: 47, bga: 124, bgb: 160, bgas: 24, bgbs: 18, bgal: 90, bgbl: 82, la: 0.23, lb: 0.35, pa: 0.53, pb: 0.70 }, // olive satin
            { h: 106, hs: 9, hsp: 34, s: 34, l: 46, bga: 110, bgb: 146, bgas: 24, bgbs: 18, bgal: 90, bgbl: 82, la: 0.23, lb: 0.35, pa: 0.53, pb: 0.70 }, // sage premium
            { h: 88, hs: 9, hsp: 34, s: 32, l: 47, bga: 92, bgb: 128, bgas: 22, bgbs: 18, bgal: 90, bgbl: 82, la: 0.23, lb: 0.35, pa: 0.52, pb: 0.69 },   // moss ledger
            { h: 320, hs: 9, hsp: 34, s: 38, l: 49, bga: 316, bgb: 348, bgas: 28, bgbs: 22, bgal: 91, bgbl: 83, la: 0.23, lb: 0.36, pa: 0.54, pb: 0.71 }, // blush noir
            { h: 338, hs: 9, hsp: 34, s: 38, l: 49, bga: 334, bgb: 8, bgas: 28, bgbs: 22, bgal: 91, bgbl: 83, la: 0.23, lb: 0.36, pa: 0.54, pb: 0.71 }    // merlot rose
        ];

        var enPremiumProfiles = [
            { h: 214, hs: 7, hsp: 24, s: 16, l: 30, bga: 210, bgb: 232, bgas: 12, bgbs: 9, bgal: 15, bgbl: 9, la: 0.29, lb: 0.43, pa: 0.60, pb: 0.79 },    // gunmetal silver
            { h: 206, hs: 7, hsp: 22, s: 14, l: 29, bga: 202, bgb: 226, bgas: 11, bgbs: 8, bgal: 15, bgbl: 9, la: 0.28, lb: 0.42, pa: 0.59, pb: 0.78 },    // titanium slate
            { h: 44, hs: 8, hsp: 28, s: 24, l: 30, bga: 38, bgb: 70, bgas: 18, bgbs: 13, bgal: 15, bgbl: 8, la: 0.30, lb: 0.45, pa: 0.62, pb: 0.81 },      // antique gold
            { h: 36, hs: 8, hsp: 26, s: 22, l: 29, bga: 30, bgb: 62, bgas: 17, bgbs: 12, bgal: 15, bgbl: 8, la: 0.29, lb: 0.44, pa: 0.61, pb: 0.80 },      // dark champagne
            { h: 206, hs: 10, hsp: 48, s: 36, l: 30, bga: 202, bgb: 236, bgas: 26, bgbs: 20, bgal: 14, bgbl: 8, la: 0.32, lb: 0.48, pa: 0.66, pb: 0.84 }, // midnight azure
            { h: 194, hs: 10, hsp: 44, s: 34, l: 29, bga: 190, bgb: 224, bgas: 24, bgbs: 18, bgal: 14, bgbl: 8, la: 0.32, lb: 0.48, pa: 0.65, pb: 0.83 }, // deep lagoon
            { h: 176, hs: 9, hsp: 40, s: 32, l: 28, bga: 172, bgb: 208, bgas: 22, bgbs: 16, bgal: 13, bgbl: 8, la: 0.31, lb: 0.47, pa: 0.64, pb: 0.82 },  // bottle teal
            { h: 160, hs: 9, hsp: 38, s: 30, l: 27, bga: 156, bgb: 194, bgas: 20, bgbs: 15, bgal: 13, bgbl: 8, la: 0.31, lb: 0.46, pa: 0.63, pb: 0.81 },  // emerald dusk
            { h: 142, hs: 9, hsp: 36, s: 30, l: 26, bga: 138, bgb: 176, bgas: 20, bgbs: 15, bgal: 13, bgbl: 8, la: 0.30, lb: 0.46, pa: 0.63, pb: 0.81 },  // deep forest
            { h: 126, hs: 8, hsp: 34, s: 28, l: 26, bga: 122, bgb: 162, bgas: 19, bgbs: 14, bgal: 13, bgbl: 8, la: 0.30, lb: 0.45, pa: 0.62, pb: 0.80 },  // moss graphite
            { h: 36, hs: 8, hsp: 32, s: 26, l: 28, bga: 30, bgb: 68, bgas: 19, bgbs: 14, bgal: 13, bgbl: 7, la: 0.29, lb: 0.44, pa: 0.62, pb: 0.80 },    // brass shadow
            { h: 26, hs: 8, hsp: 30, s: 24, l: 27, bga: 20, bgb: 56, bgas: 18, bgbs: 13, bgal: 13, bgbl: 7, la: 0.29, lb: 0.44, pa: 0.61, pb: 0.79 },    // amber graphite
            { h: 14, hs: 7, hsp: 28, s: 24, l: 27, bga: 10, bgb: 42, bgas: 18, bgbs: 13, bgal: 13, bgbl: 7, la: 0.29, lb: 0.43, pa: 0.61, pb: 0.78 },    // copper dusk
            { h: 284, hs: 9, hsp: 36, s: 28, l: 29, bga: 278, bgb: 314, bgas: 20, bgbs: 15, bgal: 14, bgbl: 8, la: 0.30, lb: 0.46, pa: 0.63, pb: 0.81 }, // dark orchid
            { h: 268, hs: 9, hsp: 36, s: 28, l: 29, bga: 262, bgb: 296, bgas: 20, bgbs: 15, bgal: 14, bgbl: 8, la: 0.30, lb: 0.46, pa: 0.63, pb: 0.81 }, // royal plum
            { h: 252, hs: 9, hsp: 34, s: 26, l: 28, bga: 246, bgb: 282, bgas: 19, bgbs: 14, bgal: 13, bgbl: 8, la: 0.30, lb: 0.45, pa: 0.62, pb: 0.80 }, // indigo noir
            { h: 222, hs: 8, hsp: 30, s: 20, l: 27, bga: 218, bgb: 248, bgas: 15, bgbs: 12, bgal: 13, bgbl: 7, la: 0.28, lb: 0.43, pa: 0.60, pb: 0.78 }, // steel navy
            { h: 212, hs: 8, hsp: 28, s: 18, l: 27, bga: 208, bgb: 236, bgas: 14, bgbs: 10, bgal: 13, bgbl: 7, la: 0.28, lb: 0.42, pa: 0.59, pb: 0.77 }, // obsidian chrome
            { h: 198, hs: 8, hsp: 28, s: 18, l: 28, bga: 194, bgb: 222, bgas: 14, bgbs: 10, bgal: 13, bgbl: 7, la: 0.28, lb: 0.42, pa: 0.59, pb: 0.77 }, // slate ice
            { h: 108, hs: 8, hsp: 30, s: 22, l: 27, bga: 104, bgb: 138, bgas: 16, bgbs: 12, bgal: 13, bgbl: 7, la: 0.29, lb: 0.43, pa: 0.60, pb: 0.78 }, // olive black
            { h: 94, hs: 8, hsp: 30, s: 22, l: 27, bga: 90, bgb: 126, bgas: 16, bgbs: 12, bgal: 13, bgbl: 7, la: 0.29, lb: 0.43, pa: 0.60, pb: 0.78 },   // sage storm
            { h: 80, hs: 8, hsp: 30, s: 20, l: 27, bga: 76, bgb: 112, bgas: 15, bgbs: 11, bgal: 13, bgbl: 7, la: 0.29, lb: 0.42, pa: 0.59, pb: 0.77 },   // olive smoke
            { h: 332, hs: 8, hsp: 30, s: 24, l: 28, bga: 326, bgb: 356, bgas: 17, bgbs: 12, bgal: 13, bgbl: 7, la: 0.29, lb: 0.43, pa: 0.60, pb: 0.78 }, // wine velvet
            { h: 346, hs: 8, hsp: 30, s: 24, l: 28, bga: 340, bgb: 12, bgas: 17, bgbs: 12, bgal: 13, bgbl: 7, la: 0.29, lb: 0.43, pa: 0.60, pb: 0.78 }   // garnet shadow
        ];

        var ruPremiumPresets = buildPremiumPresets(ruPremiumProfiles, 'light');
        var enPremiumPresets = buildPremiumPresets(enPremiumProfiles, 'dark');

        var lightWarmPreset = {
            hueBase: [34, 58], hueSpan: [26, 56], hueShift: [6, 16],
            satBase: [34, 58], satSpan: [8, 16], lightBase: [36, 58], lightSpan: [18, 30],
            lineAlpha: [0.22, 0.40], pointAlpha: [0.54, 0.74],
            bgHueA: [28, 54], bgHueB: [66, 94], bgSatA: [22, 42], bgSatB: [16, 30],
            bgLightA: [84, 95], bgLightB: [74, 90], bgAlphaA: [0.24, 0.40], bgAlphaB: [0.04, 0.12]
        };
        var uiToneDark = !!(document.body && document.body.classList.contains('ui-tone-dark'));
        var preset = null;
        if (uiToneDark) {
            var darkPresetSet = enDarkPresets.concat(enPremiumPresets);
            preset = darkPresetSet[Math.floor(rand(0, darkPresetSet.length))];
        } else {
            var lightPresetPool = ruPresets.concat(ruPremiumPresets, [lightWarmPreset]);
            var lightPresetBuckets = buildLightPresetBuckets(lightPresetPool);
            var lightBucket = lightPresetBuckets[Math.floor(rand(0, lightPresetBuckets.length))];
            preset = lightBucket[Math.floor(rand(0, lightBucket.length))];
        }
        var pick = function (r) { return rand(r[0], r[1]); };

        var hueBase = pick(preset.hueBase);
        var hueSpan = pick(preset.hueSpan);
        var hueShift = pick(preset.hueShift);
        var satBase = pick(preset.satBase);
        var satSpan = pick(preset.satSpan);
        var lightBase = pick(preset.lightBase);
        var lightSpan = pick(preset.lightSpan);
        var lineAlpha = pick(preset.lineAlpha);
        var pointAlpha = pick(preset.pointAlpha);

        var bgHueA = pick(preset.bgHueA);
        var bgHueB = pick(preset.bgHueB);
        var bgSatA = pick(preset.bgSatA);
        var bgSatB = pick(preset.bgSatB);
        var bgLightA = pick(preset.bgLightA);
        var bgLightB = pick(preset.bgLightB);
        var bgAlphaA = pick(preset.bgAlphaA);
        var bgAlphaB = pick(preset.bgAlphaB);

        // Per-render premium micro-randomization so palettes are genuinely unique,
        // while still staying inside smooth, non-aggressive color ranges.
        var driftHue = rand(-12, 12);
        var driftSat = rand(-7, 7);
        var driftLight = rand(-6, 6);
        var driftBgHue = rand(-14, 14);
        var driftBgSat = rand(-6, 6);
        var driftBgLight = rand(-5, 5);
        var driftAlpha = rand(-0.03, 0.03);
        var tonalBias = rand(-1.5, 1.5);

        hueBase = wrapHue(hueBase + driftHue);
        hueSpan = clamp(hueSpan + rand(-4, 4), 16, 96);
        hueShift = clamp(hueShift + rand(-2, 2), 4, 28);
        satBase = clamp(satBase + driftSat + tonalBias, 10, 84);
        satSpan = clamp(satSpan + rand(-2, 2), 2, 24);
        lightBase = clamp(lightBase + driftLight, 14, 72);
        lightSpan = clamp(lightSpan + rand(-3, 3), 10, 40);
        lineAlpha = clamp(lineAlpha + driftAlpha * 0.8, 0.20, 0.54);
        pointAlpha = clamp(pointAlpha + driftAlpha, 0.50, 0.90);

        bgHueA = wrapHue(bgHueA + driftBgHue);
        bgHueB = wrapHue(bgHueB + driftBgHue * 0.8 + rand(-4, 4));
        bgSatA = clamp(bgSatA + driftBgSat, 8, 64);
        bgSatB = clamp(bgSatB + driftBgSat * 0.9, 6, 56);
        bgLightA = clamp(bgLightA + driftBgLight, 6, 96);
        bgLightB = clamp(bgLightB + driftBgLight * 0.8, 4, 94);
        bgAlphaA = clamp(bgAlphaA + driftAlpha * 0.7, 0.16, 0.46);
        bgAlphaB = clamp(bgAlphaB + driftAlpha * 0.5, 0.02, 0.16);

        return {
            bgTop: 'hsla(' + bgHueA.toFixed(1) + ',' + bgSatA.toFixed(1) + '%,' + bgLightA.toFixed(1) + '%,' + bgAlphaA.toFixed(3) + ')',
            bgBottom: 'hsla(' + bgHueB.toFixed(1) + ',' + bgSatB.toFixed(1) + '%,' + bgLightB.toFixed(1) + '%,' + bgAlphaB.toFixed(3) + ')',
            hueBase: hueBase,
            hueSpan: hueSpan,
            hueShift: hueShift,
            satBase: satBase,
            satSpan: satSpan,
            lightBase: lightBase,
            lightSpan: lightSpan,
            lineAlpha: lineAlpha,
            pointAlpha: pointAlpha
        };
    }

    function smooth(t) {
        return t * t * (3 - 2 * t);
    }

    function hash2(ix, iy) {
        var n = Math.sin(ix * 127.1 + iy * 311.7) * 43758.5453;
        return n - Math.floor(n);
    }

    function valueNoise(x, y) {
        var x0 = Math.floor(x);
        var y0 = Math.floor(y);
        var x1 = x0 + 1;
        var y1 = y0 + 1;
        var tx = smooth(x - x0);
        var ty = smooth(y - y0);

        var n00 = hash2(x0, y0);
        var n10 = hash2(x1, y0);
        var n01 = hash2(x0, y1);
        var n11 = hash2(x1, y1);

        var nx0 = lerp(n00, n10, tx);
        var nx1 = lerp(n01, n11, tx);
        return lerp(nx0, nx1, ty);
    }

    function fbm(x, y, octaves) {
        var v = 0;
        var amp = 0.5;
        var freq = 1;
        var gain = 0.5;
        var lac = 2.05;
        for (var i = 0; i < octaves; i++) {
            v += valueNoise(x * freq, y * freq) * amp;
            freq *= lac;
            amp *= gain;
        }
        return v;
    }

    function project(x, y, z) {
        var scale = fov / (z + mapProfile.perspectiveBias);
        return {
            x: w * 0.5 + x * scale,
            y: h * mapProfile.horizon + y * scale + z * mapProfile.depthScale,
            s: scale
        };
    }

    function macroShape(point) {
        var widthNorm = ((cols - 1) * xStep) * 0.55;
        var xNorm = widthNorm > 0 ? (point.x / widthNorm) : 0;
        var zNorm = (point.z - nearZ) / Math.max(1, (farZ - nearZ));
        var m = xNorm * mapProfile.macroSlopeX + (zNorm - 0.5) * mapProfile.macroSlopeZ;

        for (var i = 0; i < mapProfile.macroPeaks.length; i++) {
            var pk = mapProfile.macroPeaks[i];
            var dx = xNorm - pk.cx;
            var dz = zNorm - pk.cz;
            var dist2 = dx * dx + dz * dz;
            var rad2 = pk.radius * pk.radius;
            m += Math.exp(-dist2 / Math.max(0.0001, rad2)) * pk.height;
        }
        return m;
    }

    function terrainHeight(point, t) {
        var nx = point.x * mapProfile.noiseScaleX + t * 0.045 + mapProfile.flowX;
        var nz = point.z * mapProfile.noiseScaleZ - t * 0.032 + mapProfile.flowZ;

        var low = fbm(nx * 0.7, nz * 0.7, 3);
        var mid = fbm(nx * 1.5 + 6.0, nz * 1.5 - 4.0, 4);
        var ridgedSrc = fbm(nx * 2.2 - 8.0, nz * 2.2 + 5.0, 4);
        var ridge = 1 - Math.abs(ridgedSrc * 2 - 1);

        var mountains = Math.pow(clamp(ridge, 0, 1), mapProfile.ridgePower) * mapProfile.mountainAmp;
        var valleys = (mid - 0.52) * mapProfile.valleyAmp;
        var undulate = (low - 0.5) * mapProfile.undulateAmp;
        var macro = macroShape(point);

        var y = mountains + undulate - valleys + macro;

        if (pointerFlowActive) {
            var pr = project(point.x, y, point.z);
            var dx = pr.x - pointer.smoothX;
            var dy = pr.y - pointer.smoothY;
            var dist = Math.sqrt(dx * dx + dy * dy);
            var radius = 260;
            if (dist < radius) {
                var k = 1 - dist / radius;
                var flow = Math.cos(dist * 0.045 - t * 2.9 + point.seed * 7.2) * (8.5 * k);
                var sink = 9.5 * k * k;
                var drift = (pointer.vx * 0.017 + pointer.vy * 0.012) * k;
                y += flow - sink + drift;
            }
        }

        return y;
    }

    function buildTerrain() {
        terrain = [];
        var startX = -((cols - 1) * xStep) * 0.5;
        for (var r = 0; r < rows; r++) {
            var row = [];
            var z = nearZ + r * zStep;
            var shift = (r % 2 === 0) ? 0 : xStep * 0.5;
            for (var c = 0; c < cols; c++) {
                var x = startX + c * xStep + shift;
                row.push({
                    x: x,
                    z: z,
                    seed: hash2(c * 1.37 + 11.3, r * 1.91 + 7.7),
                    sx: 0,
                    sy: 0,
                    s: 0,
                    h: 0,
                    visible: false,
                    color: 'hsla(200,70%,50%,0.5)'
                });
            }
            terrain.push(row);
        }
    }

    function resize() {
        w = window.innerWidth || document.documentElement.clientWidth || 1280;
        h = window.innerHeight || document.documentElement.clientHeight || 720;
        var rawDpr = Math.max(1, window.devicePixelRatio || 1);
        var dprCap = 2;
        var viewportLoad = w * h * rawDpr * rawDpr;
        if (viewportLoad > 14000000) {
            dprCap = 1.2;
        } else if (viewportLoad > 10000000) {
            dprCap = 1.35;
        } else if (viewportLoad > 7000000) {
            dprCap = 1.5;
        } else if (viewportLoad > 4500000) {
            dprCap = 1.7;
        }
        dpr = Math.max(1, Math.min(dprCap, rawDpr));

        canvas.width = Math.floor(w * dpr);
        canvas.height = Math.floor(h * dpr);
        canvas.style.width = w + 'px';
        canvas.style.height = h + 'px';
        ctx.setTransform(dpr, 0, 0, dpr, 0, 0);

        var pixelLoad = w * h * dpr * dpr;
        var gridLoadFactor = 1;
        if (pixelLoad > 12000000) {
            gridLoadFactor = 1.34;
        } else if (pixelLoad > 8000000) {
            gridLoadFactor = 1.24;
        } else if (pixelLoad > 4500000) {
            gridLoadFactor = 1.12;
        }

        xStep = clamp(Math.floor((w / 27) * gridLoadFactor), 42, 92);
        zStep = clamp(Math.floor((h / 13) * gridLoadFactor), 34, 82);
        cols = Math.ceil(w / xStep) + 7;
        rows = Math.ceil((farZ - nearZ) / zStep) + 4;

        buildTerrain();
        pinsBuilt = false;
    }

    function pointColor(p, depthFade, t) {
        var hn = clamp((p.h + 58) / 116, 0, 1);
        var hue = palette.hueBase + hn * palette.hueSpan + Math.sin(t * 0.8 + p.seed * 10.0) * palette.hueShift;
        var sat = palette.satBase + hn * palette.satSpan;
        var light = palette.lightBase + hn * palette.lightSpan;
        var alpha = clamp(depthFade * (0.28 + p.s * 2.1), 0.08, 0.78);
        return {
            fill: 'hsla(' + hue.toFixed(1) + ',' + sat.toFixed(1) + '%,' + light.toFixed(1) + '%,' + alpha.toFixed(3) + ')',
            point: 'hsla(' + hue.toFixed(1) + ',' + (sat + 8).toFixed(1) + '%,' + Math.min(88, light + 16).toFixed(1) + '%,' + clamp(alpha * 1.35, 0.10, palette.pointAlpha).toFixed(3) + ')'
        };
    }

    function prepareFrame(t) {
        for (var r = 0; r < rows; r++) {
            var row = terrain[r];
            for (var c = 0; c < cols; c++) {
                var p = row[c];
                p.h = terrainHeight(p, t);
                var pr = project(p.x, p.h, p.z);
                p.sx = pr.x;
                p.sy = pr.y;
                p.s = pr.s;
                p.visible = pr.y > -40 && pr.y < h + 60;

                var depth = (p.z - nearZ) / (farZ - nearZ);
                var depthFade = clamp(1 - depth * 1.08, 0, 1);
                p.color = pointColor(p, depthFade, t);
                p.depthFade = depthFade;
            }
        }
    }

    function drawTriangle(a, b, c) {
        if (!a.visible && !b.visible && !c.visible) {
            return;
        }
        var fade = (a.depthFade + b.depthFade + c.depthFade) / 3;
        if (fade < 0.03) {
            return;
        }

        var grad = ctx.createLinearGradient(a.sx, a.sy, c.sx, c.sy);
        grad.addColorStop(0, a.color.fill);
        grad.addColorStop(0.5, b.color.fill);
        grad.addColorStop(1, c.color.fill);

        ctx.beginPath();
        ctx.moveTo(a.sx, a.sy);
        ctx.lineTo(b.sx, b.sy);
        ctx.lineTo(c.sx, c.sy);
        ctx.closePath();
        ctx.fillStyle = grad;
        ctx.fill();
    }

    function drawLink(a, b) {
        if (!a.visible && !b.visible) {
            return;
        }
        var fade = (a.depthFade + b.depthFade) * 0.5;
        if (fade < 0.05) {
            return;
        }
        var grad = ctx.createLinearGradient(a.sx, a.sy, b.sx, b.sy);
        grad.addColorStop(0, a.color.point);
        grad.addColorStop(1, b.color.point);
        ctx.strokeStyle = grad;
        ctx.lineWidth = clamp((a.s + b.s) * 0.9, 0.45, 1.75);
        ctx.globalAlpha = clamp(fade * palette.lineAlpha, 0.05, 0.42);
        ctx.beginPath();
        ctx.moveTo(a.sx, a.sy);
        ctx.lineTo(b.sx, b.sy);
        ctx.stroke();
        ctx.globalAlpha = 1;
    }

    function drawPoints() {
        var pointStride = targetFrameMs >= 30 ? 3 : (targetFrameMs >= 22 ? 2 : 1);
        for (var r = rows - 1; r >= 0; r -= pointStride) {
            var row = terrain[r];
            for (var c = 0; c < cols; c += pointStride) {
                var p = row[c];
                if (!p.visible || p.depthFade < 0.05) {
                    continue;
                }
                var rad = clamp(p.s * 1.7, 0.5, 2.3);
                ctx.fillStyle = p.color.point;
                ctx.beginPath();
                ctx.arc(p.sx, p.sy, rad, 0, Math.PI * 2);
                ctx.fill();
            }
        }
    }

    function drawActivePolygonHighlight(t) {
        if (!activePinId || !pinNodes || pinNodes.length === 0) {
            return;
        }

        var pin = null;
        for (var i = 0; i < pinNodes.length; i++) {
            if (pinNodes[i].id === activePinId && pinNodes[i].active && !pinNodes[i].synthetic) {
                pin = pinNodes[i];
                break;
            }
        }
        if (!pin) {
            return;
        }

        var r = pin.row;
        var c = pin.col;
        if (r < 0 || c < 0 || r >= rows - 1 || c >= cols - 1) {
            return;
        }

        var row = terrain[r];
        var rowNext = terrain[r + 1];
        if (!row || !rowNext) return;

        var a = row[c];
        var b = row[c + 1];
        var d = rowNext[c];
        var e = rowNext[c + 1];
        if (!a || !b || !d || !e) return;
        if (!a.visible && !b.visible && !d.visible && !e.visible) return;

        var pulse = 0.5 + 0.5 * Math.sin(t * 7.2);
        var hue = (palette.hueBase + 180) % 360;
        var hueEdge = (hue + 26) % 360;
        var fillA = 0.14 + pulse * 0.16;
        var fillB = 0.08 + pulse * 0.12;
        var strokeA = 0.40 + pulse * 0.34;

        ctx.save();
        ctx.globalCompositeOperation = 'screen';

        // contrast fill for the exact polygon cell where the flag anchor lives
        ctx.beginPath();
        ctx.moveTo(a.sx, a.sy);
        ctx.lineTo(b.sx, b.sy);
        ctx.lineTo(e.sx, e.sy);
        ctx.lineTo(d.sx, d.sy);
        ctx.closePath();
        var g = ctx.createLinearGradient(a.sx, a.sy, e.sx, e.sy);
        g.addColorStop(0, 'hsla(' + hue.toFixed(1) + ',95%,68%,' + fillA.toFixed(3) + ')');
        g.addColorStop(1, 'hsla(' + hueEdge.toFixed(1) + ',98%,62%,' + fillB.toFixed(3) + ')');
        ctx.fillStyle = g;
        ctx.fill();

        ctx.strokeStyle = 'hsla(' + hueEdge.toFixed(1) + ',100%,78%,' + strokeA.toFixed(3) + ')';
        ctx.lineWidth = 1.35 + pulse * 0.95;
        ctx.beginPath();
        ctx.moveTo(a.sx, a.sy);
        ctx.lineTo(b.sx, b.sy);
        ctx.lineTo(e.sx, e.sy);
        ctx.lineTo(d.sx, d.sy);
        ctx.closePath();
        ctx.stroke();

        // anchor beacon
        var beaconR = 3.2 + pulse * 2.0;
        ctx.beginPath();
        ctx.arc(a.sx, a.sy, beaconR, 0, Math.PI * 2);
        ctx.fillStyle = 'hsla(' + hueEdge.toFixed(1) + ',100%,82%,' + (0.42 + pulse * 0.36).toFixed(3) + ')';
        ctx.fill();

        ctx.restore();
    }

    function draw(frameTs) {
        if (!animationRunning) {
            return;
        }
        var ts = (typeof frameTs === 'number') ? frameTs : performance.now();
        if (lastFrameTs > 0 && (ts - lastFrameTs) < targetFrameMs) {
            animationFrameId = window.requestAnimationFrame(draw);
            return;
        }
        lastFrameTs = ts;
        var frameStart = performance.now();
        time += 0.0105;
        pointer.smoothX += (pointer.x - pointer.smoothX) * 0.12;
        pointer.smoothY += (pointer.y - pointer.smoothY) * 0.12;
        pointer.vx *= 0.9;
        pointer.vy *= 0.9;
        pinNowTs = Date.now();
        pointerFlowActive = pointer.active && pointer.lastMoveTs > 0 && (pinNowTs - pointer.lastMoveTs) <= pointerFlowIdleMs;
        if (!pointerFlowActive && pointer.active) {
            pointer.active = false;
        }
        var recentScroll = (pinNowTs - lastScrollTs) <= 320;
        if (recentScroll && targetFrameMs > 16.7) {
            targetFrameMs = Math.max(16.7, targetFrameMs - 2.6);
        }
        canvasParallax.currentY += (canvasParallax.targetY - canvasParallax.currentY) * 0.08;
        var nextCanvasTransform = 'translate3d(0,' + canvasParallax.currentY.toFixed(2) + 'px,0) scale(1.03)';
        if (nextCanvasTransform !== lastCanvasTransform) {
            canvas.style.transform = nextCanvasTransform;
            lastCanvasTransform = nextCanvasTransform;
        }
        if (pinHud) {
            var nextPinHudTransform = 'translate3d(0,' + canvasParallax.currentY.toFixed(2) + 'px,0)';
            if (nextPinHudTransform !== lastPinHudTransform) {
                pinHud.style.transform = nextPinHudTransform;
                lastPinHudTransform = nextPinHudTransform;
            }
        }

        // Keep canvas transparent: render only terrain geometry over page background.
        ctx.clearRect(0, 0, w, h);

        prepareFrame(time);
        if (isPinsEnabled) {
            if (!pinsBuilt) {
                buildPinsFromMap();
            }
            updatePins();
        }

        var linkStride = targetFrameMs >= 30 ? 3 : (targetFrameMs >= 22 ? 2 : 1);
        for (var r = rows - 2; r >= 0; r--) {
            var row = terrain[r];
            var rowNext = terrain[r + 1];
            for (var c = 0; c < cols - 1; c++) {
                var a = row[c];
                var b = row[c + 1];
                var d = rowNext[c];
                var e = rowNext[c + 1];

                if (r % 2 === 0) {
                    drawTriangle(a, b, d);
                    drawTriangle(b, e, d);
                } else {
                    drawTriangle(a, e, d);
                    drawTriangle(a, b, e);
                }

                if (linkStride === 1 || ((c + r) % linkStride === 0)) {
                    drawLink(a, b);
                    drawLink(a, d);
                    if ((c + r) % (linkStride * 2) === 0) {
                        drawLink(a, e);
                    }
                }
            }
        }

        drawActivePolygonHighlight(time);
        drawPoints();
        var frameCost = performance.now() - frameStart;
        if (frameCost > 23) {
            slowFrameStreak += 1;
            fastFrameStreak = 0;
        } else if (frameCost < 13) {
            fastFrameStreak += 1;
            slowFrameStreak = 0;
        } else {
            slowFrameStreak = Math.max(0, slowFrameStreak - 1);
            fastFrameStreak = Math.max(0, fastFrameStreak - 1);
        }
        if (slowFrameStreak >= 14) {
            targetFrameMs = Math.min(33.3, targetFrameMs + 2.7);
            slowFrameStreak = 0;
        } else if (fastFrameStreak >= 20) {
            targetFrameMs = Math.max(16.7, targetFrameMs - 1.8);
            fastFrameStreak = 0;
        }
        animationFrameId = window.requestAnimationFrame(draw);
    }

    function onPointerMove(ev) {
        var nx = ev.clientX || 0;
        var ny = ev.clientY || 0;
        pointer.vx = (nx - pointer.x) * 0.55;
        pointer.vy = (ny - pointer.y) * 0.55;
        pointer.x = nx;
        pointer.y = ny;
        pointer.active = true;
        pointer.lastMoveTs = Date.now();
    }

    function onPointerLeave() {
        pointer.active = false;
        pointer.lastMoveTs = 0;
        pointer.vx *= 0.4;
        pointer.vy *= 0.4;
    }

    function startAnimation() {
        if (animationRunning) {
            return;
        }
        animationRunning = true;
        lastFrameTs = 0;
        animationFrameId = window.requestAnimationFrame(draw);
    }

    function stopAnimation() {
        animationRunning = false;
        if (animationFrameId) {
            window.cancelAnimationFrame(animationFrameId);
            animationFrameId = 0;
        }
        lastFrameTs = 0;
    }

    function onVisibilityChange() {
        if (document.hidden) {
            stopAnimation();
        } else {
            pointer.lastMoveTs = Date.now();
            startAnimation();
        }
    }

    function syncPinThemeClass() {
        if (!pinHud) return;
        pinHud.classList.toggle('theme-enterprise', isEnterprise);
        pinHud.classList.toggle('theme-simple', !isEnterprise);
    }

    function applyPageBackgroundFromPalette() {
        if (!palette) {
            return;
        }
        var root = document.documentElement;
        var bodyEl = document.body;
        if (!root || !bodyEl) {
            return;
        }
        var uiToneDark = bodyEl.classList.contains('ui-tone-dark');
        var bgSolid;
        var bgGrad;
        if (uiToneDark) {
            // Hard requirement: dark theme must stay black on both RU and EN.
            bgSolid = '#05070a';
            bgGrad = 'linear-gradient(180deg,#11151b 0%,#05070a 100%)';
        } else {
            // Light theme should match RU light look on both domains.
            bgSolid = '#e7e5e0';
            bgGrad = 'linear-gradient(180deg,#efefef 0%,#e7e5e0 100%)';
        }
        root.style.setProperty('--terrain-page-bg', bgSolid);
        root.style.setProperty('--terrain-page-bg-grad', bgGrad);
        // Hard-apply as well to cover browsers/themes that ignore CSS vars in cached styles.
        root.style.backgroundColor = bgSolid;
        bodyEl.style.backgroundColor = bgSolid;
        bodyEl.style.backgroundImage = bgGrad;
        bodyEl.style.backgroundRepeat = 'no-repeat';
        bodyEl.style.backgroundAttachment = 'scroll';
    }

    function applyThemeMode(nextTheme, animate) {
        var normalized = String(nextTheme || '').toLowerCase() === 'enterprise' ? 'enterprise' : 'simple';
        var doAnimate = !!animate;
        var performRebuild = function () {
            theme = normalized;
            isEnterprise = (theme === 'enterprise');
            if (document.body) {
                document.body.setAttribute('data-terrain-theme', theme);
            }
            mapProfile = makeMapProfile();
            palette = makePalette();
            applyPageBackgroundFromPalette();
            resize();
            if (isPinsEnabled) {
                syncPinThemeClass();
                applyOppositePinPalette();
            }
        };

        if (!doAnimate) {
            performRebuild();
            return;
        }

        canvas.style.transition = 'opacity .26s ease, transform .35s ease';
        canvas.style.opacity = '0.08';
        setTimeout(function () {
            performRebuild();
            canvas.style.opacity = '1';
        }, 170);
    }

    window.__terrainApplyTheme = function (nextTheme) {
        applyThemeMode(nextTheme, true);
    };

    mapProfile = makeMapProfile();
    palette = makePalette();
    applyPageBackgroundFromPalette();
    var didPendingThemeRebuild = false;
    if (window.__terrainPendingTheme) {
        applyThemeMode(window.__terrainPendingTheme, false);
        window.__terrainPendingTheme = '';
        didPendingThemeRebuild = true;
    }
    if (!didPendingThemeRebuild) {
        resize();
    }
    onScroll();
    if (isPinsEnabled) {
        ensurePinRoot();
        collectPinFeed();
        syncPinThemeClass();
        applyOppositePinPalette();
    }
    pointer.x = w * 0.5;
    pointer.y = h * 0.5;
    pointer.smoothX = pointer.x;
    pointer.smoothY = pointer.y;
    pointer.lastMoveTs = Date.now();
    startAnimation();

    window.addEventListener('resize', resize);
    window.addEventListener('scroll', onScroll, { passive: true });
    window.addEventListener('mousemove', onPointerMove, { passive: true });
    window.addEventListener('mouseleave', onPointerLeave, { passive: true });
    document.addEventListener('visibilitychange', onVisibilityChange, { passive: true });
})();
</script>
